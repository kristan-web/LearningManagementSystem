<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $table = 'quiz_questions';
    protected $primaryKey = 'question_id';

    // The quiz_questions table has no created_at/updated_at columns.
    public $timestamps = false;

    public const TYPES = ['multiple_choice', 'true_false', 'short_answer'];

    protected $fillable = [
        'quiz_id', 'question_text', 'question_type', 'options', 'correct_answer',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * Whether the given answer auto-grades as correct. Case/whitespace
     * insensitive so minor typing differences on short_answer don't
     * penalize a student. Multiple-choice/true-false compare exactly
     * against the stored option value.
     */
    public function isCorrect(?string $answer): bool
    {
        if ($answer === null) {
            return false;
        }

        return mb_strtolower(trim($answer)) === mb_strtolower(trim($this->correct_answer));
    }
}
