<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequestBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correct_request_id',

        // 🔥 before
        'before_break_start_at',
        'before_break_end_at',

        // after
        'requested_break_start_at',
        'requested_break_end_at',
    ];

    protected $casts = [
        'before_break_start_at' => 'datetime',
        'before_break_end_at' => 'datetime',
        'requested_break_start_at' => 'datetime',
        'requested_break_end_at' => 'datetime',
    ];

    /**
     * 修正申請とのリレーション
     */
    public function attendanceCorrectRequest()
    {
        return $this->belongsTo(AttendanceCorrectRequest::class);
    }
}
