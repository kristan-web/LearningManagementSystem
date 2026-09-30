<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';
    protected $primaryKey = 'announcement_id';

    // The announcements table only has posted_at (no created_at/updated_at).
    public $timestamps = false;

    protected $fillable = [
        'posted_by', 'section_id', 'title', 'body', 'thumbnail_path', 'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function section()
    {
        return $this->belongsTo(ClassSection::class, 'section_id');
    }

    public function attachments()
    {
        return $this->hasMany(AnnouncementAttachment::class, 'announcement_id');
    }

    /** Top-level discussion comments (replies nest under each via AnnouncementComment::replies()). */
    public function comments()
    {
        return $this->hasMany(AnnouncementComment::class, 'announcement_id')
            ->topLevel()
            ->with(['user', 'replies.user'])
            ->oldest('created_at');
    }

    /**
     * Single source of truth for "can this user see this announcement" —
     * shared by the index() query logic and the thumbnail/attachment
     * download routes, so the visibility rule only lives in one place.
     */
    public function isViewableBy(User $user): bool
    {
        if ($user->role === 'Admin') {
            return true;
        }

        if ($this->section_id === null) {
            return true;
        }

        if ($user->role === 'Teacher') {
            if ((int) $this->posted_by === (int) $user->user_id) {
                return true;
            }

            $teacher = Teacher::where('user_id', $user->user_id)->first();
            $teacherId = $teacher?->teacher_id ?? 0;

            return Schedule::where('teacher_id', $teacherId)->where('section_id', $this->section_id)->exists();
        }

        if ($user->role === 'Student') {
            $student = Student::where('user_id', $user->user_id)->first();

            return $student?->activeEnrollment?->section_id === $this->section_id;
        }

        return false;
    }

    /**
     * School-wide announcements (section_id null) plus any targeted at the given section.
     */
    public function scopeVisibleToSection(Builder $query, int $sectionId): Builder
    {
        return $query->where(function (Builder $q) use ($sectionId) {
            $q->whereNull('section_id')->orWhere('section_id', $sectionId);
        });
    }

    /**
     * School-wide announcements plus any targeted at a section the given
     * teacher has a schedule in — feeds the teacher dashboard's "Latest
     * Announcement" widget.
     */
    public function scopeVisibleToTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where(function (Builder $q) use ($teacherId) {
            $q->whereNull('section_id')
                ->orWhereIn('section_id', function ($sub) use ($teacherId) {
                    $sub->select('section_id')->from('schedules')->where('teacher_id', $teacherId);
                });
        });
    }
}
