<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\Enrollment;
use App\Models\FinalGrade;
use App\Models\GradeComponent;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Schedule;
use App\Models\ScheduleEvent;
use App\Models\Submission;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Teacher pages built on the teacher's own schedules: My Classes, Grades & Records, Attendance. */
class TeacherClassroomController extends Controller
{
    public function classes(Request $request): View
    {
        $teacher = $this->authorizedTeacher($request);
        $schedules = $this->schedulesFor($teacher);

        $studentCounts = Enrollment::whereIn('section_id', $schedules->pluck('section_id')->unique())
            ->where('status', 'Enrolled')
            ->selectRaw('section_id, COUNT(*) as total')->groupBy('section_id')
            ->pluck('total', 'section_id');
        $materialCounts = LearningMaterial::whereIn('schedule_id', $schedules->pluck('schedule_id'))
            ->selectRaw('schedule_id, COUNT(*) as total')->groupBy('schedule_id')
            ->pluck('total', 'schedule_id');

        // One active (not-yet-ended) meeting per schedule, if any — powers the Start/Join/End buttons on the class card.
        $meetings = ScheduleEvent::where('event_type', 'Meeting')
            ->whereIn('schedule_id', $schedules->pluck('schedule_id'))
            ->where('meeting_status', '!=', 'Ended')
            ->orderBy('start_datetime')
            ->get()
            ->keyBy('schedule_id');

        $classes = $schedules->map(fn (Schedule $s) => (object) [
            'schedule' => $s,
            'students' => (int) ($studentCounts[$s->section_id] ?? 0),
            'assignments' => $s->assignments_count,
            'quizzes' => $s->quizzes_count,
            'materials' => (int) ($materialCounts[$s->schedule_id] ?? 0),
            'meeting' => $meetings->get($s->schedule_id),
        ]);

        return view('teacher.classes.index', compact('classes'));
    }

    /** Gradebook: every student in the class against every assignment and quiz of that class. */
    public function grades(Request $request): View
    {
        $teacher = $this->authorizedTeacher($request);
        $schedules = $this->schedulesFor($teacher);
        $selected = $this->selectedSchedule($request, $schedules);

        $students = collect();
        $columns = collect();
        $rows = collect();
        $classAverage = null;
        $finalGrades = collect();

        if ($selected) {
            $book = $this->gradebook($selected);
            ['students' => $students, 'columns' => $columns, 'rows' => $rows] = $book;

            $averages = $rows->pluck('average')->filter(fn ($a) => $a !== null);
            $classAverage = $averages->isNotEmpty() ? round($averages->avg(), 1) : null;

            // Saved official grades for this class, keyed by student_id.
            $enrollments = $this->enrollmentsIn($selected->section_id);
            $finalGrades = FinalGrade::whereIn('enrollment_id', $enrollments->pluck('enrollment_id'))
                ->where('subject_id', $selected->subject_id)->get()
                ->keyBy(fn (FinalGrade $g) => (int) $enrollments->firstWhere('enrollment_id', $g->enrollment_id)?->student_id);
        }

        return view('teacher.grades.index', compact('schedules', 'selected', 'students', 'columns', 'rows', 'classAverage', 'finalGrades'));
    }

