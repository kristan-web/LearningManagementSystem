<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** The signed-in student's attendance, as recorded by their teachers. */
class StudentAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'Student', 403);
        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();

        $all = AttendanceRecord::where('student_id', $student->student_id)
            ->with(['schedule.subject', 'loggedBy'])
            ->orderByDesc('attendance_date')->orderByDesc('attendance_id')
            ->get();

        $subjects = $all->pluck('schedule.subject')->filter()->unique('subject_id')->sortBy('subject_name')->values();

        $subjectId = (int) $request->query('subject');
        $month = (string) $request->query('month'); // "YYYY-MM" from <input type="month">
        $filtered = $all
            ->when($subjectId, fn ($c) => $c->filter(fn ($r) => (int) $r->schedule?->subject_id === $subjectId))
            ->when(preg_match('/^\d{4}-\d{2}$/', $month), fn ($c) => $c->filter(fn ($r) => str_starts_with((string) $r->attendance_date, $month)));

        $records = $filtered->map(fn (AttendanceRecord $r) => (object) [
            'date' => $r->attendance_date,
            'subject_name' => $r->schedule?->subject?->subject_name ?? '—',
            'time_in' => $r->schedule ? Carbon::parse($r->schedule->start_time)->format('H:i') : null,
            'status' => $r->status,
            'recorded_by' => $r->loggedBy ? trim($r->loggedBy->first_name . ' ' . $r->loggedBy->last_name) : null,
            'remarks' => $r->remarks,
        ])->values();

        $total = $filtered->count();
        $attended = $filtered->whereIn('status', ['Present', 'Late'])->count();
        $summary = [
            'present' => $filtered->where('status', 'Present')->count(),
            'absent' => $filtered->where('status', 'Absent')->count(),
            'late' => $filtered->where('status', 'Late')->count(),
        ] + ($total ? ['rate' => (int) round($attended / $total * 100)] : []);

        return view('student.attendance.index', compact('records', 'summary', 'subjects'));
    }
}
