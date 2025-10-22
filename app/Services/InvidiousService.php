<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class InvidiousService
{
    protected $instance;

    public function __construct()
    {
        $this->instance = 'https://yewtu.be';
    }

    /**
     * Search for YouTube videos using Invidious API
     */
    public function searchVideos($query, $limit = 10)
    {
        $cacheKey = "invidious_search_{$query}_{$limit}";
        
        \Log::info('Searching for query: ' . $query);

        return Cache::remember($cacheKey, 3600, function () use ($query, $limit) {
            try {
                $response = Http::timeout(10)->get("{$this->instance}/api/v1/search", [
                    'q' => $query,
                    'type' => 'video',
                    'sort_by' => 'relevance',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data) && !empty($data)) {
                        return collect($data)->take($limit)->map(function ($video) {
                            return [
                                'id' => $video['videoId'] ?? '',
                                'title' => $video['title'] ?? 'Unknown Title',
                                'artist' => $video['author'] ?? 'Unknown Artist',
                                'duration' => $video['lengthSeconds'] ?? 0,
                                'views' => $video['viewCount'] ?? 0,
                                'thumbnail' => $video['videoThumbnails'][0]['url'] ?? "{$this->instance}/vi/{$video['videoId']}/mqdefault.jpg",
                                'url' => "{$this->instance}/watch?v={$video['videoId']}",
                            ];
                        });
                    }
                } else {
                    \Log::error('Invidious search failed: ' . $response->status() . ' - ' . $response->body());
                }
            } catch (\Exception $e) {
                \Log::error('Invidious search error: ' . $e->getMessage());
            }
            return collect();
        });
    }

    /**
     * Get detailed info for a single video
     */
    public function getVideoDetails($videoId)
    {
        $cacheKey = "invidious_video_{$videoId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($videoId) {
            try {
                $response = Http::timeout(10)->get("{$this->instance}/api/v1/videos/{$videoId}");

                if ($response->successful()) {
                    $video = $response->json();
                    return [
                        'id' => $videoId,
                        'title' => $video['title'] ?? 'Unknown Title',
                        'artist' => $video['author'] ?? 'Unknown Artist',
                        'duration' => $video['lengthSeconds'] ?? 0,
                        'views' => $video['viewCount'] ?? 0,
                        'description' => $video['description'] ?? '',
                        'url' => "{$this->instance}/watch?v={$videoId}",
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Invidious video details error: ' . $e->getMessage());
            }
            return null;
        });
    }

    /**
     * Get mood-based music recommendations
     */
    public function getMoodRecommendations($mood)
    {
        $queries = [
            'happy' => 'happy upbeat songs',
            'sad' => 'sad emotional songs',
            'energetic' => 'energetic workout music',
            'relaxed' => 'chill relaxing music',
        ];
        $query = $queries[$mood] ?? $mood . ' music';
        return $this->searchVideos($query, 5);
    }
}
