<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarEventRequest;
use App\Models\Schedule;
use App\Models\ScheduleEvent;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\Calendar\CalendarEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /** Roles allowed to view the calendar and manage their own personal events. */
    private const ALLOWED_ROLES = ['Student', 'Teacher', 'Admin'];

    public function __construct(private readonly CalendarEventService $calendar)
    {
    }

    public function index(Request $request): View
    {
        abort_unless(in_array($request->user()->role, self::ALLOWED_ROLES, true), 403);

        $view = match ($request->user()->role) {
            'Admin' => 'admin.calendar.index',
            'Teacher' => 'teacher.calendar.index',
            'Student' => 'student.calendar.index',
            default => 'shared.calendar.index',
        };

        return view($view);
    }

    /**
     * JSON feed consumed by the FullCalendar widget. Accepts FullCalendar's
     * `start`/`end` range query params (ISO datetimes).
     */
    public function events(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()->role, self::ALLOWED_ROLES, true), 403);

        $from = Carbon::parse($request->query('start', now()->startOfMonth()));
        $to = Carbon::parse($request->query('end', now()->endOfMonth()));

        if ($request->user()->role === 'Admin') {
            return response()->json($this->calendar->feedForAdmin($from, $to));
        }

        if ($request->user()->role === 'Teacher') {
            $teacher = Teacher::where('user_id', $request->user()->user_id)->firstOrFail();

            return response()->json($this->calendar->feedForTeacher($teacher, $from, $to));
        }

        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();

        return response()->json($this->calendar->feedFor($student, $from, $to));
    }

    public function store(StoreCalendarEventRequest $request): JsonResponse
    {
        if ($request->user()->role === 'Admin') {
            $event = ScheduleEvent::create([
                ...$request->validated(),
                'created_by_role' => 'Admin',
                'created_by_id' => $request->user()->user_id,
                'event_type' => 'School',
                'status' => 'Scheduled',
            ]);

            return response()->json($event, 201);
        }

        if ($request->user()->role === 'Teacher') {
            $teacher = Teacher::where('user_id', $request->user()->user_id)->firstOrFail();

            $event = ScheduleEvent::create([
                ...$request->validated(),
                'created_by_role' => 'Teacher',
                'created_by_id' => $teacher->teacher_id,
                'event_type' => 'Personal',
                'status' => 'Scheduled',
            ]);

            return response()->json($event, 201);
        }

        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();

        $event = ScheduleEvent::create([
            ...$request->validated(),
            'created_by_role' => 'Student',
            'created_by_id' => $student->student_id,
            'event_type' => 'Personal',
            'status' => 'Scheduled',
        ]);

        return response()->json($event, 201);
    }

    public function update(StoreCalendarEventRequest $request, ScheduleEvent $event): JsonResponse
    {
        $this->authorizePersonalEvent($request, $event);

        $event->update($request->validated());

        return response()->json($event);
    }

    public function destroy(Request $request, ScheduleEvent $event): JsonResponse
    {
        abort_unless(in_array($request->user()->role, self::ALLOWED_ROLES, true), 403);
        $this->authorizePersonalEvent($request, $event);

        $event->delete();

        return response()->json(['ok' => true]);
    }

    /** Students/Teachers may only edit/delete their own personal events. */
    private function authorizePersonalEvent(Request $request, ScheduleEvent $event): void
    {
        $role = $request->user()->role;

        if ($role === 'Admin') {
            return;
        }

        if ($role === 'Teacher') {
            $teacher = Teacher::where('user_id', $request->user()->user_id)->firstOrFail();

            abort_unless(
                $event->created_by_role === 'Teacher' && $event->created_by_id === $teacher->teacher_id,
                403
            );

            return;
        }

        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();

        abort_unless(
            $event->created_by_role === 'Student' && $event->created_by_id === $student->student_id,
            403
        );
    }
}
