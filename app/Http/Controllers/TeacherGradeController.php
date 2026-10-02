<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TeacherGradeController extends Controller
{
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
