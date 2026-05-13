<?php

namespace App\Actions\Fortify;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAttemptToAuthenticate
{
    public function __invoke(Request $request, $next)
    {

        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        auth()->guard('admin')->login($admin);

        return $next($request); // ← これでセッション処理に進む
    }
}
