@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list/index.css') }}">
@endsection

@section('content')
<div class="staff-content__inner">
    <div class="staff-content__heading">
        <h2>スタッフ一覧</h2>
    </div>
    <div class="staff-table">
        <table class="staff-table__inner">
            <tr class="staff-table__row">
                <th class="staff-table__header">名前</th>
                <th class="staff-table__header">メールアドレス</th>
                <th class="staff-table__header">月次勤怠</th>
            </tr>
            @foreach ($users as $user)
            <tr class="staff-table__row">
                <td class="staff-table__item">{{ $user->name }}</td>
                <td class="staff-table__item">{{ $user->email }}</td>
                <td class="staff-table__item">
                    <a class="staff-table__detail-link" href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
