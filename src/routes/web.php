<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminCorrectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --------------------
// 申請一覧（共通・Controller分岐）
// --------------------
Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
    ->middleware(['auth:web,admin'])
    ->name('stamp_correction_request.list');


// --------------------
// 一般ユーザー専用
// --------------------
Route::middleware(['auth', 'verified'])->group(function () {

    // --- 勤怠打刻画面 ---
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    // --- 勤怠打刻 ---
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    // --- 休憩打刻 ---
    Route::post('/break/start', [BreakController::class, 'start'])->name('break.start');
    Route::post('/break/end', [BreakController::class, 'end'])->name('break.end');

    // --- 勤怠一覧 ---
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // --- 勤怠詳細 ---
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
    Route::put('/attendance/update/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
});


// --------------------
// 管理者専用
// --------------------
Route::prefix('admin')->middleware('auth:admin')->group(function () {

    // --- ダッシュボード ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // --- 勤怠一覧 ---
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.list');

    // --- ユーザー別 ---
    Route::get('/attendance/list/{user}', [AdminAttendanceController::class, 'indexByUser'])
        ->name('admin.attendance.list.byUser')
        ->whereNumber('user');

    // --- スタッフ別勤怠 ---
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendance'])
        ->name('admin.attendance.staff')
        ->whereNumber('id');

    Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])
        ->name('admin.attendance.staff.csv')
        ->whereNumber('id');

    // --- 勤怠詳細 ---
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])
        ->name('admin.attendance.show')
        ->whereNumber('id');

    Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update')
        ->whereNumber('id');

    // --- スタッフ一覧 ---
    Route::get('/staff/list', [StaffController::class, 'index'])
        ->name('admin.staff.list');
});


// --------------------
// 管理者専用（申請承認）
// --------------------
Route::middleware('auth:admin')->group(function () {

    Route::get('/stamp_correction_request/approve/{id}', [AdminCorrectionController::class, 'show'])
        ->name('admin.request.approve.show');

    Route::post('/stamp_correction_request/approve/{id}', [AdminCorrectionController::class, 'approve'])
        ->name('admin.request.approve.execute');

    Route::post('/stamp_correction_request/reject/{id}', [AdminCorrectionController::class, 'reject'])
        ->name('admin.request.reject.execute');
});


// --------------------
// ログイン
// --------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'show'])
        ->name('login');
});

Route::get('/admin/login', fn() => view('admin.auth.login'))->name('admin.login');


// --------------------
// ログアウト
// --------------------
Route::post('/logout', function () {

    $guard = Auth::guard('admin')->check() ? 'admin' : 'web';

    Auth::guard($guard)->logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect($guard === 'admin' ? '/admin/login' : '/login');
})->name('logout');
