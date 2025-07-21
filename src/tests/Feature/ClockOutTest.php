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
     * 退勤ボタンが正しく機能する
     *
     * @return void
     */
    public function test_clock_out_button_works_correctly()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 出勤中の勤怠レコード作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('H:i'),
            'status' => 1 // 出勤中
        ]);

        // 勤務画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');

        // 退勤ボタンが表示されていることを確認
        $response->assertSee('退勤');

        // 退勤処理を実行
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->post('/attendance/clock-out');

        // リダイレクトされることを確認
        $response->assertRedirect('/attendance');

        // データベースの退勤記録が更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'status' => 3 // 退勤済
        ]);

        // 退勤後のページで「退勤済」ステータスが表示されることを確認
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

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