    /**
     * Save official final grades for one class into final_grades (one row per enrollment + subject),
     * and snapshot the scores behind them into grade_components. Locked grades are never overwritten.
     */
    public function saveFinalGrades(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'schedule_id' => ['required', 'integer'],
            'final_rating' => ['nullable', 'array'],
            'final_rating.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', Rule::in(FinalGrade::REMARKS)],
        ]);

        $this->authorize('view', \App\Models\Schedule::findOrFail($data['schedule_id']));

        $schedule = Schedule::where('schedule_id', $data['schedule_id'])
            ->where('teacher_id', $request->user()->teacher->teacher_id)
            ->firstOrFail();

        $enrollments = $this->enrollmentsIn($schedule->section_id)->keyBy(fn ($e) => (int) $e->student_id);
        $book = $this->gradebook($schedule);
        $saved = 0;
        $locked = 0;

        DB::transaction(function () use ($data, $schedule, $enrollments, $book, &$saved, &$locked) {
            foreach ($data['final_rating'] ?? [] as $studentId => $rating) {
                $enrollment = $enrollments[(int) $studentId] ?? null;
                if ($rating === null || $rating === '' || ! $enrollment) {
                    continue;
                }

                $keys = ['enrollment_id' => $enrollment->enrollment_id, 'subject_id' => $schedule->subject_id];
                if (FinalGrade::where($keys)->value('is_locked')) {
                    $locked++;
                    continue;
                }

                $rating = round((float) $rating, 2);
                FinalGrade::updateOrCreate($keys, [
                    'final_rating' => $rating,
                    'remarks' => ($data['remarks'][$studentId] ?? null) ?: ($rating >= FinalGrade::PASSING ? 'Passed' : 'Failed'),
                    'computed_at' => now(),
                ]);

                // ponytail: snapshot covers this schedule's items only; if one subject is split across
                // several schedules for the same section, save from each or merge them here.
                GradeComponent::where($keys)->whereIn('source_type', ['submission', 'quiz_attempt'])->delete();
                foreach ($book['assignments'] as $assignment) {
                    $submission = $book['submissions'][$studentId . ':a' . $assignment->assignment_id] ?? null;
                    if ($submission?->score !== null) {
                        GradeComponent::create($keys + [
                            'component_type' => 'performance_task', 'source_type' => 'submission', 'source_id' => $submission->submission_id,
                            'raw_score' => $submission->score, 'max_score' => $assignment->max_score,
                        ]);
                    }
                }
                foreach ($book['quizzes'] as $quiz) {
                    $best = $book['bestAttempts'][$studentId . ':q' . $quiz->quiz_id] ?? null;
                    if ($best?->score !== null) {
                        GradeComponent::create($keys + [
                            'component_type' => 'written_work', 'source_type' => 'quiz_attempt', 'source_id' => $best->attempt_id,
                            'raw_score' => $best->score, 'max_score' => 100,
                        ]);
                    }
                }
                $saved++;
            }
        });

        $message = "Final grades saved for {$saved} " . Str::plural('student', $saved) . '.';
        if ($locked) {
            $message .= " {$locked} locked " . Str::plural('grade', $locked) . ' left unchanged.';
        }

        return redirect()->route('teacher.grades.index', ['schedule_id' => $schedule->schedule_id])->with('success', $message);
    }

    /** Students, item columns, per-student cells/average, and the raw scores behind them for one class. */
    private function gradebook(Schedule $schedule): array
    {
        $students = $this->studentsIn($schedule->section_id);
        $assignments = Assignment::where('schedule_id', $schedule->schedule_id)->orderBy('due_date')->get();
        $quizzes = Quiz::where('schedule_id', $schedule->schedule_id)->orderBy('due_date')->orderBy('quiz_id')->get();

        $columns = $assignments->map(fn (Assignment $a) => (object) ['key' => 'a' . $a->assignment_id, 'type' => 'Assignment', 'title' => $a->title, 'max' => (float) $a->max_score])
            ->concat($quizzes->map(fn (Quiz $q) => (object) ['key' => 'q' . $q->quiz_id, 'type' => 'Quiz', 'title' => $q->title, 'max' => 100.0]));

        $submissions = Submission::whereIn('assignment_id', $assignments->pluck('assignment_id'))->get()
            ->keyBy(fn (Submission $s) => $s->student_id . ':a' . $s->assignment_id);
        $bestAttempts = QuizAttempt::whereIn('quiz_id', $quizzes->pluck('quiz_id'))->whereNotNull('submitted_at')->get()
            ->groupBy(fn (QuizAttempt $a) => $a->student_id . ':q' . $a->quiz_id)
            ->map(fn ($attempts) => $attempts->sortByDesc('score')->first());

        $rows = $students->map(function ($student) use ($columns, $submissions, $bestAttempts) {
            $cells = [];
            $percents = [];
            foreach ($columns as $column) {
                $key = $student->student_id . ':' . $column->key;
                if ($column->type === 'Quiz') {
                    $best = $bestAttempts[$key] ?? null;
                    $score = $best?->score !== null ? (float) $best->score : null;
                    $cells[$column->key] = (object) ['score' => $score, 'status' => $score === null ? 'Missing' : 'Graded'];
                } else {
                    $submission = $submissions[$key] ?? null;
                    $score = $submission?->score !== null ? (float) $submission->score : null;
                    $cells[$column->key] = (object) ['score' => $score, 'status' => $submission ? ($score === null ? 'To grade' : 'Graded') : 'Missing'];
                }
                if ($score !== null && $column->max > 0) {
                    $percents[] = $score / $column->max * 100;
                }
            }

            return (object) [
                'student' => $student,
                'cells' => $cells,
                'average' => $percents ? round(array_sum($percents) / count($percents), 1) : null,
            ];
        });

        return compact('students', 'columns', 'rows', 'assignments', 'quizzes', 'submissions', 'bestAttempts');
    }

    public function attendance(Request $request): View
    {
        $teacher = $this->authorizedTeacher($request);
        $schedules = $this->schedulesFor($teacher);
        $selected = $this->selectedSchedule($request, $schedules);
        $date = $this->validDate($request->query('date')) ?? today();

        $students = collect();
        $records = collect();
        $recentSessions = collect();

        if ($selected) {
            $students = $this->studentsIn($selected->section_id);
            // Only the current roster, so the summary matches the list (old rows for unenrolled students are ignored).
            $records = AttendanceRecord::where('schedule_id', $selected->schedule_id)
                ->whereDate('attendance_date', $date)
                ->whereIn('student_id', $students->pluck('student_id'))
                ->get()->keyBy('student_id');
            $recentSessions = AttendanceRecord::where('schedule_id', $selected->schedule_id)
                ->selectRaw("attendance_date, SUM(status = 'Present') as present, SUM(status = 'Late') as late, SUM(status = 'Absent') as absent, SUM(status = 'Excused') as excused")
                ->groupBy('attendance_date')->orderByDesc('attendance_date')->limit(8)->get();
        }

        $summary = collect(AttendanceRecord::STATUSES)->mapWithKeys(fn ($s) => [$s => $records->where('status', $s)->count()]);

        return view('teacher.attendance.index', compact('schedules', 'selected', 'date', 'students', 'records', 'recentSessions', 'summary'));
    }

    public function saveAttendance(Request $request): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);

        $data = $request->validate([
            'schedule_id' => ['required', 'integer'],
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'array'],
            'status.*' => ['required', Rule::in(AttendanceRecord::STATUSES)],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:255'],
        ]);

        $schedule = Schedule::where('schedule_id', $data['schedule_id'])->where('teacher_id', $teacher->teacher_id)->first();
        abort_unless($schedule !== null, 403);

        // Only students enrolled in this section can be marked.
        $enrolled = $this->studentsIn($schedule->section_id)->pluck('student_id')->map(fn ($id) => (int) $id)->all();
        foreach ($data['status'] as $studentId => $status) {
            if (! in_array((int) $studentId, $enrolled, true)) {
                continue;
            }
            AttendanceRecord::updateOrCreate(
                ['schedule_id' => $schedule->schedule_id, 'student_id' => (int) $studentId, 'attendance_date' => Carbon::parse($data['attendance_date'])->toDateString()],
                ['status' => $status, 'remarks' => trim((string) ($data['remarks'][$studentId] ?? '')) ?: null, 'logged_by' => $request->user()->user_id, 'logged_at' => now()],
            );
        }

        return redirect()
            ->route('teacher.attendance.index', ['schedule_id' => $schedule->schedule_id, 'date' => $data['attendance_date']])
            ->with('success', 'Attendance saved.');
    }

    private function validatedFinalGrades(Request $request): array
    {
        $data = $request->validate([
            'schedule_id' => ['required', 'integer'],
            'final_rating' => ['nullable', 'array'],
            'final_rating.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', Rule::in(FinalGrade::REMARKS)],
        ]);

        // Verify ownership of the schedule via policy (we already authorized in the method)
        // But we can also check here if needed, but we rely on the authorize call.
        // However, to be safe, we can check that the schedule belongs to the teacher.
        $schedule = Schedule::where('schedule_id', $data['schedule_id'])
            ->where('teacher_id', $request->user()->teacher->teacher_id)
            ->firstOrFail();

        return $data;
    }

    private function authorizedTeacher(Request $request): Teacher
    {
        abort_unless($request->user()->role === 'Teacher', 403);

        return Teacher::where('user_id', $request->user()->user_id)->firstOrFail();
    }

    /** The teacher's classes (one per schedule row), in timetable order. */
    private function schedulesFor(Teacher $teacher): Collection
    {
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return Schedule::where('teacher_id', $teacher->teacher_id)
            ->with(['subject', 'section.strand', 'room'])
            ->withCount(['assignments', 'quizzes'])
            ->get()
            ->sortBy(fn (Schedule $s) => [array_search($s->day_of_week, $dayOrder), $s->start_time])
            ->values();
    }

    /** ?schedule_id= when it is one of the teacher's, otherwise the first class. */
    private function selectedSchedule(Request $request, Collection $schedules): ?Schedule
    {
        $id = (int) $request->query('schedule_id');

        return $schedules->first(fn (Schedule $s) => (int) $s->schedule_id === $id) ?? $schedules->first();
    }

    /** Active ("Enrolled") enrollments of a section. */
    private function enrollmentsIn(int $sectionId): Collection
    {
        return Enrollment::where('section_id', $sectionId)->where('status', 'Enrolled')->get();
    }

    private function studentsIn(int $sectionId): Collection
    {
        return Enrollment::where('section_id', $sectionId)->where('status', 'Enrolled')
            ->with('student.user')->get()
            ->pluck('student')->filter()
            ->sortBy(fn ($student) => mb_strtolower(($student->user?->last_name ?? '') . ' ' . ($student->user?->first_name ?? '')))
            ->values();
    }

    private function validDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }
        try {
            $date = Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        return $date->isAfter(today()) ? today() : $date;
    }
}
