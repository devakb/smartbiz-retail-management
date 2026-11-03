<?php

use Illuminate\Support\Facades\Route;
use Modules\Brand\Http\Controllers\BrandController;

Route::middleware(['auth'])->group(function () {
    Route::get('/brands', [BrandController::class, 'index'])
        ->middleware('permission:brands.view')
        ->name('brand.index');

    Route::get('/brands/create', [BrandController::class, 'create'])
        ->middleware('permission:brands.create')
        ->name('brand.create');

    Route::post('/brands', [BrandController::class, 'store'])
        ->middleware('permission:brands.create')
        ->name('brand.store');

    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])
        ->middleware('permission:brands.update')
        ->name('brand.edit');

    Route::put('/brands/{brand}', [BrandController::class, 'update'])
        ->middleware('permission:brands.update')
        ->name('brand.update');

    Route::post('/brands/{brand}', [BrandController::class, 'update'])
        ->middleware('permission:brands.update')
        ->name('brand.update.post');

    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])
        ->middleware('permission:brands.delete')
        ->name('brand.destroy');
});
