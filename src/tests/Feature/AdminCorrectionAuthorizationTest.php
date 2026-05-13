<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCorrectionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 一般ユーザーは修正申請を承認できない()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $request = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->post("/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $request->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function 管理者だけが修正申請を承認できる()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        // 🔥 ここを修正
        $admin = Admin::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $request = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post("/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }
}
