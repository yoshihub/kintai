@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list/detail.css') }}">
@endsection

@section('content')
<div class="detail-container">
    <div>
        <span class="detail-title">修正申請認証画面（管理者）</span>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- 修正申請承認フォーム -->
    <form action="{{ route('admin.stamp_correction_request.approve.submit', $correctionRequest->id) }}" method="POST">
        @csrf

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td class="name-td">{{ $correctionRequest->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <span class="date-td" style="margin-right:57px;">{{ $correctionRequest->attendance->date ? $correctionRequest->attendance->date->format('Y年') : '' }}</span>
                    <span class="date-td">{{ $correctionRequest->attendance->date ? $correctionRequest->attendance->date->format('n月j日') : '' }}</span>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" class="time-input" name="start_time" value="{{ $correctionRequest->start_time ? $correctionRequest->start_time->format('H:i') : '' }}" readonly>
                    <span class="separator">〜</span>
                    <input type="time" class="time-input" name="end_time" value="{{ $correctionRequest->end_time ? $correctionRequest->end_time->format('H:i') : '' }}" readonly>
                </td>
            </tr>

            @if($correctionRequest->breaks && count($correctionRequest->breaks) > 0)
            @foreach($correctionRequest->breaks as $index => $break)
            <tr>
                <th>休憩{{ $index == 0 ? '' : $index + 1 }}</th>
                <td>
                    <input type="time" class="time-input" value="{{ isset($break['break_start']) ? $break['break_start'] : '' }}" readonly>
                    <span class="separator">〜</span>
                    <input type="time" class="time-input" value="{{ isset($break['break_end']) ? $break['break_end'] : '' }}" readonly>
                </td>
            </tr>
            @endforeach
            @else
            <tr>
                <th>休憩</th>
                <td>
                    <input type="time" class="time-input" readonly>
                    <span class="separator">〜</span>
                    <input type="time" class="time-input" readonly>
                </td>
            </tr>
            @endif

            <tr>
                <th>備考</th>
                <td>
                    <textarea class="note-textarea" readonly>{{ $correctionRequest->note }}</textarea>
                </td>
            </tr>
        </table>

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="button-container">
            @if($correctionRequest->status === 'pending')
            <button type="submit">承認</button>
            @else
            <div class="alert alert-success" style="text-align: center; margin: 0;">
                承認済みです
            </div>
            @endif
        </div>
    </form>
</div>
@endsection
