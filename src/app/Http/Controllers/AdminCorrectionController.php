<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectRequest;
use Illuminate\Support\Facades\DB;

class AdminCorrectionController extends Controller
{
    // 🔥 追加（これが今回の本体）
    public function getListData()
    {
        $status = request('status', 'pending');

        if (!in_array($status, ['pending', 'approved'])) {
            $status = 'pending';
        }

        $requests = AttendanceCorrectRequest::with(['attendance.user'])
            ->where('status', $status)
            ->latest()
            ->paginate(15);

        return [
            'requests' => $requests,
            'status' => $status,
        ];
    }

    // 🔥 indexは「表示だけ」にする
    public function index()
    {
        $data = $this->getListData();

        return view('stamp_correction_request.admin_list', $data);
    }

    public function show($id)
    {
        $correctionRequest = AttendanceCorrectRequest::with([
            'attendance.user',
            'breakTimes'
        ])->findOrFail($id);

        return view('admin.correction_approve', compact('correctionRequest'));
    }

    public function approve($id)
    {
        $correctionRequest = AttendanceCorrectRequest::with([
            'attendance.breakTimes',
            'breakTimes'
        ])->findOrFail($id);

        $attendance = $correctionRequest->attendance;

        DB::transaction(function () use ($attendance, $correctionRequest) {

            // 出勤・退勤更新
            $attendance->update([
                'clock_in_at'  => $correctionRequest->requested_clock_in_at,
                'clock_out_at' => $correctionRequest->requested_clock_out_at,
            ]);

            // 🔥 既存休憩を削除
            $attendance->breakTimes()->delete();

            // 🔥 承認された休憩を再登録
            foreach ($correctionRequest->breakTimes as $break) {

                // 空データ防止
                if (
                    !$break->requested_break_start_at ||
                    !$break->requested_break_end_at
                ) {
                    continue;
                }

                $attendance->breakTimes()->create([
                    'break_start_at' => $break->requested_break_start_at,
                    'break_end_at' => $break->requested_break_end_at,
                ]);
            }

            // 承認状態更新
            $correctionRequest->update([
                'status' => 'approved',
                'approved_by' => auth('admin')->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('stamp_correction_request.list')
            ->with('success', '承認しました');
    }

    public function reject($id)
    {
        try {
            DB::transaction(function () use ($id) {

                $correctionRequest = AttendanceCorrectRequest::lockForUpdate()->findOrFail($id);

                if ($correctionRequest->status !== 'pending') {
                    throw new \Exception('既に処理済みです');
                }

                $correctionRequest->update([
                    'status' => 'rejected',
                    'approved_by' => auth('admin')->id(),
                    'approved_at' => now(),
                ]);
            });

            return redirect()->route('stamp_correction_request.list')
                ->with('success', '却下しました');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
