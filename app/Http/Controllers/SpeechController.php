<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class SpeechController extends Controller
{
    public function generate(Request $request)
    {
        // Allow either post_id (for posts) or text (for ballots)
        $validated = $request->validate([
            'post_id' => 'nullable|exists:posts,id',
            'text'    => 'nullable|string|max:5000',
        ]);

        if (empty($validated['post_id']) && empty($validated['text'])) {
            return response()->json(['error' => 'Either post_id or text is required.'], 422);
        }

        $speechKey = config('services.azure.speech.key');
        $speechRegion = config('services.azure.speech.region');

        if (!$speechKey || !$speechRegion || str_contains($speechKey, 'YOUR_SPEECH_KEY_HERE')) {
            Log::error('[SpeechController] Azure AI Speech credentials are not configured.');
            return response()->json(['error' => 'Speech service not configured.'], 500);
        }

        // Determine the text to speak
        $textToSpeak = '';

        if (!empty($validated['post_id'])) {
            // Logic for Posts (Existing Feature)
            $post = Post::with('media')->findOrFail($validated['post_id']);
            $postContent = $post->content;

            $imageDescriptions = $this->getImageDescriptions($post);
            $imageNarrative = '';
            if (!empty($imageDescriptions)) {
                $imageNarrative = " The post also has an image. Here is a detailed description of it: ";
                $imageNarrative .= implode('. ', $imageDescriptions);
            }
            $textToSpeak = $postContent . $imageNarrative;
        } else {
            // Logic for Ballots (New Feature)
            $textToSpeak = $validated['text'];
        }

        // Construct SSML for Azure
        $endpoint = "https://{$speechRegion}.tts.speech.microsoft.com/cognitiveservices/v1";
        $ssml = '<speak version="1.0" xmlns="http://www.w3.org/2001/10/synthesis" xml:lang="en-US">';
        // Using JennyNeural for clarity, or you can try en-GB-RyanNeural for a different accent if preferred
        $ssml .= '<voice name="en-US-JennyNeural">';
        $ssml .= htmlspecialchars($textToSpeak);
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

            Log::info('[SpeechController] Successfully generated speech audio.');

            // Return base64 encoded audio
            return response()->json(['audio' => base64_encode($response->body())]);

        } catch (\Exception $e) {
            Log::error('[SpeechController] Failed to generate speech.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Could not generate audio.'], 500);
        }
    }

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

        $requestUrl = rtrim($visionEndpoint, '/') . '/computervision/imageanalysis:analyze?api-version=2023-10-01&features=denseCaptions';
        $descriptions = [];

        foreach ($images as $image) {
            try {
                if(!Storage::disk('public')->exists($image->path)) {
                    continue;
                }

                $fileContents = Storage::disk('public')->get($image->path);
                $mimeType = Storage::disk('public')->mimeType($image->path);

                if (!$fileContents) {
                    continue;
                }

                $response = Http::withHeaders([
                    'Ocp-Apim-Subscription-Key' => $visionApiKey,
                    'Content-Type' => 'application/octet-stream',
                ])->withBody($fileContents, $mimeType)->post($requestUrl);

                if ($response->successful()) {
                    $denseCaptions = $response->json('denseCaptionsResult.values');
                    if (is_array($denseCaptions)) {
                        foreach ($denseCaptions as $caption) {
                            $descriptions[] = $caption['text'];
                        }
                    }
                }
            } catch (\Exception $e) {
                 Log::error('[SpeechController] Exception during Azure Vision API call.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $uniqueDescriptions = array_unique($descriptions);
        return array_slice($uniqueDescriptions, 0, 5);
    }
}
