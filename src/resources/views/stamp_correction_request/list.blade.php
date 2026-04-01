<h2>承認待ち</h2>

<table>
    <tr>
        @if(Auth::user()->is_admin)
        <th>ユーザー名</th>
        @endif
        <th>日付</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>理由</th>
        <th>詳細</th>
    </tr>

    @foreach($pendingRequests as $request)
    <tr>
        @if(Auth::user()->is_admin)
        <td>{{ $request->user->name }}</td>
        @endif

        <td>{{ $request->work_date }}</td>
        <td>{{ optional($request->clock_in_at)->format('H:i') }}</td>
        <td>{{ optional($request->clock_out_at)->format('H:i') }}</td>
        <td>{{ $request->reason }}</td>
        <td>
            <a href="{{ route('attendance.detail', $request->id) }}">詳細</a>
        </td>
    </tr>
    @endforeach
</table>


<h2>承認済み</h2>

<table>
    <tr>
        @if(Auth::user()->is_admin)
        <th>ユーザー名</th>
        @endif
        <th>日付</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>理由</th>
        <th>詳細</th>
    </tr>

    @foreach($approvedRequests as $request)
    <tr>
        @if(Auth::user()->is_admin)
        <td>{{ $request->user->name }}</td>
        @endif

        <td>{{ $request->work_date }}</td>
        <td>{{ optional($request->clock_in_at)->format('H:i') }}</td>
        <td>{{ optional($request->clock_out_at)->format('H:i') }}</td>
        <td>{{ $request->reason }}</td>
        <td>
            <a href="{{ route('attendance.detail', $request->id) }}">詳細</a>
        </td>
    </tr>
    @endforeach
</table>