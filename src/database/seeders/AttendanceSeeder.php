<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        // 過去3ヶ月分のデータを作成
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subMonths(3);

        foreach ($users as $user) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                // 平日のみ勤怠データを作成（土日は除く）
                if ($currentDate->isWeekday()) {
                    $this->createAttendanceRecord($user, $currentDate->copy());
                }
                $currentDate->addDay();
            }
        }
    }

    /**
     * 単一の勤怠記録を作成
     */
    private function createAttendanceRecord($user, $date)
    {
        // 10%の確率で欠勤（勤怠データを作成しない）
        if (rand(1, 100) <= 10) {
            return;
        }

        // ランダムな出勤時間（8:00〜9:30の間）
        $clockInHour = rand(8, 9);
        $clockInMinute = $clockInHour === 9 ? rand(0, 30) : rand(0, 59);
        $clockIn = $date->copy()->setTime($clockInHour, $clockInMinute, 0);

        // ランダムな退勤時間（17:00〜19:30の間）
        $clockOutHour = rand(17, 19);
        $clockOutMinute = $clockOutHour === 19 ? rand(0, 30) : rand(0, 59);
        $clockOut = $date->copy()->setTime($clockOutHour, $clockOutMinute, 0);

        // 勤怠記録を作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'clock_in' => $clockIn->format('H:i:s'),
            'clock_out' => $clockOut->format('H:i:s'),
            'status' => 3, // 退勤済み
            'note' => $this->getRandomNote(),
        ]);

        // 休憩時間を作成
        $this->createBreakTimes($attendance, $date);
    }

    /**
     * 休憩時間を作成
     */
    private function createBreakTimes($attendance, $date)
    {
        $breakPatterns = [
            // パターン1: 昼休み1回（12:00-13:00）
            [
                ['start' => '12:00', 'end' => '13:00']
            ],
            // パターン2: 昼休み + 午後の小休憩（12:00-13:00, 15:00-15:15）
            [
                ['start' => '12:00', 'end' => '13:00'],
                ['start' => '15:00', 'end' => '15:15']
            ],
            // パターン3: 分割昼休み（12:00-12:30, 12:45-13:15）
            [
                ['start' => '12:00', 'end' => '12:30'],
                ['start' => '12:45', 'end' => '13:15']
            ],
            // パターン4: 昼休み + 朝・午後の小休憩
            [
                ['start' => '10:00', 'end' => '10:15'],
                ['start' => '12:00', 'end' => '13:00'],
                ['start' => '15:30', 'end' => '15:45']
            ],
        ];

        // ランダムに休憩パターンを選択
        $selectedPattern = $breakPatterns[array_rand($breakPatterns)];

        foreach ($selectedPattern as $break) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => $break['start'],
                'break_end' => $break['end'],
            ]);
        }
    }

    /**
     * ランダムな備考を取得
     */
    private function getRandomNote()
    {
        $notes = [
            null, // 備考なし（50%の確率）
            null,
            '会議のため遅刻',
            '体調不良により早退',
            '客先対応',
            '研修参加',
            '外出業務',
            'リモートワーク',
            '残業対応',
            '交通機関の遅延',
        ];

        return $notes[array_rand($notes)];
    }
}
