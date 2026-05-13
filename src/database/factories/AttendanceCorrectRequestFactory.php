<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;

class AttendanceCorrectRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(), // ←これが重要
            'user_id' => User::factory(),             // ←これも

            'before_clock_in_at' => now(),
            'before_clock_out_at' => now(),
            'requested_clock_in_at' => now(),
            'requested_clock_out_at' => now(),

            'status' => 'pending',
            'reason' => 'テスト',
        ];
    }
}
