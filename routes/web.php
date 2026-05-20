<?php

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminUserController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/files/{id}/download', [DashboardController::class, 'download'])->name('dashboard.download');
    Route::get('/dashboard/files/{id}/preview', [DashboardController::class, 'preview'])->name('dashboard.preview');

    // จัดการผู้ใช้งาน (ทั้ง Admin และ Manager ใช้ร่วมกันผ่าน Middleware นี้)
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::patch('/admin/users/{user}', [AdminUserController::class, 'updateRole'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        
        // นำเข้าข้อมูลผลตรวจสุขภาพและจัดการไฟล์สุขภาพ
        Route::post('/admin/health-files/upload', [DashboardController::class, 'upload'])->name('admin.health-files.upload');
        Route::post('/admin/health-files/{id}/publish', [DashboardController::class, 'publish'])->name('admin.health-files.publish');
        Route::delete('/admin/health-files/{id}', [DashboardController::class, 'destroy'])->name('admin.health-files.destroy');
    });
});
