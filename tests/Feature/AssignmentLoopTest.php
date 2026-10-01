<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssignmentLoopTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teacher teaches section A; a second teacher owns an assignment in a
     * different section (B); the student is enrolled in section A.
     */
    private function world(): array
    {
        $trackId = DB::table('tracks')->insertGetId(['track_code' => 'ACAD-' . uniqid(), 'track_name' => 'Academic', 'created_at' => now()]);
        $strand = Strand::create(['track_id' => $trackId, 'strand_code' => 'STEM-' . uniqid(), 'strand_name' => 'STEM']);

        $section = ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '11', 'section_name' => uniqid(),
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);
        $otherSection = ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '12', 'section_name' => uniqid(),
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);

        $schoolYear = SchoolYear::create(['year' => '2025-2026-' . uniqid(), 'status' => 'active']);

        $teacherUser = User::create([
            'first_name' => 'T', 'last_name' => 'Teacher', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Teacher', 'status' => 'Active',
        ]);
        $teacher = Teacher::create(['user_id' => $teacherUser->user_id, 'teacher_number' => 'T-' . uniqid(), 'specialization' => 'Mathematics']);

        $otherUser = User::create([
            'first_name' => 'O', 'last_name' => 'Other', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Teacher', 'status' => 'Active',
        ]);
        $other = Teacher::create(['user_id' => $otherUser->user_id, 'teacher_number' => 'T-' . uniqid(), 'specialization' => 'English']);

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

        $otherSchedule = Schedule::create([
            'section_id' => $otherSection->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $other->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Tuesday', 'start_time' => '10:00:00', 'end_time' => '11:00:00',
        ]);

        $studentUser = User::create([
            'first_name' => 'S', 'last_name' => 'Student', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Student', 'status' => 'Active',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->user_id, 'lrn' => (string) random_int(100000000000, 999999999999),
            'student_number' => 'S-' . uniqid(), 'grade_level' => '11',
        ]);
        Enrollment::create([
            'student_id' => $student->student_id, 'section_id' => $section->section_id,
            'school_year' => '2025-2026', 'school_year_id' => $schoolYear->school_year_id,
            'semester' => '1st Semester', 'status' => 'Enrolled',
        ]);

        return [$teacherUser, $studentUser, $student, $schedule, $otherSchedule];
    }

    public function test_teacher_creates_edits_and_deletes_assignment(): void
    {
        [$teacherUser, , , $schedule, ] = $this->world();

        // Create/edit pages render before any assignment exists or for one that does.
        $this->actingAs($teacherUser)->get('/teacher/assignments/create')->assertOk();

        $this->actingAs($teacherUser)->post('/teacher/assignments', [
            'schedule_id' => $schedule->schedule_id,
            'title' => 'Chapter 1 Essay',
            'instructions' => 'Write 500 words.',
            'due_date' => '2026-10-30T23:59',
            'max_score' => 50,
        ])->assertRedirect(route('teacher.assignments.index'));

        $this->assertDatabaseHas('assignments', [
            'schedule_id' => $schedule->schedule_id, 'title' => 'Chapter 1 Essay', 'max_score' => 50,
        ]);

        // Index page renders with per-assignment counts (withCount) and stats.
        $this->actingAs($teacherUser)->get('/teacher/assignments')->assertOk();

        $assignment = Assignment::first();

        $this->actingAs($teacherUser)->get("/teacher/assignments/{$assignment->assignment_id}/edit")->assertOk();

        $this->actingAs($teacherUser)->put("/teacher/assignments/{$assignment->assignment_id}", [
            'schedule_id' => $schedule->schedule_id,
            'title' => 'Chapter 1 Essay (revised)',
            'due_date' => '2026-10-30T23:59',
            'max_score' => 60,
        ])->assertRedirect(route('teacher.assignments.index'));

        $this->assertDatabaseHas('assignments', [
            'assignment_id' => $assignment->assignment_id, 'title' => 'Chapter 1 Essay (revised)', 'max_score' => 60,
        ]);

        $this->actingAs($teacherUser)->delete("/teacher/assignments/{$assignment->assignment_id}")->assertRedirect(route('teacher.assignments.index'));
        $this->assertDatabaseMissing('assignments', ['assignment_id' => $assignment->assignment_id]);
    }

    public function test_teacher_cannot_touch_another_teachers_assignment(): void
    {
        [$teacherUser, , , , $otherSchedule] = $this->world();

        $otherAssignment = Assignment::create([
            'schedule_id' => $otherSchedule->schedule_id, 'title' => 'Not yours',
            'due_date' => now()->addDays(7), 'max_score' => 100,
        ]);

        $this->actingAs($teacherUser)
            ->get("/teacher/assignments/{$otherAssignment->assignment_id}/submissions")
            ->assertForbidden();

        $this->actingAs($teacherUser)
            ->put("/teacher/assignments/{$otherAssignment->assignment_id}", [
                'schedule_id' => $otherSchedule->schedule_id, 'title' => 'Hijacked',
                'due_date' => '2026-10-30T23:59', 'max_score' => 100,
            ])->assertForbidden();
    }

    public function test_submission_and_grading_loop(): void
    {
        Storage::fake('local');
        [$teacherUser, $studentUser, $student, $schedule, ] = $this->world();

        $assignment = Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Essay',
            'due_date' => now()->addDays(7), 'max_score' => 100,
        ]);

        $this->actingAs($studentUser)->get('/student/assignments')->assertOk();

        $this->actingAs($studentUser)->post("/student/assignments/{$assignment->assignment_id}/submit", [
            'file' => UploadedFile::fake()->create('essay.pdf', 200),
        ])->assertRedirect(route('student.assignments.index'));

        $submission = Submission::first();
        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->assignment_id,
            'student_id' => $student->student_id,
            'status' => 'Submitted',
        ]);
        Storage::disk('local')->assertExists($submission->file_url);

        // Duplicate submission is rejected and keeps a single row.
        $this->actingAs($studentUser)->post("/student/assignments/{$assignment->assignment_id}/submit", [
            'file' => UploadedFile::fake()->create('again.pdf', 100),
        ])->assertRedirect(route('student.assignments.index'));
        $this->assertSame(1, Submission::count());

        // Teacher sees the submission and grades it.
        $this->actingAs($teacherUser)
            ->get("/teacher/assignments/{$assignment->assignment_id}/submissions")
            ->assertOk();

        $this->actingAs($teacherUser)->put("/teacher/submissions/{$submission->submission_id}", [
            'score' => 95,
        ])->assertRedirect(route('teacher.assignments.submissions', $assignment->assignment_id));

        $this->assertDatabaseHas('submissions', [
            'submission_id' => $submission->submission_id, 'status' => 'Graded', 'score' => 95,
        ]);

        // Student page renders from the graded row.
        $this->actingAs($studentUser)->get('/student/assignments')->assertOk();
    }

    public function test_teacher_can_manually_tag_a_submission_as_late_during_grading(): void
    {
        Storage::fake('local');
        [$teacherUser, $studentUser, $student, $schedule, ] = $this->world();

        $assignment = Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Essay',
            'due_date' => now()->addDays(7), 'max_score' => 100,
        ]);

        $this->actingAs($studentUser)->post("/student/assignments/{$assignment->assignment_id}/submit", [
            'file' => UploadedFile::fake()->create('essay.pdf', 200),
        ]);

        $submission = Submission::first();

        $this->actingAs($teacherUser)->put("/teacher/submissions/{$submission->submission_id}", [
            'score' => 80,
            'status' => 'Late',
        ])->assertRedirect(route('teacher.assignments.submissions', $assignment->assignment_id));

        $this->assertDatabaseHas('submissions', [
            'submission_id' => $submission->submission_id, 'status' => 'Late', 'score' => 80,
        ]);
    }

    public function test_submission_past_due_date_is_blocked(): void
    {
        Storage::fake('local');
        [, $studentUser, , $schedule, ] = $this->world();

        $assignment = Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Old essay',
            'due_date' => now()->addDays(-1), 'max_score' => 100,
        ]);

        $this->actingAs($studentUser)->get('/student/assignments');

        $this->actingAs($studentUser)->post("/student/assignments/{$assignment->assignment_id}/submit", [
            'file' => UploadedFile::fake()->create('late.pdf', 100),
        ])->assertRedirect(route('student.assignments.index'))->assertSessionHas('error');

        $this->assertDatabaseMissing('submissions', ['assignment_id' => $assignment->assignment_id]);
    }

    public function test_teacher_can_extend_assignment_deadline(): void
    {
        [$teacherUser, , , $schedule, ] = $this->world();

        $assignment = Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Old essay',
            'due_date' => now()->addDays(-1), 'max_score' => 100,
        ]);

        $newDueDate = now()->addDays(3);

        $this->actingAs($teacherUser)->put("/teacher/assignments/{$assignment->assignment_id}/extend", [
            'due_date' => $newDueDate->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $assignment->refresh();
        $this->assertFalse($assignment->isPastDue());
    }

    public function test_teacher_cannot_extend_another_teachers_assignment(): void
    {
        [$teacherUser, , , , $otherSchedule] = $this->world();

        $otherAssignment = Assignment::create([
            'schedule_id' => $otherSchedule->schedule_id, 'title' => 'Other class essay',
            'due_date' => now()->addDays(-1), 'max_score' => 100,
        ]);

        $this->actingAs($teacherUser)->put("/teacher/assignments/{$otherAssignment->assignment_id}/extend", [
            'due_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ])->assertForbidden();
    }

    public function test_student_cannot_submit_assignment_outside_enrolled_section(): void
    {
        Storage::fake('local');
        [, $studentUser, , , $otherSchedule] = $this->world();

        $otherAssignment = Assignment::create([
            'schedule_id' => $otherSchedule->schedule_id, 'title' => 'Other class essay',
            'due_date' => now()->addDays(7), 'max_score' => 100,
        ]);

        $this->actingAs($studentUser)->post("/student/assignments/{$otherAssignment->assignment_id}/submit", [
            'file' => UploadedFile::fake()->create('nope.pdf', 100),
        ])->assertForbidden();
    }
}