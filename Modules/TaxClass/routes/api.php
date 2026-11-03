<?php

use Illuminate\Support\Facades\Route;
use Modules\TaxClass\Http\Controllers\TaxClassController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('taxclasses', TaxClassController::class)->names('taxclass');
});
