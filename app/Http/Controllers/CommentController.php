<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
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

        try {
            $comment = $post->comments()->create([
                'user_id' => $request->user()->id,
                'content' => $validated['content'],
            ]);

            // Load the user relationship explicitly before broadcasting
            $comment->load('user');

            Log::info('[CivicUtopia] New comment created for Post ID: ' . $post->id . '. Firing CommentCreated event.');

            // Fire the event to broadcast the new comment
            CommentCreated::dispatch($comment);

            // We will return a JSON response for AJAX requests later
            return redirect()->back()->with('status', 'Comment posted!');

        } catch (\Exception $e) {
            Log::error('[CivicUtopia] Failed to create comment.', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'There was an error posting your comment.');
        }
    }
}
