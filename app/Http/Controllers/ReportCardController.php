<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\FinalGrade;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportCardController extends Controller
{
    public function generate(Request $request, Student $student): Response
    {
        $this->authorize('view', $student);

        $enrollment = Enrollment::where('student_id', $student->student_id)
            ->where('status', 'Enrolled')
            ->firstOrFail();

        $grades = FinalGrade::where('enrollment_id', $enrollment->enrollment_id)
            ->with('subject')
            ->get();

        $pdf = Pdf::loadView('reports.form138', compact('student', 'grades'));

        return $pdf->stream('Form138_' . $student->user->first_name . '_' . $student->user->last_name . '.pdf');
    }
}
