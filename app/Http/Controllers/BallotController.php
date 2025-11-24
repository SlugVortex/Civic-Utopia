<?php

namespace App\Http\Controllers;

use App\Models\BallotQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BallotController extends Controller
{
    /**
     * Display a listing of ballot questions.
     */
    public function index()
    {
        $ballots = BallotQuestion::orderBy('election_date', 'asc')->get();
        return view('ballots.index', compact('ballots'));
    }

    /**
     * Show the form for creating a new ballot question.
     */
    public function create()
    {
        return view('ballots.create');
    }

    /**
     * Store a newly created ballot question in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'official_text' => 'required|string',
            'election_date' => 'required|date',
        ]);

        $ballot = BallotQuestion::create($validated);

        return redirect()->route('ballots.show', $ballot->id)
            ->with('success', 'Ballot created successfully! Now click "Decode with AI Agent" to analyze it.');
    }

    /**
     * Display the specified ballot question.
     */
    public function show(BallotQuestion $ballot)
    {
        return view('ballots.show', compact('ballot'));
    }

    /**
     * Trigger Azure AI Analysis for a ballot (Summaries/Pros/Cons).
     */
    public function analyze(BallotQuestion $ballot)
    {
        try {
            Log::info("[BallotController] Starting Azure AI analysis for Ballot ID: {$ballot->id}");

            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');

            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $systemMessage = "You are an expert civic engagement assistant for Jamaica. Your goal is to explain complex legal ballot questions to the average citizen.
            You must output strict JSON.
            Language style for 'summary_patois': Authentic Jamaican Patois, accessible and friendly.
            Language style for 'summary_plain': Simple, clear English (Grade 5 level).";

            $userMessage = "Analyze this ballot text:\n\n" . $ballot->official_text . "\n\n" .
                "Provide a JSON response with the following keys:
                - summary_plain (string)
                - summary_patois (string)
                - yes_vote_meaning (string: what happens if I vote yes?)
                - no_vote_meaning (string: what happens if I vote no?)
                - pros (array of strings: arguments for)
                - cons (array of strings: arguments against)";

            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->failed()) {
                Log::error('[BallotController] Azure AI Request Failed', ['body' => $response->body()]);
                return back()->with('error', 'Azure AI request failed. Check logs.');
            }

            $data = $response->json();

            if (!isset($data['choices'][0]['message']['content'])) {
                return back()->with('error', 'AI returned unexpected format.');
            }

            $contentJson = json_decode($data['choices'][0]['message']['content'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'Failed to decode AI response.');
            }

            $ballot->update([
                'summary_plain' => $contentJson['summary_plain'] ?? null,
                'summary_patois' => $contentJson['summary_patois'] ?? null,
                'yes_vote_meaning' => $contentJson['yes_vote_meaning'] ?? null,
                'no_vote_meaning' => $contentJson['no_vote_meaning'] ?? null,
                'pros' => $contentJson['pros'] ?? [],
                'cons' => $contentJson['cons'] ?? [],
            ]);

            return back()->with('success', 'AI Analysis complete! The ballot has been decoded.');

        } catch (\Exception $e) {
            Log::error('[BallotController] Exception during analysis', ['message' => $e->getMessage()]);
            return back()->with('error', 'An error occurred during analysis.');
        }
    }

    /**
     * Chat with the Ballot (Q&A).
     */
    public function askBot(Request $request, BallotQuestion $ballot)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500'
        ]);

        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');

            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $systemMessage = "You are a helpful assistant explaining a specific ballot question to a voter.
            Use the provided Legal Text to answer their questions.
            Be neutral, factual, and concise (under 50 words).";

            $userMessage = "Legal Text: " . $ballot->official_text . "\n\n" .
                           "User Question: " . $validated['question'];

            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.7,
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'AI Service Unavailable'], 500);
            }

            $answer = $response->json('choices.0.message.content');
            return response()->json(['answer' => $answer]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error processing question'], 500);
        }
    }
}
