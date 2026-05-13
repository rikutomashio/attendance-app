@extends('layouts.auth')

@section('title', 'ログイン')

@section('content')

<div class="auth-container">

    <div class="auth-card">

        <h1 class="auth-title">ログイン</h1>

        <form
            method="POST"
            action="{{ route('login') }}"
            class="auth-form">
            @csrf

            <div class="form-group">
                <label class="form-label">
                    メールアドレス
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-input"
                    autofocus>

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

            @error('login')
            <p class="error-message">
                {{ $message }}
            </p>
            @enderror

            <button
                type="submit"
                class="auth-button">
                ログインする
            </button>

        </form>

        <div class="auth-link">
            <a href="{{ route('register') }}">
                会員登録はこちら
            </a>
        </div>

    </div>

</div>

@endsection