<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // 一般ユーザーのみ取得
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {

            // 直近30日分
            for ($i = 0; $i < 30; $i++) {

                $date = Carbon::today()->subDays($i);

                // 勤怠作成
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date,

                    'clock_in_at' => Carbon::parse($date->format('Y-m-d') . ' 09:00:00'),

                    'clock_out_at' => Carbon::parse($date->format('Y-m-d') . ' 18:00:00'),

                    'reason' => null,
                ]);

                // 休憩
                BreakTime::create([
                    'attendance_id' => $attendance->id,

                    'break_start_at' => Carbon::parse($date->format('Y-m-d') . ' 12:00:00'),

                    'break_end_at' => Carbon::parse($date->format('Y-m-d') . ' 13:00:00'),
                ]);
            }
        }
    }
}
