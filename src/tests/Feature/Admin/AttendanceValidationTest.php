<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\Attendance;

class AttendanceValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラー()
    {
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.attendance.show', $attendance->id))
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'breaks' => [],
                'reason' => 'テスト',
            ]);

        $response->assertRedirect(route('admin.attendance.show', $attendance->id));

        $response->assertSessionHasErrors([
            'clock_in',
        ]);
    }

    /** @test */
    public function 休憩開始が不正な場合エラー()
    {
        $admin = Admin::factory()->create();

        $attendance = Attendance::factory()->create([
            'clock_in_at' => '2024-01-01 09:00:00',
            'clock_out_at' => '2024-01-01 18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.attendance.show', $attendance->id))
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'start_time' => '19:00',
                        'end_time' => '17:00',
                    ]
                ],
                'reason' => 'テスト',
            ]);

        $response->assertRedirect(route('admin.attendance.show', $attendance->id));

        $response->assertSessionHasErrors([
            'breaks.0.start_time',
        ]);
    }

    /** @test */
    public function 休憩終了が不正な場合エラー()
    {
        $admin = Admin::factory()->create();

        $attendance = Attendance::factory()->create([
            'clock_in_at' => '2024-01-01 09:00:00',
            'clock_out_at' => '2024-01-01 18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.attendance.show', $attendance->id))
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'start_time' => '10:00',
                        'end_time' => '19:00',
                    ]
                ],
                'reason' => 'テスト',
            ]);

        $response->assertRedirect(route('admin.attendance.show', $attendance->id));

        $response->assertSessionHasErrors([
            'breaks.0.end_time',
        ]);
    }

    /** @test */
    public function 備考未入力でエラー()
    {
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.attendance.show', $attendance->id))
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [],
                'reason' => '',
            ]);

        $response->assertRedirect(route('admin.attendance.show', $attendance->id));

        $response->assertSessionHasErrors([
            'reason',
        ]);
    }
}
