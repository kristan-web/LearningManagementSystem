<?php

namespace App\Services\Dashboard;

use App\Models\Announcement;
use App\Models\QuizAttempt;
use App\Models\Schedule;
use App\Models\ScheduleEvent;
use App\Models\Submission;
use App\Models\Teacher;

class TeacherDashboardService
{
    /** Upcoming events cap — mirrors the student dashboard widget (see modules/16-student-dashboard.md). */
    private const UPCOMING_EVENTS_LIMIT = 5;

    /** Announcement feed cap for the dashboard's scrollable newsfeed widget — mirrors the student dashboard. */
    private const ANNOUNCEMENTS_FEED_LIMIT = 10;

    /**
     * Build the summary data for the teacher dashboard: class/period counts,
     * pending grading counts, today's schedule, upcoming events, and a feed
     * of recent announcements (plus the latest one, kept for backwards
     * compatibility).
     */
    public function summaryFor(Teacher $teacher): array
    {
        $teacherId = $teacher->teacher_id;
        $today = now()->format('l'); // e.g. "Monday" — matches schedules.day_of_week

        $todaysSchedule = Schedule::forTeacherToday($teacherId, $today)
            ->with(['section', 'subject'])
            ->get();

        $announcements = Announcement::visibleToTeacher($teacherId)
            ->with('postedBy')
            ->latest('posted_at')
            ->limit(self::ANNOUNCEMENTS_FEED_LIMIT)
            ->get();

        return [
            'classesCount' => Schedule::where('teacher_id', $teacherId)->distinct('section_id')->count('section_id'),
            'pendingGradingCount' => Submission::awaitingGradingForTeacher($teacherId)->count(),
            'quizzesAwaitingReviewCount' => QuizAttempt::awaitingReviewForTeacher($teacherId)->count(),
            'todaysPeriodsCount' => $todaysSchedule->count(),
            'todaysSchedule' => $todaysSchedule,
            'upcomingEvents' => ScheduleEvent::upcomingForTeacher($teacherId)
                ->orderBy('start_datetime')
                ->limit(self::UPCOMING_EVENTS_LIMIT)
                ->get(),
            'announcements' => $announcements,
            'latestAnnouncement' => $announcements->first(),
        ];
    }
}
