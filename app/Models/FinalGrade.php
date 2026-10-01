<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Official subject grade per enrollment (table from 2026_09_20_000021). One row per enrollment + subject. */
class FinalGrade extends Model
{
    public const REMARKS = ['Passed', 'Failed', 'Incomplete'];
    public const PASSING = 75;

    protected $table = 'final_grades';
    protected $primaryKey = 'final_grade_id';
    public $timestamps = false;

    protected $fillable = ['enrollment_id', 'subject_id', 'final_rating', 'remarks', 'is_locked', 'computed_at'];

    protected $casts = [
        'final_rating' => 'float',
        'is_locked' => 'boolean',
        'computed_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
