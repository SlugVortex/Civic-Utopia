<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsAgentService
{
    /**
     * Search News using Bing (RapidAPI)
     */
    public function searchNews(float $lat, float $lon): array
    {
        // 1. Get Location
        $locationInfo = $this->getDetailedLocation($lat, $lon);
        $query = $this->buildLocalQuery($locationInfo);

        Log::info("[NewsAgent] 📡 Searching: '$query' (Lat: $lat, Lon: $lon)");

        // 2. GET KEY (Priority: Config -> Env -> Hardcoded Debug)
        // This ensures we get the "Gary" key you showed me.
        $apiKey = config('services.rapidapi.key')
               ?? env('RAPIDAPI_KEY_GARY')
               ?? '135634c5bcmsh9ebfb79c20e98f8p15bdf9jsn7fb31eef00ef';

        $apiHost = config('services.rapidapi.host')
                ?? env('RAPIDAPI_HOST_GARY')
                ?? 'bing-search-apis.p.rapidapi.com';

        try {
            $response = Http::withHeaders([
                'x-rapidapi-key' => $apiKey,
                'x-rapidapi-host' => $apiHost,
            ])->get('https://bing-search-apis.p.rapidapi.com/api/rapid/news_search', [
                'q' => $query,
                'count' => 15,
                'freshness' => 'Day',
                'mkt' => 'en-US',
                'safeSearch' => 'Moderate'
            ]);

            if ($response->failed()) {
                Log::error("[NewsAgent] API Error (Status " . $response->status() . "): " . $response->body());
                return [];
            }

            $data = $response->json();
            $items = $data['value'] ?? $data['data'] ?? [];

            // Fallback for different API response structures
            if(empty($items) && isset($data['data']['news'])) {
                $items = $data['data']['news'];
            }

            return array_values(array_slice($items, 0, 10));

        } catch (\Exception $e) {
            Log::error("[NewsAgent] Critical Error: " . $e->getMessage());
            return [];
        }
    }

    // --- KEEP YOUR EXISTING HELPER METHODS BELOW (getDetailedLocation, etc) ---
    // I am just ensuring the searchNews method uses the correct key.

    private function getDetailedLocation(float $lat, float $lon): array
    {
        try {
            $response = Http::timeout(3)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat, 'lon' => $lon, 'format' => 'json', 'accept-language' => 'en'
            ]);

            if ($response->successful()) {
                $addr = $response->json('address') ?? [];
                return [
                    'city' => $addr['city'] ?? $addr['town'] ?? null,
                    'country' => $addr['country'] ?? 'Jamaica'
                ];
            }
        } catch (\Exception $e) {
            Log::warning("[NewsAgent] Geocoding timed out. Defaulting to Jamaica.");
        }
        return ['city' => null, 'country' => 'Jamaica'];
    }

    private function buildLocalQuery(array $locationInfo): string
    {
        $place = $locationInfo['city'] ?? $locationInfo['country'] ?? 'Jamaica';
        return "$place local news headlines";
    }

    // Dummy implementation to prevent errors if your job calls these
    public function analyzeNewsContent($t) { return ['summary'=>$t,'keywords'=>'news']; }
    public function findCivicImage($k, $n) { return null; }
    public function generateImage($p) { return null; }
}
