<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\ClassSection;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Schedule;
use App\Models\ScheduleEvent;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Submission;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function actingTeacher(): array
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'must_change_password' => false,
            'role' => 'Teacher',
            'status' => 'Active',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->user_id, 'teacher_number' => 'T-0001', 'specialization' => 'Mathematics',
        ]);

        $trackId = DB::table('tracks')->insertGetId([
            'track_code' => 'ACAD', 'track_name' => 'Academic', 'created_at' => now(),
        ]);
        $strand = Strand::create([
            'track_id' => $trackId, 'strand_code' => 'STEM', 'strand_name' => 'STEM',
        ]);
        $section = ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '11', 'section_name' => 'A',
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);

        $roomId = DB::table('rooms')->insertGetId([
            'room_name' => 'Room 1', 'building' => 'Main', 'capacity' => 40, 'created_at' => now(),
        ]);
        $subject = Subject::create([
            'strand_id' => $strand->strand_id, 'subject_code' => 'MATH1', 'subject_name' => 'Math',
            'subject_type' => 'Core', 'grade_level' => '11', 'semester' => '1st Semester', 'units' => 1.0,
        ]);
        $schedule = Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => now()->format('l'), 'start_time' => '08:00:00', 'end_time' => '09:00:00',
        ]);

        return [$user, $teacher, $section, $schedule];
    }

    public function test_dashboard_counts_classes_pending_grading_and_quizzes_awaiting_review(): void
    {
        [$user, $teacher, , $schedule] = $this->actingTeacher();

        $studentUser = User::create([
            'first_name' => 'A', 'last_name' => 'Student', 'email' => 'astudent@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Student', 'status' => 'Active',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->user_id, 'lrn' => '123456789012', 'student_number' => 'S-0001',
            'grade_level' => '11',
        ]);

        $assignment = Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Essay', 'due_date' => now()->addDay(), 'max_score' => 100,
        ]);
        Submission::create([
            'assignment_id' => $assignment->assignment_id, 'student_id' => $student->student_id,
            'status' => 'Submitted', 'submitted_at' => now(),
        ]);
        $gradedAssignment = Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Worksheet', 'due_date' => now()->addDay(), 'max_score' => 100,
        ]);
        Submission::create([
            'assignment_id' => $gradedAssignment->assignment_id, 'student_id' => $student->student_id,
            'status' => 'Graded', 'submitted_at' => now(), 'score' => 90,
        ]);

        $quiz = Quiz::create(['schedule_id' => $schedule->schedule_id, 'title' => 'Quiz 1']);
        QuizAttempt::create([
            'quiz_id' => $quiz->quiz_id, 'student_id' => $student->student_id,
            'started_at' => now(), 'submitted_at' => now(),
        ]);
        $scoredQuiz = Quiz::create(['schedule_id' => $schedule->schedule_id, 'title' => 'Quiz 2']);
        QuizAttempt::create([
            'quiz_id' => $scoredQuiz->quiz_id, 'student_id' => $student->student_id,
            'started_at' => now(), 'submitted_at' => now(), 'score' => 8,
        ]);

        $response = $this->actingAs($user)->get('/teacher/');

        $response->assertOk();
        $response->assertViewHas('classesCount', 1);
        $response->assertViewHas('pendingGradingCount', 1);
        $response->assertViewHas('quizzesAwaitingReviewCount', 1);
        $response->assertViewHas('todaysPeriodsCount', 1);
    }
    public function test_dashboard_shows_only_todays_periods_in_schedule(): void
    {
        [$user, $teacher, $section, $schedule] = $this->actingTeacher();

        $notToday = now()->addDay()->format('l');
        $subject2 = Subject::create([
            'strand_id' => $section->strand_id, 'subject_code' => 'ENG1', 'subject_name' => 'English',
            'subject_type' => 'Core', 'grade_level' => '11', 'semester' => '1st Semester', 'units' => 1.0,
        ]);
        Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject2->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $schedule->room_id,
            'day_of_week' => $notToday, 'start_time' => '10:00:00', 'end_time' => '11:00:00',
        ]);

        $response = $this->actingAs($user)->get('/teacher/');

        $response->assertOk();
        $todaysSchedule = $response->viewData('todaysSchedule');
        $this->assertCount(1, $todaysSchedule);
        $this->assertSame($schedule->schedule_id, $todaysSchedule->first()->schedule_id);
    }


    public function test_dashboard_shows_upcoming_events_for_teacher_and_their_sections(): void
    {
        [$user, $teacher, $section] = $this->actingTeacher();

        $ownEvent = ScheduleEvent::create([
            'created_by_role' => 'Teacher', 'created_by_id' => $teacher->teacher_id,
            'section_id' => null, 'title' => 'Prep period', 'event_type' => 'Personal',
            'start_datetime' => now()->addDays(1), 'end_datetime' => now()->addDays(1)->addHour(),
            'status' => 'Scheduled',
        ]);
        ScheduleEvent::create([
            'created_by_role' => 'Student', 'created_by_id' => 1,
            'section_id' => null, 'title' => 'Student personal event', 'event_type' => 'Personal',
            'start_datetime' => now()->addDays(2), 'end_datetime' => now()->addDays(2)->addHour(),
            'status' => 'Scheduled',
        ]);
        ScheduleEvent::create([
            'created_by_role' => 'Teacher', 'created_by_id' => $teacher->teacher_id,
            'section_id' => null, 'title' => 'Past event', 'event_type' => 'Personal',
            'start_datetime' => now()->subDay(), 'end_datetime' => now()->subDay()->addHour(),
            'status' => 'Scheduled',
        ]);

        $response = $this->actingAs($user)->get('/teacher/');

        $response->assertOk();
        $events = $response->viewData('upcomingEvents');
        $this->assertCount(1, $events);
        $this->assertSame($ownEvent->event_id, $events->first()->event_id);
    }


    public function test_dashboard_shows_latest_announcement_visible_to_teachers_sections(): void
    {
        [$user, , $section] = $this->actingTeacher();

        $otherSection = ClassSection::create([
            'strand_id' => $section->strand_id, 'grade_level' => '11', 'section_name' => 'B',
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);

        Announcement::create([
            'posted_by' => $user->user_id, 'section_id' => null,
            'title' => 'Old school-wide notice', 'body' => 'Body', 'posted_at' => now()->subDays(2),
        ]);
        $latest = Announcement::create([
            'posted_by' => $user->user_id, 'section_id' => $section->section_id,
            'title' => 'Section notice', 'body' => 'Body', 'posted_at' => now(),
        ]);
        Announcement::create([
            'posted_by' => $user->user_id, 'section_id' => $otherSection->section_id,
            'title' => 'Other section notice', 'body' => 'Body', 'posted_at' => now()->addMinute(),
        ]);

        $response = $this->actingAs($user)->get('/teacher/');

        $response->assertOk();
        $response->assertViewHas('latestAnnouncement', function ($announcement) use ($latest) {
            return $announcement->announcement_id === $latest->announcement_id;
        });

        $feed = $response->viewData('announcements');
        $this->assertCount(2, $feed);
        $this->assertSame($latest->announcement_id, $feed->first()->announcement_id);

        // Dashboard widget links to the announcement's anchor on the full feed page.
        $response->assertSee('href="' . route('announcements.index') . '#announcement-' . $latest->announcement_id . '"', false);
    }

    public function test_non_teacher_role_is_forbidden_from_teacher_dashboard(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $response = $this->actingAs($admin)->get('/teacher/');

        $response->assertForbidden();
    }
}


