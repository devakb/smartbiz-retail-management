<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;

Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])
        ->middleware('permission:orders.view')
        ->name('order.index');

    Route::get('/orders/create', [OrderController::class, 'create'])
        ->middleware('permission:orders.create')
        ->name('order.create');

    Route::post('/orders', [OrderController::class, 'store'])
        ->middleware('permission:orders.create')
        ->name('order.store');

    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->middleware('permission:orders.view')
        ->name('order.show');

    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->middleware('permission:orders.update')
        ->name('order.status');

    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->middleware('permission:orders.update')
        ->name('order.status.post');

    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])
        ->middleware('permission:orders.cancel|orders.update')
        ->name('order.cancel');

    Route::post('/orders/{order}/payments', [OrderController::class, 'storePayment'])
        ->middleware('permission:orders.update')
        ->name('order.payments.store');

    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])
        ->middleware('permission:orders.view')
        ->name('order.invoice');

    Route::get('/orders/{order}/invoice/pdf', [OrderController::class, 'invoicePdf'])
        ->middleware('permission:orders.view')
        ->name('order.invoice.pdf');
});
