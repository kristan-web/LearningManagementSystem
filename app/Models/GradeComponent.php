<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One scored item behind a final grade (table from 2026_09_20_000022), e.g. a graded submission or best quiz attempt. */
class GradeComponent extends Model
{
    protected $table = 'grade_components';
    protected $primaryKey = 'component_id';
    public $timestamps = false;

    protected $fillable = ['enrollment_id', 'subject_id', 'component_type', 'source_type', 'source_id', 'raw_score', 'max_score'];

    protected $casts = [
        'raw_score' => 'float',
        'max_score' => 'float',
    ];
}
