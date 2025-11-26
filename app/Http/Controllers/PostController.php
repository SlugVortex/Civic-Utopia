<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Media;
use App\Services\ContentSafetyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            'is_private' => 'boolean' // Added support for manual private posts
        ]);

        $user = Auth::user();
        $content = $validated['content'];
        $isPrivate = $request->boolean('is_private', false);

        // 1. AZURE CONTENT SAFETY CHECK
        Log::info("[PostController] Analyzing content safety for user: {$user->id}");
        $safetyResult = $safetyService->analyze($content);

        $isFlagged = !$safetyResult['safe'];
        $flagReason = $safetyResult['reason'] ?? null;

        if ($isFlagged) {
            Log::warning("[PostController] 🚩 Content Flagged: $flagReason");
            // Option: You could prevent saving entirely here if you prefer strict blocking
            // For now, we save it as flagged so admins can review, but it won't show in main feeds depending on view logic
        }

        try {
            // 2. Create Post
            $post = $user->posts()->create([
                'topic_id' => $validated['topic_id'] ?? null,
                'content' => $isFlagged ? "This post was flagged for $flagReason and is hidden." : $content,
                'is_flagged' => $isFlagged,
                'flag_reason' => $flagReason,
                'is_private' => $isPrivate,
            ]);

            // 3. Handle Media Uploads
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $path = $file->store('post_media', 'public');
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
                $msg = $isFlagged ? 'Post flagged for review: ' . $flagReason : 'Post created successfully';
                return response()->json(['message' => $msg, 'post' => $post], 201);
            }

            if ($isFlagged) {
                return redirect()->route('dashboard')->with('error', "Your post was flagged for $flagReason.");
            }

            return redirect()->route('dashboard')->with('success', 'Post created successfully!');

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
        $post->likers()->toggle($user->id);

        return response()->json([
            'action' => $post->likers()->where('user_id', $user->id)->exists() ? 'liked' : 'unliked',
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
            // 1. Get Visual Context
            $imageDescriptions = $this->getImageDescriptions($post);
            $imageContext = "";
            if (!empty($imageDescriptions)) {
                $imageContext = "\n\n[Visual Context from Images]: " . implode("; ", $imageDescriptions);
            }

            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $url = rtrim($endpoint, '/') . "/openai/deployments/" . config('services.azure.openai.deployment') . "/chat/completions?api-version=" . config('services.azure.openai.api_version');

            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => 'Summarize this post in one sentence. Incorporate visual context if provided.'],
                    ['role' => 'user', 'content' => $post->content . $imageContext],
                ],
                'temperature' => 0.5,
            ]);

            if ($response->failed()) {
                return response()->json(['summary' => 'AI service unavailable.'], 500);
            }

            $summary = $response->json('choices.0.message.content');
            $post->update(['summary' => $summary]);

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
            // 1. Get Visual Context
            $imageDescriptions = $this->getImageDescriptions($post);
            $imageContext = "";
            if (!empty($imageDescriptions)) {
                $imageContext = "\n\n[Visual Context]: " . implode("; ", $imageDescriptions);
            }

            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $url = rtrim($endpoint, '/') . "/openai/deployments/" . config('services.azure.openai.deployment') . "/chat/completions?api-version=" . config('services.azure.openai.api_version');

            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => 'Explain this text simply (ELI5).'],
                    ['role' => 'user', 'content' => $post->content . $imageContext],
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

    /**
     * Helper: Analyze Images via Azure Vision
     */
    private function getImageDescriptions(Post $post): array
    {
        $visionEndpoint = config('services.azure.vision.endpoint');
        $visionApiKey = config('services.azure.vision.api_key');

        if (!$visionEndpoint || !$visionApiKey) {
            Log::warning('Azure Vision keys missing.');
            return [];
        }

        $images = $post->media()->where('file_type', 'image')->get();
        if ($images->isEmpty()) return [];

        $url = rtrim($visionEndpoint, '/') . '/computervision/imageanalysis:analyze?api-version=2023-10-01&features=denseCaptions';
        $descriptions = [];

        foreach ($images as $image) {
            try {
                $fileContents = Storage::disk('public')->get($image->path);
                $response = Http::withHeaders([
                    'Ocp-Apim-Subscription-Key' => $visionApiKey,
                    'Content-Type' => 'application/octet-stream',
                ])->withBody($fileContents, 'application/octet-stream')->post($url);

                if ($response->successful()) {
                    $captions = $response->json('denseCaptionsResult.values');
                    if (is_array($captions) && count($captions) > 0) {
                        $descriptions[] = $captions[0]['text']; // Take top caption
                    }
                }
            } catch (\Exception $e) {
                Log::error("Vision API Error: " . $e->getMessage());
            }
        }

        return $descriptions;
    }
}
