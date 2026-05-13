<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
        'reason',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }

    public static function today($userId)
    {
        return self::where('user_id', $userId)
            ->whereDate('work_date', Carbon::today())
            ->first();
    }

    protected $casts = [
        'work_date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
    ];

    public function getTotalBreakSecondsAttribute()
    {
        return $this->breakTimes->reduce(function ($carry, $break) {
            if ($break->break_start_at && $break->break_end_at) {
                return $carry + $break->break_end_at->diffInSeconds($break->break_start_at);
            }
            return $carry;
        }, 0);
    }

    public function getClockInFormattedAttribute()
    {
        return $this->clock_in_at
            ? $this->clock_in_at->format('H:i')
            : null;
    }

    public function getClockOutFormattedAttribute()
    {
        return $this->clock_out_at
            ? $this->clock_out_at->format('H:i')
            : null;
    }

    public function getTotalBreakFormattedAttribute()
    {
        return $this->total_break_seconds
            ? gmdate('H:i', $this->total_break_seconds)
            : null;
    }

    public function getWorkSecondsAttribute()
    {
        // 出勤 or 退勤がない場合は0
        if (!$this->clock_in_at || !$this->clock_out_at) {
            return 0;
        }

        // 総勤務秒数
        $workSeconds = $this->clock_out_at->diffInSeconds($this->clock_in_at);

        // 休憩秒数を引く
        return $workSeconds - $this->total_break_seconds;
    }

    public function getWorkFormattedAttribute()
    {
        return $this->work_seconds
            ? gmdate('H:i', $this->work_seconds)
            : null;
    }
}
