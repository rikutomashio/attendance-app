<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakController extends Controller
{
    public function start()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        if (!$attendance || !$attendance->clock_in_at || $attendance->clock_out_at) {
            return back()->with('error', '勤務中ではありません');
        }

        $activeBreak = $attendance->breakTimes()
            ->whereNull('break_end_at')
            ->first();

        if ($activeBreak) {
            return back()->with('error', 'すでに休憩中です');
        }

        $attendance->breakTimes()->create([
            'break_start_at' => now(),
        ]);

        return back()->with('message', '休憩開始しました');
    }

    public function end()
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        if (!$attendance || $attendance->clock_out_at) {
            return back()->with('error', '勤務中ではありません');
        }

        $activeBreak = $attendance->breakTimes()
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
