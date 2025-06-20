<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    private function getTodayAttendance()
    {
        $user = Auth::user();
        $today = now()->toDateString();
        return Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
    }

    /**
     * 現在時刻を分単位（HH:MM形式、秒なし）で取得
     */
    private function getCurrentTimeInMinutes()
    {
        return now()->format('H:i');  // 秒を完全に排除
    }

    public function index()
    {
        $attendance = $this->getTodayAttendance();
        $status = $attendance ? $attendance->status : 0;
        return view('attendance.index', compact('attendance', 'status'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // 新規出勤記録作成（分単位のみ）
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => $this->getCurrentTimeInMinutes(),
            'status' => 1
        ]);

        return redirect()->route('attendance.index')
            ->with('success', '出勤を記録しました。');
    }

    public function clockOut()
    {
        $attendance = $this->getTodayAttendance();

        $attendance->update([
            'clock_out' => $this->getCurrentTimeInMinutes(),
            'status' => 3
        ]);

        return redirect()->route('attendance.index')
            ->with('success', '退勤を記録しました。');
    }

    public function breakStart()
    {
        $attendance = $this->getTodayAttendance();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => $this->getCurrentTimeInMinutes()
        ]);

        $attendance->update(['status' => 2]);

        return redirect()->route('attendance.index')
            ->with('success', '休憩を開始しました。');
    }

    public function breakEnd()
    {
        $attendance = $this->getTodayAttendance();

        $break = $attendance->breaks()->whereNull('break_end')->latest()->first();
        if ($break) {
            $break->update(['break_end' => $this->getCurrentTimeInMinutes()]);
        }

        $attendance->update(['status' => 1]);

        return redirect()->route('attendance.index')
            ->with('success', '休憩を終了しました。');
    }
}
