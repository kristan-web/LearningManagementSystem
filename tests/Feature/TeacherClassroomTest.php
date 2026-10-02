<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\FinalGrade;
use App\Models\GradeComponent;
use App\Models\Message;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\SupportRequest;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * My Classes, Grades & Records, Attendance, Communication, the missing-teacher-profile login fix,
 * and the add-ons: quiz detail editing and results, final grades, support requests,
 * message attachments and attendance remarks.
 */
class TeacherClassroomTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create([
            'first_name' => $role, 'last_name' => uniqid(), 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => $role, 'status' => 'Active',
        ]);
    }

    /** A teacher with one class (schedule) and one enrolled student. */
    private function world(): array
    {
        $teacherUser = $this->user('Teacher');
        $teacher = Teacher::create(['user_id' => $teacherUser->user_id, 'teacher_number' => 'T-' . uniqid(), 'specialization' => 'Math']);

        $trackId = DB::table('tracks')->insertGetId(['track_code' => 'ACAD-' . uniqid(), 'track_name' => 'Academic', 'created_at' => now()]);
        $strand = Strand::create(['track_id' => $trackId, 'strand_code' => 'STEM-' . uniqid(), 'strand_name' => 'STEM']);
        $section = ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '11', 'section_name' => 'Rizal-' . uniqid(),
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);
        $schoolYear = SchoolYear::create(['year' => '2025-2026-' . uniqid(), 'status' => 'active']);
        $roomId = DB::table('rooms')->insertGetId(['room_name' => 'Room-' . uniqid(), 'building' => 'Main', 'capacity' => 40, 'created_at' => now()]);
        $subject = Subject::create([
            'strand_id' => $strand->strand_id, 'subject_code' => 'MATH-' . uniqid(), 'subject_name' => 'General Math',
            'subject_type' => 'Core', 'grade_level' => '11', 'semester' => '1st Semester', 'units' => 1.0,
        ]);
        $schedule = Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Monday', 'start_time' => '08:00:00', 'end_time' => '09:00:00',
        ]);

        $studentUser = $this->user('Student');
        $student = Student::create([
            'user_id' => $studentUser->user_id, 'lrn' => (string) random_int(100000000000, 999999999999),
            'student_number' => 'S-' . uniqid(), 'grade_level' => '11',
        ]);
        Enrollment::create([
            'student_id' => $student->student_id, 'section_id' => $section->section_id,
            'school_year' => '2025-2026', 'school_year_id' => $schoolYear->school_year_id,
            'semester' => '1st Semester', 'status' => 'Enrolled',
        ]);

        return compact('teacherUser', 'teacher', 'schedule', 'studentUser', 'student');
    }

    public function test_teacher_without_a_profile_row_can_log_in_and_open_the_dashboard(): void
    {
        $user = $this->user('Teacher');

        $this->post('/login', ['identifier' => $user->email, 'password' => 'password'])->assertRedirect('/teacher/');

        $this->assertDatabaseHas('teachers', ['user_id' => $user->user_id, 'teacher_number' => 'TCH-PENDING-' . $user->user_id]);
        $this->get('/teacher')->assertOk();
    }

    public function test_teacher_pages_render(): void
    {
        $w = $this->world();

        foreach (['/teacher/classes', '/teacher/grades', '/teacher/attendance', '/teacher/communication', '/teacher/documentation', '/teacher/support'] as $path) {
            $this->actingAs($w['teacherUser'])->get($path)->assertOk();
        }
        $this->actingAs($w['teacherUser'])->get('/teacher/classes')->assertSee('General Math')->assertSee('1 student');
    }

    public function test_attendance_is_saved_updated_and_shown_to_the_student(): void
    {
        $w = $this->world();
        $date = now()->toDateString();
        $payload = ['schedule_id' => $w['schedule']->schedule_id, 'attendance_date' => $date, 'status' => [$w['student']->student_id => 'Late']];

        $this->actingAs($w['teacherUser'])->post('/teacher/attendance', $payload)->assertRedirect();
        $payload['status'][$w['student']->student_id] = 'Absent';
        $this->actingAs($w['teacherUser'])->post('/teacher/attendance', $payload)->assertRedirect();

        $this->assertSame(1, AttendanceRecord::count(), 'saving the same day again updates instead of duplicating');
        $this->assertSame('Absent', AttendanceRecord::first()->status);

        $this->actingAs($w['studentUser'])->get('/student/attendance')->assertOk()->assertSee('General Math')->assertSee('Absent');
    }

    public function test_teacher_cannot_take_attendance_for_another_teachers_class(): void
    {
        $w = $this->world();
        $other = $this->world();

        $this->actingAs($w['teacherUser'])->post('/teacher/attendance', [
            'schedule_id' => $other['schedule']->schedule_id, 'attendance_date' => now()->toDateString(),
            'status' => [$other['student']->student_id => 'Present'],
        ])->assertForbidden();
    }

    public function test_gradebook_averages_assignment_and_best_quiz_scores(): void
    {
        $w = $this->world();
        $assignment = Assignment::create(['schedule_id' => $w['schedule']->schedule_id, 'title' => 'Essay', 'due_date' => now()->addDay(), 'max_score' => 50]);
        Submission::create(['assignment_id' => $assignment->assignment_id, 'student_id' => $w['student']->student_id, 'submitted_at' => now(), 'score' => 40, 'status' => 'Graded']);
        $quiz = Quiz::create(['schedule_id' => $w['schedule']->schedule_id, 'title' => 'Quiz 1', 'attempts_allowed' => 2]);
        QuizAttempt::create(['quiz_id' => $quiz->quiz_id, 'student_id' => $w['student']->student_id, 'score' => 60, 'submitted_at' => now()]);
        QuizAttempt::create(['quiz_id' => $quiz->quiz_id, 'student_id' => $w['student']->student_id, 'score' => 90, 'submitted_at' => now()]);

        // (40/50 = 80%) and best quiz 90% -> 85%
        $this->actingAs($w['teacherUser'])->get('/teacher/grades?schedule_id=' . $w['schedule']->schedule_id)
            ->assertOk()->assertSee('85%');
    }

    public function test_student_and_teacher_can_message_and_unread_clears_on_open(): void
    {
        $w = $this->world();

        $this->actingAs($w['studentUser'])->post('/messages', ['receiver_id' => $w['teacherUser']->user_id, 'body' => 'Hello sir'])
            ->assertRedirect(route('student.communication.index', ['with' => $w['teacherUser']->user_id]));
        $this->assertSame(1, Message::whereNull('read_at')->count());

        $this->actingAs($w['teacherUser'])->get('/teacher/communication?with=' . $w['studentUser']->user_id)->assertOk()->assertSee('Hello sir');
        $this->assertSame(0, Message::whereNull('read_at')->count());
    }

    public function test_student_cannot_message_someone_outside_their_classes(): void
    {
        $w = $this->world();
        $stranger = $this->user('Teacher');

        $this->actingAs($w['studentUser'])->post('/messages', ['receiver_id' => $stranger->user_id, 'body' => 'Hi'])->assertForbidden();
        $this->assertSame(0, Message::count());
    }

    public function test_teacher_edits_quiz_details_without_touching_questions(): void
    {
        $w = $this->world();
        $quiz = Quiz::create(['schedule_id' => $w['schedule']->schedule_id, 'title' => 'Quiz 1', 'attempts_allowed' => 1]);
        QuizQuestion::create(['quiz_id' => $quiz->quiz_id, 'question_text' => 'Sky is green.', 'question_type' => 'true_false', 'correct_answer' => 'false']);

        $this->actingAs($w['teacherUser'])->get('/teacher/quizzes/' . $quiz->quiz_id . '/edit')->assertOk()->assertSee('Quiz 1');
        $this->actingAs($w['teacherUser'])->put('/teacher/quizzes/' . $quiz->quiz_id, [
            'title' => 'Quiz 1 (revised)', 'time_limit_minutes' => 15, 'attempts_allowed' => 2, 'due_date' => now()->addWeek()->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('teacher.quizzes.index'));

        $quiz->refresh();
        $this->assertSame('Quiz 1 (revised)', $quiz->title);
        $this->assertSame(15, (int) $quiz->time_limit_minutes);
        $this->assertSame(2, (int) $quiz->attempts_allowed);
        $this->assertSame(1, QuizQuestion::where('quiz_id', $quiz->quiz_id)->count());

        $other = $this->world();
        $this->actingAs($other['teacherUser'])->put('/teacher/quizzes/' . $quiz->quiz_id, ['title' => 'Hijack', 'attempts_allowed' => 1])->assertForbidden();
    }

    public function test_teacher_sees_quiz_results_and_a_students_answers(): void
    {
        $w = $this->world();
        $quiz = Quiz::create(['schedule_id' => $w['schedule']->schedule_id, 'title' => 'Quiz 1', 'attempts_allowed' => 1]);
        $q1 = QuizQuestion::create(['quiz_id' => $quiz->quiz_id, 'question_text' => 'Capital of PH?', 'question_type' => 'multiple_choice', 'options' => ['Manila', 'Cebu'], 'correct_answer' => 'Manila']);
        $q2 = QuizQuestion::create(['quiz_id' => $quiz->quiz_id, 'question_text' => 'Sky is green.', 'question_type' => 'true_false', 'correct_answer' => 'false']);
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->quiz_id, 'student_id' => $w['student']->student_id, 'score' => 50, 'submitted_at' => now(),
            'answers' => [$q1->question_id => 'Cebu', $q2->question_id => 'false'],
        ]);

        $this->actingAs($w['teacherUser'])->get('/teacher/quizzes/' . $quiz->quiz_id . '/results')->assertOk()->assertSee('50%')->assertSee('Attempt 1');
        $this->actingAs($w['teacherUser'])->get('/teacher/quizzes/' . $quiz->quiz_id . '/attempts/' . $attempt->attempt_id)
            ->assertOk()->assertSee('Cebu')->assertSee('Correct answer')->assertSee('1 of 2 correct');

        $other = $this->world();
        $this->actingAs($other['teacherUser'])->get('/teacher/quizzes/' . $quiz->quiz_id . '/results')->assertForbidden();
    }

    public function test_final_grades_are_saved_with_components_and_shown_to_the_student(): void
    {
        $w = $this->world();
        $assignment = Assignment::create(['schedule_id' => $w['schedule']->schedule_id, 'title' => 'Essay', 'due_date' => now()->addDay(), 'max_score' => 50]);
        Submission::create(['assignment_id' => $assignment->assignment_id, 'student_id' => $w['student']->student_id, 'submitted_at' => now(), 'score' => 40, 'status' => 'Graded']);
        $sid = $w['student']->student_id;
        $post = fn (array $rating, array $remarks = []) => $this->actingAs($w['teacherUser'])->post('/teacher/grades/final', [
            'schedule_id' => $w['schedule']->schedule_id, 'final_rating' => $rating, 'remarks' => $remarks,
        ]);

        $post([$sid => 80])->assertRedirect();
        $grade = FinalGrade::first();
        $this->assertSame(80.0, $grade->final_rating);
        $this->assertSame('Passed', $grade->remarks);
        $this->assertSame(1, GradeComponent::where('source_type', 'submission')->count());

        $post([$sid => 70], [$sid => 'Incomplete'])->assertRedirect();
        $this->assertSame(1, FinalGrade::count(), 'saving again updates instead of duplicating');
        $this->assertSame('Incomplete', FinalGrade::first()->remarks);
        $this->assertSame(1, GradeComponent::count(), 'components are replaced, not appended');

        FinalGrade::query()->update(['is_locked' => true]);
        $post([$sid => 99])->assertSessionHas('success', fn ($m) => str_contains($m, '1 locked grade'));
        $this->assertSame(70.0, FinalGrade::first()->final_rating);

        $this->actingAs($w['studentUser'])->get('/student/grades')->assertOk()->assertSee('General Math')->assertSee('Incomplete');

        $other = $this->world();
        $this->actingAs($other['teacherUser'])->post('/teacher/grades/final', ['schedule_id' => $w['schedule']->schedule_id, 'final_rating' => [$sid => 1]])->assertForbidden();
    }

    public function test_support_requests_are_saved_and_resolved_by_an_admin(): void
    {
        Storage::fake('local');
        $w = $this->world();

        $this->actingAs($w['studentUser'])->from('/student/support')->post('/support-requests', [
            'category' => 'Account & Access', 'subject' => 'Cannot see my class', 'message' => 'My schedule is empty.',
            // 1x1 PNG bytes (fake()->image() needs GD, which not every PHP install has).
            'attachment' => UploadedFile::fake()->createWithContent('screen.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==')),
        ])->assertRedirect('/student/support');

        $request = SupportRequest::first();
        $this->assertSame('Open', $request->status);
        Storage::disk('local')->assertExists($request->attachment_path);

        $this->actingAs($w['studentUser'])->get('/admin/support/requests')->assertForbidden();
        $admin = $this->user('Admin');
        $this->actingAs($admin)->get('/admin/support/requests')->assertOk()->assertSee('Cannot see my class');
        $this->actingAs($admin)->get('/admin/support/requests/' . $request->support_request_id . '/attachment')->assertOk();
        $this->actingAs($admin)->put('/admin/support/requests/' . $request->support_request_id, ['status' => 'Resolved'])->assertRedirect();
        $this->assertSame('Resolved', $request->fresh()->status);
        $this->assertNotNull($request->fresh()->resolved_at);
    }

    public function test_message_attachment_is_sent_and_only_the_two_people_can_download_it(): void
    {
        Storage::fake('local');
        $w = $this->world();

        $this->actingAs($w['teacherUser'])->post('/messages', [
            'receiver_id' => $w['studentUser']->user_id, 'attachment' => UploadedFile::fake()->create('notes.pdf', 20, 'application/pdf'),
        ])->assertRedirect();

        $message = Message::first();
        $this->assertSame('', $message->body);
        $this->assertSame('notes.pdf', $message->attachment_name);

        $this->actingAs($w['studentUser'])->get('/student/communication?with=' . $w['teacherUser']->user_id)->assertOk()->assertSee('notes.pdf');
        $this->actingAs($w['studentUser'])->get('/messages/' . $message->message_id . '/attachment')->assertOk();
        $this->actingAs($this->user('Student'))->get('/messages/' . $message->message_id . '/attachment')->assertForbidden();

        $this->actingAs($w['teacherUser'])->post('/messages', ['receiver_id' => $w['studentUser']->user_id])->assertSessionHasErrors('body');
    }

    public function test_attendance_remarks_are_saved_and_shown_to_the_student(): void
    {
        $w = $this->world();

        $this->actingAs($w['teacherUser'])->post('/teacher/attendance', [
            'schedule_id' => $w['schedule']->schedule_id, 'attendance_date' => now()->toDateString(),
            'status' => [$w['student']->student_id => 'Excused'], 'remarks' => [$w['student']->student_id => 'Medical certificate'],
        ])->assertRedirect();

        $this->assertSame('Medical certificate', AttendanceRecord::first()->remarks);
        $this->actingAs($w['studentUser'])->get('/student/attendance')->assertOk()->assertSee('Medical certificate');
    }
}
