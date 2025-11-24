<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Store a newly created resource in storage, now with media handling.
     */
    public function store(Request $request)
    {
        // Validation now includes the media files
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,wmv|max:20480', // Max 20MB per file
        ]);

        try {
            // First, create the post with the text content
            $post = $request->user()->posts()->create(['content' => $validated['content']]);

            // Then, handle any uploaded files
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    // Store the file in 'storage/app/public/post_media'
                    $path = $file->store('post_media', 'public');
                    $mimeType = $file->getMimeType();

                    // Determine if the file is an image or video based on its mime type
                    $fileType = 'other';
                    if (Str::startsWith($mimeType, 'image/')) {
                        $fileType = 'image';
                    } elseif (Str::startsWith($mimeType, 'video/')) {
                        $fileType = 'video';
                    }

                    // Create a record in our new 'media' table
                    $post->media()->create([
                        'user_id' => $request->user()->id,
                        'disk' => 'public',
                        'path' => $path,
                        'file_type' => $fileType,
                        'mime_type' => $mimeType,
                    ]);
                }
            }

            // Eager load the relationships for the response
            $post->load('user', 'media');
            Log::info('[CivicUtopia] New post with media created successfully.', ['post_id' => $post->id]);

            // --- WORKAROUND: Temporarily disable broadcasting to prevent timeout ---
            // PostCreated::dispatch($post);
            Log::warning('[CivicUtopia] Broadcasting is temporarily disabled to prevent SSL timeout.');
            // --- END WORKAROUND ---


            if ($request->wantsJson()) {
                return response()->json($post, 201);
            }
            return redirect()->back()->with('status', 'Post created successfully!');

        } catch (\Exception $e) {
            Log::error('[CivicUtopia] Failed to create post with media.', [
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

            // Delete media files from storage
            foreach ($post->media as $media) {
                Storage::disk($media->disk)->delete($media->path);
            }

            // Delete the post (this will cascade delete media records if set up in migration)
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

            return response()->json([
                'status' => 'success',
                'action' => $action,
                'likes_count' => $likesCount,
            ]);
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

            return response()->json([
                'status' => 'success',
                'action' => $action,
            ]);
        } catch (\Exception $e) {
            Log::error("[PostController] Failed to toggle bookmark for Post {$post->id}", ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Could not update bookmark status.'], 500);
        }
    }
}
