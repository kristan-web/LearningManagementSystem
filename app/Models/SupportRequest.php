<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportRequest extends Model
{
    public const PRIORITIES = ['Low', 'Normal', 'High', 'Urgent'];

    protected $table = 'support_requests';
    protected $primaryKey = 'support_request_id';

    protected $fillable = [
        'user_id', 'category', 'priority', 'subject', 'message',
        'attachment_path', 'attachment_name', 'status', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
