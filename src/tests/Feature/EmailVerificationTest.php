<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;
use App\Notifications\CustomVerifyEmail;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後、認証メールが送信される
     *
     * @return void
     */
    public function test_verification_email_sent_after_registration()
    {
        // 通知をフェイクして送信をキャプチャ
        Notification::fake();

        // 会員登録を実行
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // ユーザーがデータベースに作成されていることを確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        // 作成されたユーザーを取得
        $user = User::where('email', 'test@example.com')->first();

        // 登録したメールアドレス宛に認証メールが送信されていることを確認
        Notification::assertSentTo(
            $user,
            CustomVerifyEmail::class
        );

        // 勤怠画面にリダイレクトされることを確認（ログイン後は一旦ホーム画面に行く）
        $response->assertRedirect('/attendance');
    }

    /**
     * メール認証完了画面で「認証はこちらから」ボタンを押すとメール認証サイトに遷移する
     *
     * @return void
     */
    public function test_verification_button_redirects_to_verification_site()
    {
        // テスト用ユーザー作成（未認証）
        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // ユーザーとしてログイン
        $this->actingAs($user);

        // メール認証画面にアクセス
        $response = $this->get('/email/verify');

        $response->assertStatus(200);
        // 「認証はこちらから」ボタンが表示されていることを確認
        $response->assertSee('認証はこちらから');

        // 「認証はこちらから」ボタンを押してメール認証サイト（認証コード入力画面）に遷移
        $verificationResponse = $this->get('/email/verification-manual');

        $verificationResponse->assertStatus(200);
        // メール認証サイトに遷移していることを確認
        $verificationResponse->assertSee('認証コード');
        $verificationResponse->assertSee('6桁の認証コード');
    }

    /**
     * メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
     *
     * @return void
     */
    public function test_email_verification_completion_redirects_to_attendance()
    {
        // テスト用ユーザー作成（未認証）
        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // ユーザーとしてログイン
        $this->actingAs($user);

        // 認証コードをキャッシュに設定
        $verificationCode = '123456';
        Cache::put("email_verification_code_{$user->id}", $verificationCode, now()->addMinutes(5));

        // 認証コード入力画面にアクセス
        $response = $this->get('/email/verification-manual');
        $response->assertStatus(200);

        // 正しい認証コードで認証を完了
        $verificationResponse = $this->post('/email/verify-code', [
            'verification_code' => $verificationCode,
        ]);

        // 勤怠登録画面に遷移することを確認
        $verificationResponse->assertRedirect('/attendance');

        // ユーザーのメール認証が完了していることを確認
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // 認証コードがキャッシュから削除されていることを確認
        $this->assertNull(Cache::get("email_verification_code_{$user->id}"));
    }
}
