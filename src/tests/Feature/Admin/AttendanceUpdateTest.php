<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * FN040-1：勤怠・休憩の正常更新（複数休憩）
     */
    public function test_勤怠と複数休憩が正常に更新される()
    {
        $admin = Admin::factory()->create();

        $attendance = Attendance::factory()->create();

        // 初期データ（既存休憩）
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-01 12:00:00',
            'break_end_at' => '2026-04-01 13:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'start_time' => '12:00',
                        'end_time' => '13:00',
                    ],
                    [
                        'start_time' => '15:00',
                        'end_time' => '15:30',
                    ],
                ],
                'reason' => '更新テスト',
            ]);

        $response->assertRedirect(route('admin.attendance.show', $attendance->id));

        $attendance = $attendance->fresh();

        // 勤怠更新確認
        $this->assertEquals('更新テスト', $attendance->reason);

        // 休憩が2件に再生成されている
        $this->assertCount(2, $attendance->breakTimes);
    }

    /**
     * FN040-2：休憩は完全に置換される（削除→再作成）
     */
    public function test_既存休憩は削除され新しいものに置換される()
    {
        $admin = Admin::factory()->create();

        $attendance = Attendance::factory()->create();

        // 既存休憩
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-01 10:00:00',
            'break_end_at' => '2026-04-01 11:00:00',
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'start_time' => '12:00',
                        'end_time' => '13:00',
                    ],
                ],
                'reason' => '置換テスト',
            ]);

        $attendance = $attendance->fresh();

        // 1件だけ残る（完全置換）
        $this->assertCount(1, $attendance->breakTimes);

        $this->assertEquals('12:00:00', $attendance->breakTimes->first()->break_start_at->format('H:i:s'));
    }

    /**
     * FN040-3：休憩なしでも更新可能
     */
    public function test_休憩なしでも正常更新できる()
    {
        $admin = Admin::factory()->create();

        $attendance = Attendance::factory()->create();

        // 既存休憩あり
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-01 12:00:00',
            'break_end_at' => '2026-04-01 13:00:00',
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [],
                'reason' => '休憩なし更新',
            ]);

        $attendance = $attendance->fresh();

        // 休憩は全削除される
        $this->assertCount(0, $attendance->breakTimes);

        $this->assertEquals('休憩なし更新', $attendance->reason);
    }
}
