<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\UserSong;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::where('user_id', Auth::id())->with('songs')->get();
        return view('playlist', compact('playlists'));
    }

    public function getSongs($playlistId)
    {
        $songs = UserSong::where('playlist_id', $playlistId)->get();
        return response()->json($songs);
    }

    public function deleteSong($songId)
    {
        UserSong::destroy($songId);
        return response()->json(['message' => 'Song deleted from playlist']);
    }

    public function addSong(Request $request)
    {
        $validated = $request->validate([
            'song_id' => 'required|string',
            'title' => 'required|string',
            'artist' => 'required|string',
            'playlist_name' => 'required|string',
            'url' => 'required|url',
        ]);

        $user = Auth::user();

        // Find or create playlist
        $playlist = Playlist::firstOrCreate([
            'name' => $validated['playlist_name'],
            'user_id' => $user->id,
        ]);

        // Add song to user_songs table
        UserSong::create([
            'user_id' => $user->id,
            'playlist_id' => $playlist->id,
            'song_id' => $validated['song_id'],
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'url' => $validated['url'],
        ]);

        return response()->json(['message' => 'Song added to playlist!']);
    }
}
