<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $attendance = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        $status = $this->getStatus($attendance);

        return view('attendance.index', compact('attendance', 'status'));
    }

    /**
     * 🔥 状態判定（PG03のコア）
     */
    private function getStatus($attendance)
    {
        if (!$attendance || !$attendance->clock_in_at) {
            return 'off'; // 勤務外
        }

        if ($attendance->clock_out_at) {
            return 'done'; // 退勤済
        }

        $latestBreak = $attendance->breakTimes->last();

        if ($latestBreak && !$latestBreak->break_end_at) {
            return 'break'; // 休憩中
        }

        return 'working'; // 出勤中
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

        return back()->with('message', 'お疲れ様でした。');
    }

    /**
     * 勤怠一覧（一般ユーザー）
     */
    public function list(Request $request)
    {
        $user = Auth::user();

        $month = $request->query('month', Carbon::now()->format('Y-m'));

        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth();

        $prevMonth = Carbon::parse($month . '-01')->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($month . '-01')->addMonth()->format('Y-m');

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->orderBy('work_date', 'asc')
            ->get();

        // ★ここから置き換え

        // 日付キーで管理
        $attendancesByDate = $attendances->keyBy(function ($attendance) {
            return $attendance->work_date->format('Y-m-d');
        });

        // 全日生成
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendanceData = collect();

        foreach ($period as $date) {

            $key = $date->format('Y-m-d');

            if (isset($attendancesByDate[$key])) {

                $attendance = $attendancesByDate[$key];

                // 休憩合計
                $breakTotalMinutes = $attendance->breakTimes->sum(function ($break) {
                    if (!$break->break_end_at) return 0;

                    return Carbon::parse($break->break_end_at)
                        ->diffInMinutes(Carbon::parse($break->break_start_at));
                });

                // 勤務時間
                $workMinutes = 0;
                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $totalMinutes = Carbon::parse($attendance->clock_out_at)
                        ->diffInMinutes(Carbon::parse($attendance->clock_in_at));

                    $workMinutes = $totalMinutes - $breakTotalMinutes;
                }

                $attendanceData->push([
                    'id' => $attendance->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => $attendance->clock_in_at,
                    'end_time' => $attendance->clock_out_at,
                    'break_minutes' => $breakTotalMinutes,
                    'work_minutes' => $workMinutes,
                ]);
            } else {

                // 空データ
                $attendanceData->push([
                    'id' => null,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => null,
                    'end_time' => null,
                    'break_minutes' => null,
                    'work_minutes' => null,
                ]);
            }
        }


        return view('attendance.list', [
            'user' => $user,
            'month' => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'attendanceData' => $attendanceData,
        ]);
    }

    /**
     * 勤怠詳細
     */
    public function show($id)
    {
        $attendance = Attendance::with('breakTimes')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $latestRequest = $attendance->correctionRequests()
            ->with('breakTimes')
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('attendance.show', [
            'attendance' => $attendance,
            'latestRequest' => $latestRequest,
            'hasPendingRequest' => (bool)$latestRequest,
        ]);
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $hasPendingRequest = $attendance->correctionRequests()
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingRequest) {
            return back()->with('error', '承認待ちのため修正できません');
        }

        $userId = Auth::id();

        DB::transaction(function () use ($request, $attendance, $userId) {

            $correctRequest = AttendanceCorrectRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => $userId,
                // 🔥 ここ追加（今回の本質）
                'before_clock_in_at' => $attendance->clock_in_at,
                'before_clock_out_at' => $attendance->clock_out_at,
                'requested_clock_in_at' => Carbon::parse($request->start_time),
                'requested_clock_out_at' => Carbon::parse($request->end_time),
                'reason' => $request->note,
                'status' => 'pending',
            ]);

            foreach ($request->breaks as $index => $newBreak) {

                if (empty($newBreak['start_time']) || empty($newBreak['end_time'])) {
                    continue;
                }

                // 元休憩（存在する場合のみ）
                $originalBreak = $attendance->breakTimes[$index] ?? null;

                $correctRequest->breakTimes()->create([

                    // before
                    'before_break_start_at' => $originalBreak?->break_start_at,
                    'before_break_end_at' => $originalBreak?->break_end_at,

                    // after
                    'requested_break_start_at' => Carbon::parse($newBreak['start_time']),
                    'requested_break_end_at' => Carbon::parse($newBreak['end_time']),
                ]);
            }
        });

        return redirect()->route('attendance.list')
            ->with('message', '修正申請を送信しました');
    }
}
