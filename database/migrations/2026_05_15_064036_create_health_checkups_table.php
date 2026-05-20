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
        Schema::create('health_checkups', function (Blueprint $table) {
            $table->id();
            $table->string('hn')->nullable()->comment('เลข HN');
            $table->string('full_name')->nullable()->comment('ชื่อ-นามสกุล');
            $table->integer('age')->nullable()->comment('อายุ');
            $table->string('gender', 10)->nullable()->comment('เพศ');
            $table->text('congenital_disease')->nullable()->comment('โรคประจำตัว');
            $table->string('position')->nullable()->comment('ตำแหน่ง');
            $table->string('department')->nullable()->comment('สังกัด/แผนก');
            $table->string('bmi')->nullable()->comment('BMI');
            $table->string('waistline')->nullable()->comment('รอบเอว (CM)');
            $table->string('bp_status')->nullable()->comment('ภาวะความดันโลหิต');
            $table->string('hct')->nullable()->comment('HCT ความเข้มข้นเม็ดเลือดแดง');
            $table->string('hb')->nullable()->comment('Hb ค่าเม็ดเลือดแดง');
            $table->string('sugar')->nullable()->comment('ระดับน้ำตาลในเลือด');
            $table->string('cholesterol')->nullable()->comment('ระดับไขมันคอเลสเตอรอล');
            $table->string('triglyceride')->nullable()->comment('ระดับไขมันไตรกลีเซอไรด์');
            $table->string('urine_sugar')->nullable()->comment('น้ำตาลในปัสสาวะ');
            $table->string('xray')->nullable()->comment('เอ็กซเรย์ปอด');
            $table->string('eye_exam')->nullable()->comment('ตรวจสายตา');
            $table->string('status')->nullable()->comment('สถานะการตรวจ');
            $table->date('checkup_date')->nullable()->comment('วันที่ตรวจ');
            $table->string('work_unit')->nullable()->comment('หน่วยงานที่ตรวจ');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_checkups');
    }
};
