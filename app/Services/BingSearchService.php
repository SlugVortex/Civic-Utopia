<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BingSearchService
{
    protected $baseUrl = 'https://bing-search-apis.p.rapidapi.com/api/rapid';
    protected $headers;

    public function __construct()
    {
        // UPDATED: Using your specific 'rapidapi_gary' config keys
        $this->headers = [
            'x-rapidapi-host' => config('services.rapidapi_gary.host'),
            'x-rapidapi-key' => config('services.rapidapi_gary.key'),
        ];
    }

    /**
     * Perform a Web Search
     */
    public function searchWeb($query, $count = 10)
    {
        try {
            $response = Http::withHeaders($this->headers)
                ->get($this->baseUrl . '/web_search', [
                    'keyword' => $query,
                    'page' => 0,
                    'size' => $count,
                    'cc' => 'JM',
                    'freshness' => 'Year',
                ]);

            if ($response->failed()) {
                Log::error('[BingSearch] Web search failed: ' . $response->body());
                return [];
            }

            return $response->json('data.items') ?? [];
        } catch (\Exception $e) {
            Log::error('[BingSearch] Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Perform an Image Search (Strict Mode)
     */
    public function searchImages($query, $count = 10)
    {
        try {
            // STRATEGY: Add keywords to force a headshot and exclude wallpapers
            // "-wallpaper" tells Bing to remove results with that word
            $refinedQuery = $query . " official portrait headshot -wallpaper -background -art";

            $response = Http::withHeaders($this->headers)
                ->get($this->baseUrl . '/image_search', [
                    'keyword' => $refinedQuery,
                    'page' => 0,
                    'size' => $count,
                    'aspect' => 'Tall', // Prefer vertical images (portraits)
                    'imageType' => 'Photo',
                ]);

            if ($response->failed()) {
                Log::error('[BingSearch] Image search failed: ' . $response->body());
                return [];
            }

            return $response->json('data.images') ?? [];
        } catch (\Exception $e) {
            Log::error('[BingSearch] Exception: ' . $e->getMessage());
            return [];
        }
    }
}
