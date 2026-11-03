<?php

use Illuminate\Support\Facades\Route;
use Modules\Pos\Http\Controllers\PosController;

Route::middleware(['auth'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])
        ->middleware('permission:pos.access')
        ->name('pos.index');

    Route::get('/pos/scan', [PosController::class, 'scan'])
        ->middleware('permission:pos.access')
        ->name('pos.scan');

    Route::get('/pos/products/search', [PosController::class, 'searchProducts'])
        ->middleware('permission:pos.access')
        ->name('pos.products.search');

    Route::post('/pos/offers/validate', [PosController::class, 'validateOffer'])
        ->middleware('permission:pos.access')
        ->name('pos.offers.validate');

    Route::post('/pos/checkout', [PosController::class, 'checkout'])
        ->middleware('permission:pos.access')
        ->name('pos.checkout');
});
