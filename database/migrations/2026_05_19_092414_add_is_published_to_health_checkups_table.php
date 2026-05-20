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
        Schema::table('health_checkups', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('status')->comment('สถานะการเผยแพร่ให้ผู้ใช้เห็น (true=เห็น, false=ซ่อน)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_checkups', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }
};
