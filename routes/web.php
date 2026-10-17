<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FilmsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserFollowController;
use App\Http\Controllers\UserMovieListController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/movies/{movie}', [MovieController::class, 'show'])
    ->whereNumber('movie')
    ->name('movies.show');

Route::get('/users/{user:username}', [UserProfileController::class, 'show'])
    ->name('users.show');

// Account settings, reviews and follows are member actions, so they are
// available only to authenticated users.
Route::middleware('auth')->group(function () {
    Route::get('/settings', [UserSettingsController::class, 'show'])->name('settings');
    Route::put('/settings', [UserSettingsController::class, 'update'])->name('settings.update');

    Route::get('/my-lists', [UserMovieListController::class, 'index'])->name('movie-lists.index');
    Route::post('/movies/{movie}/lists/{list}', [UserMovieListController::class, 'store'])
        ->where(['movie' => '[1-9][0-9]*', 'list' => 'favorites|rate-later'])
        ->name('movie-lists.store');
    Route::delete('/movies/{movie}/lists/{list}', [UserMovieListController::class, 'destroy'])
        ->where(['movie' => '[1-9][0-9]*', 'list' => 'favorites|rate-later'])
        ->name('movie-lists.destroy');

    Route::post('/users/{user:username}/follow', [UserFollowController::class, 'store'])
        ->name('users.follow');
    Route::delete('/users/{user:username}/follow', [UserFollowController::class, 'destroy'])
        ->name('users.unfollow');

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
