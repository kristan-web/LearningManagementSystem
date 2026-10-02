<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'Teacher';
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return $user->role === 'Teacher' && $assignment->schedule->teacher_id === $user->teacher->teacher_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'Teacher';
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->role === 'Teacher' && $assignment->schedule->teacher_id === $user->teacher->teacher_id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->role === 'Teacher' && $assignment->schedule->teacher_id === $user->teacher->teacher_id;
    }
}
