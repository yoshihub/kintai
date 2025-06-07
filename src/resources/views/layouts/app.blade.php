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
            @if (Auth::check())
            <nav class="nav-links">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button">ログアウト</button>
                </form>
                <a href="/mypage" class="">マイページ</a>
                <a href="/sell" class="sell-button">出品</a>
            </nav>
            @else
            <nav class="nav-links" style="margin-left:auto;">
                <a href="/login">ログイン</a>
                <a href="/register">登録</a>
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
