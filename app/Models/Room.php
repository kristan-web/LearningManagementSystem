<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';
    protected $primaryKey = 'room_id';

    // The rooms table only has created_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'room_name', 'building', 'capacity',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'room_id');
    }
}
