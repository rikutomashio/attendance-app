<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceViewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外ステータス表示
     */
    public function test_off_status_is_displayed()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /**
     * 出勤中ステータス表示
     */
    public function test_working_status_is_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHour(),
            'clock_out_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * 休憩中ステータス表示
     */
    public function test_break_status_is_displayed()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(2),
            'clock_out_at' => null,
        ]);

        $attendance->breakTimes()->create([
            'break_start_at' => now()->subMinutes(30),
            'break_end_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * 退勤済ステータス表示
     */
    public function test_done_status_is_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(8),
            'clock_out_at' => now()->subHour(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    /**
     * 出勤ボタン表示（勤務外）
     */
    public function test_clock_in_button_is_visible_when_off()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('出勤');
    }

    /**
     * 休憩入ボタン表示（出勤中）
     */
    public function test_break_start_button_is_visible_when_working()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHour(),
            'clock_out_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('休憩入');
    }

    /**
     * 休憩戻ボタン表示（休憩中）
     */
    public function test_break_end_button_is_visible_when_on_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(2),
            'clock_out_at' => null,
        ]);

        $attendance->breakTimes()->create([
            'break_start_at' => now()->subMinutes(30),
            'break_end_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('休憩戻');
    }

    /**
     * 退勤ボタン表示（出勤中）
     */
    public function test_clock_out_button_is_visible_when_working()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHour(),
            'clock_out_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('退勤');
    }
}
