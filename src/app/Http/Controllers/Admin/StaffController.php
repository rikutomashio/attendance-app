<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        // 一般ユーザーのみ（ここ重要）
        $staffs = User::where('role', 'user')
            ->select('id', 'name', 'email')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.staff.list', compact('staffs'));
    }
}
