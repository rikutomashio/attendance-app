<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class StaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者ログイン
     */
    private function adminLogin()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * PG10：スタッフ一覧表示
     */
    public function test_staff_list_is_displayed()
    {
        $this->adminLogin();

        $users = User::factory()->count(3)->create();

        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /**
     * PG11：スタッフ別勤怠一覧表示
     */
    public function test_staff_attendance_is_displayed()
    {
        $this->adminLogin();

        $user = User::factory()->create();

        $attendances = Attendance::factory()
            ->count(2)
            ->sequence(
                [
                    'work_date' => now()->subDay()->format('Y-m-d'),
                    'clock_in_at' => now()->subDay()->setTime(9, 0),
                    'clock_out_at' => now()->subDay()->setTime(18, 0),
                ],
                [
                    'work_date' => now()->format('Y-m-d'),
                    'clock_in_at' => now()->setTime(9, 0),
                    'clock_out_at' => now()->setTime(18, 0),
                ]
            )
            ->create([
                'user_id' => $user->id,
            ]);

        $response = $this->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);

        $response->assertSee($user->name);

        // 画面表示形式に合わせる
        $response->assertSee(
            $attendances[0]->work_date->format('m/d')
        );

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * PG11：前月表示
     */
    public function test_can_view_previous_month_attendance()
    {
        $this->adminLogin();

        $user = User::factory()->create();

        $date = Carbon::now()->subMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
        ]);

        $response = $this->get(
            "/admin/attendance/staff/{$user->id}?month=" . $date->format('Y-m')
        );

        $response->assertStatus(200);
    }

    /**
     * PG11：翌月表示
     */
    public function test_can_view_next_month_attendance()
    {
        $this->adminLogin();

        $user = User::factory()->create();

        $date = Carbon::now()->addMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
        ]);

        $response = $this->get(
            "/admin/attendance/staff/{$user->id}?month=" . $date->format('Y-m')
        );

        $response->assertStatus(200);
    }

    /**
     * PG11 → PG09：詳細遷移
     */
    public function test_can_access_attendance_detail()
    {
        $this->adminLogin();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        // 一度スタッフ別一覧を踏む
        $this->get("/admin/attendance/staff/{$user->id}");

        // 管理者用詳細画面
        $response = $this->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
    }
}
