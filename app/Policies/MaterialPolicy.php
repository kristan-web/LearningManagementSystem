<?php

namespace App\Policies;

use App\Models\LearningMaterial;
use App\Models\User;

class MaterialPolicy
{
    /**
     * Determine whether the user can view any materials.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'Teacher';
    }

    /**
     * Determine whether the user can view the material.
     */
    public function view(User $user, LearningMaterial $material): bool
    {
        // Teachers can view their own materials
        if ($user->role === 'Teacher') {
            return $material->schedule->teacher_id === $user->teacher->teacher_id;
        }

        // Students can view published materials for their sections
        if ($user->role === 'Student') {
            return $material->status === 'Published' && 
                   $material->schedule->section_id === $user->student->section_id; 
        }

        return false;
    }

    /**
     * Determine whether the user can create materials.
     */
    public function create(User $user, ?int $scheduleId = null): bool
    {
        if ($user->role !== 'Teacher') {
            return false;
        }

        if ($scheduleId) {
            return \App\Models\Schedule::where('teacher_id', $user->teacher->teacher_id)
                ->where('schedule_id', $scheduleId)
                ->exists();
        }

        return true;
    }

    /**
     * Determine whether the user can update the material.
     */
    public function update(User $user, LearningMaterial $material): bool
    {
        return $user->role === 'Teacher' && $material->schedule->teacher_id === $user->teacher->teacher_id;
    }

    /**
     * Determine whether the user can delete the material.
     */
    public function delete(User $user, LearningMaterial $material): bool
    {
        return $user->role === 'Teacher' && $material->schedule->teacher_id === $user->teacher->teacher_id;
    }
}
