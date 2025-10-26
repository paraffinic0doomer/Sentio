<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

class LlmService
{
    /**
     * Get recommendations based on mood using Groq + yt-dlp
     */
    public function getRecommendations($feeling, $limit = null, $offset = 0)
    {
        Log::info('Received mood: ' . $feeling . ', limit: ' . $limit . ', offset: ' . $offset);

        // Step 1: Get song suggestions (Title - Artist) from Groq
        $songsToRequest = $limit ? max($limit + $offset + 10, 20) : 3; // Get larger pool for pagination, 3 for dashboard
        $songs = $this->getRecommendationFromGroq($feeling, $songsToRequest);

        if (!$songs || empty($songs)) {
            Log::warning('No songs returned from Groq for mood: ' . $feeling);
            return [];
        }

        // Apply offset and limit if specified (for pagination)
        if ($offset > 0 || $limit !== null) {
            $songs = array_slice($songs, $offset, $limit);
        } elseif ($limit === null) {
            // For dashboard (no limit specified), take only first 3 songs
            $songs = array_slice($songs, 0, 3);
        }

        // Step 2: Fetch metadata for each song using yt-dlp
        $recommendations = [];
        if (count($songs) > 3) {
            // Use async fetching for larger batches (explore page)
            $recommendations = $this->fetchMultipleSongMetadata($songs);
        } else {
            // Use sequential fetching for smaller batches (dashboard)
            foreach ($songs as $song) {
                $data = $this->getYtDlpMetadata("{$song['title']} {$song['artist']}");
                if ($data) {
                    $recommendations[] = $data;
                }
            }
        }

        Log::info('Generated ' . count($recommendations) . ' recommendations via yt-dlp');
        return $recommendations;
    }

    /**
     * Use yt-dlp to fetch metadata for a YouTube video search
     */
    private function getYtDlpMetadata($query)
    {
        try {
            Log::info("Fetching metadata with yt-dlp for query: {$query}");

            // Use yt-dlp to search and get JSON result of top 1 video
            $command = [
                'yt-dlp',
                'ytsearch1:' . $query,
                '--skip-download',
                '--print-json',
                '--socket-timeout', '5',
                '--cache-dir', storage_path('app/yt-dlp-cache'),
                '--quiet'
            ];

            $process = Process::timeout(30)->run($command);

            if ($process->successful()) {
                $output = trim($process->output());
                $json = json_decode($output, true);

                if (isset($json['id'])) {
                    return [
                        'id' => $json['id'],
                        'title' => $json['title'] ?? 'Unknown Title',
                        'artist' => $json['uploader'] ?? 'Unknown Artist',
                        'url' => 'https://www.youtube.com/watch?v=' . $json['id'],
                        'thumbnail' => $json['thumbnail'] ?? asset('images/cats.jpg'),
                    ];
                } else {
                    Log::warning("yt-dlp returned no valid result for query: {$query}");
                }
            } else {
                Log::error('yt-dlp failed: ' . $process->errorOutput());
            }
        } catch (\Exception $e) {
            Log::error('yt-dlp metadata fetch failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Use Groq API to get song recommendations (title + artist)
     */
    private function getRecommendationFromGroq($feeling, $count = null)
    {
        try {
            $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
            $apiKey = env('GROQ_API_KEY');

            if (!$apiKey) {
                Log::error('GROQ_API_KEY not set in environment');
                return null;
            }

            $songCount = $count ?: 8; // Default to 8 songs for explore
            
            // Create dynamic prompt based on song count
            $examples = [];
            for ($i = 1; $i <= min($songCount, 10); $i++) {
                $examples[] = "{$i}. Title - Artist";
            }
            $examplesStr = implode("\n", $examples);
            
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'model' => 'llama-3.1-8b-instant',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Recommend {$songCount} songs for someone feeling {$feeling}. Format strictly as:\n{$examplesStr}",
                    ],
                ],
                'max_tokens' => min(400 + ($songCount * 20), 2000), // Increase tokens for more songs
                'temperature' => 0.7,
            ]);

            if ($response->failed()) {
                Log::error('Groq API request failed: ' . $response->status() . ' - ' . $response->body());
                return null;
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';

            // Parse Groq response to extract song titles and artists
            $songs = [];
            foreach (explode("\n", $content) as $line) {
                if (preg_match('/^\d+\.\s*(.+?)\s*-\s*(.+)$/', $line, $m)) {
                    $songs[] = [
                        'title' => trim($m[1]),
                        'artist' => trim($m[2]),
                    ];
                }
            }

            return $songs;
        } catch (\Exception $e) {
            Log::error('Groq API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all song suggestions from Groq without fetching metadata
     */
    public function getAllSongSuggestions($feeling, $count = 100)
    {
        return $this->getRecommendationFromGroq($feeling, $count);
    }

    /**
     * Fetch metadata for a batch of songs
     */
    public function fetchBatchMetadata($songs)
    {
        return $this->fetchMultipleSongMetadata($songs);
    }
    private function fetchMultipleSongMetadata($songs)
    {
        $recommendations = [];

        // Process songs sequentially for now (can be optimized later)
        foreach ($songs as $song) {
            $query = "{$song['title']} {$song['artist']}";
            
            $command = [
                'yt-dlp',
                'ytsearch1:' . $query,
                '--skip-download',
                '--print-json',
                '--socket-timeout', '5',
                '--cache-dir', storage_path('app/yt-dlp-cache'),
                '--quiet'
            ];

            $process = Process::timeout(30)->run($command);
            
            if ($process->successful()) {
                $output = trim($process->output());
                $json = json_decode($output, true);

                if (isset($json['id'])) {
                    $recommendations[] = [
                        'id' => $json['id'],
                        'title' => $json['title'] ?? 'Unknown Title',
                        'artist' => $json['uploader'] ?? 'Unknown Artist',
                        'url' => 'https://www.youtube.com/watch?v=' . $json['id'],
                        'thumbnail' => $json['thumbnail'] ?? asset('images/cats.jpg'),
                    ];
                }
            }
        }

        return $recommendations;
    }
}
