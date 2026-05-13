<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class CustomLoginRequest extends FortifyLoginRequest
{
    public function rules()
    {
        if ($this->input('login_type') === 'admin') {
            return [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ];
        }

        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }
}
