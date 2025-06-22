<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        // 表示月の取得（デフォルトは現在の月）
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();
        $year = $date->year;
        $month = $date->month;

        // 勤怠情報の取得（休憩時間も一緒に取得）
        $attendances = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        // 月の日数を取得
        $daysInMonth = $date->daysInMonth;

        // 表示用のデータを整形
        $attendanceData = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $currentDate = Carbon::create($year, $month, $i);
            // Carbonインスタンス同士で比較
            $attendance = $attendances->first(function ($item) use ($currentDate) {
                return $item->date->format('Y-m-d') === $currentDate->format('Y-m-d');
            });

            // 休憩時間を分単位で計算
            $totalBreakMinutes = 0;
            $totalBreakTime = null;
            if ($attendance && $attendance->breaks->count() > 0) {
                foreach ($attendance->breaks as $break) {
                    if ($break->break_start && $break->break_end) {
                        // 分単位で計算（秒は00なので直接計算可能）
                        $startMinutes = $break->break_start->hour * 60 + $break->break_start->minute;
                        $endMinutes = $break->break_end->hour * 60 + $break->break_end->minute;

                        $breakMinutes = max(0, $endMinutes - $startMinutes);
                        $totalBreakMinutes += $breakMinutes;
                    }
                }

                if ($totalBreakMinutes > 0) {
                    $hours = intval($totalBreakMinutes / 60);
                    $minutes = $totalBreakMinutes % 60;
                    $totalBreakTime = sprintf('%d:%02d', $hours, $minutes);
                }
            }

            // 合計勤務時間を分単位で計算
            $totalWorkTime = null;
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                // 出勤・退勤時間を分単位で計算
                $clockInMinutes = $attendance->clock_in->hour * 60 + $attendance->clock_in->minute;
                $clockOutMinutes = $attendance->clock_out->hour * 60 + $attendance->clock_out->minute;

                $totalWorkMinutes = max(0, $clockOutMinutes - $clockInMinutes);

                // 休憩時間を差し引く
                $actualWorkMinutes = max(0, $totalWorkMinutes - $totalBreakMinutes);

                if ($actualWorkMinutes > 0) {
                    $hours = intval($actualWorkMinutes / 60);
                    $minutes = $actualWorkMinutes % 60;
                    $totalWorkTime = sprintf('%d:%02d', $hours, $minutes);
                }
            }

            $attendanceData[] = [
                'date' => $currentDate->format('m/d'),
                'day_of_week' => ['日', '月', '火', '水', '木', '金', '土'][$currentDate->dayOfWeek],
                'clock_in' => $attendance && $attendance->clock_in ? $attendance->clock_in->format('H:i') : null,
                'clock_out' => $attendance && $attendance->clock_out ? $attendance->clock_out->format('H:i') : null,
                'break_time' => $totalBreakTime,
                'total_time' => $totalWorkTime,
                'id' => $attendance ? $attendance->id : null,
            ];
        }

        return view('attendance.list.index', [
            'attendanceData' => $attendanceData,
            'currentDate' => $date,
        ]);
    }

    public function detail($id)
    {
        $attendance = Attendance::with(['breaks', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('attendance.list.detail', [
            'attendance' => $attendance
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'breaks' => 'nullable|array',
            'breaks.*.break_start' => 'nullable|date_format:H:i',
            'breaks.*.break_end' => 'nullable|date_format:H:i',
            'memo' => 'nullable|string|max:1000',
        ]);

        $attendance = Attendance::where('user_id', auth()->id())->findOrFail($id);

        // 出勤・退勤時間の更新（HH:MM形式、秒なし）
        $updateData = ['memo' => $request->memo];
        if ($request->clock_in) {
            $updateData['clock_in'] = $request->clock_in;  // 秒を完全に排除
        }
        if ($request->clock_out) {
            $updateData['clock_out'] = $request->clock_out;  // 秒を完全に排除
        }

        $attendance->update($updateData);

        // 既存の休憩時間をクリア
        $attendance->breaks()->delete();

        // 新しい休憩時間を追加（HH:MM形式、秒なし）
        if ($request->has('breaks')) {
            foreach ($request->breaks as $breakData) {
                // 開始時間と終了時間の両方が入力されている場合のみ保存
                if (!empty($breakData['break_start']) && !empty($breakData['break_end'])) {
                    $attendance->breaks()->create([
                        'break_start' => $breakData['break_start'],  // 秒を完全に排除
                        'break_end' => $breakData['break_end'],      // 秒を完全に排除
                    ]);
                }
            }
        }

        return redirect()->route('attendance.detail', $id)->with('success', '勤怠情報を更新しました。');
    }
}
