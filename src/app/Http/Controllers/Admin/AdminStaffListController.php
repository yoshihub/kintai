<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminStaffListController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return view('admin.staff.list.index', compact('users'));
    }

    public function detail(Request $request, $id)
    {
        // ユーザー情報の取得
        $user = User::findOrFail($id);

        // 表示月の取得（デフォルトは現在の月）
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();
        $year = $date->year;
        $month = $date->month;

        // 勤怠情報の取得（休憩時間も一緒に取得）
        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
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

        return view('admin.staff.list.detail', [
            'user' => $user,
            'attendanceData' => $attendanceData,
            'currentDate' => $date,
        ]);
    }

    public function exportCsv(Request $request, $id)
    {
        // ユーザー情報の取得
        $user = User::findOrFail($id);

        // 表示月の取得（デフォルトは現在の月）
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();
        $year = $date->year;
        $month = $date->month;

        // 勤怠情報の取得（detail メソッドと同じロジック）
        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        // 月の日数を取得
        $daysInMonth = $date->daysInMonth;

        // CSVデータの作成
        $csvData = [];

        // CSVヘッダー
        $csvData[] = ['日付', '曜日', '出勤時間', '退勤時間', '休憩時間', '勤務時間'];

        // データ行の作成
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $currentDate = Carbon::create($year, $month, $i);
            $attendance = $attendances->first(function ($item) use ($currentDate) {
                return $item->date->format('Y-m-d') === $currentDate->format('Y-m-d');
            });

            // 休憩時間を分単位で計算
            $totalBreakMinutes = 0;
            $totalBreakTime = '';
            if ($attendance && $attendance->breaks->count() > 0) {
                foreach ($attendance->breaks as $break) {
                    if ($break->break_start && $break->break_end) {
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
            $totalWorkTime = '';
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                $clockInMinutes = $attendance->clock_in->hour * 60 + $attendance->clock_in->minute;
                $clockOutMinutes = $attendance->clock_out->hour * 60 + $attendance->clock_out->minute;
                $totalWorkMinutes = max(0, $clockOutMinutes - $clockInMinutes);
                $actualWorkMinutes = max(0, $totalWorkMinutes - $totalBreakMinutes);

                if ($actualWorkMinutes > 0) {
                    $hours = intval($actualWorkMinutes / 60);
                    $minutes = $actualWorkMinutes % 60;
                    $totalWorkTime = sprintf('%d:%02d', $hours, $minutes);
                }
            }

            $csvData[] = [
                $currentDate->format('Y/m/d'),
                ['日', '月', '火', '水', '木', '金', '土'][$currentDate->dayOfWeek],
                $attendance && $attendance->clock_in ? $attendance->clock_in->format('H:i') : '',
                $attendance && $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                $totalBreakTime,
                $totalWorkTime,
            ];
        }

        // CSVファイル名の生成
        $filename = sprintf('%sさんの勤怠_%s.csv', $user->name, $date->format('Y年m月'));

        // レスポンスヘッダーの設定
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // CSVレスポンスの生成
        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');

            // BOM を追加してExcelでの文字化けを防ぐ
            fwrite($file, "\xEF\xBB\xBF");

            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
