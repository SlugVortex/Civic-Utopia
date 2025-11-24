<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class PostController extends Controller
{
    // ... store(), destroy(), toggleLike(), toggleBookmark() methods remain unchanged ...

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,wmv|max:20480',
            'topic_id' => 'nullable|exists:topics,id',
        ]);

        try {
            $post = $request->user()->posts()->create(['content' => $validated['content']]);

            if (!empty($validated['topic_id'])) {
                $post->topics()->attach($validated['topic_id']);
                Log::info('[PostController] Attached topic ID ' . $validated['topic_id'] . ' to Post ID: ' . $post->id);
            }

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $path = $file->store('post_media', 'public');
                    $mimeType = $file->getMimeType();
                    $fileType = Str::startsWith($mimeType, 'image/') ? 'image' : (Str::startsWith($mimeType, 'video/') ? 'video' : 'other');

                    $post->media()->create([
                        'user_id' => $request->user()->id,
                        'disk' => 'public',
                        'path' => $path,
                        'file_type' => $fileType,
                        'mime_type' => $mimeType,
                    ]);
                }
            }

            $post->load('user', 'media', 'topics');
            Log::info('[CivicUtopia] New post created successfully.', ['post_id' => $post->id]);

            Log::warning('[CivicUtopia] Broadcasting is temporarily disabled.');


            if ($request->wantsJson()) {
                return response()->json($post, 201);
            }
            return redirect()->back()->with('status', 'Post created successfully!');

        } catch (\Exception $e) {
            Log::error('[CivicUtopia] Failed to create post.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'There was an error creating your post.'], 500);
            }
            return redirect()->back()->with('error', 'There was an error creating your post.');
        }
    }


    /**
     * Get or generate the AI summary for a post.
     */
    public function summarize(Request $request, Post $post)
    {
        if ($post->summary) {
            Log::info('[CivicUtopia] Returning existing summary for Post ID: ' . $post->id);
            return response()->json(['summary' => $post->summary]);
        }

        Log::info('[CivicUtopia] No existing summary. Generating new one for Post ID: ' . $post->id);

        $imageDescriptions = $this->getImageDescriptions($post);
        $imageContext = '';
        if (!empty($imageDescriptions)) {
            $imageContext = "\n\nThe user also uploaded images. Here are descriptions of them:\n- " . implode("\n- ", $imageDescriptions);
        }

        $requestUrl = config('services.azure.openai.endpoint') . 'openai/deployments/' . config('services.azure.openai.deployment') . '/chat/completions?api-version=' . config('services.azure.openai.api_version');

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => config('services.azure.openai.api_key'),
            ])->post($requestUrl, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant for a civic engagement platform. Your goal is to provide clear, concise, and neutral summaries. Summarize the following user post in a single, easy-to-understand sentence. If image descriptions are provided, incorporate them naturally into the summary.'],
                    ['role' => 'user', 'content' => $post->content . $imageContext],
                ],
                'max_tokens' => 150,
            ]);

            if ($response->failed()) {
                $response->throw();
            }

            $summary = trim($response->json('choices.0.message.content'));

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if (request()->user()->id !== $post->user_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $postId = $post->id;
            foreach ($post->media as $media) {
                Storage::disk($media->disk)->delete($media->path);
            }
            $post->delete();
            Log::info('[CivicUtopia] Post ID: ' . $postId . ' and associated media deleted successfully by User ID: ' . request()->user()->id);
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Post deleted successfully.']);
            }
            return redirect()->route('dashboard')->with('status', 'Post deleted successfully.');
        } catch (\Exception $e) {
            Log::error('[CivicUtopia] Failed to delete post.', ['post_id' => $post->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'There was an error deleting the post.');
        }
    }


    /**
     * Toggle the "like" status for a post.
     */
    public function toggleLike(Request $request, Post $post)
    {
        try {
            $result = $request->user()->likes()->toggle($post->id);
            $action = count($result['attached']) > 0 ? 'liked' : 'unliked';
            $likesCount = $post->likers()->count();
            Log::info("[PostController] User {$request->user()->id} {$action} Post {$post->id}. New count: {$likesCount}");
            return response()->json(['status' => 'success', 'action' => $action, 'likes_count' => $likesCount]);
        } catch (\Exception $e) {
            Log::error("[PostController] Failed to toggle like for Post {$post->id}", ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Could not update like status.'], 500);
        }
    }

    /**
     * Toggle the "bookmark" status for a post.
     */
    public function toggleBookmark(Request $request, Post $post)
    {
        try {
            $result = $request->user()->bookmarks()->toggle($post->id);
            $action = count($result['attached']) > 0 ? 'bookmarked' : 'unbookmarked';
            Log::info("[PostController] User {$request->user()->id} {$action} Post {$post->id}.");
            return response()->json(['status' => 'success', 'action' => $action]);
        } catch (\Exception $e) {
            Log::error("[PostController] Failed to toggle bookmark for Post {$post->id}", ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Could not update bookmark status.'], 500);
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
            Log::warning('[PostController] Azure AI Vision credentials are not configured. Skipping image analysis.');
            return [];
        }

        $images = $post->media()->where('file_type', 'image')->get();
        if ($images->isEmpty()) {
            return [];
        }

        Log::info('[PostController] Analyzing ' . $images->count() . ' images for Post ID: ' . $post->id);

        // !FIX: Use the correct API endpoint and parameters from your working curl command
        $requestUrl = rtrim($visionEndpoint, '/') . '/computervision/imageanalysis:analyze?api-version=2023-10-01&features=caption';

        $responses = Http::pool(function (Pool $pool) use ($images, $requestUrl, $visionApiKey) {
            foreach ($images as $image) {
                $baseUrl = rtrim(config('app.url'), '/');
                $imageUrl = $baseUrl . Storage::url($image->path);

                Log::info('[PostController] Sending image URL to Azure Vision: ' . $imageUrl);

                $pool->withHeaders([
                    'Content-Type' => 'application/json',
                    'Ocp-Apim-Subscription-Key' => $visionApiKey,
                ])->post($requestUrl, [
                    'url' => $imageUrl
                ]);
            }
        });

        $descriptions = [];
        foreach ($responses as $response) {
            if ($response->successful()) {
                // !FIX: Parse the new JSON structure
                $descriptions[] = $response->json('captionResult.text');
            } else {
                Log::error('[PostController] Azure Vision API call failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }

        Log::info('[PostController] Image analysis complete.', ['descriptions' => $descriptions]);
        return array_filter($descriptions);
    }
}
