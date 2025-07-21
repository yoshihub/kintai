<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分が行った勤怠情報が全て表示されている
     *
     * @return void
     */
    public function test_own_attendance_records_are_displayed()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 自分の勤怠記録作成
        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->subDay()->toDateString(),
            'clock_in' => '09:30',
            'clock_out' => '17:30',
            'status' => 3
        ]);

        // 他のユーザーの勤怠記録作成
        $otherAttendance = Attendance::create([
            'user_id' => $otherUser->id,
            'date' => now()->toDateString(),
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'status' => 3
        ]);

        // 勤怠一覧画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        // 自分の勤怠情報が表示されていることを確認
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('09:30');
        $response->assertSee('17:30');
        // 他人の勤怠情報は表示されていないことを確認
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     *
     * @return void
     */
    public function test_current_month_is_displayed_on_attendance_list()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 勤怠一覧画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        // 現在の月が表示されていることを確認
        $currentMonth = Carbon::now()->format('Y/m');
        $response->assertSee($currentMonth);
    }

    /**
     * 「前月」を押した時に表示月の前月の情報が表示される
     *
     * @return void
     */
    public function test_previous_month_button_displays_previous_month_data()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 前月の勤怠記録作成
        $lastMonth = Carbon::now()->subMonth();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $lastMonth->toDateString(),
            'clock_in' => '08:30',
            'clock_out' => '17:00',
            'status' => 3
        ]);

        // 前月の勤怠一覧画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/list?date=' . $lastMonth->format('Y-m'));

        $response->assertStatus(200);
        // 前月の情報が表示されていることを確認
        $response->assertSee($lastMonth->format('Y/m'));
        $response->assertSee('08:30');
        $response->assertSee('17:00');
    }

    /**
     * 「翌月」を押した時に表示月の翌月の情報が表示される
     *
     * @return void
     */
    public function test_next_month_button_displays_next_month_data()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // 翌月の勤怠記録作成
        $nextMonth = Carbon::now()->addMonth();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $nextMonth->toDateString(),
            'clock_in' => '09:15',
            'clock_out' => '18:30',
            'status' => 3
        ]);

        // 翌月の勤怠一覧画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/list?date=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        // 翌月の情報が表示されていることを確認
        $response->assertSee($nextMonth->format('Y/m'));
        $response->assertSee('09:15');
        $response->assertSee('18:30');
    }

    /**
     * 「詳細」を押すと、その日の勤怠詳細画面に遷移する
     *
     * @return void
     */
    public function test_detail_button_redirects_to_attendance_detail()
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

        // 勤怠一覧画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        // 詳細リンクが表示されていることを確認
        $response->assertSee('詳細');

        // 詳細画面への遷移を確認
        $detailResponse = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/' . $attendance->id);
        $detailResponse->assertStatus(200);
    }
}
