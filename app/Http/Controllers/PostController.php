<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // <-- 1. Import Laravel's HTTP client
use Illuminate\Support\Facades\Log;
// We no longer need the OpenAI facade

class PostController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        // ... (this method remains the same)
        try {
            $post = $request->user()->posts()->create($request->validated());
            $post->load('user');
            Log::info('[CivicUtopia] New post created by User ID: ' . $request->user()->id . '. Firing PostCreated event.');
            PostCreated::dispatch($post);
            return redirect()->back()->with('status', 'Post created successfully!');
        } catch (\Exception $e) {
            Log::error('[CivicUtopia] Failed to create post.', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'There was an error creating your post.');
        }
    }

    /**
     * Summarize the post content using AI.
     */
    public function summarize(Request $request, Post $post)
    {
        Log::info('[CivicUtopia] Received summary request for Post ID: ' . $post->id);

        // 2. Load all necessary credentials from .env
        $apiKey = env('AZURE_AI_API_KEY');
        $apiEndpoint = env('AZURE_AI_PROJECT_ENDPOINT');
        $deploymentName = env('AZURE_AI_MODEL_DEPLOYMENT_NAME');
        $apiVersion = env('AZURE_API_VERSION');

        // Construct the full, correct URL, just like in the curl command
        $requestUrl = "{$apiEndpoint}openai/deployments/{$deploymentName}/chat/completions?api-version={$apiVersion}";

        try {
            // 3. Use Laravel's HTTP Client to make the request manually
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ])->post($requestUrl, [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant for a civic engagement platform. Your goal is to provide clear, concise, and neutral summaries. Summarize the following user post in a single, easy-to-understand sentence.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $post->content
                    ],
                ],
                'max_tokens' => 100, // Good practice to set a limit
            ]);

            // Check if the request was successful
            if ($response->failed()) {
                // Throw an exception to be caught by our catch block, and log the response body
                $response->throw();
            }

            // 4. Extract the summary from the JSON response
            $summary = $response->json('choices.0.message.content');

            Log::info('[CivicUtopia] AI summary generated successfully for Post ID: ' . $post->id);
            return response()->json(['summary' => trim($summary)]);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Catch specific HTTP errors and log detailed info
            Log::error('[CivicUtopia] AI summary generation failed due to an HTTP error.', [
                'post_id' => $post->id,
                'status_code' => $e->response->status(),
                'response_body' => $e->response->body(),
                'request_url' => $requestUrl,
            ]);
            return response()->json(['summary' => 'The AI service returned an error. Please try again later.'], 500);

        } catch (\Exception $e) {
            // Catch any other general errors
            Log::error('[CivicUtopia] AI summary generation failed.', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['summary' => 'We were unable to generate a summary at this time. Please try again later.'], 500);
        }
    }
}
