@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list/detail.css') }}">
@endsection

@section('content')
<div class="detail-container">
    <div>
        <span class="detail-title">勤怠詳細</span>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- 管理者用修正フォーム -->
    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td class="name-td">{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <span class="date-td" style="margin-right:57px;">{{ $attendance->date ? $attendance->date->format('Y年') : '' }}</span>
                    <span class="date-td">{{ $attendance->date ? $attendance->date->format('n月j日') : '' }}</span>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" class="time-input" name="start_time" value="{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}" required>
                    <span class="separator">〜</span>
                    <input type="time" class="time-input" name="end_time" value="{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}" required>
                </td>
            </tr>

            @php
            $breaks = $attendance->breaks;
            $breakCount = $breaks->count();
            // 既存の休憩数 + 1（追加入力用）、ただし最低1つは表示
            $totalBreakFields = max($breakCount + 1, 1);
            @endphp

            @for($i = 0; $i < $totalBreakFields; $i++)
                @php
                $break=$i < $breakCount ? $breaks->get($i) : null;
                $breakNumber = $i + 1;
                @endphp
                <tr>
                    <th>休憩{{ $breakNumber == 1 ? '' : $breakNumber }}</th>
                    <td>
                        <input type="time" class="time-input" name="breaks[{{ $i }}][break_start]" value="{{ $break && $break->break_start ? $break->break_start->format('H:i') : '' }}">
                        <span class="separator">〜</span>
                        <input type="time" class="time-input" name="breaks[{{ $i }}][break_end]" value="{{ $break && $break->break_end ? $break->break_end->format('H:i') : '' }}">
                    </td>
                </tr>
                @endfor

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea class="note-textarea" name="note">{{ old('note', $attendance->note) }}</textarea>
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
            <button type="submit">修正</button>
        </div>
    </form>
</div>
@endsection
