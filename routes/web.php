<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'service' => 'University Examination & Result Processing System',
    'status' => 'ok',
]));
