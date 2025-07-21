<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前が未入力の場合、バリデーションエラーが発生する
     *
     * @return void
     */
    public function test_name_required_validation()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertSame('お名前を入力してください', session('errors')->first('name'));
    }

    /**
     * メールアドレスが未入力の場合、バリデーションエラーが発生する
     *
     * @return void
     */
    public function test_email_required_validation()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('メールアドレスを入力してください', session('errors')->first('email'));
    }

    /**
     * パスワードが8文字未満の場合、バリデーションエラーが発生する
     *
     * @return void
     */
    public function test_password_min_length_validation()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertSame('パスワードは8文字以上で入力してください', session('errors')->first('password'));
    }

    /**
     * パスワードと確認用パスワードが一致しない場合、バリデーションエラーが発生する
     *
     * @return void
     */
    public function test_password_confirmation_validation()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertSame('パスワードと一致しません', session('errors')->first('password'));
    }

    /**
     * パスワードが未入力の場合、バリデーションエラーが発生する
     *
     * @return void
     */
    public function test_password_required_validation()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertSame('パスワードを入力してください', session('errors')->first('password'));
    }

    /**
     * ユーザー登録が成功する
     *
     * @return void
     */
    public function test_user_registration_success()
    {
        $response = $this->withoutMiddleware(['verified'])->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect('/attendance');
    }
}
