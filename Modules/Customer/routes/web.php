<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;

Route::middleware(['auth'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])
        ->middleware('permission:customers.manage')
        ->name('customer.index');

    Route::get('/customers/create', [CustomerController::class, 'create'])
        ->middleware('permission:customers.manage')
        ->name('customer.create');

    Route::post('/customers', [CustomerController::class, 'store'])
        ->middleware('permission:customers.manage')
        ->name('customer.store');

    Route::get('/customers/{customer}', [CustomerController::class, 'show'])
        ->middleware('permission:customers.manage')
        ->name('customer.show');

    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
        ->middleware('permission:customers.manage')
        ->name('customer.edit');

    Route::put('/customers/{customer}', [CustomerController::class, 'update'])
        ->middleware('permission:customers.manage')
        ->name('customer.update');

    Route::post('/customers/{customer}', [CustomerController::class, 'update'])
        ->middleware('permission:customers.manage')
        ->name('customer.update.post');

    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
        ->middleware('permission:customers.manage')
        ->name('customer.destroy');
});
