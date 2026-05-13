@extends('layouts.admin')

@section('title', '修正申請承認')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')

<div class="detail-container">

    {{-- ヘッダー --}}
    <div class="detail-header">
        <h1>勤怠詳細</h1>
    </div>

    {{-- メッセージ --}}
    @if(session('success'))
    <div class="message success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="message error">
        {{ session('error') }}
    </div>
    @endif

    <div class="detail-body">

        {{-- 名前 --}}
        <div class="form-row">
            <div class="form-label">名前</div>
            <div class="form-value">
                {{ $correctionRequest->attendance->user->name ?? '-' }}
            </div>
        </div>

        {{-- 日付 --}}
        <div class="form-row">
            <div class="form-label">日付</div>
            <div class="form-value inline-group date-group">
                <span>{{ optional($correctionRequest->attendance->work_date)->format('Y年') }}</span>
                <span>{{ optional($correctionRequest->attendance->work_date)->format('n月j日') }}</span>
            </div>
        </div>

        {{-- 出勤・退勤 --}}
        <div class="form-row">
            <div class="form-label">出勤・退勤</div>
            <div class="form-value inline-group">
                <input type="time"
                    value="{{ $correctionRequest->requested_clock_in_at?->format('H:i') }}"
                    disabled>

                <span>〜</span>

                <input type="time"
                    value="{{ $correctionRequest->requested_clock_out_at?->format('H:i') }}"
                    disabled>
            </div>
        </div>

        {{-- 休憩（PG05と完全一致） --}}
        @foreach($correctionRequest->breakTimes as $index => $break)
        <div class="form-row">

            <div class="form-label">
                {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
            </div>

            <div class="form-value">
                <div class="break-row inline-group">
                    <input type="time"
                        value="{{ $break->requested_break_start_at?->format('H:i') }}"
                        disabled>

                    <span>〜</span>

                    <input type="time"
                        value="{{ $break->requested_break_end_at?->format('H:i') }}"
                        disabled>
                </div>
            </div>

        </div>
        @endforeach

        {{-- 備考 --}}
        <div class="form-row">
            <div class="form-label">備考</div>
            <div class="form-value">
                <textarea disabled>{{ $correctionRequest->reason }}</textarea>
            </div>
        </div>

    </div>

    {{-- フッター --}}
    <div class="detail-footer">

        @if($correctionRequest->status === 'pending')

        <div></div>

        <div class="detail-actions">
            <form method="POST" action="{{ route('admin.request.approve.execute', $correctionRequest->id) }}">
                @csrf
                <button type="submit" class="btn-primary btn-large">承認</button>
            </form>
        </div>

        @else

        <div class="pending-message">
            この申請は処理済みです
        </div>

        @endif

    </div>

</div>

@endsection