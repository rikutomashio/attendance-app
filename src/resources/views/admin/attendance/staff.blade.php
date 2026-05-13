@extends('layouts.admin')

@section('title', 'スタッフ別勤怠')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')

<div class="attendance-page">

    <h1 class="page-title">
        {{ $user->name }} さんの勤怠（{{ \Carbon\Carbon::parse($month)->format('Y年n月') }}）
    </h1>

    <!-- 月ナビ -->
    <div class="month-nav">

        <!-- 左：前月 -->
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}" class="btn btn-secondary">
            前月
        </a>

        <!-- 中央：月選択 -->
        <form method="GET" action="{{ route('admin.attendance.staff', ['id' => $user->id]) }}" class="month-form">
            <input
                type="month"
                name="month"
                value="{{ $month }}"
                class="input"
                onchange="this.form.submit()">
        </form>

        <!-- 右：翌月 -->
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" class="btn btn-secondary">
            翌月
        </a>

    </div>

    <!-- テーブル -->
    <table class="table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($days as $day)

            @php
            $date = \Carbon\Carbon::parse($day['date']);
            @endphp

            <tr>

                <!-- 日付 -->
                <td>
                    {{ $date->format('m/d') }}（{{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }}）
                </td>

                <!-- 出勤 -->
                <td>
                    @if(!empty($day['start_time']))
                    {{ \Carbon\Carbon::parse($day['start_time'])->format('H:i') }}
                    @endif
                </td>

                <!-- 退勤 -->
                <td>
                    @if(!empty($day['end_time']))
                    {{ \Carbon\Carbon::parse($day['end_time'])->format('H:i') }}
                    @endif
                </td>

                <!-- 休憩 -->
                <td>
                    {{ $day['break_time'] ?? '' }}
                </td>

                <!-- 合計 -->
                <td>
                    {{ $day['work_time'] ?? '' }}
                </td>

                <!-- 詳細 -->
                <td>
                    @if ($day['id'])
                    <a href="{{ route('admin.attendance.show', $day['id']) }}" class="btn btn-secondary">
                        詳細
                    </a>
                    @endif
                </td>

            </tr>

            @endforeach
        </tbody>
    </table>

    <!-- CSV -->
    <div class="csv-area">
        <a href="{{ route('admin.attendance.staff.csv', [
            'id' => $user->id,
            'month' => $month
        ]) }}" class="btn">
            CSV出力
        </a>
    </div>

</div>

@endsection