<?php

namespace App\Jobs;

use App\Models\Enrollment;
use App\Services\ResultCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Recomputes one student's result for one course. ShouldBeUnique prevents
 * two queued recompute triggers for the same enrollment (e.g. a mark
 * correction followed immediately by the batch-completion trigger) from
 * running concurrently; the row lock inside the transaction is the second,
 * database-level line of defense if uniqueness is ever bypassed (e.g. a
 * manual artisan call).
 */
class CalculateStudentResult implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [5, 15, 30, 60, 120];

    public function __construct(public int $enrollmentId) {}

    public function uniqueId(): string
    {
        return "enrollment-result-{$this->enrollmentId}";
    }

    public function handle(ResultCalculationService $service): void
    {
        DB::transaction(function () use ($service) {
            $enrollment = Enrollment::with('examinationCourse.assessmentComponents', 'marks')
                ->lockForUpdate()
                ->findOrFail($this->enrollmentId);

            $service->calculate($enrollment);
        });
    }
}
