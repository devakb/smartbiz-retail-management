<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;

Route::middleware(['auth'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('permission:products.view')
        ->name('product.index');

    Route::get('/products/check-upc', [ProductController::class, 'checkUpc'])
        ->middleware('permission:products.view|products.create|products.update')
        ->name('product.check-upc');

    Route::get('/products/suggest-sku', [ProductController::class, 'suggestSku'])
        ->middleware('permission:products.create|products.update')
        ->name('product.suggest-sku');

    Route::get('/products/create', [ProductController::class, 'create'])
        ->middleware('permission:products.create')
        ->name('product.create');

    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('permission:products.create')
        ->name('product.store');

    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->middleware('permission:products.update')
        ->name('product.edit');

    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->middleware('permission:products.update')
        ->name('product.update');

    Route::post('/products/{product}', [ProductController::class, 'update'])
        ->middleware('permission:products.update')
        ->name('product.update.post');

    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('permission:products.delete')
        ->name('product.destroy');
});
