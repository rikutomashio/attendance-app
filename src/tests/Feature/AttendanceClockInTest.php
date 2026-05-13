<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤成功テスト
     */
    public function test_user_can_clock_in_successfully()
    {
        // ① Arrange（ユーザー作成＆ログイン）
        $user = User::factory()->create();

        $this->actingAs($user);

        // ② Act（出勤リクエスト）
        $response = $this->post('/attendance/start');

        // ③ Assert（リダイレクト or 正常レスポンス）
        $response->assertStatus(302);

        // ④ DB確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);

        $attendance = \App\Models\Attendance::where('user_id', $user->id)->first();

        $this->assertNotNull($attendance->clock_in_at);
        $this->assertNull($attendance->clock_out_at);
    }

    /**
     * 出勤は1日1回のみ
     */
    public function test_user_cannot_clock_in_twice_a_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1回目の出勤
        $this->post('/attendance/start');

        // 2回目の出勤
        $response = $this->post('/attendance/start');

        // 失敗 or リダイレクト（実装次第）
        $response->assertStatus(302);

        // レコードは1件のみ
        $this->assertEquals(
            1,
            \App\Models\Attendance::where('user_id', $user->id)->count()
        );
    }
}
