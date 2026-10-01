<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FinalGrade;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The signed-in student's official final grades (saved by teachers from Grades & Records), per enrollment term. */
class StudentGradeController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'Student', 403);
        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();

        $enrollments = Enrollment::where('student_id', $student->student_id)->with('section')
            ->orderByDesc('date_enrolled')->orderByDesc('enrollment_id')->get();
        $terms = $enrollments->map(fn (Enrollment $e) => (object) [
            'id' => (int) $e->enrollment_id,
            'label' => collect(['SY ' . $e->school_year, $e->semester, $e->section?->section_name])->filter()->implode(' · '),
        ]);

        $selected = $enrollments->firstWhere('enrollment_id', (int) $request->query('term'))
            ?? $enrollments->firstWhere('enrollment_id', $student->activeEnrollment?->enrollment_id)
            ?? $enrollments->first();

        $grades = collect();
        $summary = [];
        if ($selected) {
            $rows = FinalGrade::where('enrollment_id', $selected->enrollment_id)->with('subject')->get();
            $teachers = Schedule::where('section_id', $selected->section_id)->whereIn('subject_id', $rows->pluck('subject_id'))
                ->with('teacher.user')->get()->keyBy('subject_id');

            $grades = $rows->sortBy(fn (FinalGrade $g) => $g->subject?->subject_name)->map(fn (FinalGrade $g) => (object) [
                'subject_code' => $g->subject?->subject_code,
                'subject_name' => $g->subject?->subject_name ?? '—',
                'teacher_name' => trim(($teachers->get($g->subject_id)?->teacher?->user?->first_name ?? '') . ' ' . ($teachers->get($g->subject_id)?->teacher?->user?->last_name ?? '')) ?: null,
                'final_grade' => $g->final_rating,
                'remarks' => $g->remarks,
                'recorded_at' => $g->computed_at,
            ])->values();

            if ($rows->isNotEmpty()) {
                $summary = [
                    'average' => $rows->avg('final_rating'),
                    'subjects' => $rows->count(),
                    'passed' => $rows->where('remarks', 'Passed')->count(),
                ];
            }
        }

        return view('student.grades.index', compact('grades', 'summary', 'terms', 'selected'));
    }
}
