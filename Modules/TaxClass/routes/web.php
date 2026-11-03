<?php

use Illuminate\Support\Facades\Route;
use Modules\TaxClass\Http\Controllers\TaxClassController;

Route::middleware(['auth'])->group(function () {
    Route::get('/tax-classes', [TaxClassController::class, 'index'])
        ->middleware('permission:tax-classes.view')
        ->name('tax-class.index');

    Route::get('/tax-classes/create', [TaxClassController::class, 'create'])
        ->middleware('permission:tax-classes.create')
        ->name('tax-class.create');

    Route::post('/tax-classes', [TaxClassController::class, 'store'])
        ->middleware('permission:tax-classes.create')
        ->name('tax-class.store');

    Route::get('/tax-classes/{taxClass}/edit', [TaxClassController::class, 'edit'])
        ->middleware('permission:tax-classes.update')
        ->name('tax-class.edit');

    Route::put('/tax-classes/{taxClass}', [TaxClassController::class, 'update'])
        ->middleware('permission:tax-classes.update')
        ->name('tax-class.update');

    Route::patch('/tax-classes/{taxClass}', [TaxClassController::class, 'update'])
        ->middleware('permission:tax-classes.update');

    Route::delete('/tax-classes/{taxClass}', [TaxClassController::class, 'destroy'])
        ->middleware('permission:tax-classes.delete')
        ->name('tax-class.destroy');
});
