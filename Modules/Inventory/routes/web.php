<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;

Route::middleware(['auth'])->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])
        ->middleware('permission:inventory.view')
        ->name('inventory.index');

    Route::get('/inventory/{product}/edit', [InventoryController::class, 'edit'])
        ->middleware('permission:inventory.adjust')
        ->name('inventory.edit');

    Route::put('/inventory/{product}', [InventoryController::class, 'update'])
        ->middleware('permission:inventory.adjust')
        ->name('inventory.update');
});
