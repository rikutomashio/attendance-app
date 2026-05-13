<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StampCorrectionRequestListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_pending_requests()
    {
        $admin = Admin::factory()->create();

        $user1 = User::factory()->create(['name' => 'ユーザー1']);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
        ]);

        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'status' => 'pending',
        ]);

        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'status' => 'pending',
        ]);

        // 混在データ
        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/stamp_correction_request/list');

        $response->assertStatus(200);

        $response->assertSee('申請一覧（管理者）');
        $response->assertSee('ユーザー1');
        $response->assertSee('ユーザー2');
        $response->assertSee('承認待ち');

        $this->assertEquals(
            2,
            AttendanceCorrectRequest::where('status', 'pending')->count()
        );
    }

    public function test_admin_can_view_approved_requests()
    {
        $admin = Admin::factory()->create();

        $user = User::factory()->create(['name' => 'テストユーザー']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        // 混在
        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/stamp_correction_request/list');

        $response->assertStatus(200);

        $response->assertSee('申請一覧（管理者）');
        $response->assertSee('承認済み');
        $response->assertSee('テストユーザー');

        $this->assertEquals(
            1,
            AttendanceCorrectRequest::where('status', 'approved')->count()
        );
    }

    public function test_admin_can_approve_request_and_update_attendance()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $request = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'requested_clock_in_at' => now()->setTime(10, 0),
            'requested_clock_out_at' => now()->setTime(19, 0),
        ]);

        // 🔥 念のためリレーション整理
        $request->breakTimes()->delete();

        // 🔥 ここが今回の最重要修正（route使用）
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.request.approve.execute', $request->id));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // ① ステータス更新確認
        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        // ② 勤怠更新確認
        $updated = $attendance->fresh();

        $this->assertEquals(
            '10:00:00',
            $updated->clock_in_at->format('H:i:s')
        );

        $this->assertEquals(
            '19:00:00',
            $updated->clock_out_at->format('H:i:s')
        );

        // ③ breakTimesが空であること確認
        $this->assertCount(0, $updated->breakTimes);
    }
}
