<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Jobs\AiAgentJob;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ContentSafetyService;

class CommentController extends Controller
{
  // Update store method signature:
    public function store(Request $request, Post $post, ContentSafetyService $safety)
    {
        $validated = $request->validate(['content' => ['required', 'string', 'max:2000']]);
        $content = $validated['content'];
        $user = $request->user();

        // 1. SAFETY CHECK
        $safetyCheck = $safety->analyze($content);
        $isFlagged = !$safetyCheck['safe'];
        $flagReason = $safetyCheck['reason'] ?? null;

        // If highly toxic, maybe reject entirely?
        // For now, we save but mark as flagged so we can hide it in UI.

        // 2. Save
        try {
            $comment = $post->comments()->create([
                'user_id' => $user->id,
                'content' => $isFlagged ? "[Content Flagged by AI for $flagReason]" : $content, // Hide text if toxic
                'is_flagged' => $isFlagged,
                'flag_reason' => $flagReason
            ]);
            $comment->load('user');

            // Broadcast
            CommentCreated::dispatch($comment);

            Log::info('[CommentController] User comment posted.', ['id' => $comment->id]);

        } catch (\Exception $e) {
            Log::error('[CommentController] Error: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to post comment'], 500);
            }
            return back()->with('error', 'Failed to post comment.');
        }

        // 2. AI DETECTION LOGIC (Improved)

        // Strip out quotes (lines starting with >) to prevent triggering on replies
        // This regex replaces lines starting with > with empty string
        $contentForDetection = preg_replace('/^>.*$/m', '', $content);

        $pattern = '/@(FactChecker|Historian|DevilsAdvocate|Analyst)/i';
        $botTriggered = false;

        if (preg_match($pattern, $contentForDetection, $matches)) {
            $detectedBot = $this->normalizeBotName($matches[1]);
            Log::info("[CommentController] Bot summoned: {$detectedBot}");

            // Pass the actual Comment object so the bot can reply specifically to it
            AiAgentJob::dispatch($post, $comment, $detectedBot);

            $botTriggered = $detectedBot;
        }

        // 3. Return JSON
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'comment' => $comment,
                'bot_triggered' => $botTriggered
            ]);
        }

        return back()->with('status', 'Comment posted!');
    }

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
