@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="login__content">
        <div class="login-form__heading">
            <h2>メール認証</h2>
        </div>

        @if (session('resent'))
        <div class="alert alert-success" role="alert">
            認証メールを再送信しました。メールをご確認ください。
        </div>
        @endif

        <div class="verify-email__message">
            <p>登録していただいたメールアドレスに認証メールを送信しました。</p>
            <p>メール認証をしないでログインを試みた場合はメール認証誘導画面へ遷移</p>
            <p>メールをご確認いただき、認証を完了してください。</p>
        </div>

        <form class="verify-email__form" method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div class="form__button">
                <button class="form__button-submit" type="submit">認証メール再送</button>
            </div>
        </form>

        <div class="verify-email__links">
            <a href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                ログアウト
            </a>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>
@endsection
