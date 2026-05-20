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
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\HealthCheckupDataImport, $uploadedFile);
            $department = null;
            $year = null;
            
            // วนลูปหาชื่อแผนกในคอลัมน์ที่ 20 (index 19) และปีในคอลัมน์ที่ 19 (index 18)
            foreach ($data[0] as $row) {
                if (!empty($row[19])) {
                    $department = trim($row[19]);
                }
                if (!empty($row[18])) {
                    $dateValue = $row[18];
                    if (is_numeric($dateValue)) {
                        $dt = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue));
                        $year = $dt->year;
                    } else {
                        $parts = explode('/', $dateValue);
                        if (count($parts) === 3) {
                            $year = intval($parts[2]);
                        }
                    }
                }
                if ($department && $year) {
                    break;
                }
            }

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
        } catch (\Exception $e) {
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
        if (Storage::disk('public')->exists($file->file_path)) {
            return Storage::disk('public')->download($file->file_path, $file->original_name);
        }

        return back()->withErrors(['error' => 'ไม่พบไฟล์ตัวจริงในระบบเก็บข้อมูล']);
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
            // โหลดสเปรดชีตขึ้นมา
            $spreadsheet = IOFactory::load($absolutePath);
            $sheets = [];

            // วนลูปแปลงสเปรดชีตแต่ละชีทให้กลายเป็น HTML
            foreach ($spreadsheet->getSheetNames() as $index => $name) {
                $spreadsheet->setActiveSheetIndex($index);
                
                $writer = new Html($spreadsheet);
                $writer->setGenerateSheetNavigationBlock(false);
                $writer->setGenerateStyles(true);
                
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
        } catch (\Exception $e) {
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
}
