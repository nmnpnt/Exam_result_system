<?php

namespace App\Services;

use App\Jobs\ProcessMarksCsvChunk;
use App\Models\Examination;
use App\Models\MarkUploadBatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

/**
 * Splits a large CSV into fixed-size chunks and dispatches one queued job
 * per chunk, rather than parsing 100,000+ rows inline on the request thread.
 *
 * Expected CSV columns: roll_number,course_code,component_name,marks_obtained
 */
class MarkCsvImportService
{
    private int $chunkSize;

    public function __construct()
    {
        $this->chunkSize = (int) config('marks.csv_chunk_size', 2000);
    }

    public function import(Examination $examination, UploadedFile $file, string $idempotencyKey, ?int $uploadedBy = null): MarkUploadBatch
    {
        // Idempotent on the (examination, idempotency_key) pair: re-submitting
        // the same upload request returns the existing batch instead of
        // starting a second import.
        $existing = MarkUploadBatch::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        $storedPath = $file->store('mark-uploads');

        $csv = Reader::createFromPath(Storage::path($storedPath), 'r');
        $csv->setHeaderOffset(0);

        $totalRows = iterator_count($csv->getRecords());
        $totalChunks = (int) ceil($totalRows / $this->chunkSize);

        $batch = MarkUploadBatch::create([
            'examination_id' => $examination->id,
            'idempotency_key' => $idempotencyKey,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $storedPath,
            'total_rows' => $totalRows,
            'total_chunks' => $totalChunks,
            'status' => 'queued',
            'uploaded_by' => $uploadedBy,
            'started_at' => now(),
        ]);

        $jobs = [];
        for ($chunkIndex = 0; $chunkIndex < $totalChunks; $chunkIndex++) {
            $offset = $chunkIndex * $this->chunkSize;
            $jobs[] = new ProcessMarksCsvChunk($batch->id, $offset, $this->chunkSize);
        }

        // A batch job lets us mark the whole upload "completed" once every
        // chunk (successful or exhausted) has run, without a separate poller.
        Bus::batch($jobs)
            ->onQueue('marks-import')
            ->allowFailures()
            ->finally(function () use ($batch) {
                $batch->refresh();
                $batch->update([
                    'status' => $batch->failed_rows > 0 && $batch->valid_rows === 0 ? 'failed' : 'completed',
                    'completed_at' => now(),
                ]);
            })
            ->dispatch();

        $batch->update(['status' => 'processing']);

        return $batch;
    }
}
