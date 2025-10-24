<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\LlmService;
use App\Models\UserSong;

class DashboardController extends Controller
{
    protected $llm;

    public function __construct(LlmService $llm)
    {
        $this->llm = $llm;
    }

    /**
     * Display the user dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get last played songs for the logged-in user (unique songs, latest to oldest)
        $lastPlayed = UserSong::where('user_id', $user->id)
            ->whereNotNull('played_at')
            ->orderBy('played_at', 'desc')
            ->get()
            ->unique('song_id')
            ->take(5);

        return view('dashboard', compact('user', 'lastPlayed'));
    }

    /**
     * Handle mood input and get recommendations
     */
    public function getRecommendations(Request $request)
    {
        $feeling = $request->input('feeling');
        Log::info('User feeling received: ' . $feeling);

        // Get recommendations using LlmService (Groq + yt-dlp)
        $recommendations = $this->llm->getRecommendations($feeling);

        // Store the top recommendation as the "last played"
        if (!empty($recommendations)) {
            $firstSong = $recommendations[0];

            UserSong::create([
                'user_id' => Auth::id(),
                'song_id' => $firstSong['id'],
                'title' => $firstSong['title'],
                'artist' => $firstSong['artist'],
                'thumbnail' => $firstSong['thumbnail'],
                'url' => $firstSong['url'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'feeling' => $feeling,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Handle song play request
     */
    public function playSong(Request $request)
    {
        $songId = $request->input('song_id');
        $title = $request->input('title');
        $artist = $request->input('artist');
        $thumbnail = $request->input('thumbnail');
        $url = $request->input('url');

        // Update or create the played song record
        UserSong::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'song_id' => $songId,
            ],
            [
                'title' => $title,
                'artist' => $artist,
                'thumbnail' => $thumbnail,
                'url' => $url,
                'played_at' => now(),
            ]
        );

        return response()->json(['status' => 'success']);
    }

    /**
     * Show user listening history
     */
    public function history()
    {
        $songs = UserSong::where('user_id', Auth::id())
            ->whereNotNull('played_at')
            ->orderBy('played_at', 'desc')
            ->get()
            ->unique('song_id')
            ->take(20);

        return view('history', compact('songs'));
    }

    /**
     * Show user profile
     */
    public function showProfile()
    {
        $user = Auth::user();
        $totalSongs = UserSong::where('user_id', $user->id)->count();
        $totalPlaylists = $user->playlists()->count();

        return view('profile', compact('user', 'totalSongs', 'totalPlaylists'));
    }

    /**
     * Stream audio file
     */
    public function streamAudio($songId)
    {
        $song = UserSong::where('song_id', $songId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$song || !$song->audio_url) {
            abort(404, 'Audio file not found');
        }

        $audioPath = storage_path('app/audio/' . basename($song->audio_url));

        if (!file_exists($audioPath)) {
            abort(404, 'Audio file not found on disk');
        }

        return response()->file($audioPath, [
            'Content-Type' => 'audio/mpeg',
            'Content-Disposition' => 'inline; filename="' . $song->title . '.mp3"'
        ]);
    }

    /**
     * Show music player
     */
    public function showPlayer($songId = null)
    {
        $user = Auth::user();

        if ($songId) {
            // Get the specific song and related songs for playlist
            $currentSong = UserSong::where('song_id', $songId)
                ->where('user_id', $user->id)
                ->first();

            if (!$currentSong) {
                // Try to find any song with this ID
                $currentSong = UserSong::where('song_id', $songId)->first();
            }

            $playlist = UserSong::where('user_id', $user->id)
                ->whereNotNull('played_at')
                ->orderBy('played_at', 'desc')
                ->get()
                ->unique('song_id')
                ->values();

            $currentIndex = $playlist->search(function ($song) use ($songId) {
                return $song->song_id === $songId;
            });

            if ($currentIndex === false) {
                $currentIndex = 0;
            }
        } else {
            // Get user's recent songs
            $playlist = UserSong::where('user_id', $user->id)
                ->whereNotNull('played_at')
                ->orderBy('played_at', 'desc')
                ->get()
                ->unique('song_id')
                ->values();

            $currentSong = $playlist->first();
            $currentIndex = 0;
        }

        return view('player', compact('currentSong', 'playlist', 'currentIndex'));
    }
}
