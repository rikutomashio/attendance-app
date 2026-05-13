<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        $date = Carbon::now()->subDays(rand(0, 30));

        return [
            'user_id' => User::factory(), // ← 超重要
            'work_date' => $date = Carbon::today(),
            'clock_in_at' => $date->copy()->setTime(9, 0),
            'clock_out_at' => $date->copy()->setTime(18, 0),
            'reason' => 'テスト',
        ];
    }
}
