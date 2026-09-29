<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $table = 'quiz_questions';
    protected $primaryKey = 'question_id';

    // The quiz_questions table has no timestamp columns.
    public $timestamps = false;

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
     * Options as a shuffled list for the given attempt, deterministic per
     * attempt (same order every time that attempt is rendered/graded) but
     * different across attempts/students. multiple_choice only — other
     * question types have no options to shuffle.
     */
    public function shuffledOptions(int $attemptId): array
    {
        if ($this->question_type !== 'multiple_choice' || empty($this->options)) {
            return $this->options ?? [];
        }

        return self::seededShuffle($this->options, $attemptId * 1000 + $this->question_id);
    }

    /**
     * Deterministic Fisher-Yates shuffle: same $seed always produces the same
     * order. Laravel's Collection::shuffle() takes no seed (PHP 8.2 dropped
     * it from Randomizer), so per-attempt reproducibility needs this instead.
     */
    public static function seededShuffle(array $items, int $seed): array
    {
        $keys = array_keys($items);
        mt_srand($seed);
        for ($i = count($keys) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$keys[$i], $keys[$j]] = [$keys[$j], $keys[$i]];
        }
        mt_srand();

        return array_values(array_map(fn ($k) => $items[$k], $keys));
    }
}
