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
    public function getRecommendations($feeling)
    {
        Log::info('Received mood: ' . $feeling);

        // Step 1: Get song suggestions (Title - Artist) from Groq
        $songs = $this->getRecommendationFromGroq($feeling);

        if (!$songs || empty($songs)) {
            Log::warning('No songs returned from Groq for mood: ' . $feeling);
            return [];
        }

        // Step 2: Fetch metadata for each song using yt-dlp
        $recommendations = [];
        foreach ($songs as $song) {
            $data = $this->getYtDlpMetadata("{$song['title']} {$song['artist']}");
            if ($data) {
                $recommendations[] = $data;
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
                '--no-warnings',
                '--no-playlist',
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
    private function getRecommendationFromGroq($feeling)
    {
        try {
            $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
            $apiKey = env('GROQ_API_KEY');

            if (!$apiKey) {
                Log::error('GROQ_API_KEY not set in environment');
                return null;
            }

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'model' => 'llama-3.1-8b-instant',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Recommend 3 songs for someone feeling {$feeling}. Format strictly as:\n1. Title - Artist\n2. Title - Artist\n3. Title - Artist",
                    ],
                ],
                'max_tokens' => 200,
                'temperature' => 0.7,
            ]);

            if ($response->failed()) {
                Log::error('Groq API request failed: ' . $response->status() . ' - ' . $response->body());
                return null;
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';

            Log::info('Groq response: ' . $content);

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

            Log::info('Parsed ' . count($songs) . ' songs from Groq response');
            return $songs;
        } catch (\Exception $e) {
            Log::error('Groq API error: ' . $e->getMessage());
            return null;
        }
    }
}
