<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {

            // 管理者ルートなら管理者ログインへ
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            // それ以外は一般ログイン
            return route('login');
        }
    }
}
