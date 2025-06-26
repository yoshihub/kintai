@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list/index.css') }}">
@endsection

@section('content')
<div class="attendance-list-outer">
    <div class="attendance-title-bar">
        <span class="bar"></span>
        <span class="attendance-title">申請一覧</span>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px; padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
        {{ session('success') }}
    </div>
    @endif

    <div class="attendance-header-row">
        <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}"
            class="month-btn {{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
        <div style="flex: 1;"></div>
        <a href="{{ route('stamp_correction_request.list', ['status' => 'approved']) }}"
            class="month-btn {{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    <div class="attendance-table-area">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>承認状況</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                <tr>
                    <td>
                        @if($request->status === 'pending')
                        <span class="status-badge status-pending">承認待ち</span>
                        @elseif($request->status === 'approved')
                        <span class="status-badge status-approved">承認済み</span>
                        @endif
                    </td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ $request->attendance->date->format('Y/m/d') }}</td>
                    <td>{{ $request->note ?? '遅刻のため' }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #666; padding: 40px;">
                        @if($status === 'pending')
                        承認待ちの申請はありません
                        @elseif($status === 'approved')
                        承認済みの申請はありません
                        @else
                        修正申請はありません
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
