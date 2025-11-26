<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suggestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // For Azure API
use Carbon\Carbon;

class SuggestionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();

        // 1. TIMEOUT / RATE LIMIT CHECK (10 Minutes)
        $lastSuggestion = Suggestion::where('user_id', $user->id)->latest()->first();
        if ($lastSuggestion && $lastSuggestion->created_at->diffInMinutes(now()) < 10) {
            $minutesLeft = 10 - $lastSuggestion->created_at->diffInMinutes(now());
            return redirect()->back()->withErrors(['msg' => "You are on a timeout. Please wait {$minutesLeft} minutes before making another suggestion."]);
        }

        // 2. AZURE CONTENT SAFETY CHECK
        if($this->isContentUnsafe($request->title . ' ' . $request->description)) {
             return redirect()->back()->withErrors(['msg' => 'Your suggestion contains unsafe content and cannot be posted.']);
        }

        Suggestion::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        Log::info('[SuggestionController] User ' . $user->id . ' submitted a suggestion.');

        return redirect()->back()->with('success', 'Suggestion submitted for review!');
    }

    public function vote(Request $request, Suggestion $suggestion)
    {
        $user = Auth::user();

        if ($suggestion->votes->contains($user->id)) {
            $suggestion->votes()->detach($user->id);
            $action = 'unvoted';
        } else {
            $suggestion->votes()->attach($user->id);
            $action = 'voted';
        }

        return response()->json([
            'count' => $suggestion->votes()->count(),
            'action' => $action
        ]);
    }

    // 3. DELETE METHOD (For Admin)
    public function destroy(Suggestion $suggestion)
    {
        $suggestion->delete();
        return redirect()->back()->with('success', 'Suggestion deleted successfully.');
    }

    // Helper: Azure Content Safety
    private function isContentUnsafe($text)
    {
        $endpoint = config('services.azure.content_safety.endpoint'); // You might need to add this to config
        $key = config('services.azure.content_safety.key');

        // If credentials aren't set, skip check (fail open) or block (fail closed)
        if (empty($endpoint) || empty($key)) return false;

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $key,
                'Content-Type' => 'application/json'
            ])->post($endpoint . '/text:analyze?api-version=2023-10-01', [
                'text' => $text,
                'categories' => ['Hate', 'SelfHarm', 'Sexual', 'Violence'],
                'outputType' => 'FourSeverityLevels'
            ]);

            if ($response->successful()) {
                $results = $response->json();
                // Check if any category has severity > 0 (or > 2 for stricter)
                foreach ($results['categoriesAnalysis'] as $analysis) {
                    if ($analysis['severity'] > 0) return true;
                }
            }
        } catch (\Exception $e) {
            Log::error("Content Safety Error: " . $e->getMessage());
        }

        return false;
    }
}
