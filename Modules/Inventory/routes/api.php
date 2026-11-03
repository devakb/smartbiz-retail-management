<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Inventory API endpoints reserved for a later wave.
});
