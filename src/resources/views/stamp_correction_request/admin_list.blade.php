@extends('layouts.admin')

@section('title', '申請一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
<link rel="stylesheet" href="{{ asset('css/request-list.css') }}">
@endsection

@section('content')

<div class="request-list-container">

    <h2 class="page-title">申請一覧</h2>

    {{-- ステータス切替 --}}
    <div class="status-tabs">

        <a href="?status=pending"
            class="tab {{ request('status', 'pending') === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>

        <a href="?status=approved"
            class="tab {{ request('status', 'pending') === 'approved' ? 'active' : '' }}">
            承認済み
        </a>

    </div>

    @if($requests->isEmpty())

    <p class="empty-message">
        申請はありません。
    </p>

    @else

    <table class="request-table">

        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>

            @foreach($requests as $request)

            <tr>

                {{-- 状態 --}}
                <td>

                    @if($request->status === 'pending')

                    <span class="status-badge pending">
                        承認待ち
                    </span>

                    @elseif($request->status === 'approved')

                    <span class="status-badge approved">
                        承認済み
                    </span>

                    @else

                    <span class="status-badge rejected">
                        却下
                    </span>

                    @endif

                </td>

                {{-- 名前 --}}
                <td>
                    {{ $request->attendance->user->name ?? '-' }}
                </td>

                {{-- 対象日時 --}}
                <td>
                    {{ $request->attendance?->work_date?->format('Y/m/d') ?? '-' }}
                </td>

                {{-- 申請理由 --}}
                <td>
                    {{ $request->reason ?? '-' }}
                </td>

                {{-- 申請日時 --}}
                <td>
                    {{ $request->created_at->format('Y/m/d') }}
                </td>

                {{-- 詳細 --}}
                <td>
                    <a
                        href="{{ route('admin.request.approve.show', $request->id) }}"
                        class="detail-link">
                        詳細
                    </a>
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

    {{-- ページネーション --}}
    <div class="pagination-wrapper">
        {{ $requests->links() }}
    </div>

    @endif

</div>

@endsection