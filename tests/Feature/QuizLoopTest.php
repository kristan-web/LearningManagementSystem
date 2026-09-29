<?php

namespace Tests\Feature;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
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

class QuizLoopTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teacher teaches section A; a second teacher owns a quiz in a different
     * section (B); the student is enrolled in section A. Mirrors
     * AssignmentLoopTest::world().
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

    private function quizPayload(int $scheduleId): array
    {
        return [
            'schedule_id' => $scheduleId,
            'title' => 'Unit 1 Quiz',
            'due_date' => '2026-10-30T23:59',
            'time_limit_minutes' => 15,
            'questions' => [
                [
                    'question_text' => 'What is 2 + 2?',
                    'question_type' => 'multiple_choice',
                    'options' => ['3', '4', '5'],
                    'correct_answer' => '4',
                ],
                [
                    'question_text' => 'The sky is blue.',
                    'question_type' => 'true_false',
                    'correct_answer' => 'True',
                ],
            ],
        ];
    }

    public function test_teacher_creates_edits_and_deletes_quiz(): void
    {
        [$teacherUser, , , $schedule, ] = $this->world();

        $this->actingAs($teacherUser)->get('/teacher/quizzes/create')->assertOk();

        $this->actingAs($teacherUser)->post('/teacher/quizzes', $this->quizPayload($schedule->schedule_id))
            ->assertRedirect(route('teacher.quizzes.index'));

        $this->assertDatabaseHas('quizzes', ['schedule_id' => $schedule->schedule_id, 'title' => 'Unit 1 Quiz']);
        $quiz = Quiz::first();
        $this->assertSame(2, $quiz->questions()->count());

        $this->actingAs($teacherUser)->get('/teacher/quizzes')->assertOk();
        $this->actingAs($teacherUser)->get("/teacher/quizzes/{$quiz->quiz_id}/edit")->assertOk();

        $updated = $this->quizPayload($schedule->schedule_id);
        $updated['title'] = 'Unit 1 Quiz (revised)';
        unset($updated['questions'][1]); // drop the true/false question on edit
        $updated['questions'] = array_values($updated['questions']);

        $this->actingAs($teacherUser)->put("/teacher/quizzes/{$quiz->quiz_id}", $updated)
            ->assertRedirect(route('teacher.quizzes.index'));

        $this->assertDatabaseHas('quizzes', ['quiz_id' => $quiz->quiz_id, 'title' => 'Unit 1 Quiz (revised)']);
        $this->assertSame(1, $quiz->questions()->count());

        $this->actingAs($teacherUser)->delete("/teacher/quizzes/{$quiz->quiz_id}")
            ->assertRedirect(route('teacher.quizzes.index'));
        $this->assertDatabaseMissing('quizzes', ['quiz_id' => $quiz->quiz_id]);
    }

    public function test_teacher_cannot_touch_another_teachers_quiz(): void
    {
        [$teacherUser, , , , $otherSchedule] = $this->world();

        $this->actingAs($teacherUser)->post('/teacher/quizzes', $this->quizPayload($otherSchedule->schedule_id))
            ->assertForbidden();

        $otherQuiz = Quiz::create(['schedule_id' => $otherSchedule->schedule_id, 'title' => 'Not yours']);

        $this->actingAs($teacherUser)->get("/teacher/quizzes/{$otherQuiz->quiz_id}/attempts")->assertForbidden();
        $this->actingAs($teacherUser)->delete("/teacher/quizzes/{$otherQuiz->quiz_id}")->assertForbidden();
    }

    public function test_student_takes_and_auto_grades_an_objective_quiz(): void
    {
        [$teacherUser, $studentUser, $student, $schedule, ] = $this->world();

        $this->actingAs($teacherUser)->post('/teacher/quizzes', $this->quizPayload($schedule->schedule_id));
        $quiz = Quiz::first();
        [$mcq, $tf] = $quiz->questions()->orderBy('question_id')->get();

        $this->actingAs($studentUser)->get('/student/quizzes')->assertOk();
        $this->actingAs($studentUser)->get("/student/quizzes/{$quiz->quiz_id}")->assertOk();

        // Starting an attempt is idempotent — visiting again resumes the same row.
        $this->assertSame(1, QuizAttempt::count());
        $this->actingAs($studentUser)->get("/student/quizzes/{$quiz->quiz_id}")->assertOk();
        $this->assertSame(1, QuizAttempt::count());

        $this->actingAs($studentUser)->post("/student/quizzes/{$quiz->quiz_id}/submit", [
            'answers' => [
                $mcq->question_id => '4',
                $tf->question_id => 'True',
            ],
        ])->assertRedirect(route('student.quizzes.index'));

        $attempt = QuizAttempt::where('quiz_id', $quiz->quiz_id)->where('student_id', $student->student_id)->first();
        $this->assertNotNull($attempt->submitted_at);
        $this->assertSame('2', (string) $attempt->score); // both answers correct

        // A second attempt at the same quiz is blocked once submitted.
        $this->actingAs($studentUser)->get("/student/quizzes/{$quiz->quiz_id}")->assertForbidden();
        $this->actingAs($studentUser)->post("/student/quizzes/{$quiz->quiz_id}/submit", ['answers' => []])->assertForbidden();
    }

    public function test_short_answer_quiz_awaits_teacher_review_instead_of_auto_scoring(): void
    {
        [$teacherUser, $studentUser, , $schedule, ] = $this->world();

        $this->actingAs($teacherUser)->post('/teacher/quizzes', [
            'schedule_id' => $schedule->schedule_id,
            'title' => 'Essay Quiz',
            'questions' => [[
                'question_text' => 'Explain photosynthesis.',
                'question_type' => 'short_answer',
                'correct_answer' => 'Plants convert light to energy.',
            ]],
        ]);
        $quiz = Quiz::first();
        $question = $quiz->questions()->first();

        $this->actingAs($studentUser)->get("/student/quizzes/{$quiz->quiz_id}");
        $this->actingAs($studentUser)->post("/student/quizzes/{$quiz->quiz_id}/submit", [
            'answers' => [$question->question_id => 'Plants make food from sunlight.'],
        ])->assertRedirect(route('student.quizzes.index'));

        $attempt = QuizAttempt::where('quiz_id', $quiz->quiz_id)->first();
        $this->assertNotNull($attempt->submitted_at);
        $this->assertNull($attempt->score); // awaiting manual review, not auto-graded

        // Teacher grades it manually.
        $this->actingAs($teacherUser)->put("/teacher/quiz-attempts/{$attempt->attempt_id}", ['score' => 1])
            ->assertRedirect(route('teacher.quizzes.attempts', $quiz->quiz_id));
        $this->assertDatabaseHas('quiz_attempts', ['attempt_id' => $attempt->attempt_id, 'score' => 1]);
    }

    public function test_student_cannot_take_quiz_outside_enrolled_section(): void
    {
        [, $studentUser, , , $otherSchedule] = $this->world();

        $otherQuiz = Quiz::create(['schedule_id' => $otherSchedule->schedule_id, 'title' => 'Other class quiz']);

        $this->actingAs($studentUser)->get("/student/quizzes/{$otherQuiz->quiz_id}")->assertForbidden();
        $this->actingAs($studentUser)->post("/student/quizzes/{$otherQuiz->quiz_id}/submit", ['answers' => []])->assertForbidden();
    }
}
