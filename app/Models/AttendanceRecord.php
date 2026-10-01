<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Table from 2026_09_20_000019: one row per student per class period per day (unique), logged by a user. */
class AttendanceRecord extends Model
{
    public const STATUSES = ['Present', 'Late', 'Absent', 'Excused'];

    protected $table = 'attendance_records';
    protected $primaryKey = 'attendance_id';
    public $timestamps = false;

    // attendance_date stays a plain 'Y-m-d' string (no date cast) so lookups match the stored value on MySQL and SQLite alike.
    protected $fillable = ['schedule_id', 'student_id', 'attendance_date', 'status', 'remarks', 'logged_by', 'logged_at'];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
