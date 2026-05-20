<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthCheckup extends Model
{
    protected $fillable = [
        'hn',
        'full_name',
        'age',
        'gender',
        'congenital_disease',
        'position',
        'department',
        'bmi',
        'waistline',
        'bp_status',
        'hct',
        'hb',
        'sugar',
        'cholesterol',
        'triglyceride',
        'urine_sugar',
        'xray',
        'eye_exam',
        'status',
        'checkup_date',
        'work_unit',
        'is_published',
    ];
}
