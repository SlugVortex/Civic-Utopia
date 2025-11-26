<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PollController extends Controller
{
    public function vote(Request $request, Poll $poll)
    {
        $request->validate([
            'option_id' => 'required|exists:poll_options,id'
        ]);

        // Check if already voted
        if ($poll->userHasVoted(Auth::id())) {
            return response()->json(['message' => 'You have already voted.'], 403);
        }

        PollVote::create([
            'user_id' => Auth::id(),
            'poll_id' => $poll->id,
            'poll_option_id' => $request->option_id
        ]);

        Log::info('[PollController] User ' . Auth::id() . ' voted on poll ' . $poll->id);

        return response()->json(['message' => 'Vote recorded successfully.', 'success' => true]);
    }
}
