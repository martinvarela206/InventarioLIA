<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ElementoController;
use App\Http\Controllers\MovimientoController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\DashboardController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/revision', [RevisionController::class, 'index'])->name('revision.index');

// Root: show welcome page
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::resource('elementos', ElementoController::class);
    Route::resource('movimientos', MovimientoController::class);

    // Profile Routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('user_admin')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->only(['index', 'edit', 'update']);
        Route::post('/users/{user}/role', [\App\Http\Controllers\UserController::class, 'toggleRole'])->name('users.toggleRole');
    });
});
