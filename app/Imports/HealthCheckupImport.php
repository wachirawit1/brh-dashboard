<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HealthCheckupImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new HealthCheckupDataImport(), // อ่านเฉพาะชีทแรก (ดัชนี 0) คือชีทรายงานผล
        ];
    }
}
