<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @yield('css')
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <div class="logo">
                <a href="/">
                    <img src="{{ asset('images/logo.svg') }}" alt="ロゴ">
                </a>
            </div>
            @if (Auth::guard('admin')->check())
            <nav class="nav-links">
                <a href="/admin/attendance/list">勤怠一覧</a>
                <a href="#">スタッフ一覧</a>
                <a href="#">申請一覧</a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="logout-button">ログアウト</button>
                </form>
            </nav>
            @elseif (Auth::check())
            <nav class="nav-links">
                <a href="{{ route('attendance.index') }}">勤怠</a>
                <a href="#">勤怠一覧</a>
                <a href="#">申請</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button">ログアウト</button>
                </form>
            </nav>
            @else
            <nav class="nav-links">
                <a href="/login">ログイン</a>
                <a href="/register">登録</a>
                <a href="{{ route('admin.login') }}">管理者ログイン</a>
            </nav>
            @endif
        </div>
    </header>
    <main>
        @if (session('message'))
        <div class="flash-message">
            {{ session('message') }}
        </div>
        @endif

        @yield('content')
    </main>
</body>

</html>
