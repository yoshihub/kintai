@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list/detail.css') }}">
@endsection

@section('content')
<div class="attendance-list-outer">
    <div class="attendance-title-bar">
        <span class="bar"></span>
        <span class="attendance-title">{{ $user->name }}さんの勤怠</span>
    </div>
    <div class="attendance-header-row">
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'date' => $currentDate->copy()->subMonth()->format('Y-m')]) }}" class="month-btn">← 前月</a>
        <div class="attendance-month-label"><span class="calendar-icon">📅</span>{{ $currentDate->format('Y/m') }}</div>
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'date' => $currentDate->copy()->addMonth()->format('Y-m')]) }}" class="month-btn">翌月 →</a>
    </div>
    <div class="attendance-table-area">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceData as $data)
                <tr>
                    <td>{{ $data['date'] }}({{ $data['day_of_week'] }})</td>
                    <td>{{ $data['clock_in'] ?: '' }}</td>
                    <td>{{ $data['clock_out'] ?: '' }}</td>
                    <td>{{ $data['break_time'] ?: '' }}</td>
                    <td>{{ $data['total_time'] ?: '' }}</td>
                    <td>
                        @if($data['id'])
                        @php
                        // 月日から完全な日付を作成（例:01/15 → 2024-01-15）
                        $day = sprintf('%02d', explode('/', $data['date'])[1]);
                        $fullDate = $currentDate->format('Y-m') . '-' . $day;
                        @endphp
                        <a href="{{ route('admin.attendance.detail', ['user_id' => $user->id, 'date' => $fullDate]) }}" class="detail-link">詳細</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
