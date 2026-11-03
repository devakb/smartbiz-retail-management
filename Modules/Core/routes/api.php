<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Core API endpoints reserved — demo resource removed in Wave 7.
});
