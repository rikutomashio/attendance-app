<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\StampCorrectionRequestController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within the "web" middleware group.
|
*/

// --- 仮ログインルート ---
Route::get('/login', function () {
    $user = \App\Models\User::first(); // 適当なユーザー
    Auth::login($user);

    return redirect('/stamp_correction_request/list');
})->name('login');

Route::middleware('auth')->group(function () {

    // --- 勤怠打刻 ---
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    // --- 休憩打刻 ---
    Route::post('/break/start', [BreakController::class, 'start'])->name('break.start');
    Route::post('/break/end', [BreakController::class, 'end'])->name('break.end');

    // --- 勤怠一覧 ---
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // --- 勤怠詳細 ---
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])
        ->name('attendance.detail');
    Route::put('/attendance/update/{id}', [AttendanceController::class, 'update'])
        ->name('attendance.update');

    // --- 申請一覧 ---
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('request.list');
});
