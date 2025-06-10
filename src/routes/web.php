<?php

use App\Http\Controllers\Admin\AttendanceListController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
});

Route::middleware(['guest:admin'])->group(
    function () {
        // 管理者ログインフォーム表示
        Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');

        // 管理者ログイン処理
        Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
    }
);

// 管理者ログアウト処理
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/attendance/list', [AttendanceListController::class, 'index']);
});
