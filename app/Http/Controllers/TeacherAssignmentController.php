<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Submission;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TeacherAssignmentController extends Controller
{
    /** Submission files are kept off the public disk — no guessable URLs (same as materials). */
    private const DISK = 'local';

    public function index(Request $request): View
    {
        $teacher = $this->authorizedTeacher($request);

        $query = Assignment::forTeacher($teacher->teacher_id)
            ->with(['schedule.subject', 'schedule.section'])
            ->withCount([
                'submissions',
                'submissions as awaiting_grading_count' => fn ($q) => $q->whereIn('status', ['Submitted', 'Late']),
            ]);

        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', $request->input('schedule_id'));
        }

        $assignments = $query->orderBy('due_date')->get();

        $stats = [
            'total' => Assignment::forTeacher($teacher->teacher_id)->count(),
            'awaiting_grading' => Submission::awaitingGradingForTeacher($teacher->teacher_id)->count(),
            'overdue' => Assignment::forTeacher($teacher->teacher_id)->where('due_date', '<', now())->count(),
        ];

        $schedules = Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section'])
            ->orderBy('schedule_id')
            ->get();

        $selectedScheduleId = $request->filled('schedule_id') ? $request->input('schedule_id') : null;

        return view('teacher.assignments.index', compact('assignments', 'stats', 'schedules', 'selectedScheduleId'));
    }

    public function create(Request $request): View
    {
        $teacher = $this->authorizedTeacher($request);
        $schedules = Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section'])
            ->orderBy('schedule_id')
            ->get();

        return view('teacher.assignments.create', compact('schedules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $data = $this->validatedAssignment($request, $teacher->teacher_id);

        Assignment::create([
            'schedule_id' => $data['schedule_id'],
            'title' => $data['title'],
            'instructions' => $data['instructions'] ?? null,
            'due_date' => $data['due_date'],
            'max_score' => $data['max_score'],
        ]);

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment created successfully.');
    }

    public function edit(Request $request, Assignment $assignment): View
    {
        $teacher = $this->authorizedTeacher($request);
        $this->authorizeOwnership($assignment, $teacher->teacher_id);

        $schedules = Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section'])
            ->orderBy('schedule_id')
            ->get();

        return view('teacher.assignments.edit', compact('assignment', 'schedules'));
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $this->authorizeOwnership($assignment, $teacher->teacher_id);
        $data = $this->validatedAssignment($request, $teacher->teacher_id);

        $assignment->update([
            'schedule_id' => $data['schedule_id'],
            'title' => $data['title'],
            'instructions' => $data['instructions'] ?? null,
            'due_date' => $data['due_date'],
            'max_score' => $data['max_score'],
        ]);

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment updated successfully.');
    }

    public function destroy(Request $request, Assignment $assignment): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $this->authorizeOwnership($assignment, $teacher->teacher_id);

        // Remove stored student files too, so the disk does not collect orphans.
        foreach ($assignment->submissions as $submission) {
            if ($submission->file_url) {
                Storage::disk(self::DISK)->delete($submission->file_url);
            }
        }
        $assignment->delete();

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment deleted successfully.');
    }

    public function submissions(Request $request, Assignment $assignment): View
    {
        $teacher = $this->authorizedTeacher($request);
        $this->authorizeOwnership($assignment, $teacher->teacher_id);

        $students = Enrollment::where('section_id', $assignment->schedule?->section_id)
            ->where('status', 'Enrolled')
            ->with('student')
            ->orderBy('student_id')
            ->get()
            ->map(fn (Enrollment $enrollment) => $enrollment->student);

        $submissions = $assignment->submissions()->orderBy('submitted_at')->get();

        return view('teacher.assignments.submissions', compact('assignment', 'students', 'submissions'));
    }

    public function grade(Request $request, Submission $submission): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $assignment = $submission->assignment;
        abort_unless($assignment?->schedule?->teacher_id === $teacher->teacher_id, 403);

        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:' . $assignment->max_score],
        ]);

        $submission->update([
            'score' => $data['score'],
            'status' => 'Graded',
        ]);

        return redirect()
            ->route('teacher.assignments.submissions', $assignment->assignment_id)
            ->with('success', 'Submission graded successfully.');
    }

    public function download(Request $request, Submission $submission): StreamedResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $assignment = $submission->assignment;
        abort_unless($assignment?->schedule?->teacher_id === $teacher->teacher_id, 403);
        abort_unless($submission->file_url !== null, 404);

        return Storage::disk(self::DISK)->download($submission->file_url, 'submission-' . $submission->submission_id);
    }

    private function authorizedTeacher(Request $request): Teacher
    {
        abort_unless($request->user()->role === 'Teacher', 403);

        return Teacher::where('user_id', $request->user()->user_id)->firstOrFail();
    }

    /** A teacher may only manage assignments on their own schedules. */
    private function authorizeOwnership(Assignment $assignment, int $teacherId): void
    {
        abort_unless($assignment->schedule?->teacher_id === $teacherId, 403);
    }

    private function validatedAssignment(Request $request, int $teacherId): array
    {
        $data = $request->validate([
            'schedule_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
            'max_score' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
        ]);

        // exists:schedules,schedule_id isn't enough — the schedule must be this teacher's own.
        $owns = Schedule::where('schedule_id', $data['schedule_id'])->where('teacher_id', $teacherId)->exists();
        abort_unless($owns, 403);

        return $data;
    }
}