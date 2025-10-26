<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\LlmService;
use App\Models\UserSong;
use App\Models\Playlist;
use App\Models\Mood;

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
            ->where('played_at', '<=', now())
            ->orderBy('played_at', 'desc')
            ->get()
            ->unique('song_id')
            ->take(5);

        // Ensure complete data for each song before displaying
        foreach ($lastPlayed as $song) {
            $this->ensureCompleteSongData($song->song_id, $song->url, $song->title, $song->artist, $song->thumbnail);
        }

        // Refresh the collection with updated data from database
        $lastPlayed = UserSong::where('user_id', $user->id)
            ->whereNotNull('played_at')
            ->where('played_at', '<=', now())
            ->orderBy('played_at', 'desc')
            ->get()
            ->unique('song_id')
            ->take(5);

        return view('dashboard', compact('lastPlayed'));
    }

    /**
     * Handle mood input and get recommendations
     */
    public function getRecommendations(Request $request)
    {
        $feeling = $request->input('feeling');
        Log::info('User feeling received: ' . $feeling);

        // Check if we have cached recommendations for this feeling
        $cachedRecommendations = session('recommendations');
        $cachedFeeling = session('recommendations_feeling');

        // If no feeling provided and we have cached recommendations, return them
        if (!$feeling && $cachedRecommendations) {
            return response()->json([
                'status' => 'success',
                'feeling' => $cachedFeeling,
                'recommendations' => $cachedRecommendations,
                'cached' => true,
            ]);
        }

        // If same feeling as cached, return cached results
        if ($feeling && $cachedFeeling === $feeling && $cachedRecommendations) {
            return response()->json([
                'status' => 'success',
                'feeling' => $feeling,
                'recommendations' => $cachedRecommendations,
                'cached' => true,
            ]);
        }

        // Generate new recommendations
        $recommendations = $this->llm->getRecommendations($feeling);

        // Track user's mood for the last 7 days
        if ($feeling) {
            Mood::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'date' => now()->toDateString(),
                ],
                ['mood' => $feeling]
            );
        }

        // Store in session for persistence across page loads
        session([
            'recommendations' => $recommendations,
            'recommendations_feeling' => $feeling,
        ]);

        return response()->json([
            'status' => 'success',
            'feeling' => $feeling,
            'recommendations' => $recommendations,
            'cached' => false,
        ]);
    }

    /**
     * Clear cached recommendations
     */
    public function clearRecommendations()
    {
        session()->forget(['recommendations', 'recommendations_feeling']);

        return response()->json([
            'status' => 'success',
            'message' => 'Recommendations cleared',
        ]);
    }

    /**
     * Display the explore page with mood-based recommendations
     */
    public function explore()
    {
        $user = Auth::user();

        // Get user's mood history from the last 7 days
        $recentMoods = Mood::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->orderBy('date', 'desc')
            ->get();

        // If no moods in last 7 days, check for any mood history at all
        if ($recentMoods->isEmpty()) {
            $recentMoods = Mood::where('user_id', $user->id)
                ->orderBy('date', 'desc')
                ->get();
        }

        // If no mood history at all, show page with message to get recommendations first
        if ($recentMoods->isEmpty()) {
            return view('explore', [
                'recentMoods' => collect(),
                'primaryMood' => null,
                'hasMoodHistory' => false
            ]);
        }

        // Get the most common mood from available mood history
        $moodCounts = $recentMoods->groupBy('mood')->map->count()->sortDesc();
        $primaryMood = $moodCounts->keys()->first();

        return view('explore', [
            'recentMoods' => $recentMoods,
            'primaryMood' => $primaryMood,
            'hasMoodHistory' => true
        ]);
    }

    /**
     * Get explore recommendations based on mood history
     */
    public function getExploreRecommendations(Request $request)
    {
        $user = Auth::user();
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);

        // Get user's mood history from the last 7 days
        $recentMoods = Mood::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->orderBy('date', 'desc')
            ->get();

        // If no moods in last 7 days, check for any mood history at all
        if ($recentMoods->isEmpty()) {
            $recentMoods = Mood::where('user_id', $user->id)
                ->orderBy('date', 'desc')
                ->get();
        }

        if ($recentMoods->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No mood history found. Please get recommendations first.',
            ]);
        }

        // Get the most common mood from the last 7 days
        $moodCounts = $recentMoods->groupBy('mood')->map->count()->sortDesc();
        $primaryMood = $moodCounts->keys()->first();

        // Check if we have cached songs for this mood, or if we need to fetch new ones
        $cacheKey = 'explore_songs_' . $user->id . '_' . $primaryMood;
        $cachedSongs = session($cacheKey);

        if (!$cachedSongs || $offset === 0) {
            // Fetch 100 songs from Groq for the first request or if cache is empty
            $allSongs = $this->llm->getAllSongSuggestions($primaryMood, 100);
            
            if (!$allSongs || empty($allSongs)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to generate recommendations. Please try again.',
                ]);
            }

            // Cache the songs in session
            session([$cacheKey => $allSongs]);
            $cachedSongs = $allSongs;
        }

        // Get the current batch of songs
        $batchSongs = array_slice($cachedSongs, $offset, $limit);

        // Fetch metadata for this batch
        $recommendations = $this->llm->fetchBatchMetadata($batchSongs);

        // Check if there are more songs available
        $hasMore = ($offset + $limit) < count($cachedSongs);

        return response()->json([
            'status' => 'success',
            'mood' => $primaryMood,
            'recommendations' => $recommendations,
            'hasMore' => $hasMore,
        ]);
    }

    /**
     * Handle song play request
     */
    public function playSong(Request $request)
    {
        \Log::info('playSong called with data:', $request->all());

        $songId = $request->input('song_id');
        $title = $request->input('title');
        $artist = $request->input('artist');
        $thumbnail = $request->input('thumbnail');
        $url = $request->input('url');

        \Log::info("Processing song: {$songId} - {$title} by {$artist}");

        // Ensure complete song data is in database
        $songData = $this->ensureCompleteSongData($songId, $url, $title, $artist, $thumbnail);

        \Log::info('Song data after ensureCompleteSongData:', $songData);

        // Update or create the played song record
        $song = UserSong::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'song_id' => $songId,
            ],
            [
                'title' => $songData['title'],
                'artist' => $songData['artist'],
                'thumbnail' => $songData['thumbnail'],
                'url' => $songData['url'],
                'played_at' => now(),
            ]
        );

        \Log::info('Song saved to database:', $song->toArray());

        return response()->json(['status' => 'success']);
    }

    /**
     * Ensure complete song data is in database, fetch missing data from yt-dlp
     */
    private function ensureCompleteSongData($songId, $url, $providedTitle = null, $providedArtist = null, $providedThumbnail = null)
    {
        $userId = Auth::id();

        // Check if song exists in database for this user
        $song = UserSong::where('user_id', $userId)
            ->where('song_id', $songId)
            ->first();

        $needsUpdate = false;
        $updateData = [];

        if (!$song) {
            // Song not in database, fetch all data
            $fetchedData = $this->fetchSongData($url);
            return [
                'title' => $fetchedData['title'] ?? $providedTitle,
                'artist' => $fetchedData['artist'] ?? $providedArtist,
                'thumbnail' => $fetchedData['thumbnail'] ?? $providedThumbnail,
                'url' => $url,
            ];
        } else {
            // Song exists, check for missing data
            if (empty($song->title)) {
                $needsUpdate = true;
                $updateData['title'] = $providedTitle ?? $this->fetchSongData($url)['title'];
            }
            if (empty($song->artist)) {
                $needsUpdate = true;
                $updateData['artist'] = $providedArtist ?? $this->fetchSongData($url)['artist'];
            }
            if (empty($song->thumbnail)) {
                $needsUpdate = true;
                // Fetch thumbnail
                $updateData['thumbnail'] = $this->fetchThumbnail($url);
            }
            if (empty($song->url)) {
                $needsUpdate = true;
                $updateData['url'] = $url;
            }

            if ($needsUpdate) {
                $song->update($updateData);
            }

            return [
                'title' => $song->title,
                'artist' => $song->artist,
                'thumbnail' => $song->thumbnail,
                'url' => $song->url,
            ];
        }
    }

    /**
     * Show user listening history
     */
    public function history()
    {
        $songs = UserSong::where('user_id', Auth::id())
            ->whereNotNull('played_at')
            ->where('played_at', '<=', now())
            ->orderBy('played_at', 'desc')
            ->get()
            ->unique('song_id')
            ->take(20);

        // Ensure complete data for each song before displaying
        foreach ($songs as $song) {
            $this->ensureCompleteSongData($song->song_id, $song->url, $song->title, $song->artist, $song->thumbnail);
        }

        // Refresh the collection with updated data from database
        $songs = UserSong::where('user_id', Auth::id())
            ->whereNotNull('played_at')
            ->where('played_at', '<=', now())
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

        if (!$song) {
            abort(404, 'Song not found');
        }

        // If audio doesn't exist, try to extract it
        if (!$song->audio_url || !file_exists(storage_path('app/audio/' . basename($song->audio_url)))) {
            if ($song->url) {
                $this->extractAudio($song->url, $songId);
                // Refresh song data
                $song = UserSong::where('song_id', $songId)
                    ->where('user_id', Auth::id())
                    ->first();
            }
        }

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
        \Log::info('showPlayer called with songId:', [$songId]);
        
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

            // Ensure complete data for currentSong
            if ($currentSong) {
                $this->ensureCompleteSongData($currentSong->song_id, $currentSong->url, $currentSong->title, $currentSong->artist, $currentSong->thumbnail);
                // Refresh the song data
                $currentSong = UserSong::where('song_id', $songId)
                    ->where('user_id', $user->id)
                    ->first() ?? $currentSong;
            }

            // Get all user songs for the playlist, ordered by most recently played first
            $playlist = UserSong::where('user_id', $user->id)
                ->orderBy('played_at', 'desc')
                ->orderBy('created_at', 'desc')
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
            // Get all user songs
            $playlist = UserSong::where('user_id', $user->id)
                ->orderBy('played_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('song_id')
                ->values();

            $currentSong = $playlist->first();
            $currentIndex = 0;
        }

        return view('player', compact('currentSong', 'playlist', 'currentIndex'));
    }

    /**
     * Show music player for a specific playlist
     */
    public function showPlaylistPlayer($playlistId, $songId = null)
    {
        $user = Auth::user();

        // Get the playlist and ensure user owns it
        $playlistModel = Playlist::where('id', $playlistId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Get songs from this playlist
        $playlist = UserSong::where('user_id', $user->id)
            ->where('playlist_id', $playlistId)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($songId) {
            // Find the current song
            $currentSong = $playlist->where('song_id', $songId)->first();

            if (!$currentSong) {
                // If song not in playlist, find it and ensure complete data
                $currentSong = UserSong::where('song_id', $songId)
                    ->where('user_id', $user->id)
                    ->first();
                if ($currentSong) {
                    $this->ensureCompleteSongData($currentSong->song_id, $currentSong->url, $currentSong->title, $currentSong->artist, $currentSong->thumbnail);
                    $currentSong = UserSong::where('song_id', $songId)
                        ->where('user_id', $user->id)
                        ->first();
                }
            }

            $currentIndex = $playlist->search(function ($song) use ($songId) {
                return $song->song_id === $songId;
            });

            if ($currentIndex === false) {
                $currentIndex = 0;
            }
        } else {
            $currentSong = $playlist->first();
            $currentIndex = 0;
        }

        return view('player', compact('currentSong', 'playlist', 'currentIndex'));
    }

    /**
     * Fetch thumbnail from yt-dlp for a given URL
     */
    private function fetchThumbnail($url)
    {
        try {
            $command = [
                'yt-dlp',
                $url,
                '--skip-download',
                '--print-json',
                '--socket-timeout', '5',
                '--cache-dir', storage_path('app/yt-dlp-cache'),
                '--quiet',
            ];

            $process = \Illuminate\Support\Facades\Process::run($command);

            if ($process->successful() || $process->exitCode() === 101) {
                $output = trim($process->output());
                $json = json_decode($output, true);

                if ($json && isset($json['thumbnail'])) {
                    return $json['thumbnail'];
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to fetch thumbnail for URL ' . $url . ': ' . $e->getMessage());
        }

        return null; // Return null instead of fallback
    }

    /**
     * Fetch complete song data from yt-dlp for a given URL
     */
    private function fetchSongData($url)
    {
        try {
            $command = [
                'yt-dlp',
                $url,
                '--skip-download',
                '--print-json',
                '--socket-timeout', '5',
                '--cache-dir', storage_path('app/yt-dlp-cache'),
            ];

            $process = \Illuminate\Support\Facades\Process::run($command);

            if ($process->successful() || $process->exitCode() === 101) {
                $output = trim($process->output());
                $json = json_decode($output, true);

                if ($json) {
                    return [
                        'title' => $json['title'] ?? null,
                        'artist' => $json['uploader'] ?? $json['channel'] ?? null,
                        'thumbnail' => $json['thumbnail'] ?? null,
                        'duration' => $json['duration'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to fetch song data for URL ' . $url . ': ' . $e->getMessage());
        }

        return [
            'title' => null,
            'artist' => null,
            'thumbnail' => null,
            'duration' => null,
        ];
    }

    /**
     * Extract audio from YouTube URL using yt-dlp
     */
    private function extractAudio($url, $songId)
    {
        try {
            // Create audio directory if it doesn't exist
            $audioDir = storage_path('app/audio');
            if (!file_exists($audioDir)) {
                mkdir($audioDir, 0755, true);
            }

            // Generate filename
            $filename = $songId . '.mp3';
            $audioPath = $audioDir . '/' . $filename;

            // Skip if audio already exists
            if (file_exists($audioPath)) {
                return '/audio/' . $filename;
            }

            // Extract audio using yt-dlp
            $command = [
                'yt-dlp',
                '--extract-audio',
                '--audio-format', 'mp3',
                '--audio-quality', '128K',
                '--output', $audioPath,
                '--no-playlist',
                '--socket-timeout', '30',
                '--cache-dir', storage_path('app/yt-dlp-cache'),
                '--quiet',
                $url
            ];

            $process = \Illuminate\Support\Facades\Process::run($command);

            if ($process->successful()) {
                // Update the song record with audio URL and extraction timestamp
                UserSong::where('song_id', $songId)
                    ->where('user_id', Auth::id())
                    ->update([
                        'audio_url' => '/audio/' . $filename,
                        'audio_extracted_at' => now(),
                    ]);

                return '/audio/' . $filename;
            } else {
                \Illuminate\Support\Facades\Log::error('Audio extraction failed for URL ' . $url . ': ' . $process->errorOutput());
                return null;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Audio extraction error for URL ' . $url . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Rate a song
     */
    public function rateSong(Request $request)
    {
        $request->validate([
            'song_id' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $song = UserSong::where('song_id', $request->song_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$song) {
            return response()->json([
                'status' => 'error',
                'message' => 'Song not found'
            ], 404);
        }

        $song->update(['rating' => $request->rating]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rating saved successfully',
            'rating' => $request->rating
        ]);
    }
}
