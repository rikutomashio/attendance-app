<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
    'attendance_id',
    'user_id',
    'requested_clock_in_at',
    'requested_clock_out_at',
    'status',
    'approved_by',
    'approved_at',
    'reason',
    ];

    public function attendance()
    {
    return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
    return $this->belongsTo(User::class);
    }

    public function approver()
    {
    return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending()
    {
    return $this->status === 'pending';
    }

    public function isApproved()
    {
    return $this->status === 'approved';
    }

    public function isRejected()
    {
    return $this->status === 'rejected';
    }

    protected $casts = [
    'requested_clock_in_at' => 'datetime',
    'requested_clock_out_at' => 'datetime',
    'approved_at' => 'datetime',
    ];
}
