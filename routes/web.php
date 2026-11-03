<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    // TailAdmin demo aliases
    Route::get('/signin', fn () => redirect()->route('login'))->name('signin');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['auth', 'single.device'])
    ->name('logout');

Route::middleware(['auth', 'single.device'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
});
