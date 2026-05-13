<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤時間より後だとエラー()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_at' => '2026-04-01 09:00:00',
            'clock_out_at' => '2026-04-01 18:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-01 12:00:00',
            'break_end_at' => '2026-04-01 13:00:00',
        ]);

        $response = $this->actingAs($user)->put("/attendance/update/{$attendance->id}", [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'note' => 'テスト',
            'breaks' => [
                [
                    'start_time' => '12:00',
                    'end_time' => '13:00',
                ]
            ],
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function 備考未入力だとエラー()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-01 12:00:00',
            'break_end_at' => '2026-04-01 13:00:00',
        ]);

        $response = $this->actingAs($user)->put("/attendance/update/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'note' => '', // 未入力
            'breaks' => [
                [
                    'start_time' => '12:00',
                    'end_time' => '13:00',
                ]
            ],
        ]);

        $response->assertSessionHasErrors(['note']);
    }

    /** @test */
    public function 正常データで修正申請が作成される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_at' => '2026-04-01 09:00:00',
            'clock_out_at' => '2026-04-01 18:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-01 12:00:00',
            'break_end_at' => '2026-04-01 13:00:00',
        ]);

        $response = $this->actingAs($user)->put("/attendance/update/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'note' => '修正申請テスト',
            'breaks' => [
                [
                    'start_time' => '12:00',
                    'end_time' => '13:00',
                ]
            ],
        ]);

        // リダイレクト確認
        $response->assertRedirect(route('attendance.list'));

        // DB確認
        $this->assertDatabaseHas('attendance_correct_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'reason' => '修正申請テスト',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後だとエラー()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_at' => '2026-04-01 09:00:00',
            'clock_out_at' => '2026-04-01 18:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-01 12:00:00',
            'break_end_at' => '2026-04-01 13:00:00',
        ]);

        $response = $this->actingAs($user)->put("/attendance/update/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'note' => 'テスト',
            'breaks' => [
                [
                    'start_time' => '19:00', // ❌ 退勤より後
                    'end_time' => '20:00',
                ]
            ],
        ]);

        $response->assertSessionHasErrors();
    }


    /** @test */
    public function 休憩終了時間が退勤時間より後だとエラー()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_at' => '2026-04-01 09:00:00',
            'clock_out_at' => '2026-04-01 18:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-01 12:00:00',
            'break_end_at' => '2026-04-01 13:00:00',
        ]);

        $response = $this->actingAs($user)->put("/attendance/update/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'note' => 'テスト',
            'breaks' => [
                [
                    'start_time' => '12:00',
                    'end_time' => '19:00', // ❌ 退勤より後
                ]
            ],
        ]);

        $response->assertSessionHasErrors();
    }
}
