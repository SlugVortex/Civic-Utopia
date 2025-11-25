<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentSafetyService
{
    protected $endpoint;
    protected $key;

    public function __construct()
    {
        $this->endpoint = env('AZURE_CONTENT_SAFETY_ENDPOINT'); // e.g. https://<resource>.cognitiveservices.azure.com/
        $this->key = env('AZURE_CONTENT_SAFETY_KEY');
    }

    public function analyze($text)
    {
        if (!$this->endpoint || !$this->key) {
            return ['safe' => true]; // Fail open if no keys (dev mode)
        }

        $url = rtrim($this->endpoint, '/') . "/contentsafety/text:analyze?api-version=2023-10-01";

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'text' => $text,
                'categories' => ['Hate', 'SelfHarm', 'Sexual', 'Violence'],
                'outputType' => 'FourSeverityLevels',
            ]);

            if ($response->failed()) {
                Log::error('Azure Content Safety Failed: ' . $response->body());
                return ['safe' => true];
            }

            $results = $response->json('categoriesAnalysis');

            foreach ($results as $category) {
                // Severity 0=Safe, 2=Medium, 4=High (Scale varies by version, usually 0-6 or 0-4)
                // We flag anything above severity 0 to be strict, or 2 for lenient.
                if ($category['severity'] >= 2) {
                    return [
                        'safe' => false,
                        'reason' => $category['category'] . " (Severity: " . $category['severity'] . ")"
                    ];
                }
            }

            return ['safe' => true];

        } catch (\Exception $e) {
            Log::error('Content Safety Exception: ' . $e->getMessage());
            return ['safe' => true];
        }
    }
}
