<?php

use App\Http\Controllers\Admin\AdminAttendanceListController;
use App\Http\Controllers\Admin\AdminStaffListController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\StampCorrectionRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');

    Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceListController::class, 'detail'])->name('attendance.detail');
    Route::put('/attendance/{id}', [AttendanceListController::class, 'update'])->name('attendance.update');

    // 修正申請関連のルート
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])->name('stamp_correction_request.list');
    Route::post('/stamp_correction_request', [StampCorrectionRequestController::class, 'store'])->name('stamp_correction_request.store');
});

Route::middleware(['guest'])->group(function () {
    // 一般ユーザーログインフォーム表示
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    // 一般ユーザーログイン処理
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceListController::class, 'index'])->name('admin.attendance.list');
    Route::get('/admin/attendance/detail', [AdminAttendanceListController::class, 'detail'])->name('admin.attendance.detail');
    Route::put('/admin/attendance/{id}', [AdminAttendanceListController::class, 'update'])->name('admin.attendance.update');
    Route::get('/admin/staff/list', [AdminStaffListController::class, 'index'])->name('admin.staff.list');
    Route::get('/admin/attendance/staff/{id}', [AdminStaffListController::class, 'detail'])->name('admin.attendance.staff');
});

Route::prefix('admin')->name('admin.')->group(function () {
    // 管理者ログインフォーム表示
    Route::get('login', [AdminLoginController::class, 'showLoginForm'])->middleware('guest:admin')->name('login');
    // 管理者ログイン処理
    Route::post('login', [AdminLoginController::class, 'login'])->middleware('guest:admin')->name('login.submit');
    // 管理者ログアウト
    Route::post('logout', [AdminLoginController::class, 'logout'])->middleware('auth:admin')->name('logout');
});
