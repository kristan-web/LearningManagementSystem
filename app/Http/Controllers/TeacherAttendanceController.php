<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->authorizedTeacher($request);

        $schedules = Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section'])
            ->orderBy('schedule_id')
            ->get();

        $selectedScheduleId = $request->filled('schedule_id')
            ? (int) $request->input('schedule_id')
            : $schedules->first()?->schedule_id;

        $schedule = $schedules->firstWhere('schedule_id', $selectedScheduleId);
        abort_if($selectedScheduleId !== null && $schedule === null, 403);

        $date = $request->filled('date') ? $request->input('date') : now()->toDateString();

        $students = collect();

        if ($schedule !== null) {
            $existing = AttendanceRecord::where('schedule_id', $schedule->schedule_id)
                ->where('attendance_date', $date)
                ->get()
                ->keyBy('student_id');

            $students = Enrollment::where('section_id', $schedule->section_id)
                ->where('status', 'Enrolled')
                ->with('student.user')
                ->orderBy('student_id')
                ->get()
                ->map(fn (Enrollment $enrollment) => [
                    'student' => $enrollment->student,
                    'status' => $existing->get($enrollment->student_id)?->status,
                ]);
        }

        return view('teacher.attendance.index', compact('schedules', 'schedule', 'selectedScheduleId', 'date', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $scheduleIds = Schedule::where('teacher_id', $teacher->teacher_id)->pluck('schedule_id');

        $data = $request->validate([
            'schedule_id' => ['required', 'integer', Rule::in($scheduleIds)],
            'date' => ['required', 'date'],
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'integer'],
            'records.*.status' => ['required', Rule::in(AttendanceRecord::STATUSES)],
        ], [
            'schedule_id.in' => 'You can only take attendance for a class you teach.',
        ]);

        $schedule = Schedule::findOrFail($data['schedule_id']);
        $enrolledStudentIds = Enrollment::where('section_id', $schedule->section_id)
            ->where('status', 'Enrolled')
            ->pluck('student_id');

        DB::transaction(function () use ($data, $enrolledStudentIds, $request) {
            foreach ($data['records'] as $record) {
                if (! $enrolledStudentIds->contains($record['student_id'])) {
                    continue;
                }

                AttendanceRecord::updateOrCreate(
                    [
                        'schedule_id' => $data['schedule_id'],
                        'student_id' => $record['student_id'],
                        'attendance_date' => $data['date'],
                    ],
                    [
                        'status' => $record['status'],
                        'logged_by' => $request->user()->user_id,
                        'logged_at' => now(),
                    ],
                );
            }
        });

        return redirect()->route('teacher.attendance.index', [
            'schedule_id' => $data['schedule_id'],
            'date' => $data['date'],
        ])->with('success', 'Attendance saved.');
    }

    private function authorizedTeacher(Request $request): Teacher
    {
        abort_unless($request->user()->role === 'Teacher', 403);

        return Teacher::where('user_id', $request->user()->user_id)->firstOrFail();
    }
}
