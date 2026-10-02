<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Student;
use App\Events\AttendanceRecorded;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    /**
     * Display the attendance entry form for a specific schedule.
     */
    public function index(Request $request, Schedule $schedule): View
    {
        $this->authorize('update', $schedule); // Teacher can only take attendance for their own schedules

        $today = Carbon::today()->toDateString();

        // Get enrolled students for this schedule's section
        $enrollments = Enrollment::where('section_id', $schedule->section_id)
            ->where('status', 'Active')
            ->with('student.user')
            ->get();

        // Get existing attendance records for today for this schedule
        $existingRecords = AttendanceRecord::where('schedule_id', $schedule->schedule_id)
            ->where('attendance_date', $today)
            ->get()
            ->keyBy('student_id');

        $students = $enrollments->map(function ($enrollment) use ($existingRecords) {
            $student = $enrollment->student;
            $record = $existingRecords->get($student->student_id);

            return [
                'student_id' => $student->student_id,
                'student_name' => $student->user->name,
                'lr_number' => $student->lr_number,
                'current_status' => $record ? $record->status : null,
                'remarks' => $record ? $record->remarks : '',
            ];
        });

        return view('teacher.attendance.index', compact('schedule', 'students', 'today'));
    }

    /**
     * Store batch attendance records for a schedule.
     */
    public function store(Request $request, Schedule $schedule): RedirectResponse
    {
        $this->authorize('update', $schedule); // Teacher can only take attendance for their own schedules

        $request->validate([
            'attendance_date' => 'required|date',
            'records' => 'required|array',
            'records.*.student_id' => 'required|integer|exists:students,student_id',
            'records.*.status' => 'required|in:Present,Late,Absent,Excused',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        $date = $request->input('attendance_date');
        $records = $request->input('records');

        DB::transaction(function () use ($schedule, $date, $records) {
            foreach ($records as $recordData) {
                $attendanceRecord = AttendanceRecord::updateOrCreate(
                    [
                        'schedule_id' => $schedule->schedule_id,
                        'student_id' => $recordData['student_id'],
                        'attendance_date' => $date,
                    ],
                    [
                        'status' => $recordData['status'],
                        'remarks' => $recordData['remarks'] ?? null,
                        'logged_by' => $request->user()->teacher->teacher_id,
                        'logged_at' => now(),
                    ]
                );

                // Fire event for each attendance record created/updated
                event(new AttendanceRecorded($attendanceRecord));
            }
        });

        return redirect()
            ->route('teacher.attendance.index', $schedule->schedule_id)
            ->with('success', 'Attendance recorded successfully.');
    }
}