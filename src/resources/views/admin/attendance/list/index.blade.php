@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list/index.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="attendance-header">
        <h1>{{ $selectedDate->format('Y年n月j日') }}の勤怠</h1>
        <div class="date-navigation">
            <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="nav-btn prev-btn">
                &lt; 前日
            </a>
            <div class="current-date">
                <span class="date-display">{{ $selectedDate->format('Y/m/d') }}</span>
            </div>
            <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="nav-btn next-btn">
                翌日 &gt;
            </a>
        </div>
    </div>

    <div class="attendance-table-container">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendanceData as $data)
                <tr>
                    <td>{{ $data['name'] }}</td>
                    <td>{{ $data['clock_in'] ? \Carbon\Carbon::parse($data['clock_in'])->format('H:i') : '' }}</td>
                    <td>{{ $data['clock_out'] ? \Carbon\Carbon::parse($data['clock_out'])->format('H:i') : '' }}</td>
                    <td>{{ $data['total_break_time'] }}</td>
                    <td>{{ $data['total_working_time'] }}</td>
                    <td>
                        @if($data['clock_in'] || $data['clock_out'])
                        <a href="{{ route('admin.attendance.detail', ['user_id' => $data['user_id'], 'date' => $selectedDate->format('Y-m-d')]) }}" class="detail-link">詳細</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">勤怠データがありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
