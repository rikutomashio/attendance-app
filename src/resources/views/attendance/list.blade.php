@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')

<div class="attendance-page">

    <h1 class="page-title">勤怠一覧</h1>

    <!-- 月切替 -->
    <div class="month-nav">

        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="btn btn-secondary">
            前月
        </a>

        <form method="GET" action="{{ route('attendance.list') }}" class="month-form">
            <input
                type="month"
                name="month"
                value="{{ $month }}"
                class="input"
                onchange="this.form.submit()">
        </form>

        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="btn btn-secondary">
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
            @foreach ($attendanceData as $data)
            @php
            $date = \Carbon\Carbon::parse($data['date']);
            @endphp
            <tr>
                <td>{{ $date->format('m/d') }}（{{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }}）</td>

                <td>
                    {{ $data['start_time']
                        ? \Carbon\Carbon::parse($data['start_time'])->format('H:i')
                        : '' }}
                </td>

                <td>
                    {{ $data['end_time']
                        ? \Carbon\Carbon::parse($data['end_time'])->format('H:i')
                        : '' }}
                </td>

                <td>
                    @if(!empty($data['break_minutes']))
                    {{ floor($data['break_minutes'] / 60) }}:{{ str_pad($data['break_minutes'] % 60, 2, '0', STR_PAD_LEFT) }}
                    @endif
                </td>

                <td>
                    @if(!empty($data['work_minutes']))
                    {{ floor($data['work_minutes'] / 60) }}:{{ str_pad($data['work_minutes'] % 60, 2, '0', STR_PAD_LEFT) }}
                    @endif
                </td>

                <td>
                    @if(!empty($data['id']))
                    <a href="{{ route('attendance.detail', $data['id']) }}" class="btn btn-secondary">
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