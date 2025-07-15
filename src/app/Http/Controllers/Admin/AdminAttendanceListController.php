<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateAdminAttendanceRequest;

class AdminAttendanceListController extends Controller
{
    public function index(Request $request)
    {
        // 日付パラメータの取得（デフォルトは今日）
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        // 前日・翌日の日付を計算
        $prevDate = $selectedDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $selectedDate->copy()->addDay()->format('Y-m-d');

        $users = User::all();

        // 指定日の勤怠データを取得
        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('date', $selectedDate)
            ->get()
            ->keyBy('user_id');

        // ユーザーごとの勤怠情報を整理
        $attendanceData = $users->map(function ($user) use ($attendances) {
            $attendance = $attendances->get($user->id);

            // 休憩時間の計算
            $totalBreakMinutes = 0;
            if ($attendance && $attendance->breaks) {
                foreach ($attendance->breaks as $break) {
                    if ($break->break_start && $break->break_end) {
                        $breakStart = Carbon::parse($break->break_start);
                        $breakEnd = Carbon::parse($break->break_end);
                        $totalBreakMinutes += $breakEnd->diffInMinutes($breakStart);
                    }
                }
            }

            // 勤務時間の計算
            $workingMinutes = 0;
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);
                $workingMinutes = $clockOut->diffInMinutes($clockIn) - $totalBreakMinutes;
            }

            return [
                'user_id' => $user->id,
                'attendance_id' => $attendance ? $attendance->id : null,
                'name' => $user->name,
                'clock_in' => $attendance ? $attendance->clock_in : null,
                'clock_out' => $attendance ? $attendance->clock_out : null,
                'total_break_time' => $totalBreakMinutes > 0 ? $this->formatMinutesToTime($totalBreakMinutes) : '',
                'total_working_time' => $workingMinutes > 0 ? $this->formatMinutesToTime($workingMinutes) : '',
            ];
        });

        return view('admin.attendance.list.index', compact(
            'attendanceData',
            'selectedDate',
            'prevDate',
            'nextDate'
        ));
    }

    /**
     * 分を時間:分の形式に変換
     */
    private function formatMinutesToTime($minutes)
    {
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }

    /**
     * 管理者用の勤怠詳細表示
     */
    public function detail($id)
    {
        $attendance = Attendance::with(['breaks', 'user'])->findOrFail($id);

        return view('admin.attendance.list.detail', [
            'attendance' => $attendance
        ]);
    }

    /**
     * 管理者用の勤怠データ更新
     */
    public function update(UpdateAdminAttendanceRequest $request, $id)
    {

        $attendance = Attendance::with('breaks')->findOrFail($id);

        // 出勤・退勤時間の更新（HH:MM:SS形式で保存）
        $attendance->clock_in = $request->start_time;
        $attendance->clock_out = $request->end_time;
        $attendance->note = $request->note;
        $attendance->save();

        // 既存の休憩データを削除
        $attendance->breaks()->delete();

        // 新しい休憩データを保存（HH:MM:SS形式で保存）
        if ($request->has('breaks')) {
            foreach ($request->breaks as $breakData) {
                if (!empty($breakData['break_start']) && !empty($breakData['break_end'])) {
                    $attendance->breaks()->create([
                        'break_start' => $breakData['break_start'],
                        'break_end' => $breakData['break_end'],
                    ]);
                }
            }
        }

        // 該当する打刻修正リクエストのステータスを承認済みに更新
        StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->update(['status' => 'approved']);

        return redirect()->route('admin.attendance.detail', $attendance->id)
            ->with('success', '勤怠データを修正しました。');
    }
}
