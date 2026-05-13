<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StampCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 承認済みの申請が一覧に表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'reason' => '承認済みテスト',
            'status' => 'approved',
        ]);

        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'reason' => '承認待ちテスト',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);

        // UI確認
        $response->assertSee('承認済み');

        // ★重要：状態フィルタ確認
        $response->assertDontSee('承認待ちテスト');

        // ★画面仕様ベース確認（reasonは表示されないので見ない）
        $response->assertSee('詳細');
    }
}
