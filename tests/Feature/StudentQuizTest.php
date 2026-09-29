<?php

namespace Tests\Feature;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
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

class StudentQuizTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Student, 2: Schedule, 3: Schedule} [studentUser, student, ownSectionSchedule, otherSectionSchedule] */
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

        $teacherUser = User::create([
            'first_name' => 'T', 'last_name' => 'Teacher', 'email' => uniqid() . '@example.com',
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
        $otherSchedule = Schedule::create([
            'section_id' => $otherSection->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Tuesday', 'start_time' => '10:00:00', 'end_time' => '11:00:00',
        ]);

        return [$studentUser, $student, $schedule, $otherSchedule];
    }

    private function quizWithQuestions(int $scheduleId, int $attemptsAllowed = 1): Quiz
    {
        $quiz = Quiz::create(['schedule_id' => $scheduleId, 'title' => 'Quiz 1', 'attempts_allowed' => $attemptsAllowed]);

        QuizQuestion::create([
            'quiz_id' => $quiz->quiz_id, 'question_text' => 'Capital of PH?', 'question_type' => 'multiple_choice',
            'options' => ['Manila', 'Cebu', 'Davao'], 'correct_answer' => 'Manila',
        ]);
        QuizQuestion::create([
            'quiz_id' => $quiz->quiz_id, 'question_text' => 'Sky is green.', 'question_type' => 'true_false',
            'correct_answer' => 'false',
        ]);

        return $quiz;
    }

    public function test_student_can_start_and_submit_a_quiz(): void
    {
        [$studentUser, $student, $schedule] = $this->world();
        $quiz = $this->quizWithQuestions($schedule->schedule_id);

        $this->actingAs($studentUser)->get("/student/quizzes/{$quiz->quiz_id}/take")->assertOk();

        $attempt = QuizAttempt::where('quiz_id', $quiz->quiz_id)->where('student_id', $student->student_id)->firstOrFail();
        $this->assertNull($attempt->submitted_at);

        $questions = $quiz->questions()->orderBy('question_id')->get();

        $response = $this->actingAs($studentUser)->post(
            "/student/quizzes/{$quiz->quiz_id}/attempts/{$attempt->attempt_id}/submit",
            ['answers' => [
                $questions[0]->question_id => 'Manila',
                $questions[1]->question_id => 'false',
            ]]
        );

        $response->assertRedirect(route('student.quizzes.results', [$quiz->quiz_id, $attempt->attempt_id]));

        $attempt->refresh();
        $this->assertNotNull($attempt->submitted_at);
        $this->assertEquals(100.0, (float) $attempt->score);

        $this->actingAs($studentUser)
            ->get("/student/quizzes/{$quiz->quiz_id}/attempts/{$attempt->attempt_id}/results")
            ->assertOk();
    }

    public function test_wrong_answers_are_scored_correctly(): void
    {
        [$studentUser, $student, $schedule] = $this->world();
        $quiz = $this->quizWithQuestions($schedule->schedule_id);

        $this->actingAs($studentUser)->get("/student/quizzes/{$quiz->quiz_id}/take");
        $attempt = QuizAttempt::where('quiz_id', $quiz->quiz_id)->where('student_id', $student->student_id)->firstOrFail();
        $questions = $quiz->questions()->orderBy('question_id')->get();

        $this->actingAs($studentUser)->post(
            "/student/quizzes/{$quiz->quiz_id}/attempts/{$attempt->attempt_id}/submit",
            ['answers' => [
                $questions[0]->question_id => 'Cebu',
                $questions[1]->question_id => 'false',
            ]]
        );

        $attempt->refresh();
        $this->assertEquals(50.0, (float) $attempt->score);
    }

    public function test_student_cannot_exceed_attempts_allowed(): void
    {
        [$studentUser, $student, $schedule] = $this->world();
        $quiz = $this->quizWithQuestions($schedule->schedule_id, attemptsAllowed: 1);

        QuizAttempt::create([
            'quiz_id' => $quiz->quiz_id, 'student_id' => $student->student_id,
            'started_at' => now()->subMinutes(10), 'submitted_at' => now(), 'score' => 50,
        ]);

        $this->actingAs($studentUser)->get("/student/quizzes/{$quiz->quiz_id}/take")->assertForbidden();
    }

    public function test_student_can_use_second_attempt_when_allowed(): void
    {
        [$studentUser, $student, $schedule] = $this->world();
        $quiz = $this->quizWithQuestions($schedule->schedule_id, attemptsAllowed: 2);

        QuizAttempt::create([
            'quiz_id' => $quiz->quiz_id, 'student_id' => $student->student_id,
            'started_at' => now()->subMinutes(10), 'submitted_at' => now(), 'score' => 50,
        ]);

        $this->actingAs($studentUser)->get("/student/quizzes/{$quiz->quiz_id}/take")->assertOk();
        $this->assertSame(2, QuizAttempt::where('quiz_id', $quiz->quiz_id)->count());
    }

    public function test_student_cannot_take_quiz_outside_enrolled_section(): void
    {
        [$studentUser, , , $otherSchedule] = $this->world();
        $quiz = $this->quizWithQuestions($otherSchedule->schedule_id);

        $this->actingAs($studentUser)->get("/student/quizzes/{$quiz->quiz_id}/take")->assertForbidden();
    }

    public function test_student_cannot_resubmit_an_already_submitted_attempt(): void
    {
        [$studentUser, $student, $schedule] = $this->world();
        $quiz = $this->quizWithQuestions($schedule->schedule_id);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->quiz_id, 'student_id' => $student->student_id,
            'started_at' => now()->subMinutes(10), 'submitted_at' => now(), 'score' => 50,
        ]);

        $this->actingAs($studentUser)->post(
            "/student/quizzes/{$quiz->quiz_id}/attempts/{$attempt->attempt_id}/submit",
            ['answers' => []]
        )->assertForbidden();
    }

    public function test_non_student_role_is_forbidden_from_student_quizzes(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $this->actingAs($admin)->get('/student/quizzes')->assertForbidden();
    }
}
