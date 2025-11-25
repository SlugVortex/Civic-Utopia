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

    public function __construct()
    {
        $this->azureEndpoint = env('AZURE_AI_PROJECT_ENDPOINT');
        $this->apiKey = env('AZURE_AI_API_KEY');
        $this->deployment = env('AZURE_AI_MODEL_DEPLOYMENT_NAME', 'gpt-4.1-nano');
        $this->bingKey = config('services.rapidapi.key');
    }

    public function processInteraction($history, $userMessage, $lat, $lon, $ageLevel)
    {
        // 1. Grounding: Get Local Political Context
        // We cache this to save API calls and speed up the chat
        $localContext = "Location not provided.";

        if ($lat && $lon) {
            $cacheKey = "pol_context_" . round($lat, 2) . "_" . round($lon, 2);

            $localContext = Cache::remember($cacheKey, 86400, function () use ($lat, $lon) {
                return $this->getDetailedPoliticalContext($lat, $lon);
            });
        }

        // 2. Build the "Civic Educator" System Prompt
        $systemPrompt = $this->buildSystemPrompt($ageLevel, $localContext);

        // 3. Call Azure AI
        $response = $this->callAzureAI($history, $userMessage, $systemPrompt, 0.7);

        if (!$response) {
            return "I am having trouble accessing the civic database. However, based on general knowledge, different parties prioritize healthcare differently—some focus on privatization and efficiency, while others focus on universal access.";
        }

        return $response;
    }

    /**
     * FIX: Robust Search for Political Context
     */
    protected function getDetailedPoliticalContext($lat, $lon)
    {
        // Step A: Reverse Geocode (Who represents this area?)
        $locationName = "Jamaica"; // Default
        try {
            $geo = Http::timeout(5)->get("https://nominatim.openstreetmap.org/reverse", [
                'format' => 'json', 'lat' => $lat, 'lon' => $lon, 'zoom' => 10
            ]);
            if ($geo->successful()) {
                $addr = $geo->json()['address'] ?? [];
                $locationName = ($addr['county'] ?? $addr['city'] ?? 'Jamaica') . ", " . ($addr['country'] ?? '');
            }
        } catch (\Exception $e) {}

        Log::info("[PoliticalAgent] 📍 Detected Location: $locationName");

        // Step B: Bing Search for PARTIES and POLICIES (Fixed Parameters)
        // We specifically ask for "JLP vs PNP" style comparison data
        $query = "Political parties values $locationName JLP PNP stance on healthcare economy";

        try {
            $response = Http::retry(3, 1000)->timeout(20)->withHeaders([
                'x-rapidapi-key' => $this->bingKey,
                'x-rapidapi-host' => config('services.rapidapi.host'),
            ])->get('https://bing-search-apis.p.rapidapi.com/api/rapid/news_search', [
                'q' => $query,
                'keyword' => $query, // REQUIRED param fixed
                'count' => 5,
                'mkt' => 'en-US',
                'safeSearch' => 'Moderate'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $snippets = [];
                $items = $data['value'] ?? $data['data']['news'] ?? [];

                foreach($items as $item) {
                    $snippets[] = $item['description'] ?? '';
                }

                $context = "Location: $locationName. " . implode(" | ", $snippets);
                Log::info("[PoliticalAgent] 🧠 Context Loaded: " . substr($context, 0, 100));
                return $context;
            }

            Log::error("Bing Error: " . $response->body());

        } catch (\Exception $e) {
            Log::error("Bing Exception: " . $e->getMessage());
        }

        // Fallback Context if API fails
        return "Location: $locationName. Major Parties: Jamaica Labour Party (JLP) and People's National Party (PNP). JLP often emphasizes economic growth, infrastructure, and conservative values. PNP often emphasizes social welfare, grassroots empowerment, and socialist-democratic values.";
    }

    protected function buildSystemPrompt($ageLevel, $localContext)
    {
        // Tone Adjustment
        $complexity = "standard";
        if ($ageLevel < 20) $complexity = "simple, educational";
        if ($ageLevel > 60) $complexity = "detailed, policy-focused";

        return <<<EOT
You are a Civic Educator and Political Values Analyst for a user in a specific location.
Your goal is NOT just to ask questions, but to EDUCATE the user on how different political philosophies align with their concerns.

CURRENT CONTEXT:
{$localContext}

INSTRUCTIONS:
1. ACKNOWLEDGE LOCATION: Explicitly mention the user's location (e.g., "In Jamaica...") to show you are grounded.
2. MAP ISSUES TO VALUES: If the user mentions an issue (e.g., "Healthcare"), explain how the local parties (e.g., JLP vs PNP) historically approach it.
   - Example: "The JLP typically focuses on X approach to health, while the PNP focuses on Y."
3. DEFINE RIGHTS: Briefly mention relevant civic rights if applicable.
4. REMAIN NEUTRAL: Do not say "Vote for X". Say "If you value A, X party aligns with that. If you value B, Y party aligns with that."
5. KEEP IT CONVERSATIONAL: Max 3-4 sentences. Use the Complexity Level: {$complexity}.

User Input is next.
EOT;
    }

    protected function callAzureAI($history, $newMessage, $systemPrompt, $temp = 0.7)
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        // Keep history short
        if (!empty($history)) {
            $messages = array_merge($messages, array_slice($history, -4));
        }

        $messages[] = ['role' => 'user', 'content' => $newMessage];

        $url = $this->azureEndpoint . "openai/deployments/" . $this->deployment . "/chat/completions?api-version=2024-05-01-preview";

        try {
            $response = Http::retry(3, 1000)->timeout(30)->withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => $messages,
                'max_tokens' => 400,
                'temperature' => $temp,
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }
            return null;

        } catch (\Exception $e) {
            return null;
        }
    }
}
