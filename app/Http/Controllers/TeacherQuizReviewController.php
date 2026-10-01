<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Additions next to TeacherQuizController (create/delete stay there):
 * edit a quiz's details (questions stay locked so past attempts keep their answers),
 * and review results and each student's answers (read-only).
 */
class TeacherQuizReviewController extends Controller
{
    public function edit(Request $request, Quiz $quiz): View
    {
        $this->authorizeOwnership($request, $quiz);
        $quiz->load('schedule.subject', 'schedule.section')->loadCount(['questions', 'attempts']);

        return view('teacher.quizzes.edit', compact('quiz'));
    }

    /** Details only: title, timer, attempts allowed and due date. Questions are never touched here. */
    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorizeOwnership($request, $quiz);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'attempts_allowed' => ['required', 'integer', 'in:1,2'],
            'due_date' => ['nullable', 'date'],
        ]);

        $quiz->update([
            'title' => $data['title'],
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'attempts_allowed' => $data['attempts_allowed'],
            'due_date' => $data['due_date'] ?? null,
        ]);

        return redirect()->route('teacher.quizzes.index')->with('success', 'Quiz details updated.');
    }

    /** Every enrolled student with their submitted attempts, plus how often each question was answered correctly. */
    public function results(Request $request, Quiz $quiz): View
    {
        $this->authorizeOwnership($request, $quiz);
        $quiz->load('schedule.subject', 'schedule.section');

        $questions = $quiz->questions()->orderBy('question_id')->get();
        $attempts = $quiz->attempts()->whereNotNull('submitted_at')->orderBy('submitted_at')->get();

        $students = Enrollment::where('section_id', $quiz->schedule?->section_id)->where('status', 'Enrolled')
            ->with('student.user')->get()->pluck('student')->filter()
            ->sortBy(fn ($s) => mb_strtolower(($s->user?->last_name ?? '') . ' ' . ($s->user?->first_name ?? '')))
            ->values();

        $rows = $students->map(fn ($student) => (object) [
            'student' => $student,
            'attempts' => $attempts->where('student_id', $student->student_id)->values(),
            'best' => $attempts->where('student_id', $student->student_id)->sortByDesc('score')->first(),
        ]);

        // Same rule the student results page uses: case-insensitive exact match.
        $questionStats = $questions->map(function ($question) use ($attempts) {
            $answered = $attempts->filter(fn (QuizAttempt $a) => array_key_exists($question->question_id, $a->answers ?? []));
            $correct = $answered->filter(fn (QuizAttempt $a) => $this->isCorrect($a->answers[$question->question_id] ?? '', $question->correct_answer));

            return (object) [
                'question' => $question,
                'answered' => $answered->count(),
                'correct' => $correct->count(),
                'rate' => $answered->count() ? (int) round($correct->count() / $answered->count() * 100) : null,
            ];
        });

        return view('teacher.quizzes.results', compact('quiz', 'questions', 'rows', 'questionStats', 'attempts'));
    }

    public function attempt(Request $request, Quiz $quiz, QuizAttempt $attempt): View
    {
        $this->authorizeOwnership($request, $quiz);
        abort_unless($attempt->quiz_id === $quiz->quiz_id && $attempt->submitted_at !== null, 404);

        $quiz->load('schedule.subject', 'schedule.section');
        $attempt->load('student.user');
        $questions = $quiz->questions()->orderBy('question_id')->get();
        $answers = $attempt->answers ?? [];
        $isCorrect = fn ($question) => $this->isCorrect($answers[$question->question_id] ?? '', $question->correct_answer);

        return view('teacher.quizzes.attempt', compact('quiz', 'attempt', 'questions', 'answers', 'isCorrect'));
    }

    private function isCorrect(?string $given, ?string $correct): bool
    {
        $given = trim((string) $given);

        return $given !== '' && strcasecmp($given, trim((string) $correct)) === 0;
    }

    /** A teacher may only manage quizzes on their own schedules. */
    private function authorizeOwnership(Request $request, Quiz $quiz): void
    {
        abort_unless($request->user()->role === 'Teacher', 403);
        $teacherId = Teacher::where('user_id', $request->user()->user_id)->value('teacher_id');
        abort_unless($teacherId !== null && $quiz->schedule?->teacher_id === $teacherId, 403);
    }
}
