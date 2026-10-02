<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectGradingTemplate extends Model
{
    protected $table = 'subject_grading_templates';
    public $timestamps = false;
    protected $fillable = ['subject_id', 'template_id'];

    public function gradingTemplate()
    {
        return $this->belongsTo(GradingTemplate::class, 'template_id', 'template_id');
    }
}
