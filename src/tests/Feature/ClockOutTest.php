<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;



    /**
     * 退勤時刻が勤務一覧画面で確認できる
     *
     * @return void
     */
    public function test_clock_out_time_can_be_confirmed_in_attendance_list()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 出勤・退勤記録作成
        $clockInTime = '09:00';
        $clockOutTime = '18:00';
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => $clockInTime,
            'clock_out' => $clockOutTime,
            'status' => 3 // 退勤済
        ]);

        // 勤務一覧画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        // 退勤時刻が正しく表示されていることを確認
        $response->assertSee($clockOutTime);
    }
}
