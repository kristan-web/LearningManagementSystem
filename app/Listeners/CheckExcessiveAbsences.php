<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use App\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ExcessiveAbsenceAlert;
use Carbon\Carbon;

class CheckExcessiveAbsences implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(AttendanceRecorded $event): void
    {
        $attendanceRecord = $event->attendanceRecord;
        
        // Only check for unexcused absences that might trigger alerts
        if (!in_array($attendanceRecord->status, ['Absent', 'Late'])) {
            return;
        }
        
        // Get the student
        $student = $attendanceRecord->student;
        if (!$student) {
            return;
        }
        
        // Check for excessive absences in the last 30 days
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateString();
        
        $unexcusedAbsences = $student->attendanceRecords()
            ->where('attendance_date', '>=', $thirtyDaysAgo)
            ->whereIn('status', ['Absent', 'Late']) // Consider both Absent and Late as problematic
            ->count();
            
        // Define thresholds for alerts
        $absenceThreshold = 5; // 5 unexcused absences/lates in 30 days triggers alert
        
        if ($unexcusedAbsences >= $absenceThreshold) {
            // Notify the guardian(s) of this student
            $guardian = $student->guardian;
            if ($guardian && $guardian->user) {
                Notification::send($guardian->user, new ExcessiveAbsenceAlert(
                    $student,
                    $unexcusedAbsences,
                    $attendanceRecord->schedule?->subject?->subject_name ?? 'Unknown Subject'
                ));
            }
            
            // Also notify the class teacher
            $teacher = $attendanceRecord->schedule?->teacher;
            if ($teacher && $teacher->user) {
                Notification::send($teacher->user, new ExcessiveAbsenceAlert(
                    $student,
                    $unexcusedAbsences,
                    $attendanceRecord->schedule?->subject?->subject_name ?? 'Unknown Subject'
                ));
            }
        }
    }
}