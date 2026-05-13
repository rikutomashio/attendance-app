<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者は勤怠詳細を閲覧できる()
    {
        $admin = Admin::factory()->create();

        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-01',
            'clock_in_at' => '2024-01-01 09:00:00',
            'clock_out_at' => '2024-01-01 18:00:00',
            'reason' => '通常勤務',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2024-01-01 12:00:00',
            'break_end_at' => '2024-01-01 13:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);

        $response->assertSee('テスト太郎');

        // 日付表示（UI変更対応）
        $response->assertSee('2024年');
        $response->assertSee('1月1日');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('通常勤務');
        $response->assertSee('修正');
    }

    /** @test */
    public function 承認待ちの勤怠は編集できない()
    {
        $admin = Admin::factory()->create();

        $attendance = Attendance::factory()->create();

        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('承認待ちのため修正はできません。');
    }

    /** @test */
    public function 未ログインはアクセスできない()
    {
        $attendance = Attendance::factory()->create();

        $response = $this->get(route('admin.attendance.show', $attendance->id));

        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function 一般ユーザーはアクセスできない()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function 存在しないIDは404になる()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/99999');

        $response->assertStatus(404);
    }
}
