<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title')</title>

    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    @yield('css')
</head>

<body>

    <header class="auth-header">
        <div class="auth-header-inner">
            <h1 class="auth-logo">
                COACHTECH
            </h1>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

</body>

</html>