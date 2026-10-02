<?php

namespace App\Services;

use App\Models\GradeComponent;
use App\Models\GradingTemplate;
use Illuminate\Support\Collection;

class GradeCalculationService
{
    /**
     * Calculate the final grade based on components and template.
     */
    public function calculate(int $enrollmentId, int $subjectId): float
    {
        $template = \App\Models\SubjectGradingTemplate::where('subject_id', $subjectId)
            ->with('gradingTemplate')
            ->first()?->gradingTemplate;

        if (!$template) {
            return 0.0;
        }

        $components = GradeComponent::where('enrollment_id', $enrollmentId)
            ->where('subject_id', $subjectId)
            ->get();

        $scores = $this->aggregateScores($components);

        $finalGrade = ($scores['written_work'] * ($template->written_work_weight / 100)) +
                      ($scores['performance_task'] * ($template->performance_task_weight / 100)) +
                      ($scores['exam'] * ($template->exam_weight / 100));

        return round($finalGrade, 2);
    }

    private function aggregateScores(Collection $components): array
    {
        $totals = ['written_work' => 0, 'performance_task' => 0, 'exam' => 0];
        $counts = ['written_work' => 0, 'performance_task' => 0, 'exam' => 0];

        foreach ($components as $component) {
            $type = $component->component_type;
            if (isset($totals[$type])) {
                $max = $component->max_score ?: 100;
                $totals[$type] += ($component->raw_score / $max) * 100;
                $counts[$type]++;
            }
        }

        foreach ($totals as $type => $total) {
            if ($counts[$type] > 0) {
                $totals[$type] = $total / $counts[$type];
            }
        }

        return $totals;
    }
}
