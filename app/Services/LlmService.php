<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class LlmService
{
    protected $youtubeKey;

    public function __construct()
    {
        $this->youtubeKey = env('YOUTUBE_API_KEY');
    }
    
    public function getRecommendations($feeling)
    {
        Log::info('Received mood: ' . $feeling);

        // Get song recommendations from Groq
        $songs = $this->getRecommendationFromGroq($feeling);

        if (!$songs) {
            return [];
        }

        // Fetch YouTube data for each recommended song
        $recommendations = [];
        foreach ($songs as $song) {
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

    private function getRecommendationFromGroq($feeling)
    {
        try {
            $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
            $apiKey = env('GROQ_API_KEY');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'model' => 'llama-3.1-8b-instant',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Recommend 3 songs for someone feeling {$feeling}. Provide in format: 1. Title - Artist\n2. Title - Artist\n3. Title - Artist",
                    ],
                ],
            ]);

            $result = $response->json();

            if (!$result || !isset($result['choices'][0]['message']['content'])) {
                Log::warning('Groq API returned invalid data: ' . $response->body());
                return null;
            }

            $content = $result['choices'][0]['message']['content'];

            // Parse the response to extract songs
            $songs = [];
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                if (preg_match('/^\d+\.\s*(.+?)\s*-\s*(.+)$/', $line, $matches)) {
                    $songs[] = [
                        'title' => trim($matches[1]),
                        'artist' => trim($matches[2]),
                    ];
                }
            }

            return $songs;
        } catch (\Exception $e) {
            Log::error('Groq API request failed: ' . $e->getMessage());
            return null;
        }
    }
}