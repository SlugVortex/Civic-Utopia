<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PoliticalInterviewService
{
    protected $azureEndpoint;
    protected $apiKey;
    protected $deployment;
    protected $bingKey;
    protected $bingHost;

    public function __construct()
    {
        $this->azureEndpoint = env('AZURE_AI_PROJECT_ENDPOINT');
        $this->apiKey = env('AZURE_AI_API_KEY');
        $this->deployment = env('AZURE_AI_MODEL_DEPLOYMENT_NAME', 'gpt-4o');
        $this->bingKey = config('services.rapidapi.key');
        $this->bingHost = config('services.rapidapi.host');
    }

    public function processInteraction($history, $userMessage, $lat, $lon, $ageLevel)
    {
        // Gather political intelligence
        $politicalIntelligence = $this->gatherPoliticalIntelligence($lat, $lon);

        // Build system prompt
        $systemPrompt = $this->buildSystemPrompt($ageLevel, $politicalIntelligence);

        // Call GPT-4
        $response = $this->callAzureAI($history, $userMessage, $systemPrompt, 0.7);

        if (!$response) {
            return "I'm having trouble connecting right now. Generally, political parties differ on the role of government in society. Could you rephrase your question?";
        }

        return $response;
    }

    protected function gatherPoliticalIntelligence($lat, $lon)
    {
        if (!$lat || !$lon) {
            return [
                'has_location' => false,
                'location_string' => 'Location not provided',
                'raw_search_results' => null
            ];
        }

        $cacheKey = "pol_intel_v5_" . round($lat, 2) . "_" . round($lon, 2);

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lon) {
            return $this->fetchPoliticalIntelligence($lat, $lon);
        });
    }

    protected function fetchPoliticalIntelligence($lat, $lon)
    {
        // Step 1: Discover location
        $locationInfo = $this->discoverLocation($lat, $lon);

        if (!$locationInfo['success']) {
            Log::warning("[PoliticalAgent] ⚠️ Location discovery failed");
            return [
                'has_location' => false,
                'location_string' => 'Unknown location',
                'raw_search_results' => null
            ];
        }

        $locationString = $locationInfo['location_string'];
        $country = $locationInfo['country'];

        Log::info("[PoliticalAgent] 📍 Discovered: {$locationString}");

        // Step 2: Search for political information
        $searchResults = $this->performPoliticalSearch($country, $locationString);

        if (!$searchResults) {
            Log::warning("[PoliticalAgent] ⚠️ No search results for {$country}");
            return [
                'has_location' => true,
                'location_string' => $locationString,
                'country' => $country,
                'raw_search_results' => null
            ];
        }

        Log::info("[PoliticalAgent] ✅ Got " . substr_count($searchResults, '[Source') . " sources");

        return [
            'has_location' => true,
            'location_string' => $locationString,
            'country' => $country,
            'raw_search_results' => $searchResults
        ];
    }

    protected function discoverLocation($lat, $lon)
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'CivicEducationApp/2.0'])
                ->get("https://nominatim.openstreetmap.org/reverse", [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lon,
                    'zoom' => 10,
                    'addressdetails' => 1,
                    'accept-language' => 'en'
                ]);

            if (!$response->successful()) {
                return ['success' => false];
            }

            $data = $response->json();
            $addr = $data['address'] ?? [];

            $city = $addr['city']
                ?? $addr['town']
                ?? $addr['village']
                ?? $addr['municipality']
                ?? $addr['county']
                ?? '';

            $state = $addr['state']
                ?? $addr['province']
                ?? $addr['region']
                ?? '';

            $country = $addr['country'] ?? '';

            $components = array_filter([$city, $state, $country]);
            $locationString = implode(', ', $components);

            if (empty($locationString)) {
                return ['success' => false];
            }

            Log::info("[PoliticalAgent] 🗺️ Geocoded: City={$city}, State={$state}, Country={$country}");

            return [
                'success' => true,
                'location_string' => $locationString,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'country_code' => strtoupper($addr['country_code'] ?? '')
            ];

        } catch (\Exception $e) {
            Log::error("[PoliticalAgent] Geocoding failed: " . $e->getMessage());
            return ['success' => false];
        }
    }

    protected function performPoliticalSearch($country, $locationString)
    {
        $queries = [
            "{$country} political parties",
            "{$country} government current parties",
            "politics {$country} parties platforms"
        ];

        foreach ($queries as $query) {
            Log::info("[PoliticalAgent] 🔎 Searching: '{$query}'");

            try {
                $response = Http::timeout(15)
                    ->withHeaders([
                        'x-rapidapi-key' => $this->bingKey,
                        'x-rapidapi-host' => $this->bingHost,
                    ])
                    ->get('https://bing-search-apis.p.rapidapi.com/api/rapid/web_search', [
                        'q' => $query,
                        'keyword' => $query,
                        'count' => 10
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // 🔥 FIX: Correct path is data.items, not webPages.value
                    $items = $data['data']['items'] ?? [];

                    if (!empty($items)) {
                        $formatted = $this->formatSearchResults($items);

                        if ($formatted) {
                            Log::info("[PoliticalAgent] ✅ Found " . count($items) . " results");
                            return $formatted;
                        }
                    }
                }

                Log::warning("[PoliticalAgent] Query '{$query}' returned no items");

            } catch (\Exception $e) {
                Log::error("[PoliticalAgent] Search exception: " . $e->getMessage());
            }

            usleep(300000); // 0.3s delay between queries
        }

        return null;
    }

    protected function formatSearchResults($items)
    {
        if (empty($items)) return null;

        $formatted = [];

        foreach ($items as $idx => $item) {
            $title = $item['title'] ?? '';
            $description = $item['description'] ?? '';

            if (!$title && !$description) continue;

            $formatted[] = sprintf(
                "[Source %d] %s\n%s",
                $idx + 1,
                $title,
                $description
            );
        }

        if (empty($formatted)) return null;

        return implode("\n\n", $formatted);
    }

    protected function buildSystemPrompt($ageLevel, $intelligence)
    {
        $style = match(true) {
            $ageLevel < 18 => "simple, relatable language with everyday examples",
            $ageLevel < 30 => "clear, accessible language",
            $ageLevel < 60 => "balanced, informative language",
            default => "detailed, nuanced language"
        };

        $hasLocation = $intelligence['has_location'];
        $locationString = $intelligence['location_string'];
        $searchData = $intelligence['raw_search_results'];

        // No location at all
        if (!$hasLocation) {
            return $this->buildGenericPrompt($style);
        }

        // Has location but no search data
        if (!$searchData) {
            return $this->buildKnowledgeBasedPrompt($style, $locationString, $intelligence['country']);
        }

        // Has location AND search data - best case
        return $this->buildGroundedPrompt($style, $locationString, $searchData);
    }

    protected function buildGenericPrompt($style)
    {
        return <<<EOT
You are a neutral Civic Educator helping someone understand political systems.

COMMUNICATION STYLE: Use {$style}.

YOUR ROLE:
- Help users explore their political values through thoughtful questions
- Explain general concepts about how political parties differ
- Be completely neutral - never advocate for any position
- Keep responses conversational (2-4 sentences)

Ask what political issues matter most to them.
EOT;
    }

    protected function buildKnowledgeBasedPrompt($style, $locationString, $country)
    {
        return <<<EOT
You are a neutral Civic Educator helping someone in {$locationString}.

COMMUNICATION STYLE: Use {$style}.

USER'S LOCATION: {$locationString}

YOUR ROLE:
1. Use your training knowledge about {$country}'s political parties (e.g., JLP and PNP for Jamaica)
2. When users ask about issues, explain how different parties typically approach them
3. Stay completely neutral - never say which is better
4. Keep responses conversational (2-4 sentences)
5. If asked "what is my location", tell them you've detected {$locationString}

Example for Jamaica:
- JLP (Jamaica Labour Party) - center-right, pro-business
- PNP (People's National Party) - center-left, social programs

Start by asking what issues matter most to them.
EOT;
    }

    protected function buildGroundedPrompt($style, $locationString, $searchData)
    {
        return <<<EOT
You are a neutral Civic Educator helping someone in {$locationString}.

COMMUNICATION STYLE: Use {$style}.

USER'S LOCATION: {$locationString}

REAL-TIME SEARCH DATA (just retrieved):
{$searchData}

YOUR TASKS:
1. **READ THE DATA ABOVE** - Identify the political parties and their positions

2. **ANSWER USER QUESTIONS** - When they ask about issues (roads, healthcare, education):
   - Explain how different LOCAL parties approach those issues
   - Reference SPECIFIC parties from the data above
   - Example: "In Jamaica, the JLP tends to focus on [X], while the PNP emphasizes [Y]"

3. **STAY NEUTRAL** - Never say which party is better

4. **BE CONVERSATIONAL** - Keep responses 2-4 sentences, ask follow-ups

5. **LOCATION AWARENESS** - If asked "what is my location", tell them {$locationString}

CRITICAL: Base answers on the search data above. If a party name appears in the data, you can reference it.

Start by asking what issues matter most to them in their community.
EOT;
    }

    protected function callAzureAI($history, $newMessage, $systemPrompt, $temp = 0.7)
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        if (!empty($history)) {
            $messages = array_merge($messages, array_slice($history, -20));
        }

        $messages[] = ['role' => 'user', 'content' => $newMessage];

        $url = rtrim($this->azureEndpoint, '/') . "/openai/deployments/{$this->deployment}/chat/completions?api-version=2024-08-01-preview";

        try {
            $response = Http::retry(3, 1000)
                ->timeout(45)
                ->withHeaders([
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'messages' => $messages,
                    'max_tokens' => 500,
                    'temperature' => $temp,
                    'top_p' => 0.95,
                    'frequency_penalty' => 0.2,
                    'presence_penalty' => 0.2
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? null;

                if (!$content) {
                    Log::error("[PoliticalAgent] Empty response from GPT-4");
                    return null;
                }

                Log::info("[PoliticalAgent] ✅ GPT-4 responded successfully");
                return trim($content);
            }

            Log::error("[PoliticalAgent] GPT-4 Error: " . $response->status());
            return null;

        } catch (\Exception $e) {
            Log::error("[PoliticalAgent] GPT-4 Exception: " . $e->getMessage());
            return null;
        }
    }
}
