<?php

namespace App\Http\Controllers;

use App\Models\BallotQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BallotController extends Controller
{
    /**
     * AI GENERATOR: Create a ballot question from a user's idea
     */
    public function generate(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);

        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $systemMessage = "You are a legislative drafter and political scientist.
            Your task is to convert a user's idea into a realistic, neutrally worded ballot question.
            The 'official_text' should sound formal and legalistic.
            Output must be valid JSON: { \"title\": \"...\", \"official_text\": \"...\" }";

            $response = Http::withHeaders([
                'api-key' => $apiKey, 'Content-Type' => 'application/json'
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => "User Idea: " . $request->prompt],
                ],
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object'],
            ]);

            return response()->json(json_decode($response->json('choices.0.message.content')));

        } catch (\Exception $e) {
            Log::error("Ballot Generate Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to generate ballot text.'], 500);
        }
    }

    // ... Keep all your existing methods (index, create, store, etc.) ...

    /**
     * Display a listing of ballot questions with Search & Filter.
     */
    public function index(Request $request)
    {
        $query = BallotQuestion::query();

        // 1. Filter by Country
        if ($request->has('country') && $request->country != '') {
            $query->where('country', $request->country);
        }

        // 2. Search by Title or Text
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('official_text', 'like', '%' . $request->search . '%');
            });
        }

        // 3. Sort by Date
        $ballots = $query->orderBy('election_date', 'asc')->get();

        // Get unique countries for the filter dropdown
        $countries = BallotQuestion::select('country')->distinct()->pluck('country');

        return view('ballots.index', compact('ballots', 'countries'));
    }

    /**
     * Show the form for creating a new ballot question.
     */
    public function create()
    {
        return view('ballots.create');
    }

    /**
     * Store a newly created ballot question.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'official_text' => 'required|string',
            'election_date' => 'required|date',
            'country' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
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
     * Trigger Azure AI Analysis (Initial Setup).
     */
    public function analyze(BallotQuestion $ballot)
    {
        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $systemMessage = "You are an expert civic engagement assistant for " . $ballot->country . ". Your goal is to explain complex legal ballot questions. Output strict JSON.";

            $userMessage = "Analyze this ballot text:\n\n" . $ballot->official_text . "\n\n" .
                "Provide a JSON response with:
                - summary_plain (string)
                - summary_patois (string: Use local dialect if country is Jamaica, otherwise use friendly casual tone)
                - yes_vote_meaning (string)
                - no_vote_meaning (string)
                - pros (array of strings)
                - cons (array of strings)";

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
                return back()->with('error', 'Azure AI request failed.');
            }

            $data = $response->json();
            $contentJson = json_decode($data['choices'][0]['message']['content'], true);

            $ballot->update([
                'summary_plain' => $contentJson['summary_plain'] ?? null,
                'summary_patois' => $contentJson['summary_patois'] ?? null,
                'yes_vote_meaning' => $contentJson['yes_vote_meaning'] ?? null,
                'no_vote_meaning' => $contentJson['no_vote_meaning'] ?? null,
                'pros' => $contentJson['pros'] ?? [],
                'cons' => $contentJson['cons'] ?? [],
            ]);

            return back()->with('success', 'AI Analysis complete!');

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return back()->with('error', 'An error occurred during analysis.');
        }
    }

    /**
     * Translate the Ballot Analysis AND Official Text into ANY language.
     */
    public function translate(Request $request, BallotQuestion $ballot)
    {
        $validated = $request->validate([
            'language' => 'required|string|max:50'
        ]);

        $targetLang = $validated['language'];

        try {
            if (strtolower($targetLang) === 'english') {
                return response()->json([
                    'official_text' => $ballot->official_text,
                    'breakdown_label' => 'Plain English Summary',
                    'breakdown_text' => $ballot->summary_plain,
                    'yes_vote_meaning' => $ballot->yes_vote_meaning,
                    'no_vote_meaning' => $ballot->no_vote_meaning,
                    'pros' => $ballot->pros,
                    'cons' => $ballot->cons
                ]);
            }

            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $dataToContext = [
                'official_text' => $ballot->official_text,
                'analysis_summary' => $ballot->summary_plain,
                'yes_vote' => $ballot->yes_vote_meaning,
                'no_vote' => $ballot->no_vote_meaning,
                'pros' => $ballot->pros,
                'cons' => $ballot->cons
            ];

            $systemMessage = "You are a professional translator and civic educator.
            Translate the provided ballot information into {$targetLang}.
            Output must be a valid JSON object with keys: official_text, breakdown_text, yes_vote_meaning, no_vote_meaning, pros, cons.";

            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => json_encode($dataToContext)],
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Translation Service Unavailable'], 500);
            }

            $content = json_decode($response->json('choices.0.message.content'), true);
            $content['breakdown_label'] = "{$targetLang} Breakdown (ELI5)";

            return response()->json($content);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Translation failed'], 500);
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

            $systemMessage = "You are a helpful assistant explaining a specific ballot question to a voter in {$ballot->country}. Use the provided Legal Text to answer.";

            $userMessage = "Legal Text: " . $ballot->official_text . "\n\nUser Question: " . $validated['question'];

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

            return response()->json(['answer' => $response->json('choices.0.message.content')]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error processing question'], 500);
        }
    }
}
