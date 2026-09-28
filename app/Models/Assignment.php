<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $table = 'assignments';
    protected $primaryKey = 'assignment_id';

    // The assignments table only has created_at.
    const UPDATED_AT = null;

    /** Submission statuses that count as "done" for a student. */
    public const COMPLETED_SUBMISSION_STATUSES = ['Submitted', 'Late', 'Graded'];

    protected $fillable = [
        'schedule_id', 'title', 'instructions', 'due_date', 'max_score',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'assignment_id');
    }

    /**
     * Assignments in the given section that the student has not yet completed
     * (no Submitted/Late/Graded submission on file).
     */
    public function scopePendingForStudent(Builder $query, int $studentId, int $sectionId): Builder
    {
        return $query
            ->whereHas('schedule', fn (Builder $q) => $q->where('section_id', $sectionId))
            ->whereDoesntHave('submissions', function (Builder $q) use ($studentId) {
                $q->where('student_id', $studentId)
                    ->whereIn('status', self::COMPLETED_SUBMISSION_STATUSES);
            });
    }

    /**
     * Assignments (with a due date) belonging to schedules in the given section —
     * feeds the student calendar. See modules/09-calendar-events.md.
     */
    public function scopeForSectionCalendar(Builder $query, int $sectionId): Builder
    {
        return $query
            ->whereHas('schedule', fn (Builder $q) => $q->where('section_id', $sectionId))
            ->whereNotNull('due_date');
    }

    /**
     * Assignments (with a due date) belonging to any of the given teacher's
     * schedules — feeds the teacher calendar. Mirrors forSectionCalendar().
     */
    public function scopeForTeacherCalendar(Builder $query, int $teacherId): Builder
    {
        return $query
            ->whereHas('schedule', fn (Builder $q) => $q->where('teacher_id', $teacherId))
            ->whereNotNull('due_date');
    }

    /**
     * Assignments belonging to any of the given teacher's schedules — feeds the
     * teacher assignment index. Mirrors LearningMaterial::forTeacher().
     */
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->whereHas('schedule', fn (Builder $q) => $q->where('teacher_id', $teacherId));
    }

    /**
     * Assignments belonging to schedules in the given section — feeds the student
     * assignment index. Mirrors LearningMaterial::visibleToSection().
     */
    public function scopeVisibleToSection(Builder $query, int $sectionId): Builder
    {
        return $query->whereHas('schedule', fn (Builder $q) => $q->where('section_id', $sectionId));
    }
}
