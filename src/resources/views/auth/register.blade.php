@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register-container">
    <p class="title">会員登録</p>
    <form class="form" action="/register" method="post">
        @csrf
        <div class="form-group">
            <label for="name">ユーザ名</label>
            <input type="text" name="name" value="{{ old('name') }}" />
            @error('name')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group">
            <label for="email">メールアドレス</label> <input type="email" name="email" value="{{ old('email') }}" />
            @error('email')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" name="password" />
            @error('password')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group">
            <label for="password_confirmation">確認用パスワード</label><input type="password" name="password_confirmation" />
        </div>
        <div class="form-group">
            <button type="submit">
                登録する
            </button>
        </div>
        <div class="form-group">
            <a href="/login">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection
