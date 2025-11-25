<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\ContentSafetyService; // Make sure this exists
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request, ContentSafetyService $safetyService)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'topic_id' => 'nullable|exists:topics,id',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
        ]);

        $user = Auth::user();
        $content = $validated['content'];

        // 1. AZURE CONTENT SAFETY CHECK
        $safetyResult = $safetyService->analyze($content);
        $isFlagged = !$safetyResult['safe'];
        $flagReason = $safetyResult['reason'] ?? null;

        try {
            // 2. Create Post
            $post = $user->posts()->create([
                'topic_id' => $validated['topic_id'] ?? null,
                'content' => $content,
                'is_flagged' => $isFlagged,
                'flag_reason' => $flagReason,
            ]);

            // 3. Handle Media (Your existing logic)
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $path = $file->store('post_media', 'public'); // Changed folder to match your existing logic
                    $mimeType = $file->getMimeType();
                    $fileType = Str::startsWith($mimeType, 'image/') ? 'image' : (Str::startsWith($mimeType, 'video/') ? 'video' : 'other');

                    $post->media()->create([
                        'user_id' => $user->id,
                        'disk' => 'public',
                        'path' => $path,
                        'file_type' => $fileType,
                        'mime_type' => $mimeType,
                    ]);
                }
            }

            $post->load('user', 'media', 'topics');

            // 4. Return Response
            if ($request->wantsJson()) {
                $msg = $isFlagged ? 'Post created but flagged for review: ' . $flagReason : 'Post created successfully';
                return response()->json(['message' => $msg, 'post' => $post], 201);
            }

            return redirect()->route('dashboard')->with('success', $isFlagged ? 'Post flagged for review.' : 'Post created!');

        } catch (\Exception $e) {
            Log::error('Post Create Error: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Error creating post'], 500);
            }
            return back()->with('error', 'Error creating post.');
        }
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        if (request()->user()->id !== $post->user_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            foreach ($post->media as $media) {
                Storage::disk($media->disk)->delete($media->path);
            }
            $post->delete();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Post deleted successfully.']);
            }
            return redirect()->route('dashboard')->with('status', 'Post deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Error deleting post.');
        }
    }

    /**
     * Toggle Like on a Post.
     */
    public function toggleLike(Request $request, Post $post)
    {
        $user = Auth::user();
        // Use relationship toggle if setup (requires BelongsToMany)
        // If you use a package like 'overtrue/laravel-like', syntax differs.
        // Assuming standard Pivot:
        $post->likers()->toggle($user->id);

        // Calculate new state
        $isLiked = $post->likers()->where('user_id', $user->id)->exists();

        return response()->json([
            'action' => $isLiked ? 'liked' : 'unliked',
            'likes_count' => $post->likers()->count()
        ]);
    }

    /**
     * Toggle Bookmark on a Post.
     */
    public function toggleBookmark(Request $request, Post $post)
    {
        $user = Auth::user();
        $post->bookmarkers()->toggle($user->id);

        $isBookmarked = $post->bookmarkers()->where('user_id', $user->id)->exists();

        return response()->json([
            'action' => $isBookmarked ? 'bookmarked' : 'unbookmarked'
        ]);
    }

    /**
     * Summarize Post Content using Azure OpenAI.
     */
    public function summarize(Post $post)
    {
        if ($post->summary) {
            return response()->json(['summary' => $post->summary]);
        }

        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            // Include Image Context (Reuse your existing helper if available, else skip)
            $imageContext = "";
            // $imageContext = $this->getImageDescriptions($post); // Uncomment if you keep that helper in this class

            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => 'Summarize this post in one sentence.'],
                    ['role' => 'user', 'content' => $post->content . $imageContext],
                ],
                'temperature' => 0.5,
            ]);

            if ($response->failed()) {
                return response()->json(['summary' => 'AI service unavailable.'], 500);
            }

            $summary = $response->json('choices.0.message.content');
            $post->update(['summary' => $summary]); // Cache it

            return response()->json(['summary' => $summary]);

        } catch (\Exception $e) {
            Log::error('Summarize Error: ' . $e->getMessage());
            return response()->json(['summary' => 'Error generating summary.'], 500);
        }
    }

    /**
     * Explain Post Content ("Like I'm 5") using Azure OpenAI.
     */
    public function explain(Post $post)
    {
        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => 'Explain this text simply (ELI5).'],
                    ['role' => 'user', 'content' => $post->content],
                ],
                'temperature' => 0.7,
            ]);

            if ($response->failed()) {
                return response()->json(['explanation' => 'AI service unavailable.'], 500);
            }

            $explanation = $response->json('choices.0.message.content');
            return response()->json(['explanation' => $explanation]);

        } catch (\Exception $e) {
            Log::error('Explain Error: ' . $e->getMessage());
            return response()->json(['explanation' => 'Error generating explanation.'], 500);
        }
    }

    // You can keep your getImageDescriptions private method here if needed for the summaries

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

        $requestUrl = rtrim($visionEndpoint, '/') . '/computervision/imageanalysis:analyze?api-version=2023-10-01&features=denseCaptions';
        $descriptions = [];

        foreach ($images as $image) {
            try {
                $fileContents = Storage::disk('public')->get($image->path);
                $mimeType = Storage::disk('public')->mimeType($image->path);

                if (!$fileContents) {
                    Log::error('[PostController] Could not read file from storage.', ['path' => $image->path]);
                    continue;
                }

                Log::info('[PostController] Sending raw image data to Azure Vision for DENSE analysis.');

                $response = Http::withHeaders([
                    'Ocp-Apim-Subscription-Key' => $visionApiKey,
                    'Content-Type' => 'application/octet-stream',
                ])
                ->withBody($fileContents, $mimeType)
                ->post($requestUrl);

                if ($response->successful()) {
                    $denseCaptions = $response->json('denseCaptionsResult.values');
                    if (is_array($denseCaptions)) {
                        foreach ($denseCaptions as $caption) {
                            $descriptions[] = $caption['text'];
                        }
                    }
                } else {
                    Log::error('[PostController] Azure Vision API call failed for one image.', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('[PostController] Exception during Azure Vision API call.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $uniqueDescriptions = array_unique($descriptions);
        Log::info('[PostController] Image analysis complete.', ['descriptions' => $uniqueDescriptions]);
        return array_slice($uniqueDescriptions, 0, 5);
    }
}
