<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     *
     * @return void
     */
    public function test_attendance_detail_shows_logged_in_user_name()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 勤怠記録作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
        // ログインユーザーの名前が表示されていることを確認
        $response->assertSee('テストユーザー');
    }

    /**
     * 勤怠詳細画面の「日付」が選択した日付になっている
     *
     * @return void
     */
    public function test_attendance_detail_shows_selected_date()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 特定の日付で勤怠記録作成
        $testDate = Carbon::create(2024, 6, 15);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $testDate->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
        // 選択した日付が表示されていることを確認
        $response->assertSee('2024年');
        $response->assertSee('6月15日');
    }

    /**
     * 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     *
     * @return void
     */
    public function test_attendance_detail_shows_correct_clock_in_out_times()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 勤怠記録作成
        $clockInTime = '08:45';
        $clockOutTime = '17:15';
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => $clockInTime,
            'clock_out' => $clockOutTime,
            'status' => 3
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
        // 出勤・退勤時刻が正しく表示されていることを確認
        $response->assertSee($clockInTime);
        $response->assertSee($clockOutTime);
    }

    /**
     * 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     *
     * @return void
     */
    public function test_attendance_detail_shows_correct_break_times()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 勤怠記録作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 休憩時間記録作成
        $breakStartTime = '12:00';
        $breakEndTime = '13:00';
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => $breakStartTime,
            'break_end' => $breakEndTime
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
        // 休憩時刻が正しく表示されていることを確認
        $response->assertSee($breakStartTime);
        $response->assertSee($breakEndTime);
    }
}
