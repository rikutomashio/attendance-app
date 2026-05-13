<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH')</title>

    <!-- common.css -->
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">

    <!-- ページ個別CSS -->
    @yield('css')
</head>

<body>

    <!-- ヘッダー -->
    <header class="header">
        <div class="container header-inner">

            <!-- タイトル -->
            <div class="logo">
                COACHTECH
            </div>

            <!-- ナビゲーション -->
            <nav class="nav">

                <a href="{{ route('admin.attendance.list') }}" class="nav-link">
                    勤怠一覧
                </a>

                <a href="{{ route('admin.staff.list') }}" class="nav-link">
                    スタッフ一覧
                </a>

                <a href="{{ route('stamp_correction_request.list') }}" class="nav-link">
                    申請一覧
                </a>

                <form method="POST" action="{{ route('logout') }}" class="nav-form">
                    @csrf
                    <button type="submit" class="nav-link logout">
                        ログアウト
                    </button>
                </form>

            </nav>
        </div>
    </header>

    <!-- メイン -->
    <main class="main container">
        @yield('content')
    </main>

    <!-- フッター -->
    <footer class="footer">
        &copy; {{ date('Y') }} COACHTECH
    </footer>

</body>

</html>