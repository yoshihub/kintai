<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * その日になされた全ユーザーの勤怠情報が正確に確認できる
     *
     * @return void
     */
    public function test_all_users_attendance_displayed_correctly()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user1 = User::factory()->create(['name' => 'ユーザー1']);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);
        $user3 = User::factory()->create(['name' => 'ユーザー3']);

        // 今日の勤怠記録作成
        $today = Carbon::today();

        // ユーザー1の勤怠記録
        Attendance::create([
            'user_id' => $user1->id,
            'date' => $today->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // ユーザー2の勤怠記録
        Attendance::create([
            'user_id' => $user2->id,
            'date' => $today->toDateString(),
            'clock_in' => '08:30',
            'clock_out' => '17:30',
            'status' => 3
        ]);

        // ユーザー3は勤怠記録なし

        // 管理者として勤怠一覧画面にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);
        // 全ユーザーの名前が表示されていることを確認
        $response->assertSee('ユーザー1');
        $response->assertSee('ユーザー2');
        $response->assertSee('ユーザー3');
        // ユーザー1の勤怠情報が表示されていることを確認
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        // ユーザー2の勤怠情報が表示されていることを確認
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /**
     * 遷移した際に現在の日付が表示される
     *
     * @return void
     */
    public function test_current_date_displayed_on_transition()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 管理者として勤怠一覧画面にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);
        // 現在の日付が表示されていることを確認
        $currentDate = Carbon::today()->format('Y/m/d');
        $response->assertSee($currentDate);
    }

    /**
     * 「前日」を押した時に前の日の勤怠情報が表示される
     *
     * @return void
     */
    public function test_previous_day_data_displayed()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 昨日の勤怠記録作成
        $yesterday = Carbon::yesterday();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $yesterday->toDateString(),
            'clock_in' => '08:45',
            'clock_out' => '17:15',
            'status' => 3
        ]);

        // 管理者として昨日の勤怠一覧画面にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=' . $yesterday->format('Y-m-d'));

        $response->assertStatus(200);
        // 昨日の日付が表示されていることを確認
        $response->assertSee($yesterday->format('Y/m/d'));
        // 昨日の勤怠情報が表示されていることを確認
        $response->assertSee('08:45');
        $response->assertSee('17:15');
    }

    /**
     * 「翌日」を押した時に次の日の勤怠情報が表示される
     *
     * @return void
     */
    public function test_next_day_data_displayed()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 明日の勤怠記録作成
        $tomorrow = Carbon::tomorrow();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $tomorrow->toDateString(),
            'clock_in' => '09:15',
            'clock_out' => '18:45',
            'status' => 3
        ]);

        // 管理者として明日の勤怠一覧画面にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list?date=' . $tomorrow->format('Y-m-d'));

        $response->assertStatus(200);
        // 明日の日付が表示されていることを確認
        $response->assertSee($tomorrow->format('Y/m/d'));
        // 明日の勤怠情報が表示されていることを確認
        $response->assertSee('09:15');
        $response->assertSee('18:45');
    }
}
