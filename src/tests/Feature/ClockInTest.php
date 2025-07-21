<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤ボタンが正しく機能する
     *
     * @return void
     */
    public function test_clock_in_button_works_correctly()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 出勤ボタンが表示されていることを確認
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        // 出勤処理を実行
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->post('/attendance/clock-in');

        // リダイレクトされることを確認
        $response->assertRedirect('/attendance');

        // データベースに出勤記録が作成されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'status' => 1
        ]);

        // 出勤後のページで「出勤中」ステータスが表示されることを確認
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 出勤は一日一回のみできる
     *
     * @return void
     */
    public function test_clock_in_can_only_be_done_once_per_day()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 既に出勤している状態の勤怠レコード作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('H:i'),
            'status' => 1
        ]);

        // 勤務画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');

        // 出勤ボタンが表示されていないことを確認（出勤ボタンのHTMLを具体的にチェック）
        $response->assertDontSee('<button type="submit" class="attendance-button">出勤</button>');
        // 出勤中ステータスが表示されていることを確認
        $response->assertSee('出勤中');
    }

    /**
     * 出勤時刻が勤務一覧画面で確認できる
     *
     * @return void
     */
    public function test_clock_in_time_can_be_confirmed_in_attendance_list()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 出勤記録作成
        $clockInTime = '09:00';
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => $clockInTime,
            'status' => 1
        ]);

        // 勤務一覧画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        // 出勤時刻が正しく表示されていることを確認
        $response->assertSee($clockInTime);
    }
}
