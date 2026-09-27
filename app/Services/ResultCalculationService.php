<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Result;
use Illuminate\Support\Facades\DB;

/**
 * Computes the weighted result for a single enrollment. Runs inside a
 * transaction with the enrollment row locked (see CalculateStudentResult),
 * so two concurrent recompute triggers for the same student never race.
 */
class ResultCalculationService
{
    public function calculate(Enrollment $enrollment): Result
    {
        $marks = $enrollment->marks()->with('assessmentComponent')->get();
        $components = $enrollment->examinationCourse->assessmentComponents;

        $allComponentsMarked = $components->every(
            fn ($component) => $marks->firstWhere('assessment_component_id', $component->id)
        );

        if (! $allComponentsMarked || $marks->contains('status', 'rejected')) {
            return $this->upsert($enrollment, [
                'status' => 'pending',
                'total_marks_obtained' => null,
                'total_max_marks' => null,
                'percentage' => null,
                'grade' => null,
                'outcome' => 'incomplete',
                'computed_at' => now(),
            ]);
        }

        $totalObtained = 0.0;
        $totalMax = 0.0;

        foreach ($components as $component) {
            $mark = $marks->firstWhere('assessment_component_id', $component->id);
            $weight = (float) $component->weight_percentage / 100;
            $totalObtained += ((float) $mark->marks_obtained / (float) $component->max_marks) * $weight * (float) $component->max_marks;
            $totalMax += $weight * (float) $component->max_marks;
        }

        $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;
        $passMarks = $enrollment->examinationCourse->pass_marks;
        $maxMarks = $enrollment->examinationCourse->max_marks;
        $passPercentage = $maxMarks > 0 ? ($passMarks / $maxMarks) * 100 : 40;

        return $this->upsert($enrollment, [
            'total_marks_obtained' => round($totalObtained, 2),
            'total_max_marks' => round($totalMax, 2),
            'percentage' => $percentage,
            'grade' => $this->gradeFor($percentage),
            'outcome' => $percentage >= $passPercentage ? 'pass' : 'fail',
            'status' => 'computed',
            'computed_at' => now(),
        ]);
    }

    private function gradeFor(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B',
            $percentage >= 60 => 'C',
            $percentage >= 50 => 'D',
            $percentage >= 40 => 'E',
            default => 'F',
        };
    }

    private function upsert(Enrollment $enrollment, array $attributes): Result
    {
        return DB::transaction(function () use ($enrollment, $attributes) {
            return Result::updateOrCreate(
                ['enrollment_id' => $enrollment->id],
                $attributes
            );
        });
    }
}
