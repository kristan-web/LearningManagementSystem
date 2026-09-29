<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $table = 'quiz_attempts';
    protected $primaryKey = 'attempt_id';

    // The quiz_attempts table has no updated_at column; started_at/submitted_at are tracked explicitly.
    public $timestamps = false;

    protected $fillable = [
        'quiz_id', 'student_id', 'answers', 'score', 'started_at', 'submitted_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Attempts turned in (submitted_at set) but not yet scored, for quizzes
     * belonging to the given teacher's schedules — feeds the teacher
     * dashboard's "Quizzes Awaiting Review" tile.
     */
    public function scopeAwaitingReviewForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query
            ->whereNotNull('submitted_at')
            ->whereNull('score')
            ->whereHas('quiz.schedule', fn (Builder $q) => $q->where('teacher_id', $teacherId));
    }
}
