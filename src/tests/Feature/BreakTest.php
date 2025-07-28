<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakTest extends TestCase
{
    use RefreshDatabase;



    /**
     * 休憩時刻が勤務一覧画面で確認できる
     *
     * @return void
     */
    public function test_break_time_can_be_confirmed_in_attendance_list()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 出勤中の勤怠レコード作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'status' => 1
        ]);

        // 休憩時間レコード作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00'
        ]);

        // 勤務一覧画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        // 休憩時間が正しく表示されていることを確認
        $response->assertSee('1:00');
    }
}
