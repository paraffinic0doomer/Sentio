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
            'playlist_id' => 'required|integer',
            'url' => 'required|url',
            'thumbnail' => 'nullable|url'
        ]);

        $user = Auth::user();

        // Check if playlist belongs to user
        $playlist = Playlist::where('id', $validated['playlist_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if song already exists in playlist
        $existingSong = UserSong::where('user_id', $user->id)
            ->where('playlist_id', $validated['playlist_id'])
            ->where('song_id', $validated['song_id'])
            ->first();

        if ($existingSong) {
            return response()->json(['message' => 'Song already exists in this playlist!']);
        }

        // Add song to user_songs table
        UserSong::create([
            'user_id' => $user->id,
            'playlist_id' => $playlist->id,
            'song_id' => $validated['song_id'],
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'url' => $validated['url'],
            'thumbnail' => $validated['thumbnail'],
        ]);

        return response()->json(['message' => 'Song added to playlist!']);
    }

    public function createPlaylist(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $user = Auth::user();

        // Check if playlist with this name already exists
        $existingPlaylist = Playlist::where('user_id', $user->id)
            ->where('name', $validated['name'])
            ->first();

        if ($existingPlaylist) {
            return response()->json(['error' => 'Playlist with this name already exists!'], 400);
        }

        $playlist = Playlist::create([
            'name' => $validated['name'],
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Playlist created!',
            'playlist' => $playlist
        ]);
    }

    public function getUserPlaylists()
    {
        $playlists = Playlist::where('user_id', Auth::id())
            ->withCount('songs')
            ->get();

        return response()->json($playlists);
    }
}
