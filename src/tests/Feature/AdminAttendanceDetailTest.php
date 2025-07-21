<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細画面に表示されるデータが選択したものになっている
     *
     * @return void
     */
    public function test_attendance_detail_displays_selected_data()
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
            'date' => '2024-06-15',
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

        // 管理者として勤怠詳細画面にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        // 選択したデータの内容が表示されていることを確認
        $response->assertSee('テストユーザー');
        $response->assertSee('2024年');
        $response->assertSee('6月15日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function test_error_when_start_time_is_after_end_time()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user = User::factory()->create();

        // 勤怠記録作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 出勤時間が退勤時間より後の修正を送信
        $response = $this->actingAs($admin, 'admin')->put('/admin/attendance/' . $attendance->id, [
            'start_time' => '19:00', // 退勤時間より後
            'end_time' => '18:00',
            'note' => '修正理由'
        ]);

        // バリデーションエラーが発生することを確認
        $response->assertSessionHasErrors('time_order');
        $this->assertSame('出勤時間もしくは退勤時間が不適切な値です。', session('errors')->first('time_order'));
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function test_error_when_break_start_time_is_after_end_time()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user = User::factory()->create();

        // 勤怠記録作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 休憩開始時間が退勤時間より後の修正を送信
        $response = $this->actingAs($admin, 'admin')->put('/admin/attendance/' . $attendance->id, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'breaks' => [
                [
                    'break_start' => '19:00', // 退勤時間より後
                    'break_end' => '20:00'
                ]
            ],
            'note' => '修正理由'
        ]);

        // バリデーションエラーが発生することを確認
        $response->assertSessionHasErrors('break_time');
        $this->assertSame('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first('break_time'));
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function test_error_when_break_end_time_is_after_end_time()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user = User::factory()->create();

        // 勤怠記録作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 休憩終了時間が退勤時間より後の修正を送信
        $response = $this->actingAs($admin, 'admin')->put('/admin/attendance/' . $attendance->id, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'breaks' => [
                [
                    'break_start' => '12:00',
                    'break_end' => '19:00' // 退勤時間より後
                ]
            ],
            'note' => '修正理由'
        ]);

        // バリデーションエラーが発生することを確認
        $response->assertSessionHasErrors('break_time');
        $this->assertSame('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first('break_time'));
    }

    /**
     * 備考欄が未入力の場合のエラーメッセージが表示される
     *
     * @return void
     */
    public function test_error_when_note_is_empty()
    {
        // テスト用管理者作成
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // テスト用ユーザー作成
        $user = User::factory()->create();

        // 勤怠記録作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 備考が未入力の修正を送信
        $response = $this->actingAs($admin, 'admin')->put('/admin/attendance/' . $attendance->id, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'note' => '' // 未入力
        ]);

        // バリデーションエラーが発生することを確認
        $response->assertSessionHasErrors('note');
        $this->assertSame('備考を記入してください', session('errors')->first('note'));
    }
}
