<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外の場合、勤務ステータスが正しく表示される
     *
     * @return void
     */
    public function test_status_shows_correctly_when_not_working()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // ログインして勤務画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /**
     * 出勤中の場合、勤務ステータスが正しく表示される
     *
     * @return void
     */
    public function test_status_shows_correctly_when_working()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 出勤中の勤怠レコード作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('H:i'),
            'status' => 1 // 出勤中
        ]);

        // ログインして勤務画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * 休憩中の場合、勤務ステータスが正しく表示される
     *
     * @return void
     */
    public function test_status_shows_correctly_when_on_break()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 休憩中の勤怠レコード作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('H:i'),
            'status' => 2 // 休憩中
        ]);

        // 休憩時間レコード作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->format('H:i')
        ]);

        // ログインして勤務画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * 退勤済の場合、勤務ステータスが正しく表示される
     *
     * @return void
     */
    public function test_status_shows_correctly_when_finished_work()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 退勤済の勤怠レコード作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(8)->format('H:i'),
            'clock_out' => now()->format('H:i'),
            'status' => 3 // 退勤済
        ]);

        // ログインして勤務画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
