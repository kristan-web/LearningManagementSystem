<?php

namespace Tests\Feature;

use App\Models\ClassSection;
use App\Models\Schedule;
use App\Models\Strand;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherScheduleTest extends TestCase
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

        return [$user, $teacher, $section, $subject, $roomId];
    }

    public function test_teacher_sees_full_week_schedule_for_own_periods(): void
    {
        [$user, $teacher, $section, $subject, $roomId] = $this->actingTeacher();

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

        $response = $this->actingAs($user)->get('/teacher/schedule');

        $response->assertOk();
        $schedule = $response->viewData('schedule');
        $this->assertCount(2, $schedule);
        // Ordered by day-of-week, not by insertion order.
        $this->assertSame($monday->schedule_id, $schedule->first()->schedule_id);
        $this->assertSame($friday->schedule_id, $schedule->last()->schedule_id);
        $response->assertSee('Math');
        $response->assertSee('10:00 AM');
    }

    public function test_teacher_does_not_see_another_teachers_periods(): void
    {
        [$user] = $this->actingTeacher();
        [, $otherTeacher, $section, $subject, $roomId] = $this->actingTeacher();

        Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $otherTeacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Monday', 'start_time' => '10:00:00', 'end_time' => '11:00:00',
        ]);

        $response = $this->actingAs($user)->get('/teacher/schedule');

        $response->assertOk();
        $this->assertCount(0, $response->viewData('schedule'));
    }

    public function test_non_teacher_role_is_forbidden_from_teacher_schedule(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $this->actingAs($admin)->get('/teacher/schedule')->assertForbidden();
    }
}
