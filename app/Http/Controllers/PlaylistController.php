<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\UserSong;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
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
        // Ensure the playlist belongs to the authenticated user
        $playlist = Playlist::where('id', $playlistId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $songs = UserSong::where('playlist_id', $playlistId)->get();

        // Ensure complete data for each song (database-first approach)
        $songsWithMetadata = $songs->map(function ($song) {
            // Check database first, fetch missing data if needed
            $completeData = $this->ensureCompleteSongData(
                $song->song_id,
                $song->url,
                $song->title,
                $song->artist,
                $song->thumbnail
            );

            return [
                'id' => $song->id,
                'song_id' => $song->song_id,
                'title' => $completeData['title'],
                'artist' => $completeData['artist'],
                'url' => $completeData['url'],
                'thumbnail' => $completeData['thumbnail'],
                'duration' => $song->duration ?? null,
                'views' => null, // Not stored in database
            ];
        });

        return response()->json($songsWithMetadata);
    }

    /**
     * Fetch fresh metadata for a song using yt-dlp
     */
    private function fetchSongMetadata($songId)
    {
        try {
            $command = [
                'yt-dlp',
                'https://www.youtube.com/watch?v=' . $songId,
                '--skip-download',
                '--print-json',
                '--no-warnings',
                '--quiet'
            ];

            $process = \Illuminate\Support\Facades\Process::run($command);

            if ($process->successful() || $process->exitCode() === 101) {
                $output = trim($process->output());
                $json = json_decode($output, true);

                if ($json) {
                    return [
                        'title' => $json['title'] ?? 'Unknown Title',
                        'artist' => $json['uploader'] ?? $json['channel'] ?? 'Unknown Artist',
                        'url' => 'https://www.youtube.com/watch?v=' . $songId,
                        'thumbnail' => $json['thumbnail'] ?? null,
                        'duration' => isset($json['duration']) ? $this->formatDuration($json['duration']) : null,
                        'views' => $json['view_count'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to fetch metadata for song ' . $songId . ': ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Format duration from seconds to MM:SS format
     */
    private function formatDuration($seconds)
    {
        $mins = floor($seconds / 60);
        $secs = $seconds % 60;
        return sprintf('%d:%02d', $mins, $secs);
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

        // Ensure complete song data
        $songData = $this->ensureCompleteSongData($validated['song_id'], $validated['url'], $validated['title'], $validated['artist'], $validated['thumbnail']);

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
            'title' => $songData['title'],
            'artist' => $songData['artist'],
            'url' => $songData['url'],
            'thumbnail' => $songData['thumbnail'],
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

    /**
     * Show a specific playlist
     */
    public function showPlaylist(Playlist $playlist)
    {
        // Ensure user owns the playlist
        if ($playlist->user_id !== Auth::id()) {
            abort(403);
        }

        $songs = $playlist->songs()->orderBy('created_at', 'desc')->get();

        // Ensure complete data for each song before displaying
        foreach ($songs as $song) {
            $this->ensureCompleteSongData($song->song_id, $song->url, $song->title, $song->artist, $song->thumbnail);
        }

        // Refresh the songs with updated data from database
        $songs = $playlist->songs()->orderBy('created_at', 'desc')->get();

        return view('playlist-detail', ['playlist' => $playlist, 'songs' => $songs]);
    }

    /**
     * Add a song to a playlist (alternative method)
     */
    public function addSongToPlaylist(Request $request, Playlist $playlist)
    {
        // Ensure user owns the playlist
        if ($playlist->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'song_id' => 'required|string',
            'title' => 'required|string',
            'artist' => 'required|string',
            'url' => 'required|url',
            'thumbnail' => 'nullable|url'
        ]);

        // Ensure complete song data
        $songData = $this->ensureCompleteSongData($validated['song_id'], $validated['url'], $validated['title'], $validated['artist'], $validated['thumbnail']);

        // Check if song already exists in playlist
        $existingSong = UserSong::where('user_id', Auth::id())
            ->where('playlist_id', $playlist->id)
            ->where('song_id', $validated['song_id'])
            ->first();

        if ($existingSong) {
            return response()->json(['message' => 'Song already exists in this playlist!']);
        }

        // Add song to user_songs table
        UserSong::create([
            'user_id' => Auth::id(),
            'playlist_id' => $playlist->id,
            'song_id' => $validated['song_id'],
            'title' => $songData['title'],
            'artist' => $songData['artist'],
            'url' => $songData['url'],
            'thumbnail' => $songData['thumbnail'],
        ]);

        return response()->json(['message' => 'Song added to playlist!']);
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
                '--extract-flat',
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
                '--extract-flat',
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
}
