<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentQuizController extends Controller
{
    public function index(Request $request): View
    {
        $student = $this->authorizedStudent($request);
        $sectionId = $student->activeEnrollment?->section_id;

        $quizzes = $sectionId === null
            ? collect()
            : Quiz::visibleToSection($sectionId)
                ->with([
                    'schedule.subject',
                    'attempts' => fn ($q) => $q->where('student_id', $student->student_id)->orderByDesc('attempt_id'),
                ])
                ->withCount('questions')
                ->orderByDesc('created_at')
                ->get();

        // One card per subject (like Learning Materials), each expanding to
        // that subject's quiz list instead of one long flat table.
        $subjects = $quizzes
            ->groupBy(fn (Quiz $quiz) => $quiz->schedule?->subject?->subject_id)
            ->filter(fn ($group, $subjectId) => $subjectId !== null)
            ->map(fn ($group) => (object) [
                'subject' => $group->first()->schedule->subject,
                'quizzes' => $group,
            ])
            ->sortBy(fn ($entry) => $entry->subject->subject_name)
            ->values();

        return view('student.quizzes.index', compact('subjects'));
    }

    /**
     * Resumes the student's in-progress attempt if one exists, otherwise
     * starts a new one (subject to attempts_allowed), then shows the quiz.
     */
    public function take(Request $request, Quiz $quiz): View|RedirectResponse
    {
        $student = $this->authorizedStudent($request);
        $this->authorizeAccess($quiz, $student);

        $attempt = QuizAttempt::where('quiz_id', $quiz->quiz_id)
            ->where('student_id', $student->student_id)
            ->whereNull('submitted_at')
            ->first();

        if ($attempt === null) {
            $usedAttempts = QuizAttempt::where('quiz_id', $quiz->quiz_id)
                ->where('student_id', $student->student_id)
                ->whereNotNull('submitted_at')
                ->count();

            abort_unless($usedAttempts < $quiz->attempts_allowed, 403, 'No attempts remaining for this quiz.');

            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->quiz_id,
                'student_id' => $student->student_id,
                'started_at' => now(),
            ]);
        }

        $questions = $quiz->questionsForAttempt($attempt->attempt_id);

        $deadline = $quiz->time_limit_minutes
            ? $attempt->started_at->copy()->addMinutes($quiz->time_limit_minutes)
            : null;

        return view('student.quizzes.take', compact('quiz', 'attempt', 'questions', 'deadline'));
    }

    public function submit(Request $request, Quiz $quiz, QuizAttempt $attempt): RedirectResponse
    {
        $student = $this->authorizedStudent($request);
        $this->authorizeAccess($quiz, $student);
        abort_unless($attempt->quiz_id === $quiz->quiz_id && $attempt->student_id === $student->student_id, 403);
        abort_if($attempt->submitted_at !== null, 403, 'This attempt has already been submitted.');

        $submitted = $request->input('answers', []);
        $questions = $quiz->questions()->get();

        $correctCount = 0;
        $answers = [];
        foreach ($questions as $question) {
            $given = trim((string) ($submitted[$question->question_id] ?? ''));
            $isCorrect = $given !== '' && strcasecmp($given, trim($question->correct_answer)) === 0;
            if ($isCorrect) {
                $correctCount++;
            }
            $answers[$question->question_id] = $given;
        }

        $score = $questions->count() > 0 ? round(($correctCount / $questions->count()) * 100, 2) : 0;

        $attempt->update([
            'answers' => $answers,
            'score' => $score,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('student.quizzes.results', [$quiz->quiz_id, $attempt->attempt_id])
            ->with('success', 'Quiz submitted successfully.');
    }

    public function results(Request $request, Quiz $quiz, QuizAttempt $attempt): View
    {
        $student = $this->authorizedStudent($request);
        $this->authorizeAccess($quiz, $student);
        abort_unless($attempt->quiz_id === $quiz->quiz_id && $attempt->student_id === $student->student_id, 403);
        abort_unless($attempt->submitted_at !== null, 403);

        $questions = $quiz->questions()->orderBy('question_id')->get();
        $answers = $attempt->answers ?? [];

        return view('student.quizzes.results', compact('quiz', 'attempt', 'questions', 'answers'));
    }

    private function authorizedStudent(Request $request): Student
    {
        abort_unless($request->user()->role === 'Student', 403);

        return Student::where('user_id', $request->user()->user_id)->firstOrFail();
    }

    /** A student may only take/review quizzes for schedules in their own enrolled section. */
    private function authorizeAccess(Quiz $quiz, Student $student): void
    {
        $sectionId = $student->activeEnrollment?->section_id;
        abort_unless($sectionId !== null && $quiz->schedule?->section_id === $sectionId, 403);
    }
}
