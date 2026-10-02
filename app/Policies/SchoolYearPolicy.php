<?php

namespace App\Policies;

use App\Models\SchoolYear;
use App\Models\User;

class SchoolYearPolicy
{
    private const ALLOWED_ROLES = ['Admin'];

    public function viewAny(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_ROLES, true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_ROLES, true);
    }

    public function update(User $user, SchoolYear $schoolYear): bool
    {
        return in_array($user->role, self::ALLOWED_ROLES, true);
    }

    public function delete(User $user, SchoolYear $schoolYear): bool
    {
        return in_array($user->role, self::ALLOWED_ROLES, true);
    }
}
