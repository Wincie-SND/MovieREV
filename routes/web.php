<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FilmsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/movies/{movie}', [MovieController::class, 'show'])
    ->whereNumber('movie')
    ->name('movies.show');

// Writing reviews is a member action: the routes are behind `auth`, and the
// controller/policy authorise the review itself once it is bound.
Route::middleware('auth')->group(function () {
    Route::post('/movies/{movie}/reviews', [ReviewController::class, 'store'])
        ->whereNumber('movie')
        ->name('reviews.store');

    Route::match(['put', 'patch'], '/reviews/{review}', [ReviewController::class, 'update'])
        ->name('reviews.update');

    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/admin/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('admin.dashboard');

Route::get('/admin/films', FilmsController::class)
    ->middleware('auth')
    ->name('admin.films');
