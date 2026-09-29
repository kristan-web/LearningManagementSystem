<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\Schedule;
use App\Models\ScheduleEvent;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Strand;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CalendarControllerTest extends TestCase
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

    public function test_index_renders_for_student(): void
    {
        [$user] = $this->actingStudent();

        $response = $this->actingAs($user)->get('/calendar');

        $response->assertOk();
    }

    public function test_events_feed_merges_personal_events_assignments_and_quizzes(): void
    {
        [$user, $student, , $schedule] = $this->actingStudent();

        ScheduleEvent::create([
            'created_by_role' => 'Student', 'created_by_id' => $student->student_id,
            'section_id' => null, 'title' => 'Study session', 'event_type' => 'Personal',
            'start_datetime' => now()->addDays(2), 'end_datetime' => now()->addDays(2)->addHour(),
            'status' => 'Scheduled',
        ]);
        Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Essay', 'due_date' => now()->addDays(3), 'max_score' => 100,
        ]);
        Quiz::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Chapter Quiz',
            'time_limit_minutes' => 30, 'due_date' => now()->addDays(4),
        ]);

        // A wide range (not just the current month) so this doesn't go flaky
        // near month boundaries when "due in N days" rolls into next month.
        $response = $this->actingAs($user)->getJson('/calendar/events?' . http_build_query([
            'start' => now()->subMonth()->toIso8601String(),
            'end' => now()->addMonths(2)->toIso8601String(),
        ]));

        $response->assertOk();
        $sources = collect($response->json())->pluck('extendedProps.source')->sort()->values();
        $this->assertSame(['assignment', 'event', 'quiz'], $sources->all());
    }

    public function test_events_feed_excludes_other_sections_and_other_students_personal_events(): void
    {
        [$user, $student] = $this->actingStudent();

        ScheduleEvent::create([
            'created_by_role' => 'Student', 'created_by_id' => $student->student_id + 999,
            'section_id' => null, 'title' => 'Not mine', 'event_type' => 'Personal',
            'start_datetime' => now()->addDay(), 'end_datetime' => now()->addDay()->addHour(),
            'status' => 'Scheduled',
        ]);

        $response = $this->actingAs($user)->getJson('/calendar/events?' . http_build_query([
            'start' => now()->startOfMonth()->toIso8601String(),
            'end' => now()->endOfMonth()->toIso8601String(),
        ]));

        $response->assertOk();
        $this->assertCount(0, $response->json());
    }

    public function test_student_can_create_personal_event(): void
    {
        [$user, $student] = $this->actingStudent();

        $response = $this->actingAs($user)->postJson('/calendar/events', [
            'title' => 'My Event',
            'description' => 'Details',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('schedule_events', [
            'title' => 'My Event',
            'created_by_role' => 'Student',
            'created_by_id' => $student->student_id,
            'event_type' => 'Personal',
        ]);
    }

    public function test_create_event_validates_required_fields(): void
    {
        [$user] = $this->actingStudent();

        $response = $this->actingAs($user)->postJson('/calendar/events', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'start_datetime', 'end_datetime']);
    }

    public function test_student_can_update_own_personal_event(): void
    {
        [$user, $student] = $this->actingStudent();

        $event = ScheduleEvent::create([
            'created_by_role' => 'Student', 'created_by_id' => $student->student_id,
            'section_id' => null, 'title' => 'Old title', 'event_type' => 'Personal',
            'start_datetime' => now()->addDay(), 'end_datetime' => now()->addDay()->addHour(),
            'status' => 'Scheduled',
        ]);

        $response = $this->actingAs($user)->putJson("/calendar/events/{$event->event_id}", [
            'title' => 'New title',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('schedule_events', ['event_id' => $event->event_id, 'title' => 'New title']);
    }

    public function test_student_cannot_update_a_teacher_created_event(): void
    {
        [$user, , $section] = $this->actingStudent();

        $event = ScheduleEvent::create([
            'created_by_role' => 'Teacher', 'created_by_id' => 1,
            'section_id' => $section->section_id, 'title' => 'Review session', 'event_type' => 'Review',
            'start_datetime' => now()->addDay(), 'end_datetime' => now()->addDay()->addHour(),
            'status' => 'Scheduled',
        ]);

        $response = $this->actingAs($user)->putJson("/calendar/events/{$event->event_id}", [
            'title' => 'Hijacked',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertForbidden();
    }

    public function test_student_can_delete_own_personal_event(): void
    {
        [$user, $student] = $this->actingStudent();

        $event = ScheduleEvent::create([
            'created_by_role' => 'Student', 'created_by_id' => $student->student_id,
            'section_id' => null, 'title' => 'Cancel me', 'event_type' => 'Personal',
            'start_datetime' => now()->addDay(), 'end_datetime' => now()->addDay()->addHour(),
            'status' => 'Scheduled',
        ]);

        $response = $this->actingAs($user)->deleteJson("/calendar/events/{$event->event_id}");

        $response->assertOk();
        $this->assertDatabaseMissing('schedule_events', ['event_id' => $event->event_id]);
    }

    public function test_non_student_role_is_forbidden_from_calendar(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $response = $this->actingAs($admin)->get('/calendar');

        $response->assertForbidden();
    }
}
