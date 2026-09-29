<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AssignmentComment extends Model
{
    protected $table = 'assignment_comments';
    protected $primaryKey = 'comment_id';

    // The assignment_comments table only has created_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'assignment_id', 'user_id', 'parent_comment_id', 'body',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(AssignmentComment::class, 'parent_comment_id');
    }

    /** One level of replies, Facebook-style — no infinite nesting. */
    public function replies()
    {
        return $this->hasMany(AssignmentComment::class, 'parent_comment_id')->oldest('created_at');
    }

    /** Top-level comments only (replies are loaded via the replies() relation). */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_comment_id');
    }
}
