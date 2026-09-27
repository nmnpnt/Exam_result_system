<?php

return [
    // Rows per queued chunk when importing a marks CSV. Tuned so each job
    // finishes in a few seconds and a single row failure only affects this
    // many records.
    'csv_chunk_size' => env('MARKS_CSV_CHUNK_SIZE', 2000),

    'csv_max_rows' => env('MARKS_CSV_MAX_ROWS', 2000000),
];
