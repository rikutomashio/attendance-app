@extends('layouts.admin')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')

@php
$carbonDate = \Carbon\Carbon::parse($date);
@endphp

<div class="attendance-page">

    <!-- タイトル -->
    <h1 class="page-title">
        {{ $carbonDate->format('Y年n月j日') }}の勤怠
    </h1>

    <!-- 日付ナビ -->
    <div class="month-nav">

        <!-- 前日 -->
        <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="btn btn-secondary">
            前日
        </a>

        <!-- 日付選択 -->
        <form method="GET" action="{{ route('admin.attendance.list') }}" class="month-form">

            <input
                type="date"
                name="date"
                value="{{ $date }}"
                class="input"
                onchange="this.form.submit()">

        </form>

        <!-- 翌日 -->
        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="btn btn-secondary">
            翌日
        </a>

    </div>

    <!-- テーブル -->
    <table class="table">

        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($users as $user)

            @php
            $attendance = $user->attendances->first();
            @endphp

            <tr>

                <td>{{ $user->name }}</td>

                <td>
                    {{ $attendance->clock_in_formatted ?? '' }}
                </td>

                <td>
                    {{ $attendance->clock_out_formatted ?? '' }}
                </td>

                <td>
                    {{ $attendance->total_break_formatted ?? '' }}
                </td>

                <td>
                    @if($attendance && $attendance->clock_in_at && $attendance->clock_out_at)
                    {{ $attendance->work_formatted }}
                    @endif
                </td>

                <td>
                    @if ($attendance)
                    <a href="{{ url('/admin/attendance/' . $attendance->id) }}" class="btn btn-secondary">
                        詳細
                    </a>
                    @endif
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection