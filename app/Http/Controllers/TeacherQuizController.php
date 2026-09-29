<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeacherQuizController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->authorizedTeacher($request);

        $query = Quiz::forTeacher($teacher->teacher_id)
            ->with(['schedule.subject', 'schedule.section'])
            ->withCount([
                'attempts',
                'attempts as awaiting_review_count' => fn ($q) => $q->whereNotNull('submitted_at')->whereNull('score'),
            ]);

        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', $request->input('schedule_id'));
        }

        $quizzes = $query->orderBy('due_date')->get();

        $stats = [
            'total' => Quiz::forTeacher($teacher->teacher_id)->count(),
            'awaiting_review' => QuizAttempt::awaitingReviewForTeacher($teacher->teacher_id)->count(),
            'overdue' => Quiz::forTeacher($teacher->teacher_id)->whereNotNull('due_date')->where('due_date', '<', now())->count(),
        ];

        $schedules = Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section'])
            ->orderBy('schedule_id')
            ->get();

        $selectedScheduleId = $request->filled('schedule_id') ? $request->input('schedule_id') : null;

        return view('teacher.quizzes.index', compact('quizzes', 'stats', 'schedules', 'selectedScheduleId'));
    }

    public function create(Request $request): View
    {
        $teacher = $this->authorizedTeacher($request);
        $schedules = Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section'])
            ->orderBy('schedule_id')
            ->get();

        return view('teacher.quizzes.create', compact('schedules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $data = $this->validatedQuiz($request, $teacher->teacher_id);

        DB::transaction(function () use ($data) {
            $quiz = Quiz::create([
                'schedule_id' => $data['schedule_id'],
                'title' => $data['title'],
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);

            $this->syncQuestions($quiz, $data['questions']);
        });

        return redirect()->route('teacher.quizzes.index')->with('success', 'Quiz created successfully.');
    }

    public function edit(Request $request, Quiz $quiz): View
    {
        $teacher = $this->authorizedTeacher($request);
        $this->authorizeOwnership($quiz, $teacher->teacher_id);

        $quiz->load('questions');

        $schedules = Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section'])
            ->orderBy('schedule_id')
            ->get();

        return view('teacher.quizzes.edit', compact('quiz', 'schedules'));
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $this->authorizeOwnership($quiz, $teacher->teacher_id);
        $data = $this->validatedQuiz($request, $teacher->teacher_id);

        DB::transaction(function () use ($quiz, $data) {
            $quiz->update([
                'schedule_id' => $data['schedule_id'],
                'title' => $data['title'],
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);

            // Simplest correct approach: replace the question set wholesale.
            // ponytail: this discards any per-question edit history — fine while quizzes
            // aren't versioned; revisit only if in-progress attempts must survive edits.
            $quiz->questions()->delete();
            $this->syncQuestions($quiz, $data['questions']);
        });

        return redirect()->route('teacher.quizzes.index')->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Request $request, Quiz $quiz): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $this->authorizeOwnership($quiz, $teacher->teacher_id);

        $quiz->delete();

        return redirect()->route('teacher.quizzes.index')->with('success', 'Quiz deleted successfully.');
    }

    public function attempts(Request $request, Quiz $quiz): View
    {
        $teacher = $this->authorizedTeacher($request);
        $this->authorizeOwnership($quiz, $teacher->teacher_id);

        $students = Enrollment::where('section_id', $quiz->schedule?->section_id)
            ->where('status', 'Enrolled')
            ->with('student')
            ->orderBy('student_id')
            ->get()
            ->map(fn (Enrollment $enrollment) => $enrollment->student);

        $attempts = $quiz->attempts()->orderBy('submitted_at')->get();
        $totalQuestions = $quiz->questions()->count();

        return view('teacher.quizzes.attempts', compact('quiz', 'students', 'attempts', 'totalQuestions'));
    }

    public function grade(Request $request, QuizAttempt $attempt): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $quiz = $attempt->quiz;
        abort_unless($quiz?->schedule?->teacher_id === $teacher->teacher_id, 403);

        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:' . max($quiz->questions()->count(), 1)],
        ]);

        $attempt->update(['score' => $data['score']]);

        return redirect()
            ->route('teacher.quizzes.attempts', $quiz->quiz_id)
            ->with('success', 'Quiz attempt graded successfully.');
    }

    private function authorizedTeacher(Request $request): Teacher
    {
        abort_unless($request->user()->role === 'Teacher', 403);

        return Teacher::where('user_id', $request->user()->user_id)->firstOrFail();
    }

    /** A teacher may only manage quizzes on their own schedules. */
    private function authorizeOwnership(Quiz $quiz, int $teacherId): void
    {
        abort_unless($quiz->schedule?->teacher_id === $teacherId, 403);
    }

    private function validatedQuiz(Request $request, int $teacherId): array
    {
        $data = $request->validate([
            'schedule_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'due_date' => ['nullable', 'date'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.question_type' => ['required', 'in:' . implode(',', QuizQuestion::TYPES)],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['nullable', 'string', 'max:255'],
            'questions.*.correct_answer' => ['required', 'string', 'max:500'],
        ]);

        // exists:schedules,schedule_id isn't enough — the schedule must be this teacher's own.
        $owns = Schedule::where('schedule_id', $data['schedule_id'])->where('teacher_id', $teacherId)->exists();
        abort_unless($owns, 403);

        return $data;
    }

    /** @param array<int, array{question_text: string, question_type: string, options: ?array, correct_answer: string}> $questions */
    private function syncQuestions(Quiz $quiz, array $questions): void
    {
        foreach ($questions as $question) {
            // true_false always has a fixed option set regardless of what the form posted.
            $options = $question['question_type'] === 'true_false'
                ? ['True', 'False']
                : array_values(array_filter($question['options'] ?? [], fn ($opt) => trim((string) $opt) !== ''));

            QuizQuestion::create([
                'quiz_id' => $quiz->quiz_id,
                'question_text' => $question['question_text'],
                'question_type' => $question['question_type'],
                'options' => $options !== [] ? $options : null,
                'correct_answer' => $question['correct_answer'],
            ]);
        }
    }
}
