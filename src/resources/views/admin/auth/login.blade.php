@extends('layouts.auth')

@section('title', '管理者ログイン')

@section('content')

<div class="auth-container">

    <div class="auth-card">

        <h1 class="auth-title">
            管理者ログイン
        </h1>

        <form
            method="POST"
            action="/login"
            class="auth-form">
            @csrf

            <input
                type="hidden"
                name="login_type"
                value="admin">

            <div class="form-group">

                <label class="form-label">
                    メールアドレス
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-input">

                @error('email')
                <p class="error-message">
                    {{ $message }}
                </p>
                @enderror

            </div>

            <div class="form-group">

                <label class="form-label">
                    パスワード
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-input">

                @error('password')
                <p class="error-message">
                    {{ $message }}
                </p>
                @enderror

            </div>

            <button
                type="submit"
                class="auth-button">
                管理者ログインする
            </button>

        </form>

    </div>

</div>

@endsection