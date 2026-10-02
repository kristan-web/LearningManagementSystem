<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduleEvent extends Model
{
    protected $table = 'schedule_events';
    protected $primaryKey = 'event_id';

    // The schedule_events table only has created_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'created_by_role', 'created_by_id', 'section_id', 'subject_id', 'schedule_id',
        'title', 'description', 'event_type', 'start_datetime', 'end_datetime', 'status',
        'meeting_link', 'meeting_provider', 'meeting_status',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function section()
    {
        return $this->belongsTo(ClassSection::class, 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    /** A Meeting event is "live" once a teacher has started it and hasn't ended it yet. */
    public function isLive(): bool
    {
        return $this->event_type === 'Meeting' && $this->meeting_status === 'Live';
    }

    /**
     * Scheduled, not-yet-started events visible to a student: their own personal
     * events, plus any events scoped to their active section.
     */
    public function scopeUpcomingForStudent(Builder $query, int $studentId, ?int $sectionId): Builder
    {
        return $query
            ->where('status', 'Scheduled')
            ->where('start_datetime', '>=', now())
            ->where(function (Builder $q) use ($studentId, $sectionId) {
                $q->where(function (Builder $q2) use ($studentId) {
                    $q2->where('created_by_role', 'Student')->where('created_by_id', $studentId);
                });

                if ($sectionId !== null) {
                    $q->orWhere('section_id', $sectionId);
                }
            });
    }

    /**
     * Scheduled, not-yet-started events visible to a teacher: their own
     * personal events, plus any events scoped to a section they teach.
     */
    public function scopeUpcomingForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query
            ->where('status', 'Scheduled')
            ->where('start_datetime', '>=', now())
            ->where(function (Builder $q) use ($teacherId) {
                $q->where(function (Builder $q2) use ($teacherId) {
                    $q2->where('created_by_role', 'Teacher')->where('created_by_id', $teacherId);
                })->orWhereIn('section_id', function ($sub) use ($teacherId) {
                    $sub->select('section_id')->from('schedules')->where('teacher_id', $teacherId);
                });
            });
    }

    /**
     * Events visible to a student within an arbitrary date range: their own
     * personal events, plus any events scoped to their active section. Unlike
     * upcomingForStudent() (fixed 14-day dashboard widget), this powers the
     * full calendar's month/week navigation.
     */
    public function scopeForStudentCalendar(Builder $query, int $studentId, ?int $sectionId, $from, $to): Builder
    {
        return $query
            ->where('start_datetime', '<=', $to)
            ->where('end_datetime', '>=', $from)
            ->where(function (Builder $q) use ($studentId, $sectionId) {
                $q->where(function (Builder $q2) use ($studentId) {
                    $q2->where('created_by_role', 'Student')->where('created_by_id', $studentId);
                });

                if ($sectionId !== null) {
                    $q->orWhere('section_id', $sectionId);
                }
            });
    }

    /**
     * Events visible to a teacher within an arbitrary date range: their own
     * personal events, plus any events scoped to a section they teach. Mirrors
     * forStudentCalendar() for the full calendar's month/week navigation.
     */
    public function scopeForTeacherCalendar(Builder $query, int $teacherId, $from, $to): Builder
    {
        return $query
            ->where('start_datetime', '<=', $to)
            ->where('end_datetime', '>=', $from)
            ->where(function (Builder $q) use ($teacherId) {
                $q->where(function (Builder $q2) use ($teacherId) {
                    $q2->where('created_by_role', 'Teacher')->where('created_by_id', $teacherId);
                })->orWhereIn('section_id', function ($sub) use ($teacherId) {
                    $sub->select('section_id')->from('schedules')->where('teacher_id', $teacherId);
                });
            });
    }
}
