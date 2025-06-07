@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/admin/login.css') }}">
@endsection

@section('content')
<div class="login-container">
    <p class="title">ログイン</p>
    <form class="form" action="/login" method="post">
        @csrf
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}">
            @error('email')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password">
            @error('password')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group">
            <button type="submit">ログインする</button>
        </div>
        <div class="form-group">
            <a href="/register">会員登録はこちら</a>
        </div>
    </form>
</div>
@endsection
