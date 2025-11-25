<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CandidateController extends Controller
{
    /**
     * List candidates with filtering.
     */
    public function index(Request $request)
    {
        $query = Candidate::query();

        // Filter by Country
        if ($request->has('country') && $request->country != '') {
            $query->where('country', $request->country);
        }

        // Search by Name or Party
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('party', 'like', '%' . $request->search . '%');
            });
        }

        $candidates = $query->orderBy('country')->orderBy('name')->get();

        // Get unique countries for filter dropdown
        $countries = Candidate::select('country')->distinct()->pluck('country');

        return view('candidates.index', compact('candidates', 'countries'));
    }

    /**
     * Show form to add a new candidate.
     */
    public function create()
    {
        return view('candidates.create');
    }

    /**
     * Store new candidate.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'party' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'manifesto_text' => 'required|string',
        ]);

        $candidate = Candidate::create($validated);

        return redirect()->route('candidates.show', $candidate->id)
            ->with('success', 'Candidate profile created. Now use AI to analyze their manifesto.');
    }

    public function show(Candidate $candidate)
    {
        return view('candidates.show', compact('candidate'));
    }

    /**
     * The AI Agent: Reads the manifesto and extracts stances.
     */
    public function analyze(Candidate $candidate)
    {
        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $systemMessage = "You are a neutral political analyst for {$candidate->country}. Extract specific stances from the manifesto. Output valid JSON.";

            $userMessage = "Analyze this text: \n\n" . $candidate->manifesto_text . "\n\n" .
                "Return a JSON object with:
                1. 'ai_summary' (A 2-sentence neutral summary).
                2. 'stances' (An object where keys are major issues like 'Economy', 'Crime', 'Healthcare', 'Education' and values are 1-sentence summaries of their position).";

            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.5,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = json_decode($response->json('choices.0.message.content'), true);

            $candidate->update([
                'ai_summary' => $content['ai_summary'],
                'stances' => $content['stances'],
            ]);

            return back()->with('success', 'Manifesto analyzed successfully!');

        } catch (\Exception $e) {
            Log::error('Candidate AI Error: ' . $e->getMessage());
            return back()->with('error', 'AI Analysis failed.');
        }
    }

    /**
     * Chat with the Candidate (Digital Twin).
     */
    public function askBot(Request $request, Candidate $candidate)
    {
        $validated = $request->validate(['question' => 'required|string|max:500']);

        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            // We instruct the AI to pretend to be a representative of the candidate
            $systemMessage = "You are an AI assistant representing the politician {$candidate->name} from {$candidate->party} in {$candidate->country}.
            Use their manifesto text below to answer user questions accurately.
            If the answer is not in the manifesto, say 'My manifesto does not explicitly address this.'
            Keep answers concise and neutral.";

            $userMessage = "Manifesto Context: " . $candidate->manifesto_text . "\n\n" .
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

            return response()->json(['answer' => $response->json('choices.0.message.content')]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error contacting AI agent.'], 500);
        }
    }

    /**
     * Step 1: Show the selection screen.
     */
    public function compareSelect(Request $request)
    {
        // Allow filtering here too so they can find candidates to compare
        $query = Candidate::query();
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        $candidates = $query->orderBy('name')->get();
        $countries = Candidate::select('country')->distinct()->pluck('country');

        return view('candidates.compare_select', compact('candidates', 'countries'));
    }

    /**
     * Step 2: Process the comparison with AI.
     */
    public function compareAnalyze(Request $request)
    {
        $validated = $request->validate([
            'selected_candidates' => 'required|array|min:2|max:2',
            'selected_candidates.*' => 'exists:candidates,id',
        ]);

        $c1 = Candidate::findOrFail($validated['selected_candidates'][0]);
        $c2 = Candidate::findOrFail($validated['selected_candidates'][1]);

        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $systemMessage = "You are an expert political analyst. Compare these two candidates based on their manifestos.
            Identify the starkest contrasts. Output strict JSON.";

            $userMessage = "Candidate A: {$c1->name} ({$c1->party})\nManifesto: {$c1->manifesto_text}\n\n" .
                           "Candidate B: {$c2->name} ({$c2->party})\nManifesto: {$c2->manifesto_text}\n\n" .
                           "Provide a JSON object with:
                           1. 'verdict_summary': A 2-sentence summary of the main ideological difference.
                           2. 'comparison_table': An array of objects, where each object has:
                                - 'topic' (e.g. Economy, Crime, Health)
                                - 'candidate_a_stance' (10 words max)
                                - 'candidate_b_stance' (10 words max)
                                - 'winner_context' (Which demographic might prefer A vs B? e.g. 'Business owners prefer A, Unions prefer B')";

            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.5,
                'response_format' => ['type' => 'json_object'],
            ]);

            $aiData = json_decode($response->json('choices.0.message.content'), true);

            return view('candidates.compare_result', compact('c1', 'c2', 'aiData'));

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return back()->with('error', 'AI Comparison failed. Please try again.');
        }
    }
}
