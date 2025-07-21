<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;

class AdminCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 承認待ちの修正申請が全て表示されている
     *
     * @return void
     */
    public function test_pending_correction_requests_displayed_for_admin()
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

        // 勤怠記録作成
        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 承認待ちの修正申請作成
        $pendingRequest1 = StampCorrectionRequest::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'start_time' => '08:30',
            'end_time' => '17:30',
            'note' => 'ユーザー1の承認待ち申請',
            'status' => 'pending'
        ]);

        $pendingRequest2 = StampCorrectionRequest::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'start_time' => '09:15',
            'end_time' => '18:15',
            'note' => 'ユーザー2の承認待ち申請',
            'status' => 'pending'
        ]);

        // 承認済みの修正申請も作成（表示されないことを確認）
        $approvedRequest = StampCorrectionRequest::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'note' => '承認済み申請',
            'status' => 'approved'
        ]);

        // 管理者として修正申請一覧画面（承認待ち）にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        // 全ユーザーの承認待ち申請が表示されていることを確認
        $response->assertSee('ユーザー1');
        $response->assertSee('ユーザー1の承認待ち申請');
        $response->assertSee('ユーザー2');
        $response->assertSee('ユーザー2の承認待ち申請');
        // 承認済み申請は表示されていないことを確認
        $response->assertDontSee('承認済み申請');
    }

    /**
     * 承認済みの修正申請が全て表示されている
     *
     * @return void
     */
    public function test_approved_correction_requests_displayed_for_admin()
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

        // 勤怠記録作成
        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 承認済みの修正申請作成
        $approvedRequest1 = StampCorrectionRequest::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'start_time' => '08:30',
            'end_time' => '17:30',
            'note' => 'ユーザー1の承認済み申請',
            'status' => 'approved'
        ]);

        $approvedRequest2 = StampCorrectionRequest::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'start_time' => '09:15',
            'end_time' => '18:15',
            'note' => 'ユーザー2の承認済み申請',
            'status' => 'approved'
        ]);

        // 承認待ちの修正申請も作成（表示されないことを確認）
        $pendingRequest = StampCorrectionRequest::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'note' => '承認待ち申請',
            'status' => 'pending'
        ]);

        // 管理者として修正申請一覧画面（承認済み）にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        // 全ユーザーの承認済み申請が表示されていることを確認
        $response->assertSee('ユーザー1');
        $response->assertSee('ユーザー1の承認済み申請');
        $response->assertSee('ユーザー2');
        $response->assertSee('ユーザー2の承認済み申請');
        // 承認待ち申請は表示されていないことを確認
        $response->assertDontSee('承認待ち申請');
    }

    /**
     * 修正申請の詳細内容が正しく表示されている
     *
     * @return void
     */
    public function test_correction_request_details_displayed_correctly()
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

        // 修正申請作成
        $correctionRequest = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'start_time' => '08:30',
            'end_time' => '17:30',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '13:00']
            ],
            'note' => '交通機関の遅延により修正をお願いします',
            'status' => 'pending'
        ]);

        // 管理者として修正申請の詳細画面にアクセス
        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/approve/' . $correctionRequest->id);

        $response->assertStatus(200);
        // 申請内容が正しく表示されていることを確認
        $response->assertSee('テストユーザー');
        $response->assertSee('2024年');
        $response->assertSee('6月15日');
        $response->assertSee('08:30');
        $response->assertSee('17:30');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('交通機関の遅延により修正をお願いします');
    }

    /**
     * 修正申請の承認処理が正しく行われる
     *
     * @return void
     */
    public function test_correction_request_approval_process()
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

        // 修正申請作成
        $correctionRequest = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'start_time' => '08:30',
            'end_time' => '17:30',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '13:00']
            ],
            'note' => '修正理由',
            'status' => 'pending'
        ]);

        // 管理者として修正申請詳細画面で「承認」ボタンを押す
        $response = $this->actingAs($admin, 'admin')->post('/stamp_correction_request/approve/' . $correctionRequest->id);

        // リダイレクトされることを確認
        $response->assertRedirect('/stamp_correction_request/list');

        // 修正申請が承認されていることを確認
        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $correctionRequest->id,
            'status' => 'approved'
        ]);

        // 勤怠情報が更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'note' => '修正理由'
        ]);

        // 休憩時間が更新されていることを確認
        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00'
        ]);
    }
}
