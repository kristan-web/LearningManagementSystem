<?php

namespace Tests\Feature;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeacher(): array
    {
        $user = User::create([
            'first_name' => 'Test', 'last_name' => 'Teacher', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Teacher', 'status' => 'Active',
        ]);
        $teacher = Teacher::create(['user_id' => $user->user_id, 'teacher_number' => 'T-' . uniqid(), 'specialization' => 'Mathematics']);

        return [$user, $teacher];
    }

    private function makeSection(): ClassSection
    {
        $trackId = DB::table('tracks')->insertGetId(['track_code' => 'ACAD-' . uniqid(), 'track_name' => 'Academic', 'created_at' => now()]);
        $strand = Strand::create(['track_id' => $trackId, 'strand_code' => 'STEM-' . uniqid(), 'strand_name' => 'STEM']);

        return ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '11', 'section_name' => 'Section-' . uniqid(),
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);
    }

    private function linkTeacherToSection(Teacher $teacher, ClassSection $section): Schedule
    {
        $roomId = DB::table('rooms')->insertGetId(['room_name' => 'Room-' . uniqid(), 'building' => 'Main', 'capacity' => 40, 'created_at' => now()]);
        $subject = Subject::create([
            'strand_id' => $section->strand_id, 'subject_code' => 'MATH-' . uniqid(), 'subject_name' => 'Math',
            'subject_type' => 'Core', 'grade_level' => '11', 'semester' => '1st Semester', 'units' => 1.0,
        ]);

        return Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Monday', 'start_time' => '08:00:00', 'end_time' => '09:00:00',
        ]);
    }

    private function enrollStudent(ClassSection $section, string $firstName, string $lastName): Student
    {
        $studentUser = User::create([
            'first_name' => $firstName, 'last_name' => $lastName, 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Student', 'status' => 'Active',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->user_id, 'strand_id' => $section->strand_id,
            'lrn' => (string) random_int(100000000000, 999999999999), 'student_number' => 'S-' . uniqid(),
            'grade_level' => $section->grade_level === '11' ? '11' : '12',
        ]);
        $schoolYearId = DB::table('school_years')->insertGetId([
            'year' => '2025-2026-' . uniqid(), 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        Enrollment::create([
            'student_id' => $student->student_id, 'section_id' => $section->section_id,
            'school_year' => $section->school_year, 'school_year_id' => $schoolYearId,
            'semester' => '1st Semester', 'status' => 'Enrolled',
        ]);

        return $student;
    }

    public function test_teacher_sees_enrolled_students_for_own_schedule(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $section = $this->makeSection();
        $schedule = $this->linkTeacherToSection($teacher, $section);
        $this->enrollStudent($section, 'Juan', 'Dela Cruz');

        $response = $this->actingAs($user)->get('/teacher/attendance?schedule_id=' . $schedule->schedule_id);

        $response->assertOk();
        $this->assertCount(1, $response->viewData('students'));
        $response->assertSee('Dela Cruz, Juan');
    }

    public function test_teacher_cannot_view_attendance_for_a_schedule_they_do_not_teach(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $ownSection = $this->makeSection();
        $this->linkTeacherToSection($teacher, $ownSection);
        $otherSection = $this->makeSection();
        [, $otherTeacher] = $this->makeTeacher();
        $otherSchedule = $this->linkTeacherToSection($otherTeacher, $otherSection);

        $response = $this->actingAs($user)->get('/teacher/attendance?schedule_id=' . $otherSchedule->schedule_id);

        $response->assertForbidden();
    }

    public function test_teacher_can_mark_attendance_for_enrolled_students(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $section = $this->makeSection();
        $schedule = $this->linkTeacherToSection($teacher, $section);
        $student1 = $this->enrollStudent($section, 'Juan', 'Dela Cruz');
        $student2 = $this->enrollStudent($section, 'Maria', 'Santos');

        $response = $this->actingAs($user)->post('/teacher/attendance', [
            'schedule_id' => $schedule->schedule_id,
            'date' => '2026-01-15',
            'records' => [
                ['student_id' => $student1->student_id, 'status' => 'Present'],
                ['student_id' => $student2->student_id, 'status' => 'Late'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_records', [
            'schedule_id' => $schedule->schedule_id, 'student_id' => $student1->student_id,
            'attendance_date' => '2026-01-15', 'status' => 'Present', 'logged_by' => $user->user_id,
        ]);
        $this->assertDatabaseHas('attendance_records', [
            'schedule_id' => $schedule->schedule_id, 'student_id' => $student2->student_id,
            'attendance_date' => '2026-01-15', 'status' => 'Late',
        ]);
    }

    public function test_resubmitting_the_same_date_updates_instead_of_duplicating(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $section = $this->makeSection();
        $schedule = $this->linkTeacherToSection($teacher, $section);
        $student = $this->enrollStudent($section, 'Juan', 'Dela Cruz');

        $this->actingAs($user)->post('/teacher/attendance', [
            'schedule_id' => $schedule->schedule_id,
            'date' => '2026-01-15',
            'records' => [['student_id' => $student->student_id, 'status' => 'Absent']],
        ]);
        $this->actingAs($user)->post('/teacher/attendance', [
            'schedule_id' => $schedule->schedule_id,
            'date' => '2026-01-15',
            'records' => [['student_id' => $student->student_id, 'status' => 'Present']],
        ]);

        $this->assertEquals(1, DB::table('attendance_records')
            ->where('schedule_id', $schedule->schedule_id)
            ->where('student_id', $student->student_id)
            ->count());
        $this->assertDatabaseHas('attendance_records', [
            'schedule_id' => $schedule->schedule_id, 'student_id' => $student->student_id,
            'attendance_date' => '2026-01-15', 'status' => 'Present',
        ]);
    }

    public function test_teacher_cannot_mark_attendance_for_a_schedule_they_do_not_teach(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $ownSection = $this->makeSection();
        $this->linkTeacherToSection($teacher, $ownSection);
        $otherSection = $this->makeSection();
        [, $otherTeacher] = $this->makeTeacher();
        $otherSchedule = $this->linkTeacherToSection($otherTeacher, $otherSection);
        $student = $this->enrollStudent($otherSection, 'Juan', 'Dela Cruz');

        $response = $this->actingAs($user)->post('/teacher/attendance', [
            'schedule_id' => $otherSchedule->schedule_id,
            'date' => '2026-01-15',
            'records' => [['student_id' => $student->student_id, 'status' => 'Present']],
        ]);

        $response->assertSessionHasErrors('schedule_id');
        $this->assertDatabaseMissing('attendance_records', ['student_id' => $student->student_id]);
    }
}
