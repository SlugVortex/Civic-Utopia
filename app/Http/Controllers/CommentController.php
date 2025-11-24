<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Jobs\AiAgentJob; // <-- Import our new job
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $content = $validated['content'];
        $user = $request->user();

        // --- UPGRADE: DETECT @Historian MENTION ---
        if (preg_match('/@Historian/i', $content)) {
            Log::info("[CommentController] @Historian mention detected. Dispatching AiAgentJob.", [
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);

            // First, save the user's comment so it appears instantly
            $comment = $post->comments()->create([
                'user_id' => $user->id,
                'content' => $content,
            ]);
            $comment->load('user');
            CommentCreated::dispatch($comment);

            // Now, dispatch the AI job to generate a response in the background
            AiAgentJob::dispatch($post, $content);

            // Return a response so the user's UI updates
            return redirect()->back()->with('status', 'Your question has been sent to The Historian. An answer will appear shortly.');
        }

        // --- Regular Comment Logic (if no mention) ---
        try {
            $comment = $post->comments()->create([
                'user_id' => $user->id,
                'content' => $content,
            ]);
            $comment->load('user');
            Log::info('[CommentController] New comment created for Post ID: ' . $post->id);

            CommentCreated::dispatch($comment);

            return redirect()->back()->with('status', 'Comment posted!');

        } catch (\Exception $e) {
            Log::error('[CommentController] Failed to create comment.', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'There was an error posting your comment.');
        }
    }
}
