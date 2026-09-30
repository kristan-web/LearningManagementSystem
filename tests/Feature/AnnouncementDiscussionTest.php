<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
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

class AnnouncementDiscussionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Admin, a teacher teaching section A, a student enrolled in section A,
     * and a second student enrolled in an unrelated section B (no visibility
     * into section A's announcements). Mirrors AssignmentDiscussionTest::world().
     */
    private function world(): array
    {
        $trackId = DB::table('tracks')->insertGetId(['track_code' => 'ACAD-' . uniqid(), 'track_name' => 'Academic', 'created_at' => now()]);
        $strand = Strand::create(['track_id' => $trackId, 'strand_code' => 'STEM-' . uniqid(), 'strand_name' => 'STEM']);

        $sectionA = ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '11', 'section_name' => uniqid(),
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);
        $sectionB = ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '12', 'section_name' => uniqid(),
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);

        $schoolYear = SchoolYear::create(['year' => '2025-2026-' . uniqid(), 'status' => 'active']);

        $adminUser = User::create([
            'first_name' => 'A', 'last_name' => 'Admin', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $teacherUser = User::create([
            'first_name' => 'T', 'last_name' => 'Teacher', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Teacher', 'status' => 'Active',
        ]);
        $teacher = Teacher::create(['user_id' => $teacherUser->user_id, 'teacher_number' => 'T-' . uniqid(), 'specialization' => 'Mathematics']);

        $studentUser = User::create([
            'first_name' => 'S', 'last_name' => 'Student', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Student', 'status' => 'Active',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->user_id, 'lrn' => '1' . str_pad((string) rand(1, 99999999999), 11, '0', STR_PAD_LEFT),
            'student_number' => 'S-' . uniqid(), 'grade_level' => '11',
        ]);
        Enrollment::create([
            'student_id' => $student->student_id, 'section_id' => $sectionA->section_id,
            'school_year_id' => $schoolYear->school_year_id, 'school_year' => '2025-2026',
            'semester' => '1st Semester', 'status' => 'Enrolled', 'date_enrolled' => now(),
        ]);

        $strangerStudentUser = User::create([
            'first_name' => 'X', 'last_name' => 'Stranger', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Student', 'status' => 'Active',
        ]);
        $strangerStudent = Student::create([
            'user_id' => $strangerStudentUser->user_id, 'lrn' => '1' . str_pad((string) rand(1, 99999999999), 11, '0', STR_PAD_LEFT),
            'student_number' => 'S-' . uniqid(), 'grade_level' => '12',
        ]);
        Enrollment::create([
            'student_id' => $strangerStudent->student_id, 'section_id' => $sectionB->section_id,
            'school_year_id' => $schoolYear->school_year_id, 'school_year' => '2025-2026',
            'semester' => '1st Semester', 'status' => 'Enrolled', 'date_enrolled' => now(),
        ]);

        $roomId = DB::table('rooms')->insertGetId(['room_name' => 'Room-' . uniqid(), 'building' => 'Main', 'capacity' => 40, 'created_at' => now()]);
        $subject = Subject::create([
            'strand_id' => $strand->strand_id, 'subject_code' => 'MATH1-' . uniqid(), 'subject_name' => 'Math',
            'subject_type' => 'Core', 'grade_level' => '11', 'semester' => '1st Semester', 'units' => 1.0,
        ]);
        Schedule::create([
            'section_id' => $sectionA->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Monday', 'start_time' => '08:00:00', 'end_time' => '09:00:00',
        ]);

        return [$adminUser, $teacherUser, $studentUser, $strangerStudentUser, $sectionA];
    }

    public function test_student_and_teacher_can_post_a_comment_and_a_reply(): void
    {
        [, $teacherUser, $studentUser, , $sectionA] = $this->world();

        $announcement = Announcement::create([
            'posted_by' => $teacherUser->user_id, 'section_id' => $sectionA->section_id,
            'title' => 'Quiz tomorrow', 'body' => 'Bring your calculator.', 'posted_at' => now(),
        ]);

        // Student asks a question.
        $this->actingAs($studentUser)->post("/announcements/{$announcement->announcement_id}/comments", [
            'body' => 'Is it open notes?',
        ])->assertRedirect();

        $this->assertDatabaseHas('announcement_comments', [
            'announcement_id' => $announcement->announcement_id,
            'user_id' => $studentUser->user_id,
            'body' => 'Is it open notes?',
            'parent_comment_id' => null,
        ]);

        $comment = AnnouncementComment::first();

        // Teacher replies.
        $this->actingAs($teacherUser)->post("/announcements/{$announcement->announcement_id}/comments", [
            'body' => 'No, closed notes.',
            'parent_comment_id' => $comment->comment_id,
        ])->assertRedirect();

        $this->assertDatabaseHas('announcement_comments', [
            'announcement_id' => $announcement->announcement_id,
            'user_id' => $teacherUser->user_id,
            'parent_comment_id' => $comment->comment_id,
        ]);

        $this->assertSame(2, AnnouncementComment::count());

        // Both can see the thread rendered on the announcements feed page.
        $this->actingAs($studentUser)->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('Is it open notes?')
            ->assertSee('No, closed notes.');
    }

    public function test_user_without_visibility_cannot_comment(): void
    {
        [, $teacherUser, , $strangerStudentUser, $sectionA] = $this->world();

        $announcement = Announcement::create([
            'posted_by' => $teacherUser->user_id, 'section_id' => $sectionA->section_id,
            'title' => 'Section A Only', 'body' => 'Body text', 'posted_at' => now(),
        ]);

        $this->actingAs($strangerStudentUser)->post("/announcements/{$announcement->announcement_id}/comments", [
            'body' => 'Sneaky comment',
        ])->assertForbidden();
    }

    public function test_comment_author_can_delete_own_comment_but_a_stranger_cannot(): void
    {
        [, $teacherUser, $studentUser, $strangerStudentUser, ] = $this->world();

        $announcement = Announcement::create([
            'posted_by' => $teacherUser->user_id, 'section_id' => null,
            'title' => 'School-wide notice', 'body' => 'Body text', 'posted_at' => now(),
        ]);

        $this->actingAs($studentUser)->post("/announcements/{$announcement->announcement_id}/comments", [
            'body' => 'My question',
        ]);
        $comment = AnnouncementComment::first();

        // A student with no relation to this comment cannot delete it.
        $this->actingAs($strangerStudentUser)
            ->delete("/announcements/{$announcement->announcement_id}/comments/{$comment->comment_id}")
            ->assertForbidden();

        // The comment's own author may delete it.
        $this->actingAs($studentUser)
            ->delete("/announcements/{$announcement->announcement_id}/comments/{$comment->comment_id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('announcement_comments', ['comment_id' => $comment->comment_id]);
    }

    public function test_posting_teacher_and_admin_can_moderate_any_comment(): void
    {
        [$adminUser, $teacherUser, $studentUser, , $sectionA] = $this->world();

        $announcement = Announcement::create([
            'posted_by' => $teacherUser->user_id, 'section_id' => $sectionA->section_id,
            'title' => 'Reminder', 'body' => 'Body text', 'posted_at' => now(),
        ]);

        $this->actingAs($studentUser)->post("/announcements/{$announcement->announcement_id}/comments", [
            'body' => 'A comment',
        ]);
        $comment = AnnouncementComment::first();

        // The posting teacher may moderate (delete) a student's comment.
        $this->actingAs($teacherUser)
            ->delete("/announcements/{$announcement->announcement_id}/comments/{$comment->comment_id}")
            ->assertRedirect();
        $this->assertDatabaseMissing('announcement_comments', ['comment_id' => $comment->comment_id]);

        // Admin may moderate any comment too.
        $this->actingAs($studentUser)->post("/announcements/{$announcement->announcement_id}/comments", [
            'body' => 'Another comment',
        ]);
        $comment2 = AnnouncementComment::first();

        $this->actingAs($adminUser)
            ->delete("/announcements/{$announcement->announcement_id}/comments/{$comment2->comment_id}")
            ->assertRedirect();
        $this->assertDatabaseMissing('announcement_comments', ['comment_id' => $comment2->comment_id]);
    }
}
