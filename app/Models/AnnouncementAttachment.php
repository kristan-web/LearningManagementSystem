<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementAttachment extends Model
{
    protected $table = 'announcement_attachments';
    protected $primaryKey = 'attachment_id';

    // The announcement_attachments table only has uploaded_at (no updated_at).
    const CREATED_AT = 'uploaded_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'announcement_id', 'file_name', 'file_url', 'file_size',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }
}
