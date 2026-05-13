@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')

<div class="auth-container">

    <div class="auth-card">

        <h1 class="auth-title">会員登録</h1>

        <form
            method="POST"
            action="{{ route('register') }}"
            class="auth-form">
            @csrf

            <div class="form-group">
                <label class="form-label">名前</label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-input">

                @error('name')
                <p class="error-message">
                    {{ $message }}
                </p>
                @enderror
            </div>

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

            <div class="form-group">
                <label class="form-label">
                    パスワード確認
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    class="form-input">
            </div>

            <button
                type="submit"
                class="auth-button">
                登録する
            </button>

        </form>

        <div class="auth-link">
            <a href="{{ route('login') }}">
                ログインはこちら
            </a>
        </div>

    </div>

</div>

@endsection