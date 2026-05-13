@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')

<div class="detail-container">

    {{-- ヘッダー --}}
    <div class="detail-header">
        <h1>勤怠詳細</h1>

        @php
        $isPending = $attendance->correctionRequests()
        ->where('status', 'pending')
        ->exists();
        @endphp
    </div>

    {{-- 成功メッセージ --}}
    @if (session('success'))
    <div class="message success">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
        @csrf
        @method('PUT')

        <div class="detail-body">

            {{-- 名前 --}}
            @php
            $name = $attendance->user->name;
            $len = mb_strlen($name);

            if ($len === 2) {
            $last = mb_substr($name, 0, 1);
            $first = mb_substr($name, 1);
            } elseif ($len === 3) {
            $last = mb_substr($name, 0, 1);
            $first = mb_substr($name, 1);
            } else {
            $last = mb_substr($name, 0, 2);
            $first = mb_substr($name, 2);
            }
            @endphp

            <div class="form-row">
                <div class="form-label">名前</div>

                <div class="form-value">
                    {{ $last }}{{ $first }}
                </div>
            </div>

            {{-- 日付 --}}
            <div class="form-row">
                <div class="form-label">日付</div>

                <div class="form-value inline-group date-group">
                    <span>{{ $attendance->work_date->format('Y年') }}</span>
                    <span>{{ $attendance->work_date->format('n月j日') }}</span>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="form-row">
                <div class="form-label">出勤・退勤</div>

                <div class="form-value inline-group">

                    <input
                        type="time"
                        name="start_time"
                        value="{{ old('start_time', optional($attendance->clock_in_at)->format('H:i')) }}"
                        {{ $isPending ? 'disabled' : '' }}>

                    <span>〜</span>

                    <input
                        type="time"
                        name="end_time"
                        value="{{ old('end_time', optional($attendance->clock_out_at)->format('H:i')) }}"
                        {{ $isPending ? 'disabled' : '' }}>

                </div>
            </div>

            @error('start_time')
            <div class="error">{{ $message }}</div>
            @enderror

            @error('end_time')
            <div class="error">{{ $message }}</div>
            @enderror

            {{-- 休憩 --}}
            @foreach($attendance->breakTimes as $index => $break)

            <div class="form-row">

                <div class="form-label">
                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                </div>

                <div class="form-value">

                    <div class="break-row inline-group">

                        <input
                            type="time"
                            name="breaks[{{ $index }}][start_time]"
                            value="{{ old("breaks.$index.start_time", optional($break->break_start_at)->format('H:i')) }}"
                            {{ $isPending ? 'disabled' : '' }}>

                        <span>〜</span>

                        <input
                            type="time"
                            name="breaks[{{ $index }}][end_time]"
                            value="{{ old("breaks.$index.end_time", optional($break->break_end_at)->format('H:i')) }}"
                            {{ $isPending ? 'disabled' : '' }}>

                    </div>

                    @error("breaks.$index.start_time")
                    <div class="error">{{ $message }}</div>
                    @enderror

                    @error("breaks.$index.end_time")
                    <div class="error">{{ $message }}</div>
                    @enderror

                </div>

            </div>

            @endforeach

            {{-- 追加行 --}}
            @php
            $newIndex = count($attendance->breakTimes);
            @endphp

            <div class="form-row">

                <div class="form-label">
                    {{ $newIndex === 0 ? '休憩' : '休憩' . ($newIndex + 1) }}
                </div>

                <div class="form-value">

                    <div class="break-row inline-group">

                        <input
                            type="time"
                            name="breaks[{{ $newIndex }}][start_time]"
                            value="{{ old("breaks.$newIndex.start_time") }}"
                            {{ $isPending ? 'disabled' : '' }}>

                        <span>〜</span>

                        <input
                            type="time"
                            name="breaks[{{ $newIndex }}][end_time]"
                            value="{{ old("breaks.$newIndex.end_time") }}"
                            {{ $isPending ? 'disabled' : '' }}>

                    </div>

                    @error("breaks.$newIndex.start_time")
                    <div class="error">{{ $message }}</div>
                    @enderror

                    @error("breaks.$newIndex.end_time")
                    <div class="error">{{ $message }}</div>
                    @enderror

                </div>

            </div>

            {{-- 備考 --}}
            <div class="form-row">

                <div class="form-label">
                    備考
                </div>

                <div class="form-value">

                    <textarea
                        name="note"
                        {{ $isPending ? 'disabled' : '' }}>{{ old('note', $attendance->reason) }}</textarea>

                    @error('note')
                    <div class="error">{{ $message }}</div>
                    @enderror

                </div>

            </div>

        </div>

        {{-- フッター --}}
        <div class="detail-footer">

            @if ($isPending)
            <div class="pending-message">
                承認待ちのため修正はできません。
            </div>
            @endif

            @if (!$isPending)
            <div class="detail-actions">
                <button type="submit" class="btn-primary">
                    修正
                </button>
            </div>
            @endif

        </div>

    </form>

</div>

@endsection