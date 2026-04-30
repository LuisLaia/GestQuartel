<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentAssignmentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('people', PersonController::class);
    Route::resource('equipment', EquipmentController::class);

    Route::get('/assignments', [EquipmentAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [EquipmentAssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [EquipmentAssignmentController::class, 'store'])->name('assignments.store');
    Route::patch('/assignments/{assignment}/return', [EquipmentAssignmentController::class, 'returnEquipment'])
        ->name('assignments.return');
});
