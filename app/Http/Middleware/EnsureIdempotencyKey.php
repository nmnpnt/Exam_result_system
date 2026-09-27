<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generic idempotency guard for mutating endpoints (mark upload, result
 * publish). A client must send X-Idempotency-Key. We hash the request body
 * so a *different* body reusing the same key is rejected (409) rather than
 * silently served the old response, and a request still in flight is
 * rejected (409) rather than double-processed.
 */
class EnsureIdempotencyKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Idempotency-Key');

        if (! $key) {
            return response()->json([
                'message' => 'X-Idempotency-Key header is required for this operation.',
            ], 422);
        }

        $requestHash = hash('sha256', $request->getContent());
        $path = $request->path();

        return DB::transaction(function () use ($key, $requestHash, $path, $request, $next) {
            $record = IdempotencyKey::where('idempotency_key', $key)
                ->where('request_path', $path)
                ->lockForUpdate()
                ->first();

            if ($record) {
                if ($record->request_hash !== $requestHash) {
                    return response()->json([
                        'message' => 'Idempotency key reused with a different request body.',
                    ], 409);
                }

                if ($record->status === 'completed') {
                    return response()->json(
                        $record->response_body,
                        $record->response_status
                    );
                }

                return response()->json([
                    'message' => 'A request with this idempotency key is already being processed.',
                ], 409);
            }

            IdempotencyKey::create([
                'idempotency_key' => $key,
                'request_path' => $path,
                'request_hash' => $requestHash,
                'status' => 'in_progress',
                'locked_at' => now(),
            ]);

            $request->attributes->set('idempotency_key', $key);

            $response = $next($request);

            IdempotencyKey::where('idempotency_key', $key)
                ->where('request_path', $path)
                ->update([
                    'status' => 'completed',
                    'response_status' => $response->getStatusCode(),
                    'response_body' => json_decode($response->getContent(), true),
                ]);

            return $response;
        });
    }
}
