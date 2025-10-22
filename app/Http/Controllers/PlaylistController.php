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
        $user = Auth::user();
        
        // Find or create playlist
        $playlist = Playlist::firstOrCreate([
            'name' => $request->playlist_name,
            'user_id' => $user->id
        ]);

        // Add song to playlist_songs
        UserSong::create([
            'playlist_id' => $playlist->id,
            'title' => $request->title,
            'artist' => $request->artist,
            'url' => $request->url,
            'thumbnail' => $request->thumbnail
        ]);

        return response()->json(['message' => 'Song added to playlist!']);
    }
}
