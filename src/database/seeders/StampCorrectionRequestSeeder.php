<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StampCorrectionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            // 各ユーザーについて、最近の勤怠記録から1〜3件の修正申請を作成
            $attendances = Attendance::where('user_id', $user->id)
                ->where('date', '>=', Carbon::now()->subDays(30))
                ->inRandomOrder()
                ->limit(rand(1, 3))
                ->get();

            foreach ($attendances as $attendance) {
                $this->createCorrectionRequest($user, $attendance);
            }
        }
    }

    /**
     * 単一の打刻修正リクエストを作成
     */
    private function createCorrectionRequest($user, $attendance)
    {
        // 修正理由のパターン
        $correctionReasons = [
            '打刻し忘れのため修正をお願いします',
            '会議が長引いたため、退勤時刻を修正してください',
            '交通機関の遅延により遅刻しましたが、打刻できませんでした',
            '体調不良により早退しましたが、打刻を忘れました',
            'システムエラーで正しく打刻されませんでした',
            '外出業務で戻りが遅くなり、退勤打刻が遅れました',
            '昼休み時間を間違えて記録してしまいました',
            '残業時間の記録が正しくありません',
            '休憩時間の開始・終了を正しく打刻できませんでした',
            '客先での作業で打刻時刻にずれが生じました',
        ];

        // 修正後の時刻を生成
        $correctedStartTime = $this->generateCorrectedTime($attendance->clock_in, 'start');
        $correctedEndTime = $this->generateCorrectedTime($attendance->clock_out, 'end');

        // 修正後の休憩時間を生成
        $correctedBreaks = $this->generateCorrectedBreaks();

        // ランダムでステータスを決定（80%が申請中、20%が承認済み）
        $status = rand(1, 100) <= 80 ? 'pending' : 'approved';

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'start_time' => $correctedStartTime,
            'end_time' => $correctedEndTime,
            'breaks' => $correctedBreaks,
            'note' => $correctionReasons[array_rand($correctionReasons)],
            'status' => $status,
        ]);
    }

    /**
     * 修正後の時刻を生成
     */
    private function generateCorrectedTime($originalTime, $type)
    {
        if (!$originalTime) {
            // 元の時刻がない場合は適切なデフォルト値を設定
            if ($type === 'start') {
                return '09:00:00';
            } else {
                return '18:00:00';
            }
        }

        $time = Carbon::parse($originalTime);

        // 元の時刻から±30分以内でランダムに調整
        $adjustment = rand(-30, 30);
        $correctedTime = $time->copy()->addMinutes($adjustment);

        // 出勤時刻は7:00〜10:00の範囲に制限
        if ($type === 'start') {
            if ($correctedTime->hour < 7) {
                $correctedTime->setTime(7, 0, 0);
            } elseif ($correctedTime->hour > 10) {
                $correctedTime->setTime(10, 0, 0);
            }
        }
        // 退勤時刻は16:00〜22:00の範囲に制限
        else {
            if ($correctedTime->hour < 16) {
                $correctedTime->setTime(16, 0, 0);
            } elseif ($correctedTime->hour > 22) {
                $correctedTime->setTime(22, 0, 0);
            }
        }

        return $correctedTime->format('H:i:s');
    }

    /**
     * 修正後の休憩時間を生成
     */
    private function generateCorrectedBreaks()
    {
        $breakPatterns = [
            // パターン1: 標準的な昼休み
            [
                ['break_start' => '12:00:00', 'break_end' => '13:00:00']
            ],
            // パターン2: 長めの昼休み
            [
                ['break_start' => '12:00:00', 'break_end' => '13:30:00']
            ],
            // パターン3: 分割休憩
            [
                ['break_start' => '12:00:00', 'break_end' => '12:45:00'],
                ['break_start' => '15:00:00', 'break_end' => '15:15:00']
            ],
            // パターン4: 複数回の小休憩
            [
                ['break_start' => '10:15:00', 'break_end' => '10:30:00'],
                ['break_start' => '12:00:00', 'break_end' => '13:00:00'],
                ['break_start' => '15:30:00', 'break_end' => '15:45:00']
            ],
            // パターン5: 短縮昼休み
            [
                ['break_start' => '12:30:00', 'break_end' => '13:00:00']
            ],
        ];

        return $breakPatterns[array_rand($breakPatterns)];
    }
}
