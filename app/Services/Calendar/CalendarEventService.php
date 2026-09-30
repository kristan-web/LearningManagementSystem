<?php

namespace App\Services\Calendar;

use App\Models\Assignment;
use App\Models\Quiz;
use App\Models\ScheduleEvent;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CalendarEventService
{
    /**
     * Build the merged calendar feed for a student within [$from, $to]: their
     * own/section-scoped schedule events, plus assignment and quiz due dates
     * for their active section — normalized into one shape so the front-end
     * calendar doesn't need to know about three different source tables.
     *
     * See modules/09-calendar-events.md and modules/16-student-dashboard.md
     * for the section-scoping rules this reuses.
     */
    public function feedFor(Student $student, Carbon $from, Carbon $to): Collection
    {
        $sectionId = $student->activeEnrollment?->section_id;

        $events = ScheduleEvent::forStudentCalendar($student->student_id, $sectionId, $from, $to)
            ->get()
            ->map(fn (ScheduleEvent $event) => [
                'id' => 'event-' . $event->event_id,
                'title' => $event->title,
                'start' => $event->start_datetime,
                'end' => $event->end_datetime,
                'classNames' => ['fc-event--personal'],
                'extendedProps' => [
                    'source' => 'event',
                    'subject_name' => $event->subject?->subject_name,
                    'description' => $event->description,
                    'editable' => $event->created_by_role === 'Student' && $event->created_by_id === $student->student_id,
                ],
            ]);

        if ($sectionId === null) {
            return $events->values();
        }

        $assignments = Assignment::forSectionCalendar($sectionId)
            ->whereBetween('due_date', [$from, $to])
            ->with('schedule.subject')
            ->get()
            ->map(fn (Assignment $assignment) => [
                'id' => 'assignment-' . $assignment->assignment_id,
                'title' => $assignment->title,
                'start' => $assignment->due_date,
                'end' => $assignment->due_date,
                'url' => route('student.assignments.index'),
                'classNames' => ['fc-event--assignment'],
                'extendedProps' => [
                    'source' => 'assignment',
                    'subject_name' => $assignment->schedule?->subject?->subject_name,
                    'editable' => false,
                ],
            ]);

        $quizzes = Quiz::forSectionCalendar($sectionId)
            ->whereBetween('due_date', [$from, $to])
            ->with('schedule.subject')
            ->get()
            ->map(fn (Quiz $quiz) => [
                'id' => 'quiz-' . $quiz->quiz_id,
                'title' => $quiz->title,
                'start' => $quiz->due_date,
                'end' => $quiz->due_date,
                'url' => route('student.quizzes.index'),
                'classNames' => ['fc-event--quiz'],
                'extendedProps' => [
                    'source' => 'quiz',
                    'subject_name' => $quiz->schedule?->subject?->subject_name,
                    'editable' => false,
                ],
            ]);

        return $events->concat($assignments)->concat($quizzes)->values();
    }

    /**
     * Build the merged calendar feed for a teacher within [$from, $to]: their
     * own/section-scoped schedule events, plus assignment and quiz due dates
     * for every section they teach. Mirrors feedFor() for students.
     */
    public function feedForTeacher(Teacher $teacher, Carbon $from, Carbon $to): Collection
    {
        $teacherId = $teacher->teacher_id;

        $events = ScheduleEvent::forTeacherCalendar($teacherId, $from, $to)
            ->get()
            ->map(fn (ScheduleEvent $event) => [
                'id' => 'event-' . $event->event_id,
                'title' => $event->title,
                'start' => $event->start_datetime,
                'end' => $event->end_datetime,
                'classNames' => ['fc-event--personal'],
                'extendedProps' => [
                    'source' => 'event',
                    'subject_name' => $event->subject?->subject_name,
                    'description' => $event->description,
                    'editable' => $event->created_by_role === 'Teacher' && $event->created_by_id === $teacherId,
                ],
            ]);

        $assignments = Assignment::forTeacherCalendar($teacherId)
            ->whereBetween('due_date', [$from, $to])
            ->with('schedule.subject')
            ->get()
            ->map(fn (Assignment $assignment) => [
                'id' => 'assignment-' . $assignment->assignment_id,
                'title' => $assignment->title,
                'start' => $assignment->due_date,
                'end' => $assignment->due_date,
                'url' => url('/teacher/assignments'),
                'classNames' => ['fc-event--assignment'],
                'extendedProps' => [
                    'source' => 'assignment',
                    'subject_name' => $assignment->schedule?->subject?->subject_name,
                    'editable' => false,
                ],
            ]);

        $quizzes = Quiz::forTeacherCalendar($teacherId)
            ->whereBetween('due_date', [$from, $to])
            ->with('schedule.subject')
            ->get()
            ->map(fn (Quiz $quiz) => [
                'id' => 'quiz-' . $quiz->quiz_id,
                'title' => $quiz->title,
                'start' => $quiz->due_date,
                'end' => $quiz->due_date,
                'url' => url('/teacher/assignments'),
                'classNames' => ['fc-event--quiz'],
                'extendedProps' => [
                    'source' => 'quiz',
                    'subject_name' => $quiz->schedule?->subject?->subject_name,
                    'editable' => false,
                ],
            ]);

        return $events->concat($assignments)->concat($quizzes)->values();
    }

    /**
     * Build the calendar feed for an admin within [$from, $to]: all schedule events.
     */
    public function feedForAdmin(Carbon $from, Carbon $to): Collection
    {
        return ScheduleEvent::where('start_datetime', '<=', $to)
            ->where('end_datetime', '>=', $from)
            ->get()
            ->map(fn (ScheduleEvent $event) => [
                'id' => 'event-' . $event->event_id,
                'title' => $event->title,
                'start' => $event->start_datetime,
                'end' => $event->end_datetime,
                'classNames' => ['fc-event--admin'], // New class for admin events
                'extendedProps' => [
                    'source' => 'event',
                    'subject_name' => $event->subject?->subject_name,
                    'description' => $event->description,
                    'editable' => true, // Admins can edit all events
                    'created_by_role' => $event->created_by_role,
                    'created_by_id' => $event->created_by_id,
                    'section_id' => $event->section_id,
                    'subject_id' => $event->subject_id,
                ],
            ]);
    }

}
