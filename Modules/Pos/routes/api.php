<?php

use Illuminate\Support\Facades\Route;

// POS endpoints are registered as web routes under auth + session.
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    //
});
