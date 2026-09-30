<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Schedule;
use App\Models\ScheduleEvent;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Strand;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function actingStudent(): array
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'must_change_password' => false,
            'role' => 'Student',
            'status' => 'Active',
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
        $schoolYear = SchoolYear::create(['year' => '2025-2026', 'status' => 'active']);

        $student = Student::create([
            'user_id' => $user->user_id, 'lrn' => '123456789012', 'student_number' => 'S-0001',
            'grade_level' => '11',
        ]);
        Enrollment::create([
            'student_id' => $student->student_id, 'section_id' => $section->section_id,
            'school_year' => '2025-2026', 'school_year_id' => $schoolYear->school_year_id,
            'semester' => '1st Semester', 'status' => 'Enrolled',
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
            'room_id' => $roomId, 'day_of_week' => 'Monday', 'start_time' => '08:00:00', 'end_time' => '09:00:00',
        ]);

        return [$user, $student, $section, $schedule];
    }

    public function test_dashboard_counts_only_uncompleted_assignments_and_quizzes_for_the_students_section(): void
    {
        [$user, $student, , $schedule] = $this->actingStudent();

        Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Essay', 'due_date' => now()->addDay(), 'max_score' => 100,
        ]);
        $completedAssignment = Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Worksheet', 'due_date' => now()->addDay(), 'max_score' => 100,
        ]);
        Submission::create([
            'assignment_id' => $completedAssignment->assignment_id, 'student_id' => $student->student_id,
            'status' => 'Submitted', 'submitted_at' => now(),
        ]);

        Quiz::create(['schedule_id' => $schedule->schedule_id, 'title' => 'Quiz 1']);
        $completedQuiz = Quiz::create(['schedule_id' => $schedule->schedule_id, 'title' => 'Quiz 2']);
        QuizAttempt::create([
            'quiz_id' => $completedQuiz->quiz_id, 'student_id' => $student->student_id,
            'started_at' => now(), 'submitted_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/student/');

        $response->assertOk();
        $response->assertViewHas('pendingAssignmentsCount', 1);
        $response->assertViewHas('pendingQuizzesCount', 1);
    }

    public function test_dashboard_shows_only_upcoming_events_within_the_next_fourteen_days(): void
    {
        [$user, $student, $section] = $this->actingStudent();

        $inWindow = ScheduleEvent::create([
            'created_by_role' => 'Student', 'created_by_id' => $student->student_id,
            'section_id' => null, 'title' => 'Study session', 'event_type' => 'Personal',
            'start_datetime' => now()->addDays(2), 'end_datetime' => now()->addDays(2)->addHour(),
            'status' => 'Scheduled',
        ]);
        ScheduleEvent::create([
            'created_by_role' => 'Teacher', 'created_by_id' => 1,
            'section_id' => $section->section_id, 'title' => 'Too far out', 'event_type' => 'Review',
            'start_datetime' => now()->addDays(30), 'end_datetime' => now()->addDays(30)->addHour(),
            'status' => 'Scheduled',
        ]);
        ScheduleEvent::create([
            'created_by_role' => 'Student', 'created_by_id' => $student->student_id,
            'section_id' => null, 'title' => 'Past event', 'event_type' => 'Personal',
            'start_datetime' => now()->subDay(), 'end_datetime' => now()->subDay()->addHour(),
            'status' => 'Scheduled',
        ]);

        $response = $this->actingAs($user)->get('/student/');

        $response->assertOk();
        $events = $response->viewData('upcomingEvents');
        $this->assertCount(1, $events);
        $this->assertSame($inWindow->event_id, $events->first()->event_id);
    }

    public function test_dashboard_shows_latest_announcement_visible_to_students_section(): void
    {
        [$user, , $section] = $this->actingStudent();

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

        $response = $this->actingAs($user)->get('/student/');

        $response->assertOk();
        $response->assertViewHas('latestAnnouncement', function ($announcement) use ($latest) {
            return $announcement->announcement_id === $latest->announcement_id;
        });

        // Dashboard widget links to the announcement's anchor on the full feed page.
        $response->assertSee('href="' . route('announcements.index') . '#announcement-' . $latest->announcement_id . '"', false);
    }

    public function test_non_student_role_is_forbidden_from_student_dashboard(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $response = $this->actingAs($admin)->get('/student/');

        $response->assertForbidden();
    }
}
