<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Submission;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TeacherGradeController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->authorizedTeacher($request);
        $sectionIds = $this->teacherSectionIds($teacher);

        $sections = ClassSection::whereIn('section_id', $sectionIds)
            ->orderBy('grade_level')
            ->orderBy('section_name')
            ->get();

        $query = Enrollment::whereIn('section_id', $sectionIds)
            ->where('status', 'Enrolled')
            ->with(['student.user', 'section.strand']);

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->input('section_id'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_number', 'like', $search)
                  ->orWhere('lrn', 'like', $search)
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search);
                  });
            });
        }

        $enrollments = $query->orderBy('section_id')
            ->orderBy('student_id')
            ->paginate(15)
            ->withQueryString();

        $grades = $this->gradeSummaries($teacher, $enrollments->pluck('student_id'));

        $stats = [
            'sections' => $sections->count(),
            'students' => Enrollment::whereIn('section_id', $sectionIds)
                ->where('status', 'Enrolled')
                ->distinct('student_id')
                ->count('student_id'),
        ];

        $selectedSectionId = $request->filled('section_id') ? $request->input('section_id') : null;

        return view('teacher.grades.index', compact('sections', 'enrollments', 'grades', 'stats', 'selectedSectionId'));
    }

    public function show(Request $request, Student $student): View
    {
        $teacher = $this->authorizedTeacher($request);
        $sectionIds = $this->teacherSectionIds($teacher);

        $enrollment = Enrollment::where('student_id', $student->student_id)
            ->whereIn('section_id', $sectionIds)
            ->where('status', 'Enrolled')
            ->with('section.strand')
            ->first();

        abort_unless($enrollment !== null, 403);

        $student->load('user');

        $assignments = Assignment::forTeacher($teacher->teacher_id)
            ->with(['schedule.subject', 'submissions' => fn ($q) => $q->where('student_id', $student->student_id)])
            ->get()
            ->map(function (Assignment $assignment) use ($student) {
                $submission = $assignment->submissions->firstWhere('student_id', $student->student_id);

                return [
                    'subject' => $assignment->schedule?->subject?->subject_name,
                    'title' => $assignment->title,
                    'max_score' => $assignment->max_score,
                    'score' => $submission?->score,
                    'status' => $submission?->status ?? 'Missing',
                ];
            });

        $quizzes = Quiz::forTeacher($teacher->teacher_id)
            ->with(['schedule.subject', 'attempts' => fn ($q) => $q->where('student_id', $student->student_id)])
            ->get()
            ->map(function (Quiz $quiz) {
                return [
                    'subject' => $quiz->schedule?->subject?->subject_name,
                    'title' => $quiz->title,
                    'best_score' => $quiz->attempts->whereNotNull('score')->max('score'),
                    'attempts' => $quiz->attempts->whereNotNull('submitted_at')->count(),
                ];
            });

        $assignmentAvg = $this->average($assignments->filter(fn ($a) => $a['score'] !== null && $a['max_score'] > 0)
            ->map(fn ($a) => ($a['score'] / $a['max_score']) * 100));

        $quizAvg = $this->average($quizzes->pluck('best_score')->filter(fn ($v) => $v !== null));

        $overall = $this->average(collect([$assignmentAvg, $quizAvg])->filter(fn ($v) => $v !== null));

        return view('teacher.grades.show', compact(
            'student', 'enrollment', 'assignments', 'quizzes', 'assignmentAvg', 'quizAvg', 'overall'
        ));
    }

    /**
     * Per-student assignment/quiz/overall grade averages for the given teacher,
     * scoped to only the given student ids — feeds the grade book table.
     * Two batched queries (no N+1): one for graded submissions, one for scored
     * quiz attempts, both scoped to this teacher's own assignments/quizzes.
     *
     * @return array<int, array{assignment: ?float, quiz: ?float, overall: ?float}>
     */
    private function gradeSummaries(Teacher $teacher, Collection $studentIds): array
    {
        if ($studentIds->isEmpty()) {
            return [];
        }

        $submissions = Submission::whereIn('student_id', $studentIds)
            ->whereNotNull('score')
            ->whereHas('assignment', fn ($q) => $q->forTeacher($teacher->teacher_id))
            ->with('assignment:assignment_id,max_score')
            ->get()
            ->groupBy('student_id');

        $attempts = QuizAttempt::whereIn('student_id', $studentIds)
            ->whereNotNull('score')
            ->whereHas('quiz', fn ($q) => $q->forTeacher($teacher->teacher_id))
            ->get()
            ->groupBy('student_id');

        $summaries = [];

        foreach ($studentIds as $studentId) {
            $assignmentPercents = ($submissions->get($studentId) ?? collect())
                ->filter(fn ($s) => $s->assignment && $s->assignment->max_score > 0)
                ->map(fn ($s) => ($s->score / $s->assignment->max_score) * 100);

            // Best attempt per quiz, then averaged across the quizzes attempted.
            $quizPercents = ($attempts->get($studentId) ?? collect())
                ->groupBy('quiz_id')
                ->map(fn ($group) => $group->max('score'));

            $assignmentAvg = $this->average($assignmentPercents);
            $quizAvg = $this->average($quizPercents);

            $summaries[$studentId] = [
                'assignment' => $assignmentAvg,
                'quiz' => $quizAvg,
                'overall' => $this->average(collect([$assignmentAvg, $quizAvg])->filter(fn ($v) => $v !== null)),
            ];
        }

        return $summaries;
    }

    private function average(Collection $values): ?float
    {
        return $values->isEmpty() ? null : round($values->avg(), 2);
    }

    private function authorizedTeacher(Request $request): Teacher
    {
        abort_unless($request->user()->role === 'Teacher', 403);

        return Teacher::where('user_id', $request->user()->user_id)->firstOrFail();
    }

    /**
     * A teacher's sections aren't a direct relation — they're whichever
     * class_sections appear on the teacher's own schedule rows. Mirrors
     * TeacherClassController::teacherSectionIds().
     */
    private function teacherSectionIds(Teacher $teacher): Collection
    {
        return Schedule::where('teacher_id', $teacher->teacher_id)
            ->distinct()
            ->pluck('section_id');
    }
}
