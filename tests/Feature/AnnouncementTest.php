<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\Strand;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\AnnouncementAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $teacherUser;
    private Teacher $teacher;
    private User $studentUser;
    private Student $student;
    private ClassSection $sectionA;
    private ClassSection $sectionB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin_' . uniqid() . '@example.com',
            'password' => Hash::make('password'), 'role' => 'Admin', 'status' => 'Active', 'must_change_password' => false,
        ]);

        $this->teacherUser = User::create([
            'first_name' => 'John', 'last_name' => 'Doe', 'email' => 'teacher_' . uniqid() . '@example.com',
            'password' => Hash::make('password'), 'role' => 'Teacher', 'status' => 'Active', 'must_change_password' => false,
        ]);
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->user_id,
            'teacher_number' => 'TCH-' . uniqid(),
            'specialization' => 'Science',
        ]);

        $this->studentUser = User::create([
            'first_name' => 'Alice', 'last_name' => 'Student', 'email' => 'student_' . uniqid() . '@example.com',
            'password' => Hash::make('password'), 'role' => 'Student', 'status' => 'Active', 'must_change_password' => false,
        ]);

        $trackId = DB::table('tracks')->insertGetId(['track_code' => 'ACAD-' . uniqid(), 'track_name' => 'Academic', 'created_at' => now()]);
        $strand = Strand::create(['track_id' => $trackId, 'strand_code' => 'STEM-' . uniqid(), 'strand_name' => 'STEM']);

        $this->student = Student::create([
            'user_id' => $this->studentUser->user_id,
            'strand_id' => $strand->strand_id,
            'lrn' => '1' . str_pad(rand(1, 99999999999), 11, '0', STR_PAD_LEFT),
            'student_number' => 'STU-' . uniqid(),
            'grade_level' => '11',
        ]);

        $this->sectionA = ClassSection::create([
            'strand_id' => $strand->strand_id,
            'section_name' => '11-A ' . uniqid(),
            'grade_level' => '11',
            'school_year' => '2026-2027',
            'max_slots' => 40,
            'status' => 'Open',
        ]);

        $this->sectionB = ClassSection::create([
            'strand_id' => $strand->strand_id,
            'section_name' => '11-B ' . uniqid(),
            'grade_level' => '11',
            'school_year' => '2026-2027',
            'max_slots' => 40,
            'status' => 'Open',
        ]);

        $schoolYear = SchoolYear::create(['year' => '2026-2027-' . uniqid(), 'status' => 'active']);

        Enrollment::create([
            'student_id' => $this->student->student_id,
            'section_id' => $this->sectionA->section_id,
            'school_year_id' => $schoolYear->school_year_id,
            'school_year' => '2026-2027',
            'semester' => '1st Semester',
            'status' => 'Enrolled',
            'date_enrolled' => now(),
        ]);

        $subject = Subject::create([
            'strand_id' => $strand->strand_id,
            'subject_code' => 'SUBJ-' . uniqid(),
            'subject_name' => 'General Science',
            'subject_type' => 'Core',
            'grade_level' => '11',
            'semester' => '1st Semester',
            'units' => 1.0,
        ]);
        $roomId = DB::table('rooms')->insertGetId(['room_name' => 'Room-' . uniqid(), 'building' => 'Main', 'capacity' => 40, 'created_at' => now()]);

        Schedule::create([
            'section_id' => $this->sectionA->section_id,
            'subject_id' => $subject->subject_id,
            'teacher_id' => $this->teacher->teacher_id,
            'room_id' => $roomId,
            'day_of_week' => 'Monday',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
        ]);
    }


    public function test_student_sees_school_wide_and_enrolled_section_announcements(): void
    {
        Announcement::create([
            'posted_by' => $this->adminUser->user_id,
            'section_id' => null,
            'title' => 'School-Wide General Notice',
            'body' => 'Classes suspended tomorrow.',
            'posted_at' => now(),
        ]);

        Announcement::create([
            'posted_by' => $this->teacherUser->user_id,
            'section_id' => $this->sectionA->section_id,
            'title' => 'Section A Specific Notice',
            'body' => 'Bring calculator tomorrow.',
            'posted_at' => now(),
        ]);

        Announcement::create([
            'posted_by' => $this->adminUser->user_id,
            'section_id' => $this->sectionB->section_id,
            'title' => 'Section B Specific Notice',
            'body' => 'Field trip consent form due.',
            'posted_at' => now(),
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('announcements.index'));
        $response->assertOk();
        $response->assertSee('School-Wide General Notice');
        $response->assertSee('Section A Specific Notice');
        $response->assertDontSee('Section B Specific Notice');
    }

    public function test_teacher_can_post_announcement_to_their_section(): void
    {
        $response = $this->actingAs($this->teacherUser)->post(route('announcements.store'), [
            'title' => 'Lab Experiment Schedule',
            'body' => 'Wear lab coats.',
            'section_id' => (string) $this->sectionA->section_id,
        ]);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'title' => 'Lab Experiment Schedule',
            'section_id' => $this->sectionA->section_id,
            'posted_by' => $this->teacherUser->user_id,
        ]);
    }

    /**
     * Real 1x1 PNG bytes — the 'image' validation rule sniffs actual file
     * content (not the filename), and the GD extension isn't available in
     * this environment for UploadedFile::fake()->image().
     */
    private function fakePng(string $name = 'cover.png'): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    public function test_teacher_can_post_announcement_with_thumbnail_and_attachments(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->teacherUser)->post(route('announcements.store'), [
            'title' => 'Field Trip Photos',
            'body' => 'See attached forms.',
            'section_id' => (string) $this->sectionA->section_id,
            'thumbnail' => $this->fakePng(),
            'attachments' => [
                UploadedFile::fake()->create('consent.pdf', 100),
                UploadedFile::fake()->create('itinerary.docx', 50),
            ],
        ]);

        $response->assertRedirect(route('announcements.index'));

        $announcement = Announcement::where('title', 'Field Trip Photos')->firstOrFail();
        $this->assertNotNull($announcement->thumbnail_path);
        Storage::disk('local')->assertExists($announcement->thumbnail_path);

        $this->assertSame(2, AnnouncementAttachment::where('announcement_id', $announcement->announcement_id)->count());
        foreach ($announcement->attachments as $attachment) {
            Storage::disk('local')->assertExists($attachment->file_url);
        }
    }

    public function test_announcement_thumbnail_rejects_invalid_file_type(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->teacherUser)->post(route('announcements.store'), [
            'title' => 'Bad Thumbnail',
            'body' => 'Should be rejected.',
            'section_id' => (string) $this->sectionA->section_id,
            'thumbnail' => UploadedFile::fake()->create('not-an-image.pdf', 100),
        ]);

        $response->assertSessionHasErrors('thumbnail');
        $this->assertDatabaseMissing('announcements', ['title' => 'Bad Thumbnail']);
    }

    public function test_deleting_announcement_removes_thumbnail_and_attachment_files(): void
    {
        Storage::fake('local');

        $this->actingAs($this->teacherUser)->post(route('announcements.store'), [
            'title' => 'Cleanup Test',
            'body' => 'Delete me.',
            'section_id' => (string) $this->sectionA->section_id,
            'thumbnail' => $this->fakePng(),
            'attachments' => [UploadedFile::fake()->create('file.pdf', 50)],
        ]);

        $announcement = Announcement::where('title', 'Cleanup Test')->firstOrFail();
        $thumbnailPath = $announcement->thumbnail_path;
        $attachmentPath = $announcement->attachments->first()->file_url;

        $this->actingAs($this->teacherUser)->delete(route('announcements.destroy', $announcement))
            ->assertRedirect(route('announcements.index'));

        Storage::disk('local')->assertMissing($thumbnailPath);
        Storage::disk('local')->assertMissing($attachmentPath);
        $this->assertDatabaseMissing('announcement_attachments', ['announcement_id' => $announcement->announcement_id]);
    }

    public function test_teacher_cannot_post_announcement_to_section_they_do_not_teach(): void
    {
        $response = $this->actingAs($this->teacherUser)->post(route('announcements.store'), [
            'title' => 'Section B Invalid Post',
            'body' => 'Should be blocked.',
            'section_id' => (string) $this->sectionB->section_id,
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_post_school_wide_announcement(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('announcements.store'), [
            'title' => 'Flag Ceremony Notice',
            'body' => 'Assembly starts at 7:00 AM.',
            'section_id' => 'school_wide',
        ]);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'title' => 'Flag Ceremony Notice',
            'section_id' => null,
            'posted_by' => $this->adminUser->user_id,
        ]);
    }

    public function test_student_cannot_post_announcement(): void
    {
        $response = $this->actingAs($this->studentUser)->post(route('announcements.store'), [
            'title' => 'Student Post Attempt',
            'body' => 'Unauthorized.',
            'section_id' => 'school_wide',
        ]);

        $response->assertForbidden();
    }

    public function test_teacher_can_delete_own_announcement_but_not_others(): void
    {
        $own = Announcement::create([
            'posted_by' => $this->teacherUser->user_id,
            'section_id' => $this->sectionA->section_id,
            'title' => 'Own Announcement',
            'body' => 'Body text',
            'posted_at' => now(),
        ]);

        $other = Announcement::create([
            'posted_by' => $this->adminUser->user_id,
            'section_id' => null,
            'title' => 'Admin Announcement',
            'body' => 'Body text',
            'posted_at' => now(),
        ]);

        $res1 = $this->actingAs($this->teacherUser)->delete(route('announcements.destroy', $own));
        $res1->assertRedirect(route('announcements.index'));
        $this->assertDatabaseMissing('announcements', ['announcement_id' => $own->announcement_id]);

        $res2 = $this->actingAs($this->teacherUser)->delete(route('announcements.destroy', $other));
        $res2->assertForbidden();
    }

    public function test_admin_can_delete_any_announcement(): void
    {
        $teacherAnnouncement = Announcement::create([
            'posted_by' => $this->teacherUser->user_id,
            'section_id' => $this->sectionA->section_id,
            'title' => 'Teacher Announcement',
            'body' => 'Body text',
            'posted_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('announcements.destroy', $teacherAnnouncement));
        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseMissing('announcements', ['announcement_id' => $teacherAnnouncement->announcement_id]);
    }
}
