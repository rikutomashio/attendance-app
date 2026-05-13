<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $prevDate = $date->copy()->subDay()->format('Y-m-d');
        $nextDate = $date->copy()->addDay()->format('Y-m-d');

        $users = User::with(['attendances' => function ($query) use ($date) {
            $query->whereDate('work_date', $date)
                ->with('breakTimes');
        }])->get();

        return view('admin.attendance.list', [
            'users' => $users,
            'date' => $date->format('Y-m-d'),
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes', 'correctionRequests'])
            ->findOrFail($id);

        return view('admin.attendance.show', compact('attendance'));
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with(['breakTimes', 'correctionRequests'])
            ->findOrFail($id);

        if ($attendance->correctionRequests()->where('status', 'pending')->exists()) {
            return back()->with('error', '承認待ちのため修正はできません。');
        }

        $data = $request->validated();

        $attendance->update([
            'clock_in_at' => $data['clock_in'],
            'clock_out_at' => $data['clock_out'],
            'reason' => $data['reason'] ?? null,
        ]);

        $attendance->breakTimes()->delete();

        if (!empty($data['breaks']) && is_array($data['breaks'])) {
            foreach ($data['breaks'] as $break) {

                if (empty($break['start_time']) || empty($break['end_time'])) {
                    continue;
                }

                $attendance->breakTimes()->create([
                    'break_start_at' => Carbon::createFromFormat('H:i', $break['start_time']),
                    'break_end_at' => Carbon::createFromFormat('H:i', $break['end_time']),
                ]);
            }
        }

        return redirect()
            ->route('admin.attendance.show', $attendance->id)
            ->with('success', '修正しました');
    }

    public function indexByUser(Request $request, $userId)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $prevDate = $date->copy()->subDay()->format('Y-m-d');
        $nextDate = $date->copy()->addDay()->format('Y-m-d');

        $user = User::with(['attendances' => function ($query) use ($date) {
            $query->whereDate('work_date', $date)
                ->with('breakTimes');
        }])->findOrFail($userId);

        return view('admin.attendance.list', [
            'users' => collect([$user]),
            'date' => $date->format('Y-m-d'),
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
        ]);
    }

    /**
     * 🔥 PG11：スタッフ別勤怠（月次）
     */
    public function staffAttendance(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->input('month', now()->format('Y-m'));

        $current = Carbon::parse($month);
        $start = $current->copy()->startOfMonth();
        $end   = $current->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->with('breakTimes') // ←最初から読み込む
            ->orderBy('work_date')
            ->get();

        $attendanceMap = $attendances->keyBy(fn($a) => $a->work_date->format('Y-m-d'));

        $days = [];

        for ($i = 1; $i <= $current->daysInMonth; $i++) {

            $dateCarbon = $current->copy()->day($i);
            $date = $dateCarbon->format('Y-m-d');

            $attendance = $attendanceMap[$date] ?? null;

            if ($attendance) {

                // 休憩
                $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                    if (!$break->break_end_at) return 0;

                    return Carbon::parse($break->break_end_at)
                        ->diffInMinutes(Carbon::parse($break->break_start_at));
                });

                // 勤務
                $workMinutes = 0;
                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $total = Carbon::parse($attendance->clock_out_at)
                        ->diffInMinutes(Carbon::parse($attendance->clock_in_at));

                    $workMinutes = $total - $breakMinutes;
                }

                $days[] = [
                    'id' => $attendance->id,
                    'date' => $date,
                    'start_time' => $attendance->clock_in_at
                        ? Carbon::parse($attendance->clock_in_at)->format('H:i')
                        : '',
                    'end_time' => $attendance->clock_out_at
                        ? Carbon::parse($attendance->clock_out_at)->format('H:i')
                        : '',
                    'break_time' => $breakMinutes
                        ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT)
                        : '',
                    'work_time' => $workMinutes
                        ? floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT)
                        : '',
                ];
            } else {

                $days[] = [
                    'id' => null,
                    'date' => $date,
                    'start_time' => '',
                    'end_time' => '',
                    'break_time' => '',
                    'work_time' => '',
                ];
            }
        }

        return view('admin.attendance.staff', [
            'user' => $user,
            'days' => $days,
            'month' => $month,
            'prevMonth' => $current->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $current->copy()->addMonth()->format('Y-m'),
        ]);
    }

    /**
     * 🔥 CSV出力（修正版）
     */
    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->input('month', now()->format('Y-m'));

        $base = Carbon::parse($month);

        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->with('breakTimes')
            ->get();

        $attendanceMap = $attendances->keyBy(fn($a) => $a->work_date->format('Y-m-d'));

        $days = [];

        for ($i = 1; $i <= $base->daysInMonth; $i++) {

            $dateCarbon = $base->copy()->day($i);
            $date = $dateCarbon->format('Y-m-d');

            $attendance = $attendanceMap[$date] ?? null;

            if ($attendance) {

                $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                    if (!$break->break_end_at) return 0;

                    return Carbon::parse($break->break_end_at)
                        ->diffInMinutes(Carbon::parse($break->break_start_at));
                });

                $workMinutes = 0;
                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $total = Carbon::parse($attendance->clock_out_at)
                        ->diffInMinutes(Carbon::parse($attendance->clock_in_at));

                    $workMinutes = $total - $breakMinutes;
                }

                $days[] = [
                    'date' => $date,
                    'start_time' => $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '',
                    'end_time' => $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : '',
                    'break_time' => $breakMinutes
                        ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT)
                        : '',
                    'work_time' => $workMinutes
                        ? floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT)
                        : '',
                ];
            } else {
                $days[] = [
                    'date' => $date,
                    'start_time' => '',
                    'end_time' => '',
                    'break_time' => '',
                    'work_time' => '',
                ];
            }
        }

        $response = new StreamedResponse(function () use ($days) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($days as $d) {
                fputcsv($handle, [
                    $d['date'],
                    $d['start_time'],
                    $d['end_time'],
                    $d['break_time'],
                    $d['work_time'],
                ]);
            }

            fclose($handle);
        });

        $filename = "attendance_{$id}_{$month}.csv";

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename={$filename}");

        return $response;
    }
}
