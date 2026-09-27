<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMarkUploadRequest;
use App\Models\Examination;
use App\Models\MarkUploadBatch;
use App\Services\MarkCsvImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarkUploadController extends Controller
{
    public function __construct(private MarkCsvImportService $importService) {}

    /**
     * @OA\Post(
     *     path="/api/examinations/{examination}/marks/upload",
     *     tags={"Marks"},
     *     summary="Bulk upload marks via CSV",
     *     @OA\Parameter(name="examination", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="202", description="Batch job queued")
     * )
     */
    public function store(StoreMarkUploadRequest $request, Examination $examination): JsonResponse
    {
        if (! $examination->isOpenForMarksEntry()) {
            return response()->json([
                'message' => "Examination is '{$examination->status}' and not open for marks entry.",
            ], 422);
        }

        $batch = $this->importService->import(
            $examination,
            $request->file('file'),
            $request->attributes->get('idempotency_key'),
            $request->user()?->id
        );

        return response()->json([
            'batch_id' => $batch->id,
            'status' => $batch->status,
            'total_rows' => $batch->total_rows,
            'total_chunks' => $batch->total_chunks,
            'poll_url' => route('mark-uploads.show', $batch->id),
        ], 202);
    }

    /**
     * @OA\Get(
     *     path="/api/mark-uploads/{batch}",
     *     tags={"Marks"},
     *     summary="Check status of a bulk upload batch",
     *     @OA\Parameter(name="batch", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="Batch status")
     * )
     */
    public function show(MarkUploadBatch $batch): JsonResponse
    {
        return response()->json([
            'id' => $batch->id,
            'status' => $batch->status,
            'total_rows' => $batch->total_rows,
            'processed_rows' => $batch->processed_rows,
            'valid_rows' => $batch->valid_rows,
            'failed_rows' => $batch->failed_rows,
            'completed_chunks' => $batch->completed_chunks,
            'total_chunks' => $batch->total_chunks,
            'progress_percent' => $batch->total_chunks > 0
                ? round(($batch->completed_chunks / $batch->total_chunks) * 100, 1)
                : 0,
        ]);
    }

    /** GET /api/mark-uploads/{batch}/errors — paginated row-level errors. */
    public function errors(MarkUploadBatch $batch): JsonResponse
    {
        return response()->json(
            $batch->errors()->orderBy('row_number')->paginate(100)
        );
    }
}
