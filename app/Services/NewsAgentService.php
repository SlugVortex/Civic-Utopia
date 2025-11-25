<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsAgentService
{
    /**
     * FIXED: Now truly uses coordinates to get local news anywhere in the world
     */
    public function searchNews(float $lat, float $lon): array
    {
        // Get location details from coordinates
        $locationInfo = $this->getDetailedLocation($lat, $lon);

        // Build smart query based on detected location
        $query = $this->buildLocalQuery($locationInfo);

        Log::info("[NewsAgent] 📡 Searching local news: '$query' (Lat: $lat, Lon: $lon)");

        try {
            $response = Http::retry(3, 1000)->timeout(20)->withHeaders([
                'x-rapidapi-key' => config('services.rapidapi.key'),
                'x-rapidapi-host' => config('services.rapidapi.host'),
            ])->get('https://bing-search-apis.p.rapidapi.com/api/rapid/news_search', [
                'q' => $query,
                'keyword' => $query,
                'count' => 20,
                'size' => 20,
                'freshness' => 'Day',
                'safeSearch' => 'Moderate',
                'mkt' => $this->getMarketCode($locationInfo['country']),
                'offset' => rand(0, 3)
            ]);

            if ($response->failed()) return [];

            $data = $response->json();
            $items = [];
            if (isset($data['data']['news'])) $items = $data['data']['news'];
            elseif (isset($data['data']) && is_array($data['data'])) $items = $data['data'];
            elseif (isset($data['value'])) $items = $data['value'];

            // Filter for local relevance
            $items = $this->filterLocalNews($items, $locationInfo);

            shuffle($items);

            Log::info("[NewsAgent] Found " . count($items) . " localized news items");
            return array_values(array_slice($items, 0, 15));

        } catch (\Exception $e) {
            Log::error("[NewsAgent] News Search Error: " . $e->getMessage());
            return [];
        }
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
            'city' => null,
            'state' => null,
            'country' => null,
            'country_code' => null,
            'display_name' => null
        ];
    }

    /**
     * Build intelligent search query based on location
     */
    private function buildLocalQuery(array $locationInfo): string
    {
        $queryVariations = [
            'breaking news',
            'local news',
            'community updates',
            'latest headlines',
            'news today'
        ];

        $queryType = $queryVariations[array_rand($queryVariations)];

        // Build from most specific to least specific
        if ($locationInfo['city']) {
            return "{$locationInfo['city']} {$queryType}";
        } elseif ($locationInfo['state']) {
            return "{$locationInfo['state']} {$queryType}";
        } elseif ($locationInfo['country']) {
            return "{$locationInfo['country']} {$queryType}";
        }

        return "local {$queryType}";
    }

    /**
     * Filter news items for local relevance
     */
    private function filterLocalNews(array $items, array $locationInfo): array
    {
        $keywords = array_filter([
            $locationInfo['city'],
            $locationInfo['state'],
            $locationInfo['country']
        ]);

        if (empty($keywords)) {
            return $items; // No filtering if we don't know location
        }

        return array_filter($items, function($item) use ($keywords) {
            $title = strtolower($item['name'] ?? $item['title'] ?? '');
            $description = strtolower($item['description'] ?? '');
            $content = $title . ' ' . $description;

            foreach ($keywords as $keyword) {
                if (stripos($content, $keyword) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Get appropriate market code for Bing News API
     */
    private function getMarketCode(?string $country): string
    {
        $marketCodes = [
            'Jamaica' => 'en-US', // Caribbean uses US market
            'United States' => 'en-US',
            'United Kingdom' => 'en-GB',
            'Canada' => 'en-CA',
            'Australia' => 'en-AU',
            'India' => 'en-IN',
            'Nigeria' => 'en-US',
            'South Africa' => 'en-ZA',
            'Kenya' => 'en-US',
        ];

        return $marketCodes[$country] ?? 'en-US';
    }

    public function analyzeNewsContent(string $title): array
    {
        $endpoint = env('AZURE_AI_PROJECT_ENDPOINT') . "openai/deployments/" . env('AZURE_AI_MODEL_DEPLOYMENT_NAME', 'gpt-4.1-nano') . "/chat/completions?api-version=" . env('AZURE_API_VERSION', '2024-05-01-preview');
        $apiKey = env('AZURE_AI_API_KEY');

        try {
            $response = Http::timeout(10)->withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json'
            ])->post($endpoint, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a news assistant. Output JSON. "summary": 1 engaging sentence. "keywords": 2-3 simple nouns for finding relevant images (e.g. "Storm Damage", "City Council", "Hospital").'],
                    ['role' => 'user', 'content' => $title]
                ],
                'max_tokens' => 100,
                'temperature' => 0.5,
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                $json = json_decode($content, true);

                $summary = $json['summary'] ?? $title;
                if (is_array($summary)) $summary = implode(' ', $summary);

                $keywords = $json['keywords'] ?? $title;
                if (is_array($keywords)) $keywords = implode(' ', $keywords);

                return [
                    'summary' => $summary,
                    'keywords' => Str::limit($keywords, 50, '')
                ];
            }
        } catch (\Exception $e) {
            Log::warning("[NewsAgent] AI Analysis failed: " . $e->getMessage());
        }

        return [
            'summary' => "Update: $title",
            'keywords' => Str::limit($title, 25, '')
        ];
    }

    public function generateImage(string $prompt): ?string
    {
        $endpoint = config('services.azure_dalle.endpoint');
        $apiKey = config('services.azure_dalle.key');

        if (!$endpoint || !$apiKey) {
            Log::info("[NewsAgent] 🔇 DALL-E not configured, skipping...");
            return null;
        }

        $safePrompt = "News photo: " . Str::limit($prompt, 100);

        try {
            Log::info("[NewsAgent] 🎨 Attempting DALL-E generation...");

            $response = Http::timeout(8)->withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'prompt' => $safePrompt,
                'size' => '1024x1024',
                'n' => 1,
                'style' => 'natural',
                'quality' => 'standard'
            ]);

            if ($response->failed()) {
                Log::warning("[NewsAgent] DALL-E failed with status: " . $response->status());
                return null;
            }

            $data = $response->json();
            $imageUrl = $data['data'][0]['url'] ?? null;

            if ($imageUrl) {
                Log::info("[NewsAgent] ✅ DALL-E generated image");
                return $this->downloadAndSaveImage($imageUrl, 20);
            }
            return null;

        } catch (\Exception $e) {
            Log::warning("[NewsAgent] DALL-E exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * IMPROVED: Try Wikimedia first for more relevant news images
     */
    public function findCivicImage(string $smartKeywords, ?array $newsItem = null): ?string
    {
        // STRATEGY 1: Original news thumbnail (most relevant!)
        if ($newsItem && isset($newsItem['image']['thumbnail']['contentUrl'])) {
            $url = $newsItem['image']['thumbnail']['contentUrl'];
            Log::info("[NewsAgent] 📸 Trying original news thumbnail...");
            $result = $this->downloadAndSaveImage($url, 30);
            if ($result) return $result;
        }

        Log::info("[NewsAgent] 🔍 Searching free sources for: '$smartKeywords'");

        // STRATEGY 2: Wikimedia Commons (highly relevant, royalty-free)
        $wikiImage = $this->searchWikimedia($smartKeywords);
        if ($wikiImage) {
            Log::info("[NewsAgent] 🏛️ Trying Wikimedia...");
            $result = $this->downloadAndSaveImage($wikiImage, 30);
            if ($result) return $result;
        }

        // STRATEGY 3: Picsum (reliable placeholder - always works)
        Log::info("[NewsAgent] 🎲 Using Picsum placeholder...");
        $picsumUrl = "https://picsum.photos/800/600";
        $result = $this->downloadAndSaveImage($picsumUrl, 30);
        if ($result) return $result;

        Log::warning("[NewsAgent] ⚠️ All image sources failed");
        return null;
    }

    private function searchWikimedia(string $query): ?string
    {
        try {
            $url = "https://en.wikipedia.org/w/api.php";
            $response = Http::timeout(8)->get($url, [
                'action' => 'query',
                'generator' => 'search',
                'gsrsearch' => $query,
                'gsrlimit' => 1,
                'prop' => 'pageimages',
                'piprop' => 'original',
                'format' => 'json',
                'origin' => '*'
            ]);

            $data = $response->json();

            if (isset($data['query']['pages'])) {
                $pages = $data['query']['pages'];
                $firstPage = reset($pages);
                return $firstPage['original']['source'] ?? null;
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

            $response = Http::retry(3, 2000)
                ->timeout($timeoutSeconds)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ])
                ->get($url);

            if ($response->failed() || $response->status() >= 500) {
                Log::error("[NewsAgent] ❌ HTTP {$response->status()}");
                return null;
            }

            $imageContents = $response->body();

            if (strlen($imageContents) < 500) {
                Log::error("[NewsAgent] ❌ File too small");
                return null;
            }

            if (str_contains(strtolower($imageContents), '<!doctype html>') ||
                str_contains(strtolower($imageContents), '<html')) {
                Log::error("[NewsAgent] ❌ Received HTML instead of image");
                return null;
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageContents);

            if (!str_starts_with($mimeType, 'image/')) {
                Log::error("[NewsAgent] ❌ Not a valid image");
                return null;
            }

            $extension = match($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                default => 'jpg'
            };

            $filename = 'civic_' . Str::random(20) . '.' . $extension;

            if (!Storage::disk('public')->exists('post_media')) {
                Storage::disk('public')->makeDirectory('post_media');
            }

            $path = 'post_media/' . $filename;
            Storage::disk('public')->put($path, $imageContents);

            $sizeKB = round(strlen($imageContents)/1024, 2);
            Log::info("[NewsAgent] ✅ Saved {$sizeKB}KB to: $path");
            return $filename;

        } catch (\Exception $e) {
            Log::error("[NewsAgent] ❌ Download failed: " . $e->getMessage());
            return null;
        }
    }
}
