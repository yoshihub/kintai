<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 現在の日時情報が正しい形式で出力されている
     *
     * @return void
     */
    public function test_current_datetime_format_is_correct()
    {
        // テスト用ユーザー作成
        $user = User::factory()->create();

        // ログインしてトップページにアクセス
        $response = $this->withoutMiddleware(['verified'])->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        // 現在の日付が正しい形式（Y年n月j日）で表示されているかテスト
        $currentDate = Carbon::now()->format('Y年n月j日');
        $response->assertSee($currentDate);

        // 現在の時刻が正しい形式（H:i）で表示されているかテスト
        $currentTime = Carbon::now()->format('H:i');
        $response->assertSee($currentTime);
    }
}
