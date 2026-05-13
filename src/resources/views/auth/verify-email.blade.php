@extends('layouts.auth')

@section('title', 'メール認証')

@section('content')

<div class="auth-container">

    <div class="auth-card">

        <p class="auth-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        @if (session('status') == 'verification-link-sent')
        <p class="success-message">
            認証メールを再送しました。
        </p>
        @endif

        {{-- Mailtrap確認 --}}
        <div class="form-group">

            <a
                href="https://mailtrap.io/inboxes"
                target="_blank"
                class="sub-button">
                認証はこちらから
            </a>

        </div>

        {{-- 認証メール再送 --}}
        <form
            method="POST"
            action="{{ route('verification.send') }}"
            class="auth-form">
            @csrf

            <div class="auth-link">
                <button
                    type="submit"
                    class="auth-text-button">
                    認証メールを再送する
                </button>
            </div>

        </form>

    </div>

</div>

@endsection