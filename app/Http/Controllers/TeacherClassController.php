<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherClassController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->authorizedTeacher($request);
        $sectionIds = $this->teacherSectionIds($teacher);

        $sections = ClassSection::whereIn('section_id', $sectionIds)
            ->with('strand:strand_id,strand_code')
            ->withCount(['enrollments as students_count' => fn ($q) => $q->where('status', 'Enrolled')])
            ->orderBy('grade_level')
            ->orderBy('section_name')
            ->get();

        $query = Enrollment::whereIn('section_id', $sectionIds)
            ->where('status', 'Enrolled')
            ->with(['student.user', 'section.strand']);

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->input('section_id'));
        }

        if ($request->filled('grade_level')) {
            $query->whereHas('section', fn ($q) => $q->where('grade_level', $request->input('grade_level')));
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

        $students = $query->orderBy('section_id')
            ->orderBy('student_id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'sections' => $sections->count(),
            'students' => Enrollment::whereIn('section_id', $sectionIds)
                ->where('status', 'Enrolled')
                ->distinct('student_id')
                ->count('student_id'),
        ];

        $selectedSectionId = $request->filled('section_id') ? $request->input('section_id') : null;
        $selectedGradeLevel = $request->filled('grade_level') ? $request->input('grade_level') : null;

        // Needed for the "Add Student" modal — scoped to the teacher's own sections only.
        $schoolYears = SchoolYear::orderByDesc('year')->get(['school_year_id', 'year', 'status']);
        $allStudents = Student::with('user:user_id,first_name,last_name')
            ->get(['student_id', 'user_id', 'student_number'])
            ->sortBy(fn ($student) => $student->user?->last_name)
            ->values();

        return view('teacher.classes.index', compact(
            'sections', 'students', 'stats', 'selectedSectionId', 'selectedGradeLevel', 'schoolYears', 'allStudents'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->authorizedTeacher($request);
        $sectionIds = $this->teacherSectionIds($teacher);

        $data = $request->validate([
            'student_id' => [
                'required', 'integer', 'exists:students,student_id',
                Rule::unique('enrollments', 'student_id')
                    ->where('school_year_id', $request->input('school_year_id'))
                    ->where('semester', $request->input('semester')),
            ],
            // Rule::in() keeps a teacher from enrolling into a section they don't teach.
            'section_id' => ['required', 'integer', Rule::in($sectionIds)],
            'school_year_id' => 'required|integer|exists:school_years,school_year_id',
            'semester' => 'required|in:1st Semester,2nd Semester',
        ], [
            'student_id.unique' => 'This student already has an enrollment for the selected school year and semester.',
            'section_id.in' => 'You can only add students to a section you teach.',
        ]);

        $section = ClassSection::findOrFail($data['section_id']);
        $taken = Enrollment::where('section_id', $section->section_id)
            ->where('status', 'Enrolled')
            ->count();

        if ($taken >= $section->max_slots) {
            throw ValidationException::withMessages([
                'section_id' => 'Section "' . $section->section_name . '" is full (' . $section->max_slots . ' slots).',
            ]);
        }

        Enrollment::create([
            'student_id' => $data['student_id'],
            'section_id' => $data['section_id'],
            'school_year' => SchoolYear::findOrFail($data['school_year_id'])->year,
            'school_year_id' => $data['school_year_id'],
            'semester' => $data['semester'],
            'status' => 'Enrolled',
        ]);

        return redirect()->route('teacher.classes.index')->with('success', 'Student enrolled successfully.');
    }

    private function authorizedTeacher(Request $request): Teacher
    {
        abort_unless($request->user()->role === 'Teacher', 403);

        return Teacher::where('user_id', $request->user()->user_id)->firstOrFail();
    }

    /**
     * A teacher's sections aren't a direct relation — they're whichever
     * class_sections appear on the teacher's own schedule rows.
     */
    private function teacherSectionIds(Teacher $teacher): Collection
    {
        return Schedule::where('teacher_id', $teacher->teacher_id)
            ->distinct()
            ->pluck('section_id');
    }
}
