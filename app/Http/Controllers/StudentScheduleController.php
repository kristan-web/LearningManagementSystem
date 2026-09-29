<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentScheduleController extends Controller
{
    /** Weekday ordering for the timetable — day_of_week is stored as text, not a natural sort order. */
    private const DAY_ORDER = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'Student', 403);

        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();
        $sectionId = $student->activeEnrollment?->section_id;

        $schedule = $sectionId === null
            ? collect()
            : Schedule::where('section_id', $sectionId)
                ->with(['subject', 'teacher', 'room'])
                ->get()
                ->sortBy(fn (Schedule $s) => [array_search($s->day_of_week, self::DAY_ORDER), $s->start_time])
                ->values();

        return view('student.schedule.index', compact('schedule'));
    }
}
