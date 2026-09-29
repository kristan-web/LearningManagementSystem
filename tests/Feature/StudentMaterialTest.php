<?php

namespace Tests\Feature;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentMaterialTest extends TestCase
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
        $schedule = Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Monday', 'start_time' => '08:00:00', 'end_time' => '09:00:00',
        ]);

        return [$user, $student, $section, $schedule, $teacherUser, $teacher];
    }
public function test_student_sees_only_published_materials_for_own_section(): void
    {
        [$user, , , $schedule] = $this->actingStudent();

        LearningMaterial::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Published Module', 'status' => 'Published',
            'file_url' => 'learning_materials/1/pub.pdf', 'uploaded_by' => $user->user_id,
        ]);
        LearningMaterial::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Draft Module', 'status' => 'Draft',
            'file_url' => 'learning_materials/1/draft.pdf', 'uploaded_by' => $user->user_id,
        ]);

        $response = $this->actingAs($user)->get('/student/materials');

        $response->assertOk();
        $subjects = $response->viewData('subjects');
        $this->assertCount(1, $subjects);
        $this->assertSame(1, $subjects->first()->count);

        $showResponse = $this->actingAs($user)->get("/student/materials/{$schedule->subject_id}");
        $titles = $showResponse->viewData('materials')->pluck('title');
        $this->assertTrue($titles->contains('Published Module'));
        $this->assertFalse($titles->contains('Draft Module'));
    }

    public function test_student_does_not_see_materials_from_other_sections(): void
    {
        [$user] = $this->actingStudent();
        [, , , $otherSchedule] = $this->actingStudent();

        LearningMaterial::create([
            'schedule_id' => $otherSchedule->schedule_id, 'title' => 'Other Section Module', 'status' => 'Published',
            'file_url' => 'learning_materials/2/other.pdf', 'uploaded_by' => $user->user_id,
        ]);

        $response = $this->actingAs($user)->get('/student/materials');

        $this->assertCount(0, $response->viewData('subjects'));
    }

    public function test_student_can_view_materials_for_own_subject(): void
    {
        [$user, , , $schedule] = $this->actingStudent();

        LearningMaterial::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Published Module', 'status' => 'Published',
            'file_url' => 'learning_materials/1/pub.pdf', 'uploaded_by' => $user->user_id,
        ]);

        $response = $this->actingAs($user)->get("/student/materials/{$schedule->subject_id}");

        $response->assertOk();
        $this->assertTrue($response->viewData('materials')->pluck('title')->contains('Published Module'));
    }

    public function test_student_cannot_view_materials_for_subject_outside_their_section(): void
    {
        [$user] = $this->actingStudent();
        [, , , $otherSchedule] = $this->actingStudent();

        LearningMaterial::create([
            'schedule_id' => $otherSchedule->schedule_id, 'title' => 'Other Section Module', 'status' => 'Published',
            'file_url' => 'learning_materials/2/other.pdf', 'uploaded_by' => $user->user_id,
        ]);

        $this->actingAs($user)->get("/student/materials/{$otherSchedule->subject_id}")->assertForbidden();
    }

    public function test_student_can_download_published_material_for_own_section(): void
    {
        Storage::fake('local');
        [$user, , , $schedule] = $this->actingStudent();

        Storage::disk('local')->put('learning_materials/1/pub.pdf', 'content');
        $material = LearningMaterial::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Published Module', 'status' => 'Published',
            'file_url' => 'learning_materials/1/pub.pdf', 'uploaded_by' => $user->user_id,
        ]);

        $this->actingAs($user)->get("/materials/{$material->material_id}/download")->assertOk();
    }

    public function test_student_cannot_download_draft_material(): void
    {
        Storage::fake('local');
        [$user, , , $schedule] = $this->actingStudent();

        Storage::disk('local')->put('learning_materials/1/draft.pdf', 'content');
        $material = LearningMaterial::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Draft Module', 'status' => 'Draft',
            'file_url' => 'learning_materials/1/draft.pdf', 'uploaded_by' => $user->user_id,
        ]);

        $this->actingAs($user)->get("/materials/{$material->material_id}/download")->assertForbidden();
    }

    public function test_student_can_preview_published_material_for_own_section(): void
    {
        Storage::fake('local');
        [$user, , , $schedule] = $this->actingStudent();

        Storage::disk('local')->put('learning_materials/1/pub.pdf', 'content');
        $material = LearningMaterial::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Published Module', 'status' => 'Published',
            'file_url' => 'learning_materials/1/pub.pdf', 'uploaded_by' => $user->user_id,
        ]);

        $this->actingAs($user)->get("/materials/{$material->material_id}/preview")->assertOk();
    }

    public function test_student_cannot_preview_draft_material(): void
    {
        Storage::fake('local');
        [$user, , , $schedule] = $this->actingStudent();

        Storage::disk('local')->put('learning_materials/1/draft.pdf', 'content');
        $material = LearningMaterial::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Draft Module', 'status' => 'Draft',
            'file_url' => 'learning_materials/1/draft.pdf', 'uploaded_by' => $user->user_id,
        ]);

        $this->actingAs($user)->get("/materials/{$material->material_id}/preview")->assertForbidden();
    }

    public function test_student_cannot_download_material_from_another_section(): void
    {
        Storage::fake('local');
        [$user] = $this->actingStudent();
        [, , , $otherSchedule] = $this->actingStudent();

        Storage::disk('local')->put('learning_materials/2/pub.pdf', 'content');
        $material = LearningMaterial::create([
            'schedule_id' => $otherSchedule->schedule_id, 'title' => 'Other Module', 'status' => 'Published',
            'file_url' => 'learning_materials/2/pub.pdf', 'uploaded_by' => $user->user_id,
        ]);

        $this->actingAs($user)->get("/materials/{$material->material_id}/download")->assertForbidden();
    }

    public function test_non_student_role_is_forbidden_from_student_materials(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $this->actingAs($admin)->get('/student/materials')->assertForbidden();
    }
}
