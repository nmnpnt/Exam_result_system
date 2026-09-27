<?php

namespace App\Jobs;

use App\Models\AssessmentComponent;
use App\Models\Enrollment;
use App\Models\Mark;
use App\Models\MarkUploadBatch;
use App\Models\MarkUploadError;
use App\Services\MarksValidationService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Statement;
use Throwable;

/**
 * Processes a single slice of the uploaded CSV (rows [offset, offset+limit)).
 * Chunking lets many workers process one large file in parallel, and keeps
 * a single failure contained to ~2,000 rows instead of the whole import.
 */
class ProcessMarksCsvChunk implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public int $batchId,
        public int $offset,
        public int $limit,
    ) {}

    public function handle(MarksValidationService $validator): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $batch = MarkUploadBatch::findOrFail($this->batchId);

        $csv = Reader::createFromPath(Storage::path($batch->storage_path), 'r');
        $csv->setHeaderOffset(0);

        $records = Statement::create()
            ->offset($this->offset)
            ->limit($this->limit)
            ->process($csv);

        $processed = 0;
        $valid = 0;
        $failed = 0;
        $rowNumber = $this->offset;

        foreach ($records as $record) {
            $rowNumber++;
            $processed++;

            try {
                $this->importRow($record, $batch, $validator);
                $valid++;
            } catch (Throwable $e) {
                $failed++;
                MarkUploadError::create([
                    'mark_upload_batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'raw_row' => $record,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        // Atomic counters: many chunk-workers update the same batch row
        // concurrently, so we use a single UPDATE ... SET x = x + ? rather
        // than read-modify-write, which would lose updates under concurrency.
        MarkUploadBatch::where('id', $batch->id)->update([
            'processed_rows' => DB::raw("processed_rows + {$processed}"),
            'valid_rows' => DB::raw("valid_rows + {$valid}"),
            'failed_rows' => DB::raw("failed_rows + {$failed}"),
            'completed_chunks' => DB::raw('completed_chunks + 1'),
        ]);
    }

    /**
     * @param  array<string, string>  $record
     */
    private function importRow(array $record, MarkUploadBatch $batch, MarksValidationService $validator): void
    {
        $rollNumber = trim($record['roll_number'] ?? '');
        $courseCode = trim($record['course_code'] ?? '');
        $componentName = trim($record['component_name'] ?? '');
        $marksObtained = $record['marks_obtained'] ?? null;

        $enrollment = Enrollment::whereHas('student', fn ($q) => $q->where('roll_number', $rollNumber))
            ->whereHas('examinationCourse', fn ($q) => $q
                ->where('examination_id', $batch->examination_id)
                ->whereHas('course', fn ($c) => $c->where('code', $courseCode)))
            ->first();

        if (! $enrollment) {
            throw new \RuntimeException("No enrollment found for roll_number={$rollNumber}, course_code={$courseCode}");
        }

        $component = AssessmentComponent::where('examination_course_id', $enrollment->examination_course_id)
            ->where('name', $componentName)
            ->first();

        if (! $component) {
            throw new \RuntimeException("Unknown assessment component '{$componentName}' for course_code={$courseCode}");
        }

        [$status, $error] = $validator->validate((float) $marksObtained, (float) $component->max_marks);

        // Composite-unique upsert keyed on (enrollment_id, assessment_component_id):
        // re-processing the same row (retry, or the same file re-uploaded
        // under a *new* idempotency key) overwrites rather than duplicates,
        // and bumps `version` so concurrent manual edits can detect staleness.
        Mark::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'assessment_component_id' => $component->id,
            ],
            [
                'marks_obtained' => $marksObtained,
                'status' => $status,
                'validation_error' => $error,
                'mark_upload_batch_id' => $batch->id,
                'version' => DB::raw('COALESCE(version, 0) + 1'),
            ]
        );
    }

    public function failed(Throwable $exception): void
    {
        MarkUploadBatch::where('id', $this->batchId)->update([
            'completed_chunks' => DB::raw('completed_chunks + 1'),
        ]);
    }
}
