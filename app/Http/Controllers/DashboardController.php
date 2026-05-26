<?php

namespace App\Http\Controllers;

use App\Models\HealthFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Html;

class DashboardController extends Controller
{
    /**
     * หน้าหลักแสดงรายการไฟล์เอกสารแยกตามแผนกและปีงบประมาณ
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = HealthFile::query();

        // 1. ดึงรายการ ปี พ.ศ. และ แผนก ทั้งหมดที่มีในระบบเพื่อไปแสดงใน Dropdown ฟิลเตอร์
        if ($user->role === 'admin') {
            $years = HealthFile::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
            $departments = HealthFile::select('department')->distinct()->orderBy('department', 'asc')->pluck('department');

            // กรองข้อมูลตามที่เลือก
            if ($request->filled('department')) {
                $query->where('department', $request->department);
            }
            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }
        } else {
            // ถ้าเป็นผู้ใช้ทั่วไป เห็นเฉพาะแผนกตัวเองและไฟล์ที่เผยแพร่ (Publish) แล้วเท่านั้น
            $years = HealthFile::where('department', $user->dept_name)
                ->where('is_published', true)
                ->select('year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');

            $departments = collect([$user->dept_name]);

            $query->where('department', $user->dept_name)
                ->where('is_published', true);

            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }
        }

        // 2. ดึงรายการไฟล์พร้อมชื่อผู้ใช้ที่อัปโหลด
        $files = $query->with('uploader')->orderBy('year', 'desc')->orderBy('created_at', 'desc')->paginate(15);

        return view('dashboard', compact('files', 'departments', 'years'));
    }

    /**
     * อัปโหลดไฟล์ Excel ของผลตรวจสุขภาพ
     */
    public function upload(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'คุณไม่มีสิทธิ์อัปโหลดไฟล์');
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $uploadedFile = $request->file('file');

