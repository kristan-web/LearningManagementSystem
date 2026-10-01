<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quizzes';
    protected $primaryKey = 'quiz_id';

    // The quizzes table only has created_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'schedule_id', 'title', 'time_limit_minutes', 'attempts_allowed', 'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'attempts_allowed' => 'integer',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    /** Whether this quiz's due date has already passed (quizzes without a due date never lock). */
    public function isPastDue(): bool
    {
        return $this->due_date !== null && $this->due_date->isPast();
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id');
    }

    /**
     * This quiz's questions in a fixed-but-per-attempt-random order, each
     * question's options likewise shuffled — see QuizQuestion::shuffledOptions().
     */
    public function questionsForAttempt(int $attemptId)
    {
        return QuizQuestion::seededShuffle($this->questions()->orderBy('question_id')->get()->all(), $attemptId);
    }

    /**
     * Quizzes belonging to any of the given teacher's schedules — feeds the
     * teacher quiz index. Mirrors Assignment::forTeacher().
     */
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->whereHas('schedule', fn (Builder $q) => $q->where('teacher_id', $teacherId));
    }

    /**
     * Quizzes belonging to schedules in the given section — feeds the student
     * quiz index. Mirrors Assignment::visibleToSection().
     */
    public function scopeVisibleToSection(Builder $query, int $sectionId): Builder
    {
        return $query->whereHas('schedule', fn (Builder $q) => $q->where('section_id', $sectionId));
    }

    /**
     * Quizzes in the given section that the student has not yet completed
     * (no attempt with a submitted_at timestamp on file).
     */
    public function scopePendingForStudent(Builder $query, int $studentId, int $sectionId): Builder
    {
        return $query
            ->whereHas('schedule', fn (Builder $q) => $q->where('section_id', $sectionId))
            ->whereDoesntHave('attempts', function (Builder $q) use ($studentId) {
                $q->where('student_id', $studentId)->whereNotNull('submitted_at');
            });
    }

    /**
     * Quizzes with a due date set, belonging to schedules in the given section —
     * feeds the student calendar. See modules/09-calendar-events.md.
     */
    public function scopeForSectionCalendar(Builder $query, int $sectionId): Builder
    {
        return $query
            ->whereHas('schedule', fn (Builder $q) => $q->where('section_id', $sectionId))
            ->whereNotNull('due_date');
    }

    /**
     * Quizzes with a due date set, belonging to any of the given teacher's
     * schedules — feeds the teacher calendar. Mirrors forSectionCalendar().
     */
    public function scopeForTeacherCalendar(Builder $query, int $teacherId): Builder
    {
        return $query
            ->whereHas('schedule', fn (Builder $q) => $q->where('teacher_id', $teacherId))
            ->whereNotNull('due_date');
    }
}
