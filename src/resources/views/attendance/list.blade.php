@extends('layouts.app')

@section('title', "勤怠一覧（{$month}）")

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold mb-4">勤怠一覧（{{ $month }}）</h1>

    <!-- 月切替 -->
    <div class="flex items-center mb-4 space-x-2">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}"
            class="bg-gray-200 px-4 py-2 rounded">前月</a>

        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}"
            class="bg-gray-200 px-4 py-2 rounded">翌月</a>

        <form method="GET" action="{{ route('attendance.list') }}" class="ml-4 flex items-center">
            <input type="month" name="month" value="{{ $month }}" class="border p-1">
            <button type="submit" class="ml-2 bg-blue-500 text-white px-4 py-2 rounded">表示</button>
        </form>
    </div>

    <!-- テーブル -->
    <table class="w-full border border-gray-300">
        <thead>
            <tr class="bg-gray-100 text-center">
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩（分）</th>
                <th>勤務時間</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendanceData as $data)
            <tr class="text-center">
                <td>{{ $data['date'] }}</td>

                <td>
                    {{ $data['start_time'] ? \Carbon\Carbon::parse($data['start_time'])->format('H:i') : '-' }}
                </td>

                <td>
                    {{ $data['end_time'] ? \Carbon\Carbon::parse($data['end_time'])->format('H:i') : '-' }}
                </td>

                <td>{{ $data['break_minutes'] ?? '-' }}</td>

                <td>
                    {{ $data['work_minutes']
                        ? intdiv($data['work_minutes'], 60) . '時間 ' . ($data['work_minutes'] % 60) . '分'
                        : '-' }}
                </td>

                <td>
                    <!-- ★ここが重要（id渡す） -->
                    <a href="{{ route('attendance.detail', $data['id']) }}"
                        class="bg-green-500 text-white px-3 py-1 rounded">
                        詳細
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">データなし</td>
            </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr class="bg-gray-200 text-center">
                <td colspan="3">合計</td>
                <td>{{ $totalBreakMinutes }}</td>
                <td>
                    {{ intdiv($totalWorkMinutes, 60) }}時間 {{ $totalWorkMinutes % 60 }}分
                </td>
                <td>-</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection