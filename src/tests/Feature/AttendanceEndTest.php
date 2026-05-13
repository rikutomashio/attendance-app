<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceEndTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 退勤成功テスト
     */
    public function test_user_can_clock_out_successfully()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(8),
            'clock_out_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance/end');

        $response->assertStatus(302);

        $this->assertNotNull($attendance->fresh()->clock_out_at);
    }

    /**
     * 退勤は1日1回のみ
     */
    public function test_user_cannot_clock_out_twice()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(8),
            'clock_out_at' => now()->subHour(),
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance/end');

        $response->assertStatus(302);

        $this->assertDatabaseCount('attendances', 1);
    }

    /**
     * 退勤には出勤が必要
     */
    public function test_user_cannot_clock_out_without_clock_in()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/attendance/end');

        $response->assertStatus(302);

        $this->assertDatabaseMissing('attendances', [
            'user_id' => $user->id,
            'clock_out_at' => null,
        ]);
    }
}
