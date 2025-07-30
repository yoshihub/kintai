<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminUserInfoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者が全スタッフの氏名とメールアドレスの「氏名」「メールアドレス」を確認できる
     *
     * @return void
     */
    public function test_admin_can_view_all_staff_name_and_email()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user1 = User::factory()->create([
            'name' => '田中太郎',
            'email' => 'tanaka@example.com'
        ]);
        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com'
        ]);
        $user3 = User::factory()->create([
            'name' => '山田次郎',
            'email' => 'yamada@example.com'
        ]);

        // 管理者としてスタッフ一覧画面にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/staff/list');

        $response->assertStatus(200);
        // 全スタッフの氏名とメールアドレスが表示されていることを確認
        $response->assertSee('田中太郎');
        $response->assertSee('tanaka@example.com');
        $response->assertSee('佐藤花子');
        $response->assertSee('sato@example.com');
        $response->assertSee('山田次郎');
        $response->assertSee('yamada@example.com');
    }

    /**
     * ユーザーの勤怠情報が正しく表示される
     *
     * @return void
     */
    public function test_user_attendance_data_displayed_correctly()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 勤怠記録作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 休憩時間記録作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00'
        ]);

        // 管理者として選択したユーザーの勤怠一覧ページにアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id);

        $response->assertStatus(200);
        // ユーザー名が表示されていることを確認
        $response->assertSee('テストユーザー');
        // 勤怠情報が正確に表示されていることを確認
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00'); // 休憩時間
    }

    /**
     * 「前月」を押した時に表示月の前月の情報が表示される
     *
     * @return void
     */
    public function test_previous_month_data_displayed()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 前月の勤怠記録作成
        $previousMonth = now()->subMonth();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $previousMonth->toDateString(),
            'clock_in' => '08:30',
            'clock_out' => '17:30',
            'status' => 3
        ]);

        // 管理者として前月のユーザー勤怠一覧ページにアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?date=' . $previousMonth->format('Y-m'));

        $response->assertStatus(200);
        // 前月の月表示が表示されていることを確認
        $response->assertSee($previousMonth->format('Y/m'));
        // 前月の勤怠情報が表示されていることを確認
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    /**
     * 「翌月」を押した時に表示月の翌月の情報が表示される
     *
     * @return void
     */
    public function test_next_month_data_displayed()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 翌月の勤怠記録作成
        $nextMonth = now()->addMonth();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $nextMonth->toDateString(),
            'clock_in' => '09:15',
            'clock_out' => '18:45',
            'status' => 3
        ]);

        // 管理者として翌月のユーザー勤怠一覧ページにアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?date=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        // 翌月の月表示が表示されていることを確認
        $response->assertSee($nextMonth->format('Y/m'));
        // 翌月の勤怠情報が表示されていることを確認
        $response->assertSee('09:15');
        $response->assertSee('18:45');
    }

    /**
     * 「詳細」を押すと、その日の勤怠詳細画面に遷移する
     *
     * @return void
     */
    public function test_detail_button_redirects_to_attendance_detail()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // 勤怠記録作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 管理者としてユーザー勤怠一覧ページにアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id);

        $response->assertStatus(200);
        // 詳細リンクが表示されていることを確認
        $response->assertSee('詳細');

        // 勤怠詳細画面への遷移を確認
        $detailResponse = $this->actingAs($admin, 'admin')->get('/admin/attendance/' . $attendance->id);
        $detailResponse->assertStatus(200);
        // 詳細画面の内容確認
        $detailResponse->assertSee('テストユーザー');
        $detailResponse->assertSee('勤怠詳細');
    }
}
