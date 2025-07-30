<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function test_error_when_start_time_is_after_end_time()
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

        // 出勤時間が退勤時間より後の修正申請を送信
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'start_time' => '19:00', // 退勤時間より後
            'end_time' => '18:00',
            'note' => '修正理由'
        ]);

        // バリデーションエラーが発生することを確認
        $response->assertSessionHasErrors('start_time');
        $this->assertSame('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first('start_time'));
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function test_error_when_break_start_time_is_after_end_time()
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

        // 休憩開始時間が退勤時間より後の修正申請を送信
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
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
        $response->assertSessionHasErrors('breaks.0.break_start');
        $this->assertSame('休憩時間が不適切な値です', session('errors')->first('breaks.0.break_start'));
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     *
     * @return void
     */
    public function test_error_when_break_end_time_is_after_end_time()
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

        // 休憩終了時間が退勤時間より後の修正申請を送信
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
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
        $response->assertSessionHasErrors('breaks.0.break_end');
        $this->assertSame('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first('breaks.0.break_end'));
    }

    /**
     * 備考欄が未入力の場合のエラーメッセージが表示される
     *
     * @return void
     */
    public function test_error_when_note_is_empty()
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

        // 備考が未入力の修正申請を送信
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'note' => '' // 未入力
        ]);

        // バリデーションエラーが発生することを確認
        $response->assertSessionHasErrors('note');
        $this->assertSame('備考を記入してください', session('errors')->first('note'));
    }



    /**
     * 「承認待ち」にログインユーザーが行った申請が全て表示されていること
     *
     * @return void
     */
    public function test_pending_requests_display_user_own_requests()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 勤怠記録作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        $otherAttendance = Attendance::create([
            'user_id' => $otherUser->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'status' => 3
        ]);

        // 自分の修正申請作成
        $myRequest = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'start_time' => '08:30',
            'end_time' => '17:30',
            'note' => '自分の申請',
            'status' => 'pending'
        ]);

        // 他人の修正申請作成
        $otherRequest = StampCorrectionRequest::create([
            'user_id' => $otherUser->id,
            'attendance_id' => $otherAttendance->id,
            'start_time' => '09:15',
            'end_time' => '18:15',
            'note' => '他人の申請',
            'status' => 'pending'
        ]);

        // 申請一覧画面（承認待ち）にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        // 自分の申請が表示されていることを確認
        $response->assertSee('自分の申請');
        // 他人の申請は表示されていないことを確認
        $response->assertDontSee('他人の申請');
    }

    /**
     * 「承認済み」に管理者が承認した修正申請が全て表示されている
     *
     * @return void
     */
    public function test_approved_requests_display_approved_requests()
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

        // 承認済みの修正申請作成
        $approvedRequest = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'start_time' => '08:30',
            'end_time' => '17:30',
            'note' => '承認済みの申請',
            'status' => 'approved'
        ]);

        // 承認待ちの修正申請作成
        $pendingRequest = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'start_time' => '09:15',
            'end_time' => '18:15',
            'note' => '承認待ちの申請',
            'status' => 'pending'
        ]);

        // 申請一覧画面（承認済み）にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        // 承認済みの申請が表示されていることを確認
        $response->assertSee('承認済みの申請');
        // 承認待ちの申請は表示されていないことを確認
        $response->assertDontSee('承認待ちの申請');
    }

    /**
     * 各申請の「詳細」を押すと申請詳細画面に遷移する
     *
     * @return void
     */
    public function test_detail_button_redirects_to_request_detail()
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

        // 修正申請作成
        $request = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'start_time' => '08:30',
            'end_time' => '17:30',
            'note' => 'テスト申請',
            'status' => 'pending'
        ]);

        // 申請一覧画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        // 詳細リンクが表示されていることを確認
        $response->assertSee('詳細');

        // 詳細画面への遷移を確認（一般ユーザーは勤怠詳細画面に遷移）
        $detailResponse = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance/' . $attendance->id);
        $detailResponse->assertStatus(200);
    }
}
