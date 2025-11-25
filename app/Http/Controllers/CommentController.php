<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Jobs\AiAgentJob;
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

        // 1. Save the User's Comment Immediately
        try {
            $comment = $post->comments()->create([
                'user_id' => $user->id,
                'content' => $content,
            ]);
            $comment->load('user');

            CommentCreated::dispatch($comment);
            Log::info('[CommentController] User comment posted.', ['id' => $comment->id]);

        } catch (\Exception $e) {
            Log::error('[CommentController] Error saving comment: ' . $e->getMessage());
            return back()->with('error', 'Failed to post comment.');
        }

        // 2. AI COUNCIL DETECTION LOGIC
        // Regex matches: @FactChecker, @Historian, @DevilsAdvocate, @Analyst
        $pattern = '/@(FactChecker|Historian|DevilsAdvocate|Analyst)/i';

        if (preg_match($pattern, $content, $matches)) {
            // Normalize name (e.g. "historian" -> "Historian")
            $detectedBot = $this->normalizeBotName($matches[1]);

            Log::info("[CommentController] Bot summoned: {$detectedBot}");

            // Dispatch Job with Bing Search Capability
            AiAgentJob::dispatch($post, $content, $detectedBot);

            return back()->with('status', "Comment posted! @{$detectedBot} is investigating...");
        }

        return back()->with('status', 'Comment posted!');
    }

    /**
     * Helper to standardize bot names
     */
    private function normalizeBotName($input)
    {
        $input = strtolower($input);
        switch ($input) {
            case 'factchecker': return 'FactChecker';
            case 'historian': return 'Historian';
            case 'devilsadvocate': return 'DevilsAdvocate';
            case 'analyst': return 'Analyst';
            default: return 'FactChecker';
        }
    }
}
