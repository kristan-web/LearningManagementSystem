<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\ScheduleEvent;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Teacher-created Google Meet links (instant or scheduled) tied to one of the teacher's own class periods. */
class TeacherMeetingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);

        $data = $request->validate([
            'schedule_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:150'],
            'meeting_link' => ['required', 'url', 'max:255', 'starts_with:https://meet.google.com/'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
        ]);

        $schedule = $this->ownedSchedule($data['schedule_id'], $teacher->teacher_id);

        $event = $this->createMeeting($teacher, $schedule, $data, 'Scheduled');

        return redirect()->route('teacher.classes.index')->with('success', 'Meeting "' . $event->title . '" scheduled.');
    }

    /** Starts a Meet immediately for one of the teacher's classes — live for the rest of the period. */
    public function instant(Request $request): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);

        $data = $request->validate([
            'schedule_id' => ['required', 'integer'],
            'meeting_link' => ['required', 'url', 'max:255', 'starts_with:https://meet.google.com/'],
        ]);

        $schedule = $this->ownedSchedule($data['schedule_id'], $teacher->teacher_id);

        $event = $this->createMeeting($teacher, $schedule, [
            'title' => $schedule->subject?->subject_name . ' — Live Meeting',
            'meeting_link' => $data['meeting_link'],
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ], 'Live');

        return redirect()->route('teacher.classes.index')->with('success', 'Meeting "' . $event->title . '" started.');
    }

    public function end(Request $request, ScheduleEvent $event): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $this->authorizeOwnership($event, $teacher->teacher_id);

        $event->update(['meeting_status' => 'Ended', 'status' => 'Done']);

        return redirect()->route('teacher.classes.index')->with('success', 'Meeting ended.');
    }

    public function destroy(Request $request, ScheduleEvent $event): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $this->authorizeOwnership($event, $teacher->teacher_id);

        $event->delete();

        return redirect()->route('teacher.classes.index')->with('success', 'Meeting cancelled.');
    }

    private function authorizedTeacher(Request $request): Teacher
    {
        abort_unless($request->user()->role === 'Teacher', 403);

        return Teacher::where('user_id', $request->user()->user_id)->firstOrFail();
    }

    /** A teacher may only create/manage meetings on their own schedules. */
    private function ownedSchedule(int $scheduleId, int $teacherId): Schedule
    {
        $schedule = Schedule::where('schedule_id', $scheduleId)->where('teacher_id', $teacherId)->first();
        abort_unless($schedule !== null, 403);

        return $schedule;
    }

    private function authorizeOwnership(ScheduleEvent $event, int $teacherId): void
    {
        abort_unless(
            $event->event_type === 'Meeting' && $event->created_by_role === 'Teacher' && $event->created_by_id === $teacherId,
            403
        );
    }

    private function createMeeting(Teacher $teacher, Schedule $schedule, array $data, string $meetingStatus): ScheduleEvent
    {
        $event = ScheduleEvent::create([
            'created_by_role' => 'Teacher',
            'created_by_id' => $teacher->teacher_id,
            'section_id' => $schedule->section_id,
            'subject_id' => $schedule->subject_id,
            'schedule_id' => $schedule->schedule_id,
            'title' => $data['title'],
            'event_type' => 'Meeting',
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
            'status' => 'Scheduled',
            'meeting_link' => $data['meeting_link'],
            'meeting_provider' => 'manual',
            'meeting_status' => $meetingStatus,
        ]);

        $this->notifyEnrolledStudents($schedule, $event, $meetingStatus);

        return $event;
    }

    /** Notifies every actively enrolled student in the class — synchronous, matching the rest of this app (no queue). */
    private function notifyEnrolledStudents(Schedule $schedule, ScheduleEvent $event, string $meetingStatus): void
    {
        $userIds = Enrollment::where('section_id', $schedule->section_id)
            ->where('status', 'Enrolled')
            ->with('student')
            ->get()
            ->pluck('student.user_id')
            ->filter();

        if ($userIds->isEmpty()) {
            return;
        }

        $verb = $meetingStatus === 'Live' ? 'started' : 'scheduled';
        $message = $schedule->subject?->subject_name . ': "' . $event->title . '" ' . $verb . ' for ' . $event->start_datetime->format('M j, g:i A') . '.';

        $rows = $userIds->map(fn ($userId) => [
            'user_id' => $userId,
            'type' => 'Meeting',
            'message' => $message,
            'is_read' => false,
            'created_at' => now(),
        ])->all();

        Notification::insert($rows);
    }
}
