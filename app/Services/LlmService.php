<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class LlmService
{
    protected $youtubeKey;
    protected $useDummy;

    public function __construct($useDummy = true)
    {
        $this->youtubeKey = 'AIzaSyDG9b_9nvvA3u1m6pzES-oytl-H_RSwOQk';
        $this->useDummy = $useDummy; // true = dummy mode, false = GPT4All mode
    }

    public function getRecommendations($feeling)
    {
        Log::info('Received mood: ' . $feeling);

        if ($this->useDummy) {
            // ---------- Dummy mode ----------
            return $this->getDummySongsByMood($feeling);
        }

        // ---------- GPT4All mode ----------
        return $this->getRecommendationFromGPT4All($feeling);
    }

    /**
     * Get dummy songs based on mood/feeling with YouTube data
     */
    private function getDummySongsByMood($feeling)
    {
        $feeling = strtolower(trim($feeling));
        $dummySongs = [];

        if (strpos($feeling, 'sad') !== false || strpos($feeling, 'depressed') !== false || strpos($feeling, 'lonely') !== false) {
            $dummySongs = [
                ['title' => 'Someone Like You', 'artist' => 'Adele'],
                ['title' => 'The Night We Met', 'artist' => 'Lord Huron'],
                ['title' => 'Creep', 'artist' => 'Radiohead'],
            ];
        } elseif (strpos($feeling, 'happy') !== false || strpos($feeling, 'joyful') !== false || strpos($feeling, 'excited') !== false) {
            $dummySongs = [
                ['title' => 'Happy', 'artist' => 'Pharrell Williams'],
                ['title' => 'Good as Hell', 'artist' => 'Lizzo'],
                ['title' => 'Walking on Sunshine', 'artist' => 'Katrina and the Waves'],
            ];
        } elseif (strpos($feeling, 'energetic') !== false || strpos($feeling, 'workout') !== false || strpos($feeling, 'motivated') !== false) {
            $dummySongs = [
                ['title' => 'Lose Yourself', 'artist' => 'Eminem'],
                ['title' => 'Eye of the Tiger', 'artist' => 'Survivor'],
                ['title' => 'Pump It Up', 'artist' => 'Endor'],
            ];
        } elseif (strpos($feeling, 'relaxed') !== false || strpos($feeling, 'chill') !== false || strpos($feeling, 'calm') !== false) {
            $dummySongs = [
                ['title' => 'Weightless', 'artist' => 'Marconi Union'],
                ['title' => 'Sunset', 'artist' => 'The Midnight'],
                ['title' => 'Breathe', 'artist' => 'Pink Floyd'],
            ];
        } elseif (strpos($feeling, 'romantic') !== false || strpos($feeling, 'love') !== false) {
            $dummySongs = [
                ['title' => 'Perfect', 'artist' => 'Ed Sheeran'],
                ['title' => 'Thinking Out Loud', 'artist' => 'Ed Sheeran'],
                ['title' => 'All of Me', 'artist' => 'John Legend'],
            ];
        } else {
            // Default songs
            $dummySongs = [
                ['title' => 'Let It Be', 'artist' => 'The Beatles'],
                ['title' => 'Bohemian Rhapsody', 'artist' => 'Queen'],
                ['title' => 'Imagine', 'artist' => 'John Lennon'],
            ];
        }

        // Fetch YouTube data for each dummy song
        $recommendations = [];
        foreach ($dummySongs as $song) {
            $youtubeData = $this->getYouTubeData($song['title'], $song['artist']);
            if ($youtubeData) {
                $recommendations[] = $youtubeData;
            }
        }

        return $recommendations;
    }

    /**
     * Fetch proper YouTube data for a song
     */
    private function getYouTubeData($title, $artist)
    {
        try {
            $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
                'key' => $this->youtubeKey,
                'q' => "{$title} {$artist}",
                'part' => 'snippet',
                'maxResults' => 1,
                'type' => 'video',
            ]);

            $data = $response->json();

            if (isset($data['items']) && count($data['items']) > 0) {
                $item = $data['items'][0];
                $videoId = $item['id']['videoId'];
                $thumbnail = $item['snippet']['thumbnails']['high']['url'] ?? $item['snippet']['thumbnails']['medium']['url'];

                return [
                    'id' => $videoId,
                    'title' => $title,
                    'artist' => $artist,
                    'url' => "https://www.youtube.com/watch?v={$videoId}",
                    'thumbnail' => $thumbnail,
                ];
            }

            Log::warning("YouTube API: No results found for {$title} by {$artist}");
            return null;

        } catch (\Exception $e) {
            Log::error('YouTube API failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get recommendation from GPT4All (for Tinker testing)
     */
    private function getRecommendationFromGPT4All($feeling)
    {
        try {
            $pythonPath = '/usr/bin/python3';
            $gptScript = '/media/saif/New Volume/gpt4all/gpt4all_runner.py';
            $escapedPrompt = escapeshellarg($feeling);
            $command = "{$pythonPath} {$gptScript} {$escapedPrompt}";
            $output = shell_exec($command);

            Log::info('GPT4All Output: ' . $output);

            $result = json_decode($output, true);

            if (!$result || !isset($result['title']) || !isset($result['artist'])) {
                Log::warning('GPT4All returned invalid data: ' . $output);
                return null;
            }

            $title = $result['title'];
            $artist = $result['artist'];

            // Search YouTube for the song
            $youtubeData = $this->getYouTubeData($title, $artist);

            if ($youtubeData) {
                return [$youtubeData];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('GPT4All request failed: ' . $e->getMessage());
            return null;
        }
    }
}
