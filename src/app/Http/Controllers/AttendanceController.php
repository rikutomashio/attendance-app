<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use App\Http\Requests\AttendanceUpdateRequest;


class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $attendance = Attendance::today($user->id);

        return view('attendance.index', compact('attendance'));
    }

    public function start()
    {
        $user = Auth::user();
        $today = today();

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $today,
            ]
        );

        if ($attendance->clock_in_at) {
            return back()->with('error', '既に出勤済みです');
        }

        $attendance->update([
            'clock_in_at' => now(),
        ]);

        return back()->with('message', '出勤しました');
    }

    public function end()
    {
        $user = Auth::user();

        $attendance = Attendance::today($user->id);

        if (!$attendance || !$attendance->clock_in_at) {
            return back()->with('error', '出勤打刻がありません');
        }

        if ($attendance->clock_out_at) {
            return back()->with('error', '既に退勤済みです');
        }

        $attendance->update([
            'clock_out_at' => now(),
        ]);

        return back()->with('message', '退勤しました');
    }

    /**
     * 勤怠一覧（一般ユーザー）
     */
    public function list(Request $request)
    {
        $user = Auth::user();

        // --- 1. 月の指定 ---
        $month = $request->query('month', Carbon::now()->format('Y-m'));

        // 月の開始・終了日
        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth();

        // 前月・翌月
        $prevMonth = Carbon::parse($month . '-01')->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($month . '-01')->addMonth()->format('Y-m');

        // --- 2. 勤怠取得 ---
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->orderBy('work_date', 'asc')
            ->get();

        // --- 3. 日ごとの計算 ---
        $attendanceData = $attendances->map(function ($attendance) {
            $breakTotalMinutes = $attendance->breakTimes->sum(function ($break) {
                return Carbon::parse($break->end_time)
                    ->diffInMinutes(Carbon::parse($break->start_time));
            });

            $workMinutes = 0;
            if ($attendance->clock_in_at && $attendance->clock_out_at) {
                $totalMinutes = Carbon::parse($attendance->clock_out_at)
                    ->diffInMinutes(Carbon::parse($attendance->clock_in_at));
                $workMinutes = $totalMinutes - $breakTotalMinutes;
            }

            return [
                'id' => $attendance->id, // ← 追加
                'date' => $attendance->work_date,
                'start_time' => $attendance->clock_in_at,
                'end_time' => $attendance->clock_out_at,
                'break_minutes' => $breakTotalMinutes,
                'work_minutes' => $workMinutes,
            ];
        });

        // --- 4. 月の合計 ---
        $totalBreakMinutes = $attendanceData->sum('break_minutes');
        $totalWorkMinutes = $attendanceData->sum('work_minutes');

        // Blade に渡す
        return view('attendance.list', [
            'user' => $user,
            'month' => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'attendanceData' => $attendanceData,
            'totalBreakMinutes' => $totalBreakMinutes,
            'totalWorkMinutes' => $totalWorkMinutes,
        ]);
    }

    /**
     * 勤怠詳細（一般ユーザー）
     */

    public function show($id)
    {
        $attendance = Attendance::with('breakTimes')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('attendance.show', compact('attendance'));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($attendance->status === 'pending') {
            return back()->with('error', '承認待ちのため修正できません');
        }

        $attendance->update([
            'clock_in_at' => Carbon::parse($request->start_time),
            'clock_out_at' => Carbon::parse($request->end_time),
            'note' => $request->note,
            'status' => 'pending',
        ]);

        // ★既存休憩削除
        $attendance->breakTimes()->delete();

        // ★再登録
        foreach ($request->breaks ?? [] as $break) {

            if (empty($break['start_time']) || empty($break['end_time'])) {
                continue;
            }

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::parse($break['start_time']),
                'end_time' => Carbon::parse($break['end_time']),
            ]);
        }

        return redirect()->route('attendance.list')
            ->with('message', '修正申請を送信しました');
    }
}
