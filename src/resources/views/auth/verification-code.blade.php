@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="login__content">

        @if ($errors->any())
        <div class="alert alert-error" role="alert">
            @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        @if (session('resent'))
        <div class="alert alert-success" role="alert">
            新しい認証コードを生成しました。メールをご確認ください。
        </div>
        @endif

        @if (session('code_generated'))
        <div class="alert alert-success" role="alert">
            {{ session('code_generated') }}
        </div>
        @endif

        <div class="verify-email__message">
            <p>送信されたメールに記載されている認証コードを入力してください。</p>
        </div>

        <form class="verify-email__form" method="POST" action="{{ route('verification.verify-code') }}">
            @csrf

            <div class="form__group">
                <div class="form__group-title">
                    <span class="form__label--item">認証コード</span>
                    <span class="form__label--required">※</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="text" name="verification_code" value="{{ old('verification_code') }}" placeholder="6桁の認証コード" maxlength="6" required />
                    </div>
                    <div class="form__error">
                        @error('verification_code')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form__button">
                <button class="form__button-submit" type="submit">認証する</button>
            </div>
        </form>

        <div class="verify-email__links">
            <a href="{{ route('verification.notice') }}">メール認証画面に戻る</a>
            <span class="separator">|</span>
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
