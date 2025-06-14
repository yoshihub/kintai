@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if (!$attendance)
    {{-- 未出勤の場合: 出勤ボタンのみ表示 --}}
    <span class="attendance-status">勤務外</span>
    <span class="attendance-date">{{ \Carbon\Carbon::now()->format('Y年n月j日') }}</span>
    <span class="attendance-time" id="current-time">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
    <form method="POST" action="{{ route('attendance.clockIn') }}">
        @csrf
        <button type="submit" class="attendance-button">出勤</button>
    </form>

    @elseif ($attendance->clock_in && !$attendance->clock_out)
    @if ($status === 1)
    {{-- 出勤中の場合: 休憩と退勤ボタンを表示 --}}
    <span class="attendance-status">出勤中</span>
    <span class="attendance-date">{{ \Carbon\Carbon::now()->format('Y年n月j日') }}</span>
    <span class="attendance-time" id="current-time">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
    <div class="button-group">
        <form method="POST" action="{{ route('attendance.clockOut') }}">
            @csrf
            <button type="submit" class="attendance-button">退勤</button>
        </form>
        <form method="POST" action="{{ route('attendance.breakStart') }}">
            @csrf
            <button type="submit" class="break-button">休憩入</button>
        </form>
    </div>

    @elseif ($status === 2)
    {{-- 休憩中の場合: 休憩終了ボタンのみ表示 --}}
    <span class="attendance-status">休憩中</span>
    <span class="attendance-date">{{ \Carbon\Carbon::now()->format('Y年n月j日') }}</span>
    <span class="attendance-time" id="current-time">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
    <form method="POST" action="{{ route('attendance.breakEnd') }}">
        @csrf
        <button type="submit" class="break-button">休憩戻</button>
    </form>
    @endif

    @else
    {{-- 退勤済みの場合: メッセージのみ表示 --}}
    <span class="attendance-status">退勤済</span>
    <span class="attendance-date">{{ \Carbon\Carbon::now()->format('Y年n月j日') }}</span>
    <span class="attendance-time" id="current-time">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
    <p class="attendance-message">お疲れ様でした。</p>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const timeString = `${hours}:${minutes}`;

        document.getElementById('current-time').textContent = timeString;
    }

    // 1秒ごとに時間を更新
    setInterval(updateTime, 1000);

    // ページ読み込み時にも実行
    document.addEventListener('DOMContentLoaded', updateTime);
</script>
@endsection
