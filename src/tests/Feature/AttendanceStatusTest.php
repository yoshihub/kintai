<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外の場合、勤務ステータスが正しく表示される
     *
     * @return void
     */
    public function test_status_shows_correctly_when_not_working()
    {
        // テスト用ユーザー作成
        /** @var User $user */
        $user = User::factory()->create();

        // ログインして勤務画面にアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }
}
