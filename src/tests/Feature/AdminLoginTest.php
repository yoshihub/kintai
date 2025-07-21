<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションエラーが発生する
     *
     * @return void
     */
    public function test_email_required_validation()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/admin/login', [
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
        $response = $this->withoutMiddleware(['verified'])->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertSame('パスワードを入力してください', session('errors')->first('password'));
    }

    /**
     * 存在しない管理者でログインを試行した場合、バリデーションエラーが発生する
     *
     * @return void
     */
    public function test_invalid_credentials_validation()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/admin/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('ログイン情報が登録されていません', session('errors')->first('email'));
    }
}
