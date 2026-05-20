<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('health_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_name')->comment('ชื่อไฟล์ต้นฉบับ');
            $table->string('file_path')->comment('เส้นทางเก็บไฟล์ใน Storage');
            $table->bigInteger('file_size')->comment('ขนาดไฟล์ (Bytes)');
            $table->string('department')->comment('แผนกของข้อมูลสุขภาพ');
            $table->integer('year')->comment('ปี พ.ศ. ของข้อมูลสุขภาพ');
            $table->boolean('is_published')->default(false)->comment('สถานะการเผยแพร่');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade')->comment('แอดมินที่อัปโหลด');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_files');
    }
};
