<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingTemplate extends Model
{
    protected $table = 'grading_templates';
    protected $primaryKey = 'template_id';
    public $timestamps = false;
    protected $fillable = ['name', 'written_work_weight', 'performance_task_weight', 'exam_weight'];
}
