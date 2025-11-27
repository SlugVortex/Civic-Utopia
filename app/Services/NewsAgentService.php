<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsAgentService
{
    /**
     * Search for hyper-local news using Bing (RapidAPI)
     * Uses 'keyword', 'size', 'cc' parameters based on your successful curl test.
     */
    public function searchNews(float $lat, float $lon): array
    {
        // 1. Get location details (with fallback)
        $locationInfo = $this->getDetailedLocation($lat, $lon);

        // 2. Build smart query
        $query = $this->buildLocalQuery($locationInfo);

        Log::info("[NewsAgent] 📡 Searching local news: '$query' (Lat: $lat, Lon: $lon)");

        // 3. Get API Key (Prioritize Gary's key if config is missing)
        $apiKey = config('services.rapidapi.key') ?? env('RAPIDAPI_KEY_GARY');
        $apiHost = config('services.rapidapi.host') ?? env('RAPIDAPI_HOST_GARY') ?? 'bing-search-apis.p.rapidapi.com';

        if (empty($apiKey)) {
            Log::error("[NewsAgent] ❌ API Key missing. Please check .env");
            return [];
        }

        try {
            // 4. Call Bing Search API
            $response = Http::retry(3, 1000)->timeout(25)->withHeaders([
                'x-rapidapi-key' => $apiKey,
                'x-rapidapi-host' => $apiHost,
            ])->get("https://{$apiHost}/api/rapid/news_search", [
                'keyword' => $query,   // Correct param
                'size'    => 15,       // Correct param
                'cc'      => 'US',     // Correct param (US/JM market)
                'page'    => 0,
                'safeSearch' => 'Moderate'
            ]);

            if ($response->failed()) {
                Log::error("[NewsAgent] API Error: " . $response->body());
                return [];
            }

            $data = $response->json();

            // Handle response structure flexibility
            $items = $data['data'] ?? $data['value'] ?? [];

            // Filter results to ensure relevance
            $items = $this->filterLocalNews($items, $locationInfo);

            shuffle($items);

            Log::info("[NewsAgent] Found " . count($items) . " localized news items");

            // Return top 10
            return array_values(array_slice($items, 0, 10));

        } catch (\Exception $e) {
            Log::error("[NewsAgent] News Search Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Reverse Geocoding (Coords -> City/Country)
     */
    private function getDetailedLocation(float $lat, float $lon): array
    {
        try {
            // Timeout reduced to 5s to prevent job hanging
            $response = Http::timeout(5)
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
                    'country' => $address['country'] ?? 'Jamaica', // Default to Jamaica
                    'display_name' => $data['display_name'] ?? null
                ];

                Log::info("[NewsAgent] 📍 Detected Location: " . implode(', ', array_filter($locationInfo)));
                return $locationInfo;
            }
        } catch (\Exception $e) {
            Log::warning("[NewsAgent] Geocoding timed out or failed. Defaulting to Jamaica.");
        }

        // Fallback if geocoding fails
        return [
            'city' => null,
            'state' => null,
            'country' => 'Jamaica',
            'display_name' => 'Jamaica'
        ];
    }

    /**
     * Construct Search Query
     */
    private function buildLocalQuery(array $locationInfo): string
    {
        // Prefer specific city, fallback to country
        $place = $locationInfo['city'] ?? $locationInfo['state'] ?? $locationInfo['country'] ?? 'Jamaica';

        $topics = ['community news', 'local events', 'developments', 'headlines'];
        $topic = $topics[array_rand($topics)];

        return "$place $topic";
    }

    /**
     * Filter results to ensure they match the location
     */
    private function filterLocalNews(array $items, array $locationInfo): array
    {
        // If we defaulted to Jamaica, don't filter too strictly
        $keywords = array_filter([
            $locationInfo['city'],
            $locationInfo['state'],
            $locationInfo['country']
        ]);

        if (empty($keywords)) return $items;

        return array_filter($items, function($item) use ($keywords) {
            $text = strtolower(($item['title'] ?? '') . ' ' . ($item['description'] ?? ''));

            foreach ($keywords as $keyword) {
                if ($keyword && str_contains($text, strtolower($keyword))) {
                    return true;
                }
            }
            // Return true by default if strict filtering returns nothing
            return true;
        });
    }

    /**
     * Generate AI Summary & Visual Keywords using Azure OpenAI
     */
    public function analyzeNewsContent(string $title): array
    {
        $endpoint = config('services.azure.openai.endpoint');
        $apiKey = config('services.azure.openai.api_key');
        $deployment = config('services.azure.openai.deployment');
        $apiVersion = config('services.azure.openai.api_version');

        if(!$endpoint || !$apiKey) return ['summary' => $title, 'keywords' => $title];

        $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

        try {
            $response = Http::timeout(15)->withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json'
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a news assistant. Output valid JSON with keys: "summary" (1 sentence) and "keywords" (3 visual nouns for DALL-E).'],
                    ['role' => 'user', 'content' => "Analyze headline: " . $title]
                ],
                'temperature' => 0.5,
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                $content = json_decode($response->json('choices.0.message.content'), true);
                return [
                    'summary' => $content['summary'] ?? $title,
                    'keywords' => $content['keywords'] ?? $title
                ];
            }
        } catch (\Exception $e) {
            Log::warning("[NewsAgent] AI Analysis failed: " . $e->getMessage());
        }

        return ['summary' => $title, 'keywords' => $title];
    }

    /**
     * Generate Image using DALL-E 3
     */
    public function generateImage(string $prompt): ?string
    {
        $endpoint = config('services.azure_dalle.endpoint');
        $apiKey = config('services.azure_dalle.key');

        if (!$endpoint || !$apiKey) return null;

        try {
            Log::info("[NewsAgent] 🎨 Generating DALL-E image for: " . Str::limit($prompt, 50));

            // Extended timeout for DALL-E (45s)
            $response = Http::timeout(45)->withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'prompt' => "News photo style, high quality: " . Str::limit($prompt, 300),
                'size' => '1024x1024',
                'n' => 1,
                'style' => 'natural',
                'quality' => 'standard'
            ]);

            if ($response->failed()) {
                Log::warning("[NewsAgent] DALL-E Failed: " . $response->status());
                return null;
            }

            $imageUrl = $response->json('data.0.url');
            if ($imageUrl) {
                return $this->downloadAndSaveImage($imageUrl, 45);
            }

        } catch (\Exception $e) {
            Log::warning("[NewsAgent] DALL-E Exception: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Find Image (DALL-E -> Thumbnail -> Wikimedia -> Picsum)
     */
    public function findCivicImage(string $smartKeywords, ?array $newsItem = null): ?string
    {
        // 1. Try DALL-E
        $img = $this->generateImage($smartKeywords);
        if ($img) return $img;

        // 2. Try Original News Image
        if ($newsItem && isset($newsItem['image']['thumbnail']['contentUrl'])) {
            $img = $this->downloadAndSaveImage($newsItem['image']['thumbnail']['contentUrl'], 15);
            if ($img) return $img;
        }

        // 3. Try Wikimedia
        $img = $this->searchWikimedia($smartKeywords);
        if ($img) return $img;

        // 4. Fallback Picsum
        $picsumUrl = "https://picsum.photos/800/600?random=" . rand(1, 999);
        return $this->downloadAndSaveImage($picsumUrl, 15);
    }

    private function searchWikimedia(string $query): ?string
    {
        try {
            $response = Http::timeout(5)->get("https://en.wikipedia.org/w/api.php", [
                'action' => 'query', 'generator' => 'search', 'gsrsearch' => $query,
                'gsrlimit' => 1, 'prop' => 'pageimages', 'piprop' => 'original', 'format' => 'json', 'origin' => '*'
            ]);

            $pages = $response->json('query.pages');
            if ($pages) {
                $page = reset($pages);
                $url = $page['original']['source'] ?? null;
                if ($url) return $this->downloadAndSaveImage($url, 15);
            }
        } catch (\Exception $e) {}
        return null;
    }

    private function downloadAndSaveImage(string $url, int $timeout): ?string
    {
        try {
            $content = Http::timeout($timeout)->get($url)->body();
            if (strlen($content) < 1000) return null;

            $filename = 'news_' . Str::random(20) . '.jpg';
            Storage::disk('public')->put('post_media/' . $filename, $content);

            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }
}
