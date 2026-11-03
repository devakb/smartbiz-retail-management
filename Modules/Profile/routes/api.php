<?php

use Illuminate\Support\Facades\Route;

// Profile APIs are served via web routes under auth for Wave 1.
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    //
});
