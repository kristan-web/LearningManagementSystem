<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Services\Quiz\QuizCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class TeacherQuizController extends Controller
{
    public function __construct(private readonly QuizCsvImporter $importer)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quiz::class);
        $teacher = $request->user()->teacher;

        $query = Quiz::forTeacher($teacher->teacher_id)
            ->with(['schedule.subject', 'schedule.section'])
            ->withCount(['questions', 'attempts']);

        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', $request->input('schedule_id'));
        }

        $quizzes = $query->orderByDesc('created_at')->get();

        $schedules = Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section'])
            ->orderBy('schedule_id')
            ->get();

        $selectedScheduleId = $request->filled('schedule_id') ? $request->input('schedule_id') : null;

        return view('teacher.quizzes.index', compact('quizzes', 'schedules', 'selectedScheduleId'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Quiz::class);
        $teacher = $request->user()->teacher;
        $schedules = Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section'])
            ->orderBy('schedule_id')
            ->get();

        return view('teacher.quizzes.create', compact('schedules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Quiz::class);
        $data = $this->validatedQuiz($request);

        $quiz = Quiz::create([
            'schedule_id' => $data['schedule_id'],
            'title' => $data['title'],
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'attempts_allowed' => $data['attempts_allowed'],
            'due_date' => $data['due_date'] ?? null,
        ]);

        try {
            $count = $this->importer->import($quiz, $request->file('csv_file')->getRealPath());
        } catch (RuntimeException $e) {
            $quiz->delete();

            return redirect()->back()->withInput()->with('error', "CSV import failed:\n" . $e->getMessage());
        }

        return redirect()->route('teacher.quizzes.index')->with('success', "Quiz created with {$count} question(s).");
    }

    public function destroy(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('delete', $quiz);

        $quiz->delete();

        return redirect()->route('teacher.quizzes.index')->with('success', 'Quiz deleted successfully.');
    }

    /** Pushes a quiz's due date forward — re-opens attempts for students once it has passed. */
    public function extendDeadline(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        // Quizzes may have no due date yet — fall back to "after now" so a first deadline can be set.
        $after = $quiz->due_date ?? now();

        $data = $request->validate([
            'due_date' => ['required', 'date', 'after:' . $after],
        ]);

        $quiz->update(['due_date' => $data['due_date']]);

        return redirect()->route('teacher.quizzes.index')->with('success', 'Deadline extended successfully.');
    }

    private function validatedQuiz(Request $request): array
    {
        $data = $request->validate([
            'schedule_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'attempts_allowed' => ['required', 'integer', 'in:1,2'],
            'due_date' => ['nullable', 'date'],
            'csv_file' => ['required', 'file', 'max:2048', 'mimes:csv,txt'],
        ]);

        // Verify the teacher owns this schedule
        $owns = \App\Models\Schedule::where('schedule_id', $data['schedule_id'])
            ->where('teacher_id', $request->user()->teacher->teacher_id)
            ->exists();
        
        abort_unless($owns, 403);

        return $data;
    }
}
