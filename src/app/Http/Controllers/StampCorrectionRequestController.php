<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrectRequest;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        // 🔥 管理者
        if (auth('admin')->check()) {

            $data = app(\App\Http\Controllers\AdminCorrectionController::class)->getListData();

            return view('stamp_correction_request.admin_list', $data);
        }

        // ----------------------------
        // 一般ユーザー
        // ----------------------------

        $status = $request->query('status', 'pending');

        if (!in_array($status, ['pending', 'approved'])) {
            $status = 'pending';
        }

        $requests = AttendanceCorrectRequest::with(['attendance', 'user'])
            ->where('user_id', Auth::id())
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('stamp_correction_request.user_list', compact('requests', 'status'));
    }
}
