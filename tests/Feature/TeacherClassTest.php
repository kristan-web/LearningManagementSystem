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

class TeacherClassTest extends TestCase
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

    private function makeStudent(ClassSection $section, string $firstName, string $lastName): Student
    {
        $studentUser = User::create([
            'first_name' => $firstName, 'last_name' => $lastName, 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Student', 'status' => 'Active',
        ]);

        return Student::create([
            'user_id' => $studentUser->user_id, 'strand_id' => $section->strand_id,
            'lrn' => (string) random_int(100000000000, 999999999999), 'student_number' => 'S-' . uniqid(),
            'grade_level' => $section->grade_level === '11' ? '11' : '12',
        ]);
    }

    private function makeSchoolYear(): int
    {
        return DB::table('school_years')->insertGetId([
            'year' => '2025-2026-' . uniqid(), 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function linkTeacherToSection(Teacher $teacher, ClassSection $section): void
    {
        $roomId = DB::table('rooms')->insertGetId(['room_name' => 'Room-' . uniqid(), 'building' => 'Main', 'capacity' => 40, 'created_at' => now()]);
        $subject = Subject::create([
            'strand_id' => $section->strand_id, 'subject_code' => 'MATH-' . uniqid(), 'subject_name' => 'Math',
            'subject_type' => 'Core', 'grade_level' => '11', 'semester' => '1st Semester', 'units' => 1.0,
        ]);
        Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Monday', 'start_time' => '08:00:00', 'end_time' => '09:00:00',
        ]);
    }

    public function test_teacher_sees_own_sections_and_enrolled_students(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $section = $this->makeSection();
        $this->linkTeacherToSection($teacher, $section);
        $this->enrollStudent($section, 'Juan', 'Dela Cruz');

        $response = $this->actingAs($user)->get('/teacher/classes');

        $response->assertOk();
        $this->assertCount(1, $response->viewData('sections'));
        $this->assertEquals(1, $response->viewData('stats')['students']);
        $response->assertSee('Dela Cruz, Juan');
    }

    public function test_teacher_does_not_see_students_from_sections_they_do_not_teach(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        [, $otherTeacher] = $this->makeTeacher();
        $ownSection = $this->makeSection();
        $otherSection = $this->makeSection();
        $this->linkTeacherToSection($teacher, $ownSection);
        $this->linkTeacherToSection($otherTeacher, $otherSection);

        $this->enrollStudent($ownSection, 'Juan', 'Dela Cruz');
        $this->enrollStudent($otherSection, 'Maria', 'Santos');

        $response = $this->actingAs($user)->get('/teacher/classes');

        $response->assertOk();
        $this->assertCount(1, $response->viewData('sections'));
        $response->assertSee('Dela Cruz, Juan');
        // "Santos, Maria" still appears in the Add Student modal's full roster dropdown,
        // so assert against the students table data rather than raw page text.
        $this->assertCount(1, $response->viewData('students'));
    }

    public function test_section_filter_narrows_the_student_list(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $sectionA = $this->makeSection();
        $sectionB = $this->makeSection();
        $this->linkTeacherToSection($teacher, $sectionA);
        $this->linkTeacherToSection($teacher, $sectionB);

        $this->enrollStudent($sectionA, 'Juan', 'Dela Cruz');
        $this->enrollStudent($sectionB, 'Maria', 'Santos');

        $response = $this->actingAs($user)->get('/teacher/classes?section_id=' . $sectionA->section_id);

        $response->assertOk();
        $response->assertSee('Dela Cruz, Juan');
        // "Santos, Maria" still appears in the Add Student modal's full roster dropdown,
        // so assert against the students table data rather than raw page text.
        $this->assertCount(1, $response->viewData('students'));
    }

    public function test_non_teacher_role_is_forbidden_from_teacher_classes(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $this->actingAs($admin)->get('/teacher/classes')->assertForbidden();
    }

    public function test_teacher_can_enroll_an_existing_student_into_their_own_section(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $section = $this->makeSection();
        $this->linkTeacherToSection($teacher, $section);
        $student = $this->makeStudent($section, 'Juan', 'Dela Cruz');
        $schoolYearId = $this->makeSchoolYear();

        $response = $this->actingAs($user)->post('/teacher/classes', [
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'school_year_id' => $schoolYearId,
            'semester' => '1st Semester',
        ]);

        $response->assertRedirect('/teacher/classes');
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->student_id, 'section_id' => $section->section_id,
            'school_year_id' => $schoolYearId, 'semester' => '1st Semester', 'status' => 'Enrolled',
        ]);
    }

    public function test_teacher_cannot_enroll_a_student_into_a_section_they_do_not_teach(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $ownSection = $this->makeSection();
        $otherSection = $this->makeSection();
        $this->linkTeacherToSection($teacher, $ownSection);
        $student = $this->makeStudent($otherSection, 'Juan', 'Dela Cruz');
        $schoolYearId = $this->makeSchoolYear();

        $response = $this->actingAs($user)->post('/teacher/classes', [
            'student_id' => $student->student_id,
            'section_id' => $otherSection->section_id,
            'school_year_id' => $schoolYearId,
            'semester' => '1st Semester',
        ]);

        $response->assertSessionHasErrors('section_id');
        $this->assertDatabaseMissing('enrollments', ['student_id' => $student->student_id]);
    }

    public function test_teacher_cannot_create_a_duplicate_enrollment_for_the_same_year_and_semester(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $section = $this->makeSection();
        $this->linkTeacherToSection($teacher, $section);
        $student = $this->enrollStudent($section, 'Juan', 'Dela Cruz');
        $existing = Enrollment::where('student_id', $student->student_id)->first();

        $response = $this->actingAs($user)->post('/teacher/classes', [
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'school_year_id' => $existing->school_year_id,
            'semester' => $existing->semester,
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertEquals(1, Enrollment::where('student_id', $student->student_id)->count());
    }

    public function test_teacher_cannot_enroll_a_student_into_a_section_at_max_capacity(): void
    {
        [$user, $teacher] = $this->makeTeacher();
        $section = $this->makeSection();
        $section->update(['max_slots' => 1]);
        $this->linkTeacherToSection($teacher, $section);
        $this->enrollStudent($section, 'Maria', 'Santos');
        $student = $this->makeStudent($section, 'Juan', 'Dela Cruz');
        $schoolYearId = $this->makeSchoolYear();

        $response = $this->actingAs($user)->post('/teacher/classes', [
            'student_id' => $student->student_id,
            'section_id' => $section->section_id,
            'school_year_id' => $schoolYearId,
            'semester' => '1st Semester',
        ]);

        $response->assertSessionHasErrors('section_id');
        $this->assertDatabaseMissing('enrollments', ['student_id' => $student->student_id]);
    }
}
