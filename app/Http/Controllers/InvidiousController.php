<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class InvidiousController extends Controller
{
    /**
     * Search for songs using yt-dlp
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return view('search-results', [
                'query' => '',
                'results' => collect()
            ]);
        }

        // Search for songs using yt-dlp
        $results = $this->searchWithYtDlp($query);

        return view('search-results', [
            'query' => $query,
            'results' => $results
        ]);
    }

    /**
     * Use yt-dlp to search for songs
     */
    private function searchWithYtDlp($query)
    {
        try {
            Log::info("Searching with yt-dlp for query: {$query}");

            // Use yt-dlp to search and get JSON results
            $command = [
                'yt-dlp',
                'ytsearch10:' . $query,  // Get 10 results
                '--skip-download',
                '--print-json',
                '--no-warnings',
                '--quiet'
            ];

            $process = Process::run($command);

            // yt-dlp returns exit code 101 when successful but with some conditions
            if ($process->successful() || $process->exitCode() === 101) {
                $output = trim($process->output());
                $lines = explode("\n", $output);
                $results = [];

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    $json = json_decode($line, true);
                    if (isset($json['id']) && isset($json['title'])) {
                        $results[] = [
                            'id' => $json['id'],
                            'title' => $json['title'] ?? 'Unknown Title',
                            'artist' => $json['uploader'] ?? $json['channel'] ?? 'Unknown Artist',
                            'url' => 'https://www.youtube.com/watch?v=' . $json['id'],
                            'thumbnail' => $json['thumbnail'] ?? asset('images/cats.jpg'),
                            'views' => $json['view_count'] ?? 0,
                            'duration' => $this->formatDuration($json['duration'] ?? 0),
                        ];
                    }
                }

                Log::info('Found ' . count($results) . ' search results');
                return collect($results);
            } else {
                Log::error('yt-dlp search failed: ' . $process->errorOutput());
            }
        } catch (\Exception $e) {
            Log::error('yt-dlp search failed: ' . $e->getMessage());
        }

        return collect();
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
}
