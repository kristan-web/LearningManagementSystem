<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentAssignmentController extends Controller
{
    /** Submission files are kept off the public disk (same as materials). */
    private const DISK = 'local';

    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'Student', 403);

        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();
        $sectionId = $student->activeEnrollment?->section_id;
        $studentId = $student->student_id;

        $assignments = $sectionId === null
            ? collect()
            : Assignment::visibleToSection($sectionId)
                ->with([
                    'schedule.subject',
                    'submissions' => function ($q) use ($studentId) {
                        $q->where('student_id', $studentId);
                    },
                ])
                ->orderBy('due_date')
                ->get();

        return view('student.assignments.index', compact('assignments'));
    }

    public function show(Request $request, Assignment $assignment): View
    {
        abort_unless($request->user()->role === 'Student', 403);
        abort_unless($assignment->isAccessibleBy($request->user()), 403);

        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();
        $assignment->load(['schedule.subject', 'comments']);
        $submission = Submission::where('assignment_id', $assignment->assignment_id)
            ->where('student_id', $student->student_id)
            ->first();

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_unless($request->user()->role === 'Student', 403);

        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();
        $sectionId = $student->activeEnrollment?->section_id;

        abort_unless($sectionId !== null && $assignment->schedule?->section_id === $sectionId, 403);

        $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip'],
        ]);

        $alreadySubmitted = Submission::where('assignment_id', $assignment->assignment_id)
            ->where('student_id', $student->student_id)
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->back()->with('error', 'You have already submitted this assignment.');
        }

        if ($assignment->isPastDue()) {
            return redirect()->back()->with('error', 'The deadline for this assignment has passed. Ask your teacher for an extension.');
        }

        $path = $request->file('file')->store('submissions/' . $assignment->assignment_id, self::DISK);

        Submission::create([
            'assignment_id' => $assignment->assignment_id,
            'student_id' => $student->student_id,
            'submitted_at' => now(),
            'file_url' => $path,
            'status' => 'Submitted',
        ]);

        return redirect()->route('student.assignments.index')->with('success', 'Assignment submitted successfully.');
    }
}