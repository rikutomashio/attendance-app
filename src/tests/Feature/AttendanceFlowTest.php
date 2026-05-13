<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1日の勤怠フロー
     * 出勤 → 休憩 → 戻る → 退勤
     */
    public function test_full_attendance_flow()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // -------------------
        // 出勤
        // -------------------
        $this->post('/attendance/start')->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);

        // 🔥 出勤直後の状態チェック（追加）
        $attendance = \App\Models\Attendance::where('user_id', $user->id)->first();
        $this->assertNotNull($attendance->clock_in_at);
        $this->assertNull($attendance->clock_out_at);

        // -------------------
        // 休憩開始
        // -------------------
        $this->post('/break/start')->assertStatus(302);

        $this->assertDatabaseCount('break_times', 1);

        // -------------------
        // 休憩終了
        // -------------------
        $this->post('/break/end')->assertStatus(302);

        $break = \App\Models\BreakTime::first();
        $this->assertNotNull($break->break_end_at);

        // -------------------
        // 再度休憩（複数回確認）
        // -------------------
        $this->post('/break/start')->assertStatus(302);
        $this->assertDatabaseCount('break_times', 2);

        // -------------------
        // 退勤
        // -------------------
        $this->post('/attendance/end')->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);

        $attendance = \App\Models\Attendance::where('user_id', $user->id)->first();

        $this->assertNotNull($attendance->clock_in_at);
        $this->assertNotNull($attendance->clock_out_at);

        // 🔥 レコード数保証（追加）
        $this->assertDatabaseCount('attendances', 1);
    }
}
