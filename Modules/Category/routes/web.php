<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\CategoryController;

Route::middleware(['auth'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])
        ->middleware('permission:categories.view')
        ->name('category.index');

    Route::get('/categories/create', [CategoryController::class, 'create'])
        ->middleware('permission:categories.create')
        ->name('category.create');

    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('permission:categories.create')
        ->name('category.store');

    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])
        ->middleware('permission:categories.update')
        ->name('category.edit');

    Route::put('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('permission:categories.update')
        ->name('category.update');

    Route::post('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('permission:categories.update')
        ->name('category.update.post');

    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('permission:categories.delete')
        ->name('category.destroy');
});
