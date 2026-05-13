<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-01
     * 自分の勤怠情報が全て表示されている
     */
    public function test_user_can_see_only_own_attendance_list()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $today = Carbon::now();

        // ------------------------
        // 自分の勤怠
        // ------------------------
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today->copy()->subDays(2),
            'clock_in_at' => '2026-05-12 09:00:00',
            'clock_out_at' => '2026-05-12 18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today->copy()->subDays(1),
            'clock_in_at' => '2026-05-12 10:00:00',
            'clock_out_at' => '2026-05-12 19:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in_at' => '2026-05-12 11:00:00',
            'clock_out_at' => '2026-05-12 20:00:00',
        ]);

        // ------------------------
        // 他人の勤怠
        // ------------------------
        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => $today->copy()->subDays(5),
            'clock_in_at' => '2026-05-12 07:00:00',
            'clock_out_at' => '2026-05-12 16:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => $today->copy()->subDays(6),
            'clock_in_at' => '2026-05-12 08:00:00',
            'clock_out_at' => '2026-05-12 17:00:00',
        ]);

        // ------------------------
        // 一覧取得
        // ------------------------
        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        // ------------------------
        // 自分の勤怠は表示される
        // ------------------------
        $response->assertSee('09:00');
        $response->assertSee('10:00');
        $response->assertSee('11:00');

        // ------------------------
        // 他人の勤怠は表示されない
        // ------------------------
        $response->assertDontSee('07:00');
        $response->assertDontSee('08:00');
    }

    /**
     * TC-02
     * 現在の月が表示される
     */
    public function test_current_month_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $currentMonth = Carbon::now()->format('Y-m');

        $response->assertSee($currentMonth);
    }

    /**
     * TC-03
     * 前月ボタンで前月のデータが表示される
     */
    public function test_previous_month_attendance_is_displayed()
    {
        $user = User::factory()->create();

        $prevMonth = Carbon::now()->subMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $prevMonth,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $prevMonth->format('Y-m'));

        $response->assertStatus(200);

        $response->assertSee($prevMonth->format('Y-m'));
    }

    /**
     * TC-04
     * 翌月ボタンで翌月のデータが表示される
     */
    public function test_next_month_attendance_is_displayed()
    {
        $user = User::factory()->create();

        $nextMonth = Carbon::now()->addMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonth,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);

        $response->assertSee($nextMonth->format('Y-m'));
    }

    /**
     * TC-05
     * 詳細ボタンで勤怠詳細画面へ遷移できる
     */
    public function test_user_can_navigate_to_attendance_detail()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        // 詳細リンクが存在する
        $response->assertSee('/attendance/detail/' . $attendance->id);

        // 実際に遷移できる
        $detailResponse = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $detailResponse->assertStatus(200);
    }
}
