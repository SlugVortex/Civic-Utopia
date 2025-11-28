<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsAgentService
{
    /**
     * Search for hyper-local news with fallback queries.
     */
    public function searchNews(float $lat, float $lon): array
    {
        $locationInfo = $this->getDetailedLocation($lat, $lon);

        // --- NEW: Query Loop Strategy ---
        // We will try multiple queries, from most specific to most broad.
        $queries = [
            $this->buildLocalQuery($locationInfo, 'specific'), // e.g., "Mandeville local events"
            $this->buildLocalQuery($locationInfo, 'broad'),   // e.g., "Jamaica national news"
            "Caribbean regional news", // Last resort
        ];

        // Use fallback to ensure the correct key is used from your .env
        $apiKey = config('services.rapidapi.key') ?? env('RAPIDAPI_KEY_GARY');
        $apiHost = config('services.rapidapi.host') ?? env('RAPIDAPI_HOST_GARY') ?? 'bing-search-apis.p.rapidapi.com';

        foreach ($queries as $query) {
            Log::info("[NewsAgent] 📡 Trying query: '$query'");

            try {
                $response = Http::timeout(25)->withHeaders([
                    'x-rapidapi-key' => $apiKey,
                    'x-rapidapi-host' => $apiHost,
                ])->get("https://{$apiHost}/api/rapid/news_search", [
                    // FIX: Using correct parameters for this specific RapidAPI provider
                    'keyword' => $query,
                    'size'    => 20, // Get more results to filter through
                    'cc'      => $this->getMarketCode($locationInfo['country_code']),
                    'page'    => 0,
                    'freshness' => 'Week', // Broaden to a week to find more articles
                ]);

                if ($response->failed()) {
                    Log::error("[NewsAgent] API call for query '$query' failed: " . $response->body());
                    continue; // Try next query if this one fails
                }

                $data = $response->json();
                // Handle various potential response structures from wrappers
                $items = $data['data']['news'] ?? $data['data'] ?? $data['value'] ?? [];

                if (!empty($items)) {
                    // We got results! Filter them and return.
                    $filteredItems = $this->filterLocalNews($items, $locationInfo);

                    if(!empty($filteredItems)) {
                        shuffle($filteredItems);
                        Log::info("[NewsAgent] Found " . count($filteredItems) . " relevant news items for query '$query'.");
                        return array_values(array_slice($filteredItems, 0, 10));
                    }
                }

                // If we are here, the search worked but found nothing. Wait and try next query.
                sleep(1);

            } catch (\Exception $e) {
                Log::error("[NewsAgent] Search Exception for query '$query': " . $e->getMessage());
                continue; // Try next query
            }
        }

        // If all queries failed to find anything
        Log::warning("[NewsAgent] All search queries returned 0 results.");
        return [];
    }

    /**
     * Get detailed location info from coordinates using reverse geocoding
     */
    private function getDetailedLocation(float $lat, float $lon): array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'CivicUtopia/1.0'])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lon,
                    'format' => 'json',
                    'accept-language' => 'en',
                    'addressdetails' => 1
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $address = $data['address'] ?? [];

                $locationInfo = [
                    'city' => $address['city'] ?? $address['town'] ?? $address['municipality'] ?? null,
                    'state' => $address['state'] ?? $address['region'] ?? null,
                    'country' => $address['country'] ?? null,
                    'country_code' => $address['country_code'] ?? null,
                    'display_name' => $data['display_name'] ?? null
                ];

                Log::info("[NewsAgent] 📍 Location: {$locationInfo['city']}, {$locationInfo['state']}, {$locationInfo['country']}");
                return $locationInfo;
            }
        } catch (\Exception $e) {
            Log::warning("[NewsAgent] Geocoding failed: " . $e->getMessage());
        }

        return [
            'city' => null, 'state' => null, 'country' => 'Jamaica', 'country_code' => 'jm', 'display_name' => null
        ];
    }

    /**
     * Build intelligent search query based on location
     */
    private function buildLocalQuery(array $locationInfo, string $specificity = 'specific'): string
    {
        $queryVariations = ['breaking news', 'local news', 'community updates', 'headlines', 'today'];
        $queryType = $queryVariations[array_rand($queryVariations)];

        if ($specificity === 'specific' && $locationInfo['city']) {
            return "{$locationInfo['city']} {$queryType}";
        }

        if ($locationInfo['country']) {
            return "{$locationInfo['country']} national {$queryType}";
        }

        return "local news";
    }

    /**
     * Filter news items for local relevance (soft filter)
     */
    private function filterLocalNews(array $items, array $locationInfo): array
    {
        $keywords = array_filter([
            $locationInfo['city'],
            $locationInfo['state'],
            $locationInfo['country']
        ]);

        if (empty($keywords)) return $items;

        $localItems = array_filter($items, function($item) use ($keywords) {
            $text = strtolower(($item['name'] ?? $item['title'] ?? '') . ' ' . ($item['description'] ?? ''));
            foreach ($keywords as $keyword) {
                if (stripos($text, strtolower($keyword)) !== false) {
                    return true;
                }
            }
            return false;
        });

        // If strict filtering found results, use them. Otherwise, use the original unfiltered list.
        return !empty($localItems) ? $localItems : $items;
    }

    /**
     * Get appropriate market/country code for Bing News API
     */
    private function getMarketCode(?string $countryCode): string
    {
        // RapidAPI uses 'cc' which is a 2-letter country code.
        return strtoupper($countryCode) ?? 'US';
    }

    public function analyzeNewsContent(string $title): array
    {
        $endpoint = config('services.azure.openai.endpoint') . "openai/deployments/" . config('services.azure.openai.deployment') . "/chat/completions?api-version=" . config('services.azure.openai.api_version');
        $apiKey = config('services.azure.openai.api_key');

        try {
            $response = Http::timeout(20)->withHeaders([
                'api-key' => $apiKey, 'Content-Type' => 'application/json'
            ])->post($endpoint, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a news assistant. Output valid JSON. {"summary": "1 engaging sentence", "keywords": "3 visual nouns for image generation"}.'],
                    ['role' => 'user', 'content' => $title]
                ],
                'max_tokens' => 100, 'temperature' => 0.5, 'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                $json = json_decode($content, true);
                return [
                    'summary' => $json['summary'] ?? $title,
                    'keywords' => $json['keywords'] ?? $title
                ];
            }
        } catch (\Exception $e) {
            Log::warning("[NewsAgent] AI Analysis failed: " . $e->getMessage());
        }
        return ['summary' => "Update: $title", 'keywords' => Str::limit($title, 25, '')];
    }

    public function generateImage(string $prompt): ?string
    {
        $endpoint = config('services.azure_dalle.endpoint');
        $apiKey = config('services.azure_dalle.key');

        if (!$endpoint || !$apiKey) return null;

        $safePrompt = "News photo style: " . Str::limit($prompt, 250);

        try {
            Log::info("[NewsAgent] 🎨 Attempting DALL-E generation...");

            // FIX: Increased timeout to 45 seconds
            $response = Http::timeout(45)->withHeaders([
                'api-key' => $apiKey, 'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'prompt' => $safePrompt, 'size' => '1024x1024', 'n' => 1, 'style' => 'natural', 'quality' => 'standard'
            ]);

            if ($response->failed()) {
                Log::warning("[NewsAgent] DALL-E failed with status: " . $response->status() . " Body: " . $response->body());
                return null;
            }

            $imageUrl = $response->json('data.0.url');
            if ($imageUrl) {
                Log::info("[NewsAgent] ✅ DALL-E generated image");
                return $this->downloadAndSaveImage($imageUrl, 45);
            }
            return null;

        } catch (\Exception $e) {
            Log::warning("[NewsAgent] DALL-E exception: " . $e->getMessage());
            return null;
        }
    }

    public function findCivicImage(string $smartKeywords, ?array $newsItem = null): ?string
    {
        // Try DALL-E first for the "wow" factor
        $generatedImage = $this->generateImage($smartKeywords);
        if ($generatedImage) return $generatedImage;

        if ($newsItem && isset($newsItem['image']['thumbnail']['contentUrl'])) {
            $url = $newsItem['image']['thumbnail']['contentUrl'];
            Log::info("[NewsAgent] 📸 Trying original news thumbnail...");
            $result = $this->downloadAndSaveImage($url, 30);
            if ($result) return $result;
        }

        $wikiImage = $this->searchWikimedia($smartKeywords);
        if ($wikiImage) return $wikiImage;

        Log::info("[NewsAgent] 🎲 Using Picsum placeholder...");
        $picsumUrl = "https://picsum.photos/800/600?random=" . rand(1, 1000);
        return $this->downloadAndSaveImage($picsumUrl, 30);
    }

    private function searchWikimedia(string $query): ?string
    {
        try {
            $response = Http::timeout(8)->get("https://en.wikipedia.org/w/api.php", [
                'action' => 'query', 'generator' => 'search', 'gsrsearch' => $query,
                'gsrlimit' => 1, 'prop' => 'pageimages', 'piprop' => 'original', 'format' => 'json', 'origin' => '*'
            ]);
            $pages = $response->json('query.pages');
            if ($pages) {
                $page = reset($pages);
                $url = $page['original']['source'] ?? null;
                if($url) return $this->downloadAndSaveImage($url, 15);
            }
        } catch (\Exception $e) {
            Log::warning("[NewsAgent] Wikimedia failed: " . $e->getMessage());
        }
        return null;
    }

    private function downloadAndSaveImage(string $url, int $timeoutSeconds): ?string
    {
        try {
            Log::info("[NewsAgent] ⬇️ Downloading: " . Str::limit($url, 60));
            $response = Http::retry(2, 1000)->timeout($timeoutSeconds)->get($url);
            if ($response->failed()) return null;

            $imageContents = $response->body();
            if (strlen($imageContents) < 1000 || str_contains(strtolower(substr($imageContents, 0, 100)), '<html')) {
                return null;
            }

            $filename = 'civic_' . Str::random(20) . '.jpg';
            if (!Storage::disk('public')->exists('post_media')) {
                Storage::disk('public')->makeDirectory('post_media');
            }
            $path = 'post_media/' . $filename;
            Storage::disk('public')->put($path, $imageContents);
            Log::info("[NewsAgent] ✅ Saved image to: $path");
            return $filename;
        } catch (\Exception $e) {
            Log::error("[NewsAgent] ❌ Download failed: " . $e->getMessage());
            return null;
        }
    }
}
