<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class SpeechController extends Controller
{
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        $speechKey = config('services.azure.speech.key');
        $speechRegion = config('services.azure.speech.region');

        if (!$speechKey || !$speechRegion || str_contains($speechKey, 'YOUR_SPEECH_KEY_HERE')) {
            Log::error('[SpeechController] Azure AI Speech credentials are not configured.');
            return response()->json(['error' => 'Speech service not configured.'], 500);
        }

        $post = Post::with('media')->findOrFail($validated['post_id']);
        $postContent = $post->content;

        $imageDescriptions = $this->getImageDescriptions($post);
        $imageNarrative = '';
        if (!empty($imageDescriptions)) {
            $imageNarrative = " The post includes " . count($imageDescriptions) . " image" . (count($imageDescriptions) > 1 ? 's' : '') . ". ";
            $narratives = array_map(fn($desc, $i) => "Image " . ($i + 1) . " is described as: " . $desc, $imageDescriptions, array_keys($imageDescriptions));
            $imageNarrative .= implode(". ", $narratives);
        }

        $fullTextToSpeak = $postContent . $imageNarrative;
        $endpoint = "https://{$speechRegion}.tts.speech.microsoft.com/cognitiveservices/v1";
        $ssml = '<speak version="1.0" xmlns="http://www.w3.org/2001/10/synthesis" xml:lang="en-US">';
        $ssml .= '<voice name="en-US-JennyNeural">';
        $ssml .= htmlspecialchars($fullTextToSpeak);
        $ssml .= '</voice></speak>';

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $speechKey,
                'Content-Type' => 'application/ssml+xml',
                'X-Microsoft-OutputFormat' => 'audio-16khz-128kbitrate-mono-mp3',
                'User-Agent' => 'CivicUtopia',
            ])->withBody($ssml, 'application/ssml+xml')->post($endpoint);

            if ($response->failed()) {
                Log::error('[SpeechController] Azure TTS API call failed.', ['status' => $response->status(), 'body' => $response->body()]);
                $response->throw();
            }

            Log::info('[SpeechController] Successfully generated speech audio for Post ID: ' . $post->id);
            return response()->json(['audio' => base64_encode($response->body())]);

        } catch (\Exception $e) {
            Log::error('[SpeechController] Failed to generate speech.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Could not generate audio.'], 500);
        }
    }

    /**
     * Get image descriptions from Azure AI Vision.
     */
    private function getImageDescriptions(Post $post): array
    {
        $visionEndpoint = config('services.azure.vision.endpoint');
        $visionApiKey = config('services.azure.vision.api_key');

        if (!$visionEndpoint || !$visionApiKey || str_contains($visionEndpoint, 'YOUR_VISION_ENDPOINT_HERE')) {
            Log::warning('[SpeechController] Azure AI Vision credentials not configured. Skipping image analysis.');
            return [];
        }

        $images = $post->media()->where('file_type', 'image')->get();
        if ($images->isEmpty()) return [];

        Log::info('[SpeechController] Analyzing ' . $images->count() . ' images for Post ID: ' . $post->id);

        // !FIX: Use the correct API endpoint and parameters
        $requestUrl = rtrim($visionEndpoint, '/') . '/computervision/imageanalysis:analyze?api-version=2023-10-01&features=caption';

        $responses = Http::pool(function (Pool $pool) use ($images, $requestUrl, $visionApiKey) {
            foreach ($images as $image) {
                $baseUrl = rtrim(config('app.url'), '/');
                $imageUrl = $baseUrl . Storage::url($image->path);

                Log::info('[SpeechController] Sending image URL to Azure Vision: ' . $imageUrl);

                $pool->withHeaders([
                    'Content-Type' => 'application/json',
                    'Ocp-Apim-Subscription-Key' => $visionApiKey,
                ])->post($requestUrl, ['url' => $imageUrl]);
            }
        });

        $descriptions = [];
        foreach ($responses as $response) {
            if ($response->successful()) {
                // !FIX: Parse the new JSON structure
                $descriptions[] = $response->json('captionResult.text');
            } else {
                Log::error('[SpeechController] Azure Vision API call failed.', ['status' => $response->status(), 'body' => $response->body()]);
            }
        }

        return array_filter($descriptions);
    }
}

