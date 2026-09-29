<?php

namespace Tests\Feature;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Schedule;
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

class StudentScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function actingStudent(): array
    {
        $user = User::create([
            'first_name' => 'Test', 'last_name' => 'Student', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Student', 'status' => 'Active',
        ]);

        $trackId = DB::table('tracks')->insertGetId(['track_code' => 'ACAD-' . uniqid(), 'track_name' => 'Academic', 'created_at' => now()]);
        $strand = Strand::create(['track_id' => $trackId, 'strand_code' => 'STEM-' . uniqid(), 'strand_name' => 'STEM']);
        $section = ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '11', 'section_name' => uniqid(),
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);
        $schoolYear = SchoolYear::create(['year' => '2025-2026-' . uniqid(), 'status' => 'active']);

        $student = Student::create([
            'user_id' => $user->user_id, 'lrn' => (string) random_int(100000000000, 999999999999),
            'student_number' => 'S-' . uniqid(), 'grade_level' => '11',
        ]);
        Enrollment::create([
            'student_id' => $student->student_id, 'section_id' => $section->section_id,
            'school_year' => '2025-2026', 'school_year_id' => $schoolYear->school_year_id,
            'semester' => '1st Semester', 'status' => 'Enrolled',
        ]);

        $teacherUser = User::create([
            'first_name' => 'A', 'last_name' => 'Teacher', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Teacher', 'status' => 'Active',
        ]);
        $teacher = Teacher::create(['user_id' => $teacherUser->user_id, 'teacher_number' => 'T-' . uniqid(), 'specialization' => 'Mathematics']);

        $roomId = DB::table('rooms')->insertGetId(['room_name' => 'Room-' . uniqid(), 'building' => 'Main', 'capacity' => 40, 'created_at' => now()]);
        $subject = Subject::create([
            'strand_id' => $strand->strand_id, 'subject_code' => 'MATH1-' . uniqid(), 'subject_name' => 'Math',
            'subject_type' => 'Core', 'grade_level' => '11', 'semester' => '1st Semester', 'units' => 1.0,
        ]);

        return [$user, $student, $section, $teacher, $subject, $roomId];
    }

    public function test_student_sees_full_week_schedule_for_own_section(): void
    {
        [$user, , $section, $teacher, $subject, $roomId] = $this->actingStudent();

        $monday = Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Monday', 'start_time' => '10:00:00', 'end_time' => '11:00:00',
        ]);
        $friday = Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Friday', 'start_time' => '08:00:00', 'end_time' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/student/schedule');

        $response->assertOk();
        $schedule = $response->viewData('schedule');
        $this->assertCount(2, $schedule);
        // Ordered by day-of-week, not by insertion order.
        $this->assertSame($monday->schedule_id, $schedule->first()->schedule_id);
        $this->assertSame($friday->schedule_id, $schedule->last()->schedule_id);
        $response->assertSee('Math');
        $response->assertSee('10:00 AM');
    }

    public function test_student_does_not_see_other_sections_schedule(): void
    {
        [$user, , , $teacher, $subject, $roomId] = $this->actingStudent();
        [, , $otherSection] = $this->actingStudent();

        Schedule::create([
            'section_id' => $otherSection->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Monday', 'start_time' => '10:00:00', 'end_time' => '11:00:00',
        ]);

        $response = $this->actingAs($user)->get('/student/schedule');

        $response->assertOk();
        $this->assertCount(0, $response->viewData('schedule'));
    }

    public function test_student_without_active_enrollment_sees_empty_schedule(): void
    {
        $user = User::create([
            'first_name' => 'No', 'last_name' => 'Enrollment', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Student', 'status' => 'Active',
        ]);
        Student::create([
            'user_id' => $user->user_id, 'lrn' => (string) random_int(100000000000, 999999999999),
            'student_number' => 'S-' . uniqid(), 'grade_level' => '11',
        ]);

        $response = $this->actingAs($user)->get('/student/schedule');

        $response->assertOk();
        $this->assertCount(0, $response->viewData('schedule'));
    }

    public function test_non_student_role_is_forbidden_from_student_schedule(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $this->actingAs($admin)->get('/student/schedule')->assertForbidden();
    }
}
