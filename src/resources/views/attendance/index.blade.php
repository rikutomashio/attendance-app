@extends('layouts.app')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-register.css') }}">
@endsection

@section('content')

<div class="register-container">

    {{-- ステータス --}}
    <div class="status-text">
        @if($status === 'off')
        勤務外
        @elseif($status === 'working')
        出勤中
        @elseif($status === 'break')
        休憩中
        @elseif($status === 'done')
        退勤済
        @endif
    </div>

    {{-- 現在時刻 --}}
    <div class="time-box">
        <div class="date">
            {{ now()->isoFormat('YYYY年MM月DD日（ddd）') }}
        </div>
        <div class="time">
            {{ now()->format('H:i') }}
        </div>
    </div>

    {{-- メッセージ --}}
    @if(session('message'))
    <div class="message success">
        {{ session('message') }}
    </div>
    @endif

    @if(session('error'))
    <div class="message error">
        {{ session('error') }}
    </div>
    @endif

    {{-- 操作 --}}
    <div class="action-area">

        @if($status === 'off')
        <form method="POST" action="{{ route('attendance.start') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-large">
                出勤
            </button>
        </form>
        @endif

        @if($status === 'working')
        <form method="POST" action="{{ route('attendance.end') }}">
            @csrf
            {{-- ★ 退勤も黒に統一 --}}
            <button type="submit" class="btn btn-primary btn-large">
                退勤
            </button>
        </form>

        <form method="POST" action="{{ route('break.start') }}">
            @csrf
            {{-- ★ 白ボタンに変更 --}}
            <button type="submit" class="btn btn-outline btn-large">
                休憩入
            </button>
        </form>
        @endif

        @if($status === 'break')
        <form method="POST" action="{{ route('break.end') }}">
            @csrf
            {{-- ★ 白ボタンに変更 --}}
            <button type="submit" class="btn btn-outline btn-large">
                休憩戻
            </button>
        </form>
        @endif

    </div>

</div>

@endsection