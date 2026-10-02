<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;

class GradePolicy
{
    /**
     * Determine whether the user can view the student's grades.
     */
    public function view(User $user, Student $student): bool
    {
        if ($user->role !== 'Teacher') {
            return false;
        }

        // Check if the teacher teaches the student in any section
        $teacherSectionIds = Schedule::where('teacher_id', $user->teacher->teacher_id)
            ->distinct()
            ->pluck('section_id');

        return Enrollment::where('student_id', $student->student_id)
            ->whereIn('section_id', $teacherSectionIds)
            ->where('status', 'Enrolled')
            ->exists();
    }
}
