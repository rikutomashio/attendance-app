<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start_at',
        'break_end_at',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public static function isOnBreak($attendanceId)
    {
        return self::where('attendance_id', $attendanceId)
            ->whereNull('break_end_at')
            ->exists();
    }

    protected $casts = [
        'break_start_at' => 'datetime',
        'break_end_at' => 'datetime',
    ];
}
