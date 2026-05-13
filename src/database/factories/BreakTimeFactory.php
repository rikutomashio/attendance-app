<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

class BreakTimeFactory extends Factory
{
    public function definition()
    {
        $start = now()->setTime(12, 0);

        return [
            'attendance_id' => Attendance::factory(),
            'break_start_at' => $start,
            'break_end_at' => $start->copy()->addHour(),
        ];
    }
}
