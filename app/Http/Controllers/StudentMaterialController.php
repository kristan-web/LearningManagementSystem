<?php

namespace App\Http\Controllers;

use App\Models\LearningMaterial;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentMaterialController extends Controller
{
    /**
     * Landing page: one card per subject (with a material count) instead of
     * every material on one long page. Click through to show() for the list.
     */
    public function index(Request $request): View
    {
        [, $sectionId] = $this->authorizedStudent($request);

        $materials = $sectionId === null
            ? collect()
            : LearningMaterial::visibleToSection($sectionId)
                ->with(['schedule.subject'])
                ->get();

        $subjects = $materials
            ->groupBy(fn (LearningMaterial $material) => $material->schedule?->subject?->subject_id)
            ->filter(fn ($group, $subjectId) => $subjectId !== null)
            ->map(fn ($group) => (object) [
                'subject' => $group->first()->schedule->subject,
                'count' => $group->count(),
            ])
            ->sortBy(fn ($entry) => $entry->subject->subject_name)
            ->values();

        return view('student.materials.index', compact('subjects'));
    }

    /**
     * A single subject's materials — only reachable for subjects the student
     * actually has a schedule for in their active section.
     */
    public function show(Request $request, Subject $subject): View
    {
        [, $sectionId] = $this->authorizedStudent($request);

        abort_unless(
            $sectionId !== null && LearningMaterial::visibleToSection($sectionId)
                ->whereHas('schedule', fn ($q) => $q->where('subject_id', $subject->subject_id))
                ->exists(),
            403
        );

        $materials = LearningMaterial::visibleToSection($sectionId)
            ->whereHas('schedule', fn ($q) => $q->where('subject_id', $subject->subject_id))
            ->orderByDesc('uploaded_at')
            ->get();

        return view('student.materials.show', compact('subject', 'materials'));
    }

    /** @return array{0: Student, 1: int|null} */
    private function authorizedStudent(Request $request): array
    {
        abort_unless($request->user()->role === 'Student', 403);

        $student = Student::where('user_id', $request->user()->user_id)->firstOrFail();

        return [$student, $student->activeEnrollment?->section_id];
    }
}
