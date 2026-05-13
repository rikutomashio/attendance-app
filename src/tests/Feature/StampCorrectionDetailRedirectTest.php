<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StampCorrectionDetailRedirectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 申請一覧から詳細を押すと勤怠詳細に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $request = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // 一覧画面
        $listResponse = $this->actingAs($user)
            ->get('/stamp_correction_request/list');

        $listResponse->assertStatus(200);

        // 詳細リンク存在確認（UI保証）
        $listResponse->assertSee("/attendance/detail/{$attendance->id}");

        // 実際にクリック相当の遷移
        $detailResponse = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $detailResponse->assertStatus(200);

        // 最低限の画面保証
        $detailResponse->assertSee('勤怠詳細');
    }
}
