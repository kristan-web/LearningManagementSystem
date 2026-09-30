<?php

namespace App\Services\Dashboard;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Quiz;
use App\Models\ScheduleEvent;
use App\Models\Student;

class StudentDashboardService
{
    /** Upcoming events window and cap — see modules/16-student-dashboard.md Definitions. */
    private const UPCOMING_EVENTS_LIMIT = 5;
    private const UPCOMING_EVENTS_WITHIN_DAYS = 14;

    /** Announcement feed cap for the dashboard's scrollable newsfeed widget. */
    private const ANNOUNCEMENTS_FEED_LIMIT = 10;

    /**
     * Build the summary data for the student dashboard: pending assignment/quiz
     * counts, the next few upcoming calendar events, and a feed of recent
     * announcements (plus the latest one, kept for backwards compatibility).
     *
     * All scoping (section, visibility) is delegated to Eloquent scopes on the
     * respective models so the same logic is reusable by the real Assignments,
     * Quizzes, and Calendar modules later.
     */
    public function summaryFor(Student $student): array
    {
        $sectionId = $student->activeEnrollment?->section_id;

        $announcements = ($sectionId
            ? Announcement::visibleToSection($sectionId)
            : Announcement::whereNull('section_id'))
            ->with('postedBy')
            ->latest('posted_at')
            ->limit(self::ANNOUNCEMENTS_FEED_LIMIT)
            ->get();

        return [
            'pendingAssignmentsCount' => $sectionId
                ? Assignment::pendingForStudent($student->student_id, $sectionId)->count()
                : 0,
            'pendingQuizzesCount' => $sectionId
                ? Quiz::pendingForStudent($student->student_id, $sectionId)->count()
                : 0,
            'upcomingEvents' => ScheduleEvent::upcomingForStudent($student->student_id, $sectionId)
                ->where('start_datetime', '<=', now()->addDays(self::UPCOMING_EVENTS_WITHIN_DAYS))
                ->orderBy('start_datetime')
                ->limit(self::UPCOMING_EVENTS_LIMIT)
                ->get(),
            'announcements' => $announcements,
            'latestAnnouncement' => $announcements->first(),
        ];
    }
}
