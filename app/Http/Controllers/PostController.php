<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    // ... (store method is unchanged) ...
    public function store(StorePostRequest $request)
    {
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

    public function destroy(Post $post)
    {
        // Second layer of security: Ensure the user is authorized to delete this post.
        // We'll use Laravel's Gate for this, which is more robust.
        // For now, a simple check is fine.
        if (request()->user()->id !== $post->user_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $postId = $post->id;
            $post->delete();
            Log::info('[CivicUtopia] Post ID: ' . $postId . ' deleted successfully by User ID: ' . request()->user()->id);

            return redirect()->route('dashboard')->with('status', 'Post deleted successfully.');

        } catch (\Exception $e) {
            Log::error('[CivicUtopia] Failed to delete post.', ['post_id' => $post->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'There was an error deleting the post.');
        }
    }


    /**
     * Get or generate the AI summary for a post.
     */
    public function summarize(Request $request, Post $post)
    {
        // 1. Check if the summary already exists.
        if ($post->summary) {
            Log::info('[CivicUtopia] Returning existing summary for Post ID: ' . $post->id);
            return response()->json(['summary' => $post->summary]);
        }

        Log::info('[CivicUtopia] No existing summary. Generating new one for Post ID: ' . $post->id);

        // 2. If it doesn't exist, generate it via Azure API.
        $apiKey = env('AZURE_AI_API_KEY');
        $apiEndpoint = env('AZURE_AI_PROJECT_ENDPOINT');
        $deploymentName = env('AZURE_AI_MODEL_DEPLOYMENT_NAME');
        $apiVersion = env('AZURE_API_VERSION');
        $requestUrl = "{$apiEndpoint}openai/deployments/{$deploymentName}/chat/completions?api-version={$apiVersion}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ])->post($requestUrl, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant for a civic engagement platform. Your goal is to provide clear, concise, and neutral summaries. Summarize the following user post in a single, easy-to-understand sentence.'],
                    ['role' => 'user', 'content' => $post->content],
                ],
                'max_tokens' => 100,
            ]);

            if ($response->failed()) {
                $response->throw();
            }

            $summary = trim($response->json('choices.0.message.content'));

            // 3. Save the new summary to the database.
            $post->update(['summary' => $summary]);
            Log::info('[CivicUtopia] AI summary generated and SAVED for Post ID: ' . $post->id);

            return response()->json(['summary' => $summary]);

        } catch (\Exception $e) {
            Log::error('[CivicUtopia] AI summary generation failed.', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['summary' => 'We were unable to generate a summary at this time.'], 500);
        }
    }
}
