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
        $this->authorize('view', $student);
        $teacher = $request->user()->teacher;

        $enrollment = Enrollment::where('student_id', $student->student_id)
            ->where('status', 'Enrolled')
            ->with('section.strand')
            ->first();

        // The policy ensures the teacher teaches this student, so enrollment should exist
        abort_if($enrollment === null, 404);

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

    //
}
