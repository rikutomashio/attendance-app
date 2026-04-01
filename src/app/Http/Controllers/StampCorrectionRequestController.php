<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;


class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->is_admin) {

            // 👑 管理者：全ユーザー
            $pendingRequests = Attendance::with('user')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            $approvedRequests = Attendance::with('user')
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {

            // 👤 一般ユーザー：自分のみ
            $pendingRequests = Attendance::with('user') // ← ここ追加
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            $approvedRequests = Attendance::with('user') // ← ここ追加
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('stamp_correction_request.list', compact(
            'pendingRequests',
            'approvedRequests'
        ));
    }
}
