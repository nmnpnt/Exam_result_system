<?php

namespace App\Jobs;

use App\Models\Examination;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

/**
 * Fans out to one CalculateStudentResult job per enrollment across every
 * course in the examination, rather than looping synchronously — at
 * 100,000+ students per examination this must be parallelized across
 * workers, not run on a single request/console thread.
 */
class CalculateExaminationResults implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $examinationId) {}

    public function handle(): void
    {
        $examination = Examination::with('examinationCourses.enrollments')->findOrFail($this->examinationId);

        $examination->update(['status' => 'computing']);

        $jobs = $examination->examinationCourses
            ->flatMap(fn ($ec) => $ec->enrollments)
            ->map(fn ($enrollment) => new CalculateStudentResult($enrollment->id))
            ->all();

        Bus::batch($jobs)
            ->onQueue('results')
            ->allowFailures()
            ->finally(function () use ($examination) {
                $examination->update(['status' => 'locked']);
            })
            ->dispatch();
    }
}
