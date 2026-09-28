<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    protected $table = 'guardians';
    protected $primaryKey = 'guardian_id';

    // The guardians table only has created_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'full_name', 'relationship', 'contact_number',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The linked students via the primary FK (students.guardian_id). This is the
     * canonical Guardian ⇄ Student link — it is enforced by the schema, matches the
     * seeded data, and covers the common "one guardian, several children" case
     * directly. The student_guardians many-to-many pivot is intentionally not
     * modeled: see docs/spec/batch-a-model-fixes.md (M12-03 decision, deferred).
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'guardian_id');
    }
}