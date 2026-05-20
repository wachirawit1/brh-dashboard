<?php

namespace App\Imports;

use App\Models\HealthCheckup;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class HealthCheckupDataImport implements ToModel, WithStartRow
{
    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // ข้ามหัวตารางแถวที่ 1
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // ป้องกัน Error Undefined index โดยใช้ ?? null
        $hn = $row[2] ?? null;
        $name = $row[1] ?? null;

        // ข้ามแถวที่ไม่มีข้อมูลสำคัญ เช่น HN หรือ ชื่อ
        if (empty($hn) || empty($name)) {
            return null;
        }

        // ข้ามแถวที่เป็นสูตรคำนวณ (กรณีหลุดมา)
        if (str_starts_with($row[3] ?? '', '=')) {
            return null;
        }

        return new HealthCheckup([
            'hn'                 => $hn,
            'full_name'          => $name,
            'age'                => $this->sanitize($row[3] ?? null),
            'gender'             => $this->sanitize($row[4] ?? null),
            'congenital_disease' => $this->sanitize($row[5] ?? null),
            'position'           => $this->sanitize($row[6] ?? null),
            'bmi'                => $this->sanitize($row[7] ?? null),
            'waistline'          => $this->sanitize($row[8] ?? null),
            'bp_status'          => $this->sanitize($row[9] ?? null),
            'hct'                => $this->sanitize($row[10] ?? null),
            'hb'                 => $this->sanitize($row[11] ?? null),
            'sugar'              => $this->sanitize($row[12] ?? null),
            'cholesterol'        => $this->sanitize($row[13] ?? null),
            'triglyceride'       => $this->sanitize($row[14] ?? null),
            'urine_sugar'        => $this->sanitize($row[15] ?? null),
            'xray'               => $this->sanitize($row[16] ?? null),
            'status'             => $this->sanitize($row[17] ?? null),
            'checkup_date'       => $this->transformDate($row[18] ?? null),
            'department'         => $row[19] ?? null,
        ]);
    }

    /**
     * ดักจับค่า 0 หรือ 0.0 ที่ถูก Excel แปลงมาจากขีด (-)
     */
    private function sanitize($value)
    {
        if ($value === 0 || $value === 0.0 || $value === '0' || $value === '0.00' || $value === '0.0') {
            return '-';
        }
        return $value;
    }
    /**
     * แปลงวันที่จาก Excel ให้เป็น Y-m-d สำหรับ DB
     */
    private function transformDate($value)
    {
        if (empty($value)) {
            return null;
        }
        
        // ถ้าเป็นตัวเลข (Excel timestamp)
        if (is_numeric($value)) {
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
        }

        // ถ้าเป็นสตริง เช่น "04/03/2569"
        try {
            // แยกส่วน วว/ดด/ปปปป
            $parts = explode('/', $value);
            if (count($parts) === 3) {
                $day = $parts[0];
                $month = $parts[1];
                $year = $parts[2] - 543; // แปลง พ.ศ. เป็น ค.ศ.
                return "{$year}-{$month}-{$day}";
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}
