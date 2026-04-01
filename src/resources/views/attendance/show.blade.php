@extends('layouts.app')

@section('content')
<div>
    <h2>勤怠詳細</h2>

    <!-- 名前 -->
    <p>名前：{{ auth()->user()->name }}</p>

    <!-- 日付（フォーマット修正） -->
    <p>日付：{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d') }}</p>

    <!-- 承認待ち -->
    @if($attendance->status === 'pending')
    <p style="color:red;">承認待ちのため修正はできません。</p>
    @endif

    <!-- エラー（全体） -->
    @if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- 出勤 -->
        <div>
            <label>出勤</label>
            <input type="time" name="start_time"
                value="{{ old('start_time', optional($attendance->clock_in_at)->format('H:i')) }}"
                @if($attendance->status === 'pending') disabled @endif>

            @error('start_time')
            <div style="color:red;">{{ $message }}</div>
            @enderror
        </div>

        <!-- 退勤 -->
        <div>
            <label>退勤</label>
            <input type="time" name="end_time"
                value="{{ old('end_time', optional($attendance->clock_out_at)->format('H:i')) }}"
                @if($attendance->status === 'pending') disabled @endif>

            @error('end_time')
            <div style="color:red;">{{ $message }}</div>
            @enderror
        </div>

        <!-- 休憩 -->
        <h3>休憩</h3>

        @foreach($attendance->breakTimes as $index => $break)
        <div>
            <input type="time" name="breaks[{{ $index }}][start_time]"
                value="{{ optional($break->start_time)->format('H:i') }}"
                @if($attendance->status === 'pending') disabled @endif>

            〜

            <input type="time" name="breaks[{{ $index }}][end_time]"
                value="{{ optional($break->end_time)->format('H:i') }}"
                @if($attendance->status === 'pending') disabled @endif>

            @error("breaks.$index.start_time")
            <div style="color:red;">{{ $message }}</div>
            @enderror

            @error("breaks.$index.end_time")
            <div style="color:red;">{{ $message }}</div>
            @enderror
        </div>
        @endforeach

        <!-- ★追加用1行 -->
        <div>
            <input type="time" name="breaks[new][start_time]"
                @if($attendance->status === 'pending') disabled @endif>

            〜

            <input type="time" name="breaks[new][end_time]"
                @if($attendance->status === 'pending') disabled @endif>
        </div>

        <!-- 備考（textarea修正済み） -->
        <div>
            <label>備考</label>
            <textarea name="note"
                @if($attendance->status === 'pending') disabled @endif>{{ old('note', $attendance->note) }}</textarea>

            @error('note')
            <div style="color:red;">{{ $message }}</div>
            @enderror
        </div>

        <!-- ボタン -->
        @if($attendance->status !== 'pending')
        <button type="submit">修正</button>
        @endif
    </form>
</div>
@endsection