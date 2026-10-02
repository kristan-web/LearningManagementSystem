<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    private const ALLOWED_ROLES = ['Admin', 'Staff', 'Registrar', 'Accounting'];

    /**
     * Determine whether the user can view any schedules.
     */
    public function viewAny(User $user): bool
    {
        // Teachers can view their own schedules
        if ($user->role === 'Teacher') {
            return true;
        }

        return in_array($user->role, self::ALLOWED_ROLES, true);
    }

    /**
     * Determine whether the user can view the schedule.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        // Teachers can view their own schedules
        if ($user->role === 'Teacher') {
            return $schedule->teacher_id === $user->teacher->teacher_id;
        }

        return in_array($user->role, self::ALLOWED_ROLES, true);
    }

    /**
     * Determine whether the user can create schedules.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, self::ALLOWED_ROLES, true);
    }

    /**
     * Determine whether the user can update the schedule.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        return in_array($user->role, self::ALLOWED_ROLES, true);
    }

    /**
     * Determine whether the user can delete the schedule.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        return in_array($user->role, self::ALLOWED_ROLES, true);
    }
}
