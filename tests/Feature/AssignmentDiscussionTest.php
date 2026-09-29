<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentComment;
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

class AssignmentDiscussionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teacher teaches section A; a second teacher owns a schedule in a
     * different section (B); the student is enrolled in section A.
     * Mirrors AssignmentLoopTest::world().
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

    public function test_teacher_and_student_can_view_the_post_page(): void
    {
        [$teacherUser, $studentUser, , $schedule, ] = $this->world();

        $assignment = Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Essay',
            'due_date' => now()->addDays(7), 'max_score' => 100,
        ]);

        $this->actingAs($teacherUser)->get("/teacher/assignments/{$assignment->assignment_id}/view")->assertOk();
        $this->actingAs($studentUser)->get("/student/assignments/{$assignment->assignment_id}")->assertOk();
    }

    public function test_student_outside_the_section_cannot_view_or_comment(): void
    {
        [, $studentUser, , , $otherSchedule] = $this->world();

        $otherAssignment = Assignment::create([
            'schedule_id' => $otherSchedule->schedule_id, 'title' => 'Not yours',
            'due_date' => now()->addDays(7), 'max_score' => 100,
        ]);

        $this->actingAs($studentUser)->get("/student/assignments/{$otherAssignment->assignment_id}")->assertForbidden();

        $this->actingAs($studentUser)->post("/assignments/{$otherAssignment->assignment_id}/comments", [
            'body' => 'Sneaky comment',
        ])->assertForbidden();
    }

    public function test_student_and_teacher_can_post_a_comment_and_a_reply(): void
    {
        [$teacherUser, $studentUser, , $schedule, ] = $this->world();

        $assignment = Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Essay',
            'due_date' => now()->addDays(7), 'max_score' => 100,
        ]);

        // Student asks a question.
        $this->actingAs($studentUser)->post("/assignments/{$assignment->assignment_id}/comments", [
            'body' => 'Does this need a bibliography?',
        ])->assertRedirect();

        $this->assertDatabaseHas('assignment_comments', [
            'assignment_id' => $assignment->assignment_id,
            'user_id' => $studentUser->user_id,
            'body' => 'Does this need a bibliography?',
            'parent_comment_id' => null,
        ]);

        $comment = AssignmentComment::first();

        // Teacher replies.
        $this->actingAs($teacherUser)->post("/assignments/{$assignment->assignment_id}/comments", [
            'body' => 'Yes, at least 3 sources.',
            'parent_comment_id' => $comment->comment_id,
        ])->assertRedirect();

        $this->assertDatabaseHas('assignment_comments', [
            'assignment_id' => $assignment->assignment_id,
            'user_id' => $teacherUser->user_id,
            'parent_comment_id' => $comment->comment_id,
        ]);

        $this->assertSame(2, AssignmentComment::count());

        // Both can see the thread rendered on the post page.
        $this->actingAs($studentUser)->get("/student/assignments/{$assignment->assignment_id}")
            ->assertOk()
            ->assertSee('Does this need a bibliography?')
            ->assertSee('Yes, at least 3 sources.');
    }

    public function test_comment_author_can_delete_own_comment_but_a_stranger_cannot(): void
    {
        [$teacherUser, $studentUser, , $schedule, ] = $this->world();

        $assignment = Assignment::create([
            'schedule_id' => $schedule->schedule_id, 'title' => 'Essay',
            'due_date' => now()->addDays(7), 'max_score' => 100,
        ]);

        $this->actingAs($studentUser)->post("/assignments/{$assignment->assignment_id}/comments", [
            'body' => 'My question',
        ]);
        $comment = AssignmentComment::first();

        // A teacher who does not own this schedule cannot even reach the comment (isAccessibleBy fails first).
        $strangerTeacherUser = User::create([
            'first_name' => 'X', 'last_name' => 'Stranger', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Teacher', 'status' => 'Active',
        ]);
        Teacher::create(['user_id' => $strangerTeacherUser->user_id, 'teacher_number' => 'T-' . uniqid(), 'specialization' => 'Science']);

        $this->actingAs($strangerTeacherUser)
            ->delete("/assignments/{$assignment->assignment_id}/comments/{$comment->comment_id}")
            ->assertForbidden();

        // The owning teacher may moderate (delete) any comment on their assignment.
        $this->actingAs($teacherUser)
            ->delete("/assignments/{$assignment->assignment_id}/comments/{$comment->comment_id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('assignment_comments', ['comment_id' => $comment->comment_id]);
    }
}
