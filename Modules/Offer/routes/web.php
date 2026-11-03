<?php

use Illuminate\Support\Facades\Route;
use Modules\Offer\Http\Controllers\OfferController;

Route::middleware(['auth'])->group(function () {
    Route::get('/offers', [OfferController::class, 'index'])
        ->middleware('permission:offers.manage')
        ->name('offer.index');

    Route::get('/offers/create', [OfferController::class, 'create'])
        ->middleware('permission:offers.manage')
        ->name('offer.create');

    Route::post('/offers', [OfferController::class, 'store'])
        ->middleware('permission:offers.manage')
        ->name('offer.store');

    Route::get('/offers/{offer}/edit', [OfferController::class, 'edit'])
        ->middleware('permission:offers.manage')
        ->name('offer.edit');

    Route::put('/offers/{offer}', [OfferController::class, 'update'])
        ->middleware('permission:offers.manage')
        ->name('offer.update');

    Route::post('/offers/{offer}', [OfferController::class, 'update'])
        ->middleware('permission:offers.manage')
        ->name('offer.update.post');

    Route::delete('/offers/{offer}', [OfferController::class, 'destroy'])
        ->middleware('permission:offers.manage')
        ->name('offer.destroy');

    Route::post('/offers/{offer}/activate', [OfferController::class, 'activate'])
        ->middleware('permission:offers.manage')
        ->name('offer.activate');

    Route::post('/offers/{offer}/deactivate', [OfferController::class, 'deactivate'])
        ->middleware('permission:offers.manage')
        ->name('offer.deactivate');
});
