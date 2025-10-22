<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvidiousController;
use App\Http\Controllers\PlaylistController;

Route::get('/search', [InvidiousController::class, 'search']);


Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [DashboardController::class, 'search'])->name('search');
    Route::post('/recommendations', [DashboardController::class, 'getRecommendations']);
    Route::post('/play-song', [DashboardController::class, 'playSong']);
    Route::get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index')->middleware('auth');
    Route::get('/playlists/{id}/songs', [PlaylistController::class, 'getSongs'])->middleware('auth');
    Route::delete('/songs/{id}', [PlaylistController::class, 'deleteSong'])->middleware('auth');
    Route::get('/profile', [DashboardController::class, 'showProfile'])->name('profile')->middleware('auth');
});
