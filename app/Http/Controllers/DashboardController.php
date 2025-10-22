<?php

namespace App\Http\Controllers;

use App\Models\UserSong;
use App\Models\Playlist;
use App\Services\InvidiousService;
use App\Services\LlmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $invidious;
    protected $llm;

    public function __construct(InvidiousService $invidious)
    {
        $this->invidious = $invidious;
        $this->llm = new LlmService(true); // true = dummy mode
    }

    public function index()
    {
        $user = Auth::user();
        $lastPlayed = UserSong::where('user_id', $user->id)
            ->orderBy('played_at', 'desc')
            ->take(5)
            ->get();

        $playlists = Playlist::where('user_id', $user->id)->get();

        return view('dashboard', compact('lastPlayed', 'playlists'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        if (!$query) {
            return redirect()->back()->with('error', 'Please enter a search query.');
        }

        $results = $this->invidious->searchVideos($query);

        return view('search-results', compact('query', 'results'));
    }

    public function getRecommendations(Request $request)
    {
        $mood = $request->input('mood');
        \Log::info('Received mood: ' . $mood);

        // Use LlmService to get recommendations
        $recommendations = $this->llm->getRecommendations($mood);

        return response()->json($recommendations);
    }

    public function addToPlaylist(Request $request)
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
        $playlist = Playlist::firstOrCreate(
            [
                'user_id' => $user->id,
                'name' => $validated['playlist_name'],
            ]
        );

        // Add song to playlist
        UserSong::firstOrCreate([
            'user_id' => $user->id,
            'playlist_id' => $playlist->id,
            'song_id' => $validated['song_id'],
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'url' => $validated['url'],
        ]);

        return response()->json(['success' => true, 'message' => 'Added to playlist!']);
    }

    public function playSong(Request $request)
    {
        $songId = $request->input('song_id');
        $title = $request->input('title', 'Unknown');
        $artist = $request->input('artist', 'Unknown');
        $url = $request->input('url', '');

        UserSong::create([
            'user_id' => Auth::id(),
            'song_id' => $songId,
            'title' => $title,
            'artist' => $artist,
            'url' => $url,
            'played_at' => now(),
        ]);

        return response()->json(['success' => true, 'url' => $url]);
    }

    public function showProfile()
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }
}
