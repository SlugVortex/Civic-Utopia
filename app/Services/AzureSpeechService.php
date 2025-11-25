<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AzureSpeechService
{
    protected $region;
    protected $apiKey;

    public function __construct()
    {
        $this->region = env('AZURE_AI_SPEECH_REGION', 'eastus');
        $this->apiKey = env('AZURE_AI_SPEECH_KEY');
    }

    public function textToSpeech($text)
    {
        if (!$this->apiKey || !$this->region) {
            Log::error("Azure Speech: Config missing.");
            return null;
        }

        // 1. Validate Text
        if (empty(trim($text))) {
            Log::warning("Azure TTS: Attempted to speak empty text.");
            return null;
        }

        $url = "https://{$this->region}.tts.speech.microsoft.com/cognitiveservices/v1";

        // 2. Sanitize for SSML
        $cleanText = htmlspecialchars(strip_tags($text));

        // 3. Use a standard voice
        $voiceName = 'en-US-GuyNeural';

        $ssml = "<speak version='1.0' xml:lang='en-US'><voice xml:lang='en-US' xml:gender='Male' name='{$voiceName}'>{$cleanText}</voice></speak>";

        try {
            // 4. Request with error handling options
            $response = Http::timeout(15)->retry(2, 500)->withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->apiKey,
                'Content-Type' => 'application/ssml+xml',
                'X-Microsoft-OutputFormat' => 'audio-16khz-128kbitrate-mono-mp3',
                'User-Agent' => 'CivicUtopia'
            ])->send('POST', $url, [
                'body' => $ssml
            ]);

            if ($response->successful()) {
                return $response->body();
            }

            // 5. Log exact Azure error
            Log::error("Azure TTS Error [{$response->status()}]: " . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error("Azure TTS Exception: " . $e->getMessage());
            return null;
        }
    }
}
