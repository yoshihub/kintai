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
     * 休憩ボタンが正しく機能する
     *
     * @return void
     */
    public function test_break_button_works_correctly()
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

        // 休憩入ボタンが表示されていることを確認
        $response->assertSee('休憩入');

        // 休憩入処理を実行
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->post('/attendance/break-start');

        // リダイレクトされることを確認
        $response->assertRedirect('/attendance');

        // 休憩後のページで「休憩中」ステータスが表示されることを確認
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    /**
     * 休憩は一日に何回でもできる
     *
     * @return void
     */
    public function test_break_can_be_taken_multiple_times_per_day()
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

        // 1回目の休憩
        $this->withoutMiddleware(['verified'])->actingAs($user)->post('/attendance/break-start');
        $this->withoutMiddleware(['verified'])->actingAs($user)->post('/attendance/break-end');

        // 2回目の休憩が取れることを確認
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    /**
     * 休憩戻ボタンが正しく機能する
     *
     * @return void
     */
    public function test_break_end_button_works_correctly()
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

        // 休憩入処理を実行
        $this->withoutMiddleware(['verified'])->actingAs($user)->post('/attendance/break-start');

        // 休憩中の画面で休憩戻ボタンが表示されることを確認
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        // 休憩戻処理を実行
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->post('/attendance/break-end');

        // リダイレクトされることを確認
        $response->assertRedirect('/attendance');

        // 休憩戻後のページで「出勤中」ステータスが表示されることを確認
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 休憩戻は一日に何回でもできる
     *
     * @return void
     */
    public function test_break_end_can_be_done_multiple_times_per_day()
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

        // 1回目の休憩入・休憩戻
        $this->withoutMiddleware(['verified'])->actingAs($user)->post('/attendance/break-start');
        $this->withoutMiddleware(['verified'])->actingAs($user)->post('/attendance/break-end');

        // 2回目の休憩入
        $this->withoutMiddleware(['verified'])->actingAs($user)->post('/attendance/break-start');

        // 2回目の休憩戻ボタンが表示されることを確認
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

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
