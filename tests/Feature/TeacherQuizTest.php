<?php

namespace Tests\Feature;

use App\Models\ClassSection;
use App\Models\Quiz;
use App\Models\Schedule;
use App\Models\Strand;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherQuizTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Teacher, 2: Schedule, 3: Schedule} [teacherUser, teacher, ownSchedule, otherTeacherSchedule] */
    private function world(): array
    {
        $trackId = DB::table('tracks')->insertGetId(['track_code' => 'ACAD-' . uniqid(), 'track_name' => 'Academic', 'created_at' => now()]);
        $strand = Strand::create(['track_id' => $trackId, 'strand_code' => 'STEM-' . uniqid(), 'strand_name' => 'STEM']);
        $section = ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '11', 'section_name' => uniqid(),
            'school_year' => '2025-2026', 'max_slots' => 40, 'status' => 'Open',
        ]);
        $roomId = DB::table('rooms')->insertGetId(['room_name' => 'Room-' . uniqid(), 'building' => 'Main', 'capacity' => 40, 'created_at' => now()]);
        $subject = Subject::create([
            'strand_id' => $strand->strand_id, 'subject_code' => 'MATH1-' . uniqid(), 'subject_name' => 'Math',
            'subject_type' => 'Core', 'grade_level' => '11', 'semester' => '1st Semester', 'units' => 1.0,
        ]);

        $user = User::create([
            'first_name' => 'Test', 'last_name' => 'Teacher', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Teacher', 'status' => 'Active',
        ]);
        $teacher = Teacher::create(['user_id' => $user->user_id, 'teacher_number' => 'T-' . uniqid(), 'specialization' => 'Mathematics']);
        $schedule = Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Monday', 'start_time' => '08:00:00', 'end_time' => '09:00:00',
        ]);

        $otherUser = User::create([
            'first_name' => 'Other', 'last_name' => 'Teacher', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Teacher', 'status' => 'Active',
        ]);
        $otherTeacher = Teacher::create(['user_id' => $otherUser->user_id, 'teacher_number' => 'T-' . uniqid(), 'specialization' => 'English']);
        $otherSchedule = Schedule::create([
            'section_id' => $section->section_id, 'subject_id' => $subject->subject_id,
            'teacher_id' => $otherTeacher->teacher_id, 'room_id' => $roomId,
            'day_of_week' => 'Tuesday', 'start_time' => '10:00:00', 'end_time' => '11:00:00',
        ]);

        return [$user, $teacher, $schedule, $otherSchedule];
    }

    private function validCsv(): UploadedFile
    {
        $content = "question_text,question_type,option_a,option_b,option_c,option_d,correct_answer\n"
            . "Capital of PH?,multiple_choice,Manila,Cebu,Davao,Baguio,Manila\n"
            . "Sky is green.,true_false,,,,,false\n"
            . "2+2?,short_answer,,,,,4\n";

        return UploadedFile::fake()->createWithContent('questions.csv', $content);
    }

    public function test_teacher_can_create_quiz_with_valid_csv(): void
    {
        [$user, , $schedule] = $this->world();

        $response = $this->actingAs($user)->post('/teacher/quizzes', [
            'schedule_id' => $schedule->schedule_id,
            'title' => 'Quiz 1',
            'time_limit_minutes' => 20,
            'attempts_allowed' => 2,
            'csv_file' => $this->validCsv(),
        ]);

        $response->assertRedirect(route('teacher.quizzes.index'));
        $this->assertDatabaseHas('quizzes', ['schedule_id' => $schedule->schedule_id, 'title' => 'Quiz 1', 'attempts_allowed' => 2]);
        $quiz = Quiz::first();
        $this->assertSame(3, $quiz->questions()->count());
    }

    public function test_invalid_csv_rolls_back_quiz_creation(): void
    {
        [$user, , $schedule] = $this->world();

        $badCsv = UploadedFile::fake()->createWithContent(
            'bad.csv',
            "question_text,question_type,option_a,option_b,option_c,option_d,correct_answer\n"
            . ",multiple_choice,A,B,,,A\n"
        );

        $response = $this->actingAs($user)->post('/teacher/quizzes', [
            'schedule_id' => $schedule->schedule_id,
            'title' => 'Bad Quiz',
            'attempts_allowed' => 1,
            'csv_file' => $badCsv,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('quizzes', ['title' => 'Bad Quiz']);
    }

    public function test_teacher_cannot_create_quiz_on_another_teachers_schedule(): void
    {
        [$user, , , $otherSchedule] = $this->world();

        $this->actingAs($user)->post('/teacher/quizzes', [
            'schedule_id' => $otherSchedule->schedule_id,
            'title' => 'Hijack',
            'attempts_allowed' => 1,
            'csv_file' => $this->validCsv(),
        ])->assertForbidden();
    }

    public function test_teacher_can_delete_own_quiz(): void
    {
        [$user, , $schedule] = $this->world();
        $quiz = Quiz::create(['schedule_id' => $schedule->schedule_id, 'title' => 'Delete me', 'attempts_allowed' => 1]);

        $this->actingAs($user)->delete("/teacher/quizzes/{$quiz->quiz_id}")
            ->assertRedirect(route('teacher.quizzes.index'));

        $this->assertDatabaseMissing('quizzes', ['quiz_id' => $quiz->quiz_id]);
    }

    public function test_teacher_cannot_delete_another_teachers_quiz(): void
    {
        [$user, , , $otherSchedule] = $this->world();
        $quiz = Quiz::create(['schedule_id' => $otherSchedule->schedule_id, 'title' => 'Not yours', 'attempts_allowed' => 1]);

        $this->actingAs($user)->delete("/teacher/quizzes/{$quiz->quiz_id}")->assertForbidden();
        $this->assertDatabaseHas('quizzes', ['quiz_id' => $quiz->quiz_id]);
    }

    public function test_non_teacher_role_is_forbidden_from_teacher_quizzes(): void
    {
        $admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'User', 'email' => 'admin@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Admin', 'status' => 'Active',
        ]);

        $this->actingAs($admin)->get('/teacher/quizzes')->assertForbidden();
    }
}
