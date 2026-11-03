<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['auth'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::get('/users/create', [UserController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');

    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:users.edit')
        ->name('users.edit');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.edit')
        ->name('users.update');

    Route::post('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.edit')
        ->name('users.update.post');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');
});
