<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentQuizController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'Student', 403);

        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();
        $sectionId = $student->activeEnrollment?->section_id;
        $studentId = $student->student_id;

        $quizzes = $sectionId === null
            ? collect()
            : Quiz::visibleToSection($sectionId)
                ->withCount('questions')
                ->with([
                    'schedule.subject',
                    'attempts' => function ($q) use ($studentId) {
                        $q->where('student_id', $studentId);
                    },
                ])
                ->orderBy('due_date')
                ->get();

        return view('student.quizzes.index', compact('quizzes'));
    }

    public function show(Request $request, Quiz $quiz): View
    {
        [$student, ] = $this->authorizedStudentFor($request, $quiz);

        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->quiz_id)
            ->where('student_id', $student->student_id)
            ->first();

        abort_if($existingAttempt?->submitted_at !== null, 403, 'You have already taken this quiz.');

        $quiz->load('questions');

        // Starting an attempt is idempotent — reloading the page shouldn't create duplicates,
        // it just resumes the same in-progress attempt (started_at stays the original time).
        $attempt = $existingAttempt ?? QuizAttempt::create([
            'quiz_id' => $quiz->quiz_id,
            'student_id' => $student->student_id,
            'started_at' => now(),
        ]);

        return view('student.quizzes.take', compact('quiz', 'attempt'));
    }

    public function submit(Request $request, Quiz $quiz): RedirectResponse
    {
        [$student, ] = $this->authorizedStudentFor($request, $quiz);

        $attempt = QuizAttempt::where('quiz_id', $quiz->quiz_id)
            ->where('student_id', $student->student_id)
            ->first();

        abort_unless($attempt !== null && $attempt->submitted_at === null, 403);

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string', 'max:500'],
        ]);
        $answers = $data['answers'] ?? [];

        $questions = $quiz->questions;
        $hasShortAnswer = $questions->contains(fn (QuizQuestion $q) => $q->question_type === 'short_answer');

        // Objective items (multiple_choice/true_false) auto-grade; short_answer needs a
        // teacher's eye, so leave the score null (awaiting review) if any are present.
        $score = null;
        if (!$hasShortAnswer) {
            $score = $questions->reduce(
                fn (int $carry, QuizQuestion $q) => $carry + ($q->isCorrect($answers[$q->question_id] ?? null) ? 1 : 0),
                0
            );
        }

        $attempt->update([
            'submitted_at' => now(),
            'score' => $score,
        ]);

        return redirect()->route('student.quizzes.index')->with('success', 'Quiz submitted successfully.');
    }

    /** @return array{0: Student, 1: int} */
    private function authorizedStudentFor(Request $request, Quiz $quiz): array
    {
        abort_unless($request->user()->role === 'Student', 403);

        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();
        $sectionId = $student->activeEnrollment?->section_id;

        abort_unless($sectionId !== null && $quiz->schedule?->section_id === $sectionId, 403);

        return [$student, $sectionId];
    }
}
