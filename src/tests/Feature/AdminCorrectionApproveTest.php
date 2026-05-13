<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminCorrectionApproveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者が修正申請を承認できる()
    {
        // ------------------------
        // 一般ユーザー作成
        // ------------------------
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $request = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'reason' => '承認テスト',
            'status' => 'pending',
        ]);

        // ------------------------
        // 管理者作成（adminsテーブル）
        // ------------------------
        $admin = Admin::factory()->create();

        // ------------------------
        // 承認処理
        // ------------------------
        $response = $this->actingAs($admin, 'admin')
            ->post("/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(302);

        // ------------------------
        // DB確認
        // ------------------------
        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);
    }
}
