<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Schedule;
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

class TeacherGradeTest extends TestCase
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
            'grade_level' => $section->grade_level,
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

    // Note: "Grades & Records" page rendering (GET /teacher/grades, including
    // search/section filtering and per-student averages) is covered by
    // TeacherClassroomTest — this file only covers TeacherGradeController::show(),
    // the per-student grade detail page, which is the only route this controller
    // still serves (see BUG_REPORT.md section 0a).

    public function test_non_teacher_role_is_forbidden_from_teacher_grades(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $this->actingAs($admin)->get('/teacher/grades')->assertForbidden();
    }

    public function test_teacher_can_view_grade_detail_for_own_student(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $section = $this->makeSection();
        $schedule = $this->linkTeacherToSection($teacher, $section);
        $student = $this->enrollStudent($section, 'Juan', 'Dela Cruz');

        $assignment = Assignment::create(['schedule_id' => $schedule->schedule_id, 'title' => 'HW 1', 'max_score' => 100, 'due_date' => now()->addWeek()]);
        Submission::create(['assignment_id' => $assignment->assignment_id, 'student_id' => $student->student_id, 'score' => 80, 'status' => 'Graded']);

        $response = $this->actingAs($user)->get("/teacher/grades/{$student->student_id}");

        $response->assertOk();
        $response->assertSee('HW 1');
        $response->assertSee('Dela Cruz, Juan');
    }

    public function test_teacher_cannot_view_grade_detail_for_student_outside_their_sections(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        [, $otherTeacher] = $this->makeTeacher();
        $ownSection = $this->makeSection();
        $otherSection = $this->makeSection();
        $this->linkTeacherToSection($teacher, $ownSection);
        $this->linkTeacherToSection($otherTeacher, $otherSection);
        $student = $this->enrollStudent($otherSection, 'Maria', 'Santos');

        $this->actingAs($user)->get("/teacher/grades/{$student->student_id}")->assertForbidden();
    }
}
