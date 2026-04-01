@if (!$attendance || !$attendance->clock_in_at)
<form method="POST" action="{{ route('attendance.start') }}">
    @csrf
    <button type="submit">出勤</button>
</form>
@endif

@if ($attendance && $attendance->clock_in_at && !$attendance->clock_out_at)
<form method="POST" action="{{ route('attendance.end') }}">
    @csrf
    <button type="submit">退勤</button>
</form>
@endif

@if ($attendance && $attendance->clock_out_at)
<p>本日の勤務は終了しています</p>
@endif

{{-- 出勤中かつ休憩していない --}}
@if ($attendance && $attendance->clock_in_at && !$attendance->clock_out_at &&
!$attendance->breaks->whereNull('break_end_at')->count())

<form method="POST" action="{{ route('break.start') }}">
    @csrf
    <button type="submit">休憩開始</button>
</form>
@endif

{{-- 休憩中 --}}
@if ($attendance && $attendance->breaks->whereNull('break_end_at')->count())

<form method="POST" action="{{ route('break.end') }}">
    @csrf
    <button type="submit">休憩終了</button>
</form>
@endif