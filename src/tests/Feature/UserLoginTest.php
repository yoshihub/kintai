<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションエラーが発生する
     *
     * @return void
     */
    public function test_email_required_validation()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('メールアドレスを入力してください', session('errors')->first('email'));
    }

    /**
     * パスワードが未入力の場合、バリデーションエラーが発生する
     *
     * @return void
     */
    public function test_password_required_validation()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertSame('パスワードを入力してください', session('errors')->first('password'));
    }

    /**
     * 存在しないユーザーでログインを試行した場合、バリデーションエラーが発生する
     *
     * @return void
     */
    public function test_invalid_credentials_validation()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('ログイン情報が登録されていません', session('errors')->first('email'));
    }
}
