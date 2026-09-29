<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherScheduleController extends Controller
{
    /** Weekday ordering for the timetable — day_of_week is stored as text, not a natural sort order. */
    private const DAY_ORDER = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'Teacher', 403);

        $teacher = Teacher::where('user_id', $request->user()->user_id)->firstOrFail();

        $schedule = Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section', 'room'])
            ->get()
            ->sortBy(fn (Schedule $s) => [array_search($s->day_of_week, self::DAY_ORDER), $s->start_time])
            ->values();

        return view('teacher.schedule.index', compact('schedule'));
    }
}