            // 1. สแกนอ่านข้อมูลเบื้องต้นเพื่อดึงชื่อแผนกและปีการตรวจสุขภาพในไฟล์ Excel
            // ใช้ ReadFilter จำกัดการอ่านข้อมูลเฉพาะ 200 แถวแรก เพื่อป้องกันปัญหาหน่วยความจำเต็ม
            // กรณีไฟล์ที่มีสูตรคำนวณ (เช่น =C2*100/C$2) ถูก copy ลงมาจนถึงแถวสุดท้ายของ Excel
            // ตรวจสอบประเภทไฟล์จากชื่อไฟล์ต้นฉบับ เนื่องจากไฟล์ชั่วคราวของ PHP ไม่มีนามสกุลต่อท้าย
            // ทำให้ IOFactory::createReaderForFile() ไม่สามารถตรวจจับประเภทไฟล์ได้โดยอัตโนมัติ
            $ext = strtolower($uploadedFile->getClientOriginalExtension());
            $readerType = $ext === 'xls' ? 'Xls' : 'Xlsx';
            $scanReader = IOFactory::createReader($readerType);
            $scanReader->setReadDataOnly(true);
            $scanFilter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
                public function readCell($columnAddress, $row, $worksheetName = ''): bool
                {
                    return $row <= 200;
                }
            };
            $scanReader->setReadFilter($scanFilter);
            $scanSpreadsheet = $scanReader->load($uploadedFile->getPathname());
            $scanSheet = $scanSpreadsheet->getSheet(0);

            $department = null;
            $year = null;

            // วนลูปหาชื่อแผนกในคอลัมน์ T (index 19) และปีในคอลัมน์ S (index 18)
            $scanHighest = $scanSheet->getHighestDataRow();
            for ($row = 2; $row <= $scanHighest; $row++) {
                $deptVal = $scanSheet->getCell('T' . $row)->getValue();
                $dateVal = $scanSheet->getCell('S' . $row)->getValue();

                if (!empty($deptVal)) {
                    $department = trim($deptVal);
                }
                if (!empty($dateVal)) {
                    if (is_numeric($dateVal)) {
                        $dt = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateVal));
                        $year = $dt->year;
                    } else {
                        $parts = explode('/', $dateVal);
                        if (count($parts) === 3) {
                            $year = intval($parts[2]);
                        }
                    }
                }
                if ($department && $year) {
                    break;
                }
            }
            $scanSpreadsheet->disconnectWorksheets();
            unset($scanSpreadsheet);

            if (!$department) {
                return back()->withErrors(['file' => 'ไม่พบข้อมูลแผนกในไฟล์ Excel (คอลัมน์ที่ 20)']);
            }

            // แปลงปีเป็น พ.ศ. (บวก 543 หากเป็น ค.ศ.)
            $buddhistYear = ($year && $year < 2500) ? $year + 543 : ($year ?? now()->year + 543);

            // 2. ตรวจสอบว่าเคยมีไฟล์ของแผนกนี้และปีงบประมาณนี้อยู่แล้วหรือไม่
            $existing = HealthFile::where('department', $department)
                ->where('year', $buddhistYear)
                ->first();

            // ลบไฟล์เก่าออกถ้าตรวจพบ (Smart Overwrite)
            if ($existing) {
                Storage::disk('public')->delete($existing->file_path);
                $existing->delete();
            }

            // 3. เซฟไฟล์ลง Storage disk 'public'
            $safeDeptName = preg_replace('/[^A-Za-z0-9ก-๙\s\-]/u', '_', $department);
            $fileName = $safeDeptName . '_' . $buddhistYear . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            $filePath = $uploadedFile->storeAs('health_files', $fileName, 'public');

            // 4. บันทึกระเบียนข้อมูลลงตาราง health_files
            HealthFile::create([
                'original_name' => $uploadedFile->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $uploadedFile->getSize(),
                'department' => $department,
                'year' => $buddhistYear,
                'is_published' => false, // เริ่มต้นในสถานะ Draft (แบบร่าง) เพื่อให้แอดมินตรวจเช็คก่อนเผยแพร่
                'uploaded_by' => Auth::id(),
            ]);

            return back()->with('success', "นำเข้าไฟล์ '{$uploadedFile->getClientOriginalName()}' ของแผนก {$department} ประจำปี {$buddhistYear} สำเร็จ (ไฟล์อยู่ในสถานะแบบร่าง)");
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'เกิดข้อผิดพลาดในการนำเข้าไฟล์: ' . $e->getMessage()]);
        }
    }

    /**
     * ดาวน์โหลดไฟล์ Excel สุขภาพ
     */
    public function download($id)
    {
        $user = Auth::user();
        $file = HealthFile::findOrFail($id);

        // ตรวจสอบสิทธิ์การเข้าถึงไฟล์ (ไม่ใช่แอดมิน และ แผนกไม่ตรงกัน หรือ ยังไม่ได้เผยแพร่)
        if ($user->role !== 'admin') {
            if ($file->department !== $user->dept_name || !$file->is_published) {
                abort(403, 'คุณไม่มีสิทธิ์ดาวน์โหลดไฟล์นี้');
            }
        }

        $absolutePath = Storage::disk('public')->path($file->file_path);
        if (!Storage::disk('public')->exists($file->file_path)) {
            return back()->withErrors(['error' => 'ไม่พบไฟล์ตัวจริงในระบบเก็บข้อมูล']);
        }

        // หากเป็นแอดมิน ให้ดาวน์โหลดไฟล์ตัวจริงเต็มรูปแบบ
        if ($user->role === 'admin') {
            return Storage::disk('public')->download($file->file_path, $file->original_name);
        }

        
        try {
            // เพิ่ม memory_limit ชั่วคราวสำหรับการโหลดและแปลงไฟล์ที่อาจมีขนาดใหญ่
            ini_set('memory_limit', '512M');

            // โหลดสเปรดชีตสำหรับผู้ใช้งานทั่วไปด้วย ReadFilter จำกัดไว้ที่ 5000 แถว
            // ป้องกันปัญหาหน่วยความจำเต็มจากสูตรที่ถูก copy ลงมาจนถึงแถวสุดท้ายของ Excel
            $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
            $readerType = $ext === 'xls' ? 'Xls' : 'Xlsx';
            $dlReader = IOFactory::createReader($readerType);
            // โหลดสไตล์ด้วยเพื่อรักษารูปแบบดั้งเดิมของตารางเวลาผู้ใช้ดาวน์โหลด
            $dlReader->setReadDataOnly(false);
            $dlFilter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
                public function readCell($columnAddress, $row, $worksheetName = ''): bool
                {
                    return $row <= 5000;
                }
            };
            $dlReader->setReadFilter($dlFilter);
            $spreadsheet = $dlReader->load($absolutePath);
            $this->sanitizeSpreadsheetForUser($spreadsheet);

            // บังคับลบแถวและคอลัมน์ที่ไม่มีข้อมูลจริง ทิ้งในทุกชีท
            foreach ($spreadsheet->getSheetNames() as $index => $name) {
                $sheet = $spreadsheet->getSheet($index);
                $this->trimEmptyRowsAndColumns($sheet);
            }

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
            }, $file->original_name, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'เกิดข้อผิดพลาดในการดาวน์โหลดไฟล์แบบปลอดภัย: ' . $e->getMessage()]);
        }
    }

    /**
     * พรีวิวไฟล์ Excel แปลงออกเป็น HTML แยกตามชีท เพื่อดูบนเบราว์เซอร์โดยตรง
     */
    public function preview($id)
    {
        $user = Auth::user();
        $file = HealthFile::findOrFail($id);

        // ตรวจสอบสิทธิ์การเข้าถึงพรีวิว (ไม่ใช่แอดมิน และ แผนกไม่ตรงกัน หรือ ยังไม่ได้เผยแพร่)
        if ($user->role !== 'admin') {
            if ($file->department !== $user->dept_name || !$file->is_published) {
                return response()->json(['error' => 'คุณไม่มีสิทธิ์พรีวิวไฟล์นี้'], 403);
            }
        }

        $absolutePath = Storage::disk('public')->path($file->file_path);
        if (!file_exists($absolutePath)) {
            return response()->json(['error' => 'ไม่พบไฟล์ในระบบเก็บข้อมูล'], 404);
        }

        try {
            // เพิ่ม memory_limit ชั่วคราวสำหรับการโหลดและแปลงไฟล์ที่อาจมีขนาดใหญ่
            ini_set('memory_limit', '512M');

            // โหลดสเปรดชีตด้วย ReadFilter จำกัดไว้ที่ 5000 แถวต่อชีท
            // ป้องกันปัญหาหน่วยความจำเต็มจากสูตรที่ถูก copy ลงมาจนถึงแถวสุดท้ายของ Excel
            $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
            $readerType = $ext === 'xls' ? 'Xls' : 'Xlsx';
            $pvReader = IOFactory::createReader($readerType);
            // อนุญาตให้โหลดรูปแบบเซลล์ (Styles) เพื่อการแสดงผลตารางพรีวิวและส่วนการผสานเซลล์ (Merge Cells) ที่สวยงามและถูกต้อง
            $pvReader->setReadDataOnly(false);
            $pvFilter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
                public function readCell($columnAddress, $row, $worksheetName = ''): bool
                {
                    return $row <= 5000;
                }
            };
            $pvReader->setReadFilter($pvFilter);
            $spreadsheet = $pvReader->load($absolutePath);

            // ทำการล้าง/ปิดบังข้อมูลส่วนตัวและ B24 ในหน้าพรีวิวออนไลน์สำหรับทุกคน (รวมถึงแอดมินด้วย)
            $this->sanitizeSpreadsheetForUser($spreadsheet);

            $sheets = [];

            // วนลูปแปลงสเปรดชีตแต่ละชีทให้กลายเป็น HTML
            foreach ($spreadsheet->getSheetNames() as $index => $name) {
                $spreadsheet->setActiveSheetIndex($index);
                $sheet = $spreadsheet->getActiveSheet();

                // ค้นหาขอบเขตข้อมูลจริงและลบแถว/คอลัมน์เปล่าที่ไม่มีข้อมูลทิ้งทั้งหมด
                $this->trimEmptyRowsAndColumns($sheet);

                // ปัดเศษทศนิยมสำหรับข้อมูลตัวเลขและสูตรคำนวณ (เช่น เปอร์เซ็นต์) ให้เหลือเพียง 2 ตำแหน่งสำหรับการพรีวิว
                foreach ($sheet->getCoordinates() as $coordinate) {
                    $cell = $sheet->getCell($coordinate);
                    if ($cell) {
                        $value = $cell->getValue();
                        $calculatedValue = null;

                        if (is_string($value) && strpos($value, '=') === 0) {
                            try {
                                $calculatedValue = $cell->getCalculatedValue();
                            } catch (\Throwable $e) {
                                // ข้ามหากมีสูตรคำนวณผิดพลาด
                            }
                        } else {
                            $calculatedValue = $value;
                        }

                        if (is_numeric($calculatedValue)) {
                            $floatVal = (float)$calculatedValue;
                            if (floor($floatVal) != $floatVal) {
                                $cell->setValue(round($floatVal, 2));
                            }
                        }
                    }
                }

                $writer = new Html($spreadsheet);
                $writer->setGenerateSheetNavigationBlock(false);
                $writer->setSheetIndex($index); // ป้องกันบั๊กการสลับชีท 2 นิ่งสนิท

                // ใช้การตัดฟิลด์ที่ไม่จำเป็นในรหัส HTML และส่งออกมาเฉพาะเนื้อข้อมูลตาราง
                $html = $writer->generateSheetData();

                $sheets[] = [
                    'name' => $name,
                    'html' => $html
                ];
            }

            return response()->json([
                'filename' => $file->original_name,
                'department' => $file->department,
                'year' => $file->year,
                'sheets' => $sheets
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'เกิดข้อผิดพลาดในการโหลดไฟล์ตัวอย่าง: ' . $e->getMessage()], 500);
        }
    }

    /**
     * เปลี่ยนแปลงสถานะการเผยแพร่ไฟล์ (Publish / Unpublish)
     */
    public function publish($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $file = HealthFile::findOrFail($id);
        $file->is_published = !$file->is_published;
        $file->save();

        $statusText = $file->is_published ? 'เผยแพร่ไฟล์ให้ผู้ใช้ดาวน์โหลดเรียบร้อยแล้ว' : 'ยกเลิกการเผยแพร่ไฟล์เรียบร้อยแล้ว';
        return back()->with('success', $statusText);
    }

    /**
     * ลบระเบียนไฟล์พร้อมไฟล์ต้นฉบับใน Storage
     */
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $file = HealthFile::findOrFail($id);

        // ลบไฟล์ตัวจริงใน Storage
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return back()->with('success', 'ลบไฟล์ในระบบออกเรียบร้อยแล้ว');
    }

    /**
     * กรองข้อมูลลับและลบคอลัมน์ที่ไม่ต้องการสำหรับผู้ใช้ทั่วไป
     * 1. ตรวจสอบคอลัมน์โรคประจำตัว (F) หากมี B24 ให้เปลี่ยนเป็น 'ไม่ระบุ'
     * 2. ลบคอลัมน์ C (HN) และคอลัมน์ S (วันที่ตรวจ) ทิ้งอย่างสิ้นเชิง (โดยลบ S ก่อนแล้วค่อยลบ C เพื่อเลี่ยงการขยับผิดหลัก)
     */
    private function sanitizeSpreadsheetForUser($spreadsheet)
    {
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = $sheet->getTitle();

            // ข้ามหน้าสรุป หน้าสถิติ หรือหน้าภาพรวมทั้งหมดที่ไม่มีข้อมูลรายละเอียดของผู้ป่วยรายบุคคล
            if (
                stripos($sheetName, 'สรุป') !== false ||
                stripos($sheetName, 'summary') !== false ||
                stripos($sheetName, 'dashboard') !== false ||
                stripos($sheetName, 'total') !== false
            ) {
                continue;
            }

            // ใช้ getHighestDataRow() แทน getHighestRow() เพื่อข้ามแถวสูตรคำนวณที่ว่างเปล่าซึ่งอาจมีอยู่จนถึงแถวสุดท้าย
            $highestRow = $sheet->getHighestDataRow();

            // 1. ตรวจสอบและแทนที่ B24 ด้วยคำว่า "ไม่ระบุ" ในคอลัมน์ F (โรคประจำตัว)
            for ($row = 2; $row <= $highestRow; $row++) {
                $diseaseCell = $sheet->getCell('F' . $row);
                if ($diseaseCell) {
                    $diseaseValue = $diseaseCell->getValue();
                    $diseaseStr = is_object($diseaseValue) ? $diseaseValue->__toString() : (string)$diseaseValue;
                    if (stripos($diseaseStr, 'B24') !== false) {
                        $sheet->setCellValue('F' . $row, 'ไม่ระบุ ;');
                    }
                }
            }

            // 2. ลบคอลัมน์ C (HN) ออกจากแผ่นชีท เพื่อไม่ให้แสดงตัวคอลัมน์นี้เลย
            $sheet->removeColumn('C');
        }
    }

    /**
     * ค้นหาขอบเขตข้อมูลจริง (แถวและคอลัมน์สูงสุดที่ไม่ได้มีแต่ค่าว่าง/ช่องว่าง)
     * และทำการลบแถวกับคอลัมน์ส่วนเกินออกเพื่อกระชับขนาดตารางให้เบาที่สุด
     */
    private function trimEmptyRowsAndColumns($sheet)
    {
        $maxRow = 0;
        $maxColIndex = 0;

        // วนลูปเฉพาะเซลล์ที่มีการจัดสรรในหน่วยความจำแล้ว (ผ่านการกรองโดย ReadFilter)
        foreach ($sheet->getCoordinates() as $coordinate) {
            $cell = $sheet->getCell($coordinate);
            if ($cell) {
                $val = $cell->getValue();

                // ตรวจสอบสูตรคำนวณและค่าว่าง
                $calculatedValue = null;
                if (is_string($val) && strpos($val, '=') === 0) {
                    try {
                        $calculatedValue = $cell->getCalculatedValue();
                    } catch (\Throwable $e) {
                        $calculatedValue = '';
                    }
                } else {
                    $calculatedValue = $val;
                }

                $strVal = trim((string)$calculatedValue);
                if ($strVal !== '') {
                    list($col, $row) = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($coordinate);
                    $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($col);

                    if ($row > $maxRow) {
                        $maxRow = $row;
                    }
                    if ($colIndex > $maxColIndex) {
                        $maxColIndex = $colIndex;
                    }
                }
            }
        }

        // ค่าเริ่มต้นหากไม่พบข้อมูล
        if ($maxRow === 0) {
            $maxRow = 1;
        }
        if ($maxColIndex === 0) {
            $maxColIndex = 1;
        }

        // 1. ลบแถวที่เกินมา
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > $maxRow) {
            $sheet->removeRow($maxRow + 1, $highestRow - $maxRow);
        }

        // 2. ลบคอลัมน์ที่เกินมา
        $highestCol = $sheet->getHighestColumn();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
        if ($highestColIndex > $maxColIndex) {
            $startColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxColIndex + 1);
            $sheet->removeColumn($startColLetter, $highestColIndex - $maxColIndex);
        }
    }
}
