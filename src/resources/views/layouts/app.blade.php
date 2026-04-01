<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '勤怠管理アプリ')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 font-sans text-gray-800">

    <!-- ヘッダー -->
    <header class="bg-white shadow mb-6">
        <div class="container mx-auto flex justify-between items-center p-4">
            <div class="text-xl font-bold">
                勤怠管理アプリ
            </div>
            <nav class="space-x-4">
                <a href="/" class="text-gray-700 hover:text-blue-500">ホーム</a>
                <a href="{{ route('attendance.list') }}" class="text-gray-700 hover:text-blue-500">勤怠一覧</a>
                <a href="/attendance/start" class="text-gray-700 hover:text-blue-500">出勤</a>
                <a href="/attendance/end" class="text-gray-700 hover:text-blue-500">退勤</a>
                <a href="/logout" class="text-gray-700 hover:text-red-500">ログアウト</a>
            </nav>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="container mx-auto p-4">
        @yield('content')
    </main>

    <!-- フッター -->
    <footer class="bg-white shadow mt-6 p-4 text-center text-gray-500 text-sm">
        &copy; {{ date('Y') }} 勤怠管理アプリ
    </footer>

</body>

</html>