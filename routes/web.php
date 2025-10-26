<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvidiousController;
use App\Http\Controllers\PlaylistController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/search', [InvidiousController::class, 'search'])->name('search');
    
    Route::post('/add-to-playlist', [PlaylistController::class, 'addSong'])->name('playlists.addSong');
    
    Route::get('/stream-audio/{songId}', [DashboardController::class, 'streamAudio'])->name('stream.audio');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
    Route::post('/play-song', [DashboardController::class, 'playSong'])->name('play.song');
    Route::post('/get-recommendations', [DashboardController::class, 'getRecommendations'])->name('get.recommendations');
    Route::post('/clear-recommendations', [DashboardController::class, 'clearRecommendations'])->name('clear.recommendations');
    Route::get('/profile', [DashboardController::class, 'showProfile'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/update-preferences', [ProfileController::class, 'updatePreferences'])->name('profile.update-preferences');
    Route::post('/songs/rate', [DashboardController::class, 'rateSong'])->name('songs.rate');
    Route::get('/explore', [DashboardController::class, 'explore'])->name('explore');
    Route::post('/get-explore-recommendations', [DashboardController::class, 'getExploreRecommendations'])->name('get.explore.recommendations');
    
    // Music Player Route
    Route::get('/player/{songId?}', [DashboardController::class, 'showPlayer'])->name('player');
    Route::get('/player/playlist/{playlistId}/{songId?}', [DashboardController::class, 'showPlaylistPlayer'])->name('player.playlist');
    
    // Playlist routes
    Route::get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index');
    Route::get('/playlists/user', [PlaylistController::class, 'getUserPlaylists'])->name('playlists.user');
    Route::post('/playlists/create', [PlaylistController::class, 'createPlaylist'])->name('playlists.create');
    Route::get('/playlists/{playlist}', [PlaylistController::class, 'showPlaylist'])->name('playlists.show');
    Route::post('/playlists/{playlist}/add-song', [PlaylistController::class, 'addSongToPlaylist'])->name('playlists.add-song');
    Route::get('/playlists/{id}/songs', [PlaylistController::class, 'getSongs'])->name('playlists.songs');
    Route::delete('/songs/{id}', [PlaylistController::class, 'deleteSong'])->name('songs.delete');
});
