<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakController extends Controller
{
    public function start()
    {
        $user = Auth::user() ?? \App\Models\User::first();

        $attendance = Attendance::today($user->id);

        if (!$attendance || !$attendance->clock_in_at || $attendance->clock_out_at) {
            return back()->with('error', '勤務中ではありません');
        }

        // すでに休憩中かチェック
        $activeBreak = $attendance->breaks()
            ->whereNull('break_end_at')
            ->first();

        if ($activeBreak) {
            return back()->with('error', 'すでに休憩中です');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => now(),
        ]);

        return back()->with('message', '休憩開始しました');
    }

    public function end()
    {
        $user = Auth::user() ?? \App\Models\User::first();

        $attendance = Attendance::today($user->id);

        if (!$attendance || $attendance->clock_out_at) {
            return back()->with('error', '勤務中ではありません');
        }

        $activeBreak = $attendance->breaks()
            ->whereNull('break_end_at')
            ->first();

        if (!$activeBreak) {
            return back()->with('error', '休憩中ではありません');
        }

        $activeBreak->update([
            'break_end_at' => now(),
        ]);

        return back()->with('message', '休憩終了しました');
    }
}
