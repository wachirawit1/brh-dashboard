<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthFile extends Model
{
    protected $fillable = [
        'original_name',
        'file_path',
        'file_size',
        'department',
        'year',
        'is_published',
        'uploaded_by',
    ];

    /**
     * ดึงข้อมูลผู้ใช้อัปโหลดไฟล์นี้
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
