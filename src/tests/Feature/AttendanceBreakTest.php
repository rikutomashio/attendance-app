<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 休憩開始テスト
     */
    public function test_user_can_start_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHour(),
            'clock_out_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post('/break/start'); // ← 修正

        $response->assertStatus(302);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
        ]);
    }

    /**
     * 休憩終了テスト
     */
    public function test_user_can_end_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(2),
            'clock_out_at' => null,
        ]);

        $break = $attendance->breakTimes()->create([
            'break_start_at' => now()->subHour(),
            'break_end_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post('/break/end'); // ← 修正

        $response->assertStatus(302);

        $this->assertNotNull($break->fresh()->break_end_at);
    }

    /**
     * 休憩は複数回可能
     */
    public function test_user_can_take_multiple_breaks()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(3),
            'clock_out_at' => null,
        ]);

        $this->actingAs($user);

        // 1回目
        $this->post('/break/start');
        $this->post('/break/end');

        // 2回目
        $this->post('/break/start');

        $this->assertDatabaseCount('break_times', 2);
    }
}
