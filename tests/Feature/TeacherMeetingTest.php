<?php

namespace Tests\Feature;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\ScheduleEvent;
use App\Models\SchoolYear;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherMeetingTest extends TestCase
{
    use RefreshDatabase;

    private function actingTeacher(): array
    {
        $user = User::create([
            'first_name' => 'Test', 'last_name' => 'Teacher', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Teacher', 'status' => 'Active',
        ]);
        $teacher = Teacher::create(['user_id' => $user->user_id, 'teacher_number' => 'T-' . uniqid(), 'specialization' => 'Mathematics']);

        $trackId = DB::table('tracks')->insertGetId(['track_code' => 'ACAD-' . uniqid(), 'track_name' => 'Academic', 'created_at' => now()]);
        $strand = Strand::create(['track_id' => $trackId, 'strand_code' => 'STEM-' . uniqid(), 'strand_name' => 'STEM']);
        $section = ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '11', 'section_name' => uniqid(),
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);
        $roomId = DB::table('rooms')->insertGetId(['room_name' => 'Room-' . uniqid(), 'building' => 'Main', 'capacity' => 40, 'created_at' => now()]);
        $subject = Subject::create([
            'strand_id' => $strand->strand_id, 'subject_code' => 'MATH1-' . uniqid(), 'subject_name' => 'Math',
            'subject_type' => 'Core', 'grade_level' => '11', 'semester' => '1st Semester', 'units' => 1.0,
        ]);
        $schedule = Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Monday', 'start_time' => '08:00:00', 'end_time' => '09:00:00',
        ]);

        return [$user, $teacher, $section, $schedule];
    }

    private function enrollStudent(int $sectionId): User
    {
        $studentUser = User::create([
            'first_name' => 'Test', 'last_name' => 'Student', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Student', 'status' => 'Active',
        ]);
        $schoolYear = SchoolYear::create(['year' => '2025-2026', 'status' => 'active']);
        $student = Student::create([
            'user_id' => $studentUser->user_id, 'lrn' => (string) random_int(100000000000, 999999999999),
            'student_number' => 'S-' . uniqid(), 'grade_level' => '11',
        ]);
        Enrollment::create([
            'student_id' => $student->student_id, 'section_id' => $sectionId,
            'school_year' => '2025-2026', 'school_year_id' => $schoolYear->school_year_id,
            'semester' => '1st Semester', 'status' => 'Enrolled',
        ]);

        return $studentUser;
    }

    public function test_teacher_can_schedule_a_meeting_for_own_class(): void
    {
        [$user, , , $schedule] = $this->actingTeacher();

        $response = $this->actingAs($user)->post('/teacher/meetings', [
            'schedule_id' => $schedule->schedule_id,
            'title' => 'Review Session',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('teacher.classes.index'));
        $this->assertDatabaseHas('schedule_events', [
            'title' => 'Review Session',
            'event_type' => 'Meeting',
            'meeting_status' => 'Scheduled',
            'schedule_id' => $schedule->schedule_id,
        ]);
    }

    public function test_teacher_can_start_an_instant_meeting_and_notifies_enrolled_students(): void
    {
        [$user, , $section, $schedule] = $this->actingTeacher();
        $studentUser = $this->enrollStudent($section->section_id);

        $response = $this->actingAs($user)->post('/teacher/meetings/instant', [
            'schedule_id' => $schedule->schedule_id,
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
        ]);

        $response->assertRedirect(route('teacher.classes.index'));
        $this->assertDatabaseHas('schedule_events', [
            'event_type' => 'Meeting',
            'meeting_status' => 'Live',
            'schedule_id' => $schedule->schedule_id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $studentUser->user_id,
            'type' => 'Meeting',
        ]);
    }

    public function test_teacher_cannot_create_meeting_for_schedule_they_do_not_own(): void
    {
        [$user] = $this->actingTeacher();
        [, , , $otherSchedule] = $this->actingTeacher();

        $response = $this->actingAs($user)->post('/teacher/meetings', [
            'schedule_id' => $otherSchedule->schedule_id,
            'title' => 'Hijack',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertForbidden();
    }

    public function test_teacher_can_end_own_live_meeting(): void
    {
        [$user, $teacher, $section, $schedule] = $this->actingTeacher();

        $event = ScheduleEvent::create([
            'created_by_role' => 'Teacher', 'created_by_id' => $teacher->teacher_id,
            'section_id' => $section->section_id, 'schedule_id' => $schedule->schedule_id,
            'title' => 'Live Class', 'event_type' => 'Meeting',
            'start_datetime' => now(), 'end_datetime' => now()->addHour(), 'status' => 'Scheduled',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij', 'meeting_provider' => 'manual',
            'meeting_status' => 'Live',
        ]);

        $response = $this->actingAs($user)->put("/teacher/meetings/{$event->event_id}/end");

        $response->assertRedirect(route('teacher.classes.index'));
        $this->assertDatabaseHas('schedule_events', ['event_id' => $event->event_id, 'meeting_status' => 'Ended']);
    }

    public function test_teacher_cannot_end_another_teachers_meeting(): void
    {
        [$user] = $this->actingTeacher();
        [, $otherTeacher, $otherSection, $otherSchedule] = $this->actingTeacher();

        $event = ScheduleEvent::create([
            'created_by_role' => 'Teacher', 'created_by_id' => $otherTeacher->teacher_id,
            'section_id' => $otherSection->section_id, 'schedule_id' => $otherSchedule->schedule_id,
            'title' => 'Live Class', 'event_type' => 'Meeting',
            'start_datetime' => now(), 'end_datetime' => now()->addHour(), 'status' => 'Scheduled',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij', 'meeting_provider' => 'manual',
            'meeting_status' => 'Live',
        ]);

        $response = $this->actingAs($user)->put("/teacher/meetings/{$event->event_id}/end");

        $response->assertForbidden();
    }

    public function test_teacher_can_cancel_a_scheduled_meeting(): void
    {
        [$user, $teacher, $section, $schedule] = $this->actingTeacher();

        $event = ScheduleEvent::create([
            'created_by_role' => 'Teacher', 'created_by_id' => $teacher->teacher_id,
            'section_id' => $section->section_id, 'schedule_id' => $schedule->schedule_id,
            'title' => 'Upcoming Class', 'event_type' => 'Meeting',
            'start_datetime' => now()->addDay(), 'end_datetime' => now()->addDay()->addHour(), 'status' => 'Scheduled',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij', 'meeting_provider' => 'manual',
            'meeting_status' => 'Scheduled',
        ]);

        $response = $this->actingAs($user)->delete("/teacher/meetings/{$event->event_id}");

        $response->assertRedirect(route('teacher.classes.index'));
        $this->assertDatabaseMissing('schedule_events', ['event_id' => $event->event_id]);
    }

    public function test_meeting_link_must_be_a_google_meet_url(): void
    {
        [$user, , , $schedule] = $this->actingTeacher();

        $response = $this->actingAs($user)->post('/teacher/meetings', [
            'schedule_id' => $schedule->schedule_id,
            'title' => 'Review Session',
            'meeting_link' => 'https://zoom.us/j/12345',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('meeting_link');
    }

    public function test_non_teacher_role_is_forbidden_from_teacher_meetings(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $response = $this->actingAs($admin)->post('/teacher/meetings/instant', [
            'schedule_id' => 1,
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
        ]);

        $response->assertForbidden();
    }
}
