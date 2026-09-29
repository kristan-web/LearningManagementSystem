<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $table = 'tracks';
    protected $primaryKey = 'track_id';

    // The tracks table only has created_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'track_code', 'track_name', 'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function strands()
    {
        return $this->hasMany(Strand::class, 'track_id');
    }
}