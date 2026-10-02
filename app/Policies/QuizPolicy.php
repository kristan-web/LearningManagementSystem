<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'Teacher';
    }

    public function view(User $user, Quiz $quiz): bool
    {
        return $user->role === 'Teacher' && $quiz->schedule->teacher_id === $user->teacher->teacher_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'Teacher';
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $user->role === 'Teacher' && $quiz->schedule->teacher_id === $user->teacher->teacher_id;
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->role === 'Teacher' && $quiz->schedule->teacher_id === $user->teacher->teacher_id;
    }
}
