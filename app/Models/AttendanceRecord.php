<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $table = 'attendance_records';
    protected $primaryKey = 'attendance_id';

    // The attendance_records table only has logged_at (DB default CURRENT_TIMESTAMP).
    public $timestamps = false;

    /** Statuses a teacher can mark from the attendance sheet. */
    public const STATUSES = ['Present', 'Late', 'Absent'];

    protected $fillable = [
        'schedule_id', 'student_id', 'attendance_date', 'status', 'logged_by',
    ];

    // attendance_date is kept as a plain 'Y-m-d' string (not cast to Carbon) so that
    // query matching in updateOrCreate() stays consistent with the stored value.
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
        return $this->belongsTo(User::class, 'logged_by', 'user_id');
    }
}
