<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class IssueController extends Controller
{
    // A mini database of Jamaican Agencies for the Hackathon
    private $agencyDirectory = [
        'National Works Agency (Roads)' => 'commsmanager@nwa.gov.jm',
        'National Water Commission (Water)' => 'pr@nwc.com.jm',
        'NSWMA (Garbage)' => 'complaints@nswma.gov.jm',
        'JPS (Electricity)' => 'customer@jpsco.com',
        'KSAMC (Kingston Corp)' => 'customerservice@ksamc.gov.jm',
        'Police (JCF)' => 'contact@jcf.gov.jm',
    ];

    public function index()
    {
        $issues = Issue::with('user')->latest()->get();
        return view('issues.index', compact('issues'));
    }

    public function create()
    {
        return view('issues.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'user_description' => 'nullable|string',
            'image' => 'required|image|max:10240',
        ]);

        $path = $request->file('image')->store('issues', 'public');

        $issue = Issue::create([
            'user_id' => Auth::id(),
            'location' => $validated['location'],
            'user_description' => $validated['user_description'],
            'image_path' => $path,
            'title' => 'New Report @ ' . $validated['location'],
        ]);

        return redirect()->route('issues.show', $issue->id)
            ->with('success', 'Photo uploaded! Now let AI analyze it.');
    }

    public function show(Issue $issue)
    {
        return view('issues.show', compact('issue'));
    }

    /**
     * THE MAGIC: Azure Vision -> Azure OpenAI -> Email Matching
     */
    public function analyze(Issue $issue)
    {
        try {
            // STEP 1: AZURE VISION
            $visionEndpoint = config('services.azure.vision.endpoint');
            $visionKey = config('services.azure.vision.api_key');
            $imageContent = Storage::disk('public')->get($issue->image_path);
            $visionUrl = rtrim($visionEndpoint, '/') . '/computervision/imageanalysis:analyze?api-version=2023-10-01&features=caption,tags';

            $visionResponse = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $visionKey,
                'Content-Type' => 'application/octet-stream',
            ])->withBody($imageContent, 'application/octet-stream')->post($visionUrl);

            if ($visionResponse->failed()) {
                return back()->with('error', 'Could not analyze image. Check Vision keys.');
            }

            $visionData = $visionResponse->json();
            $caption = $visionData['captionResult']['text'] ?? 'An issue on the street';

            $tags = [];
            if (isset($visionData['tagsResult']['values'])) {
                foreach ($visionData['tagsResult']['values'] as $tag) {
                    $tags[] = $tag['name'];
                }
            }

            // STEP 2: AZURE OPENAI
            $openaiEndpoint = config('services.azure.openai.endpoint');
            $openaiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $openaiUrl = rtrim($openaiEndpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $agencyList = implode(', ', array_keys($this->agencyDirectory));

            $systemMessage = "You are a professional civic advocate in Jamaica. Write a formal complaint letter.
            Also, identify the best agency to receive this complaint from this list: [{$agencyList}].
            Output valid JSON.";

            $userMessage = "Location: " . $issue->location . "\n" .
                "User Note: " . $issue->user_description . "\n" .
                "AI Visual Analysis: " . $caption . "\n" .
                "Tags: " . implode(', ', $tags) . "\n\n" .
                "Return JSON with keys: \n" .
                "- title (Short 5-word summary)\n" .
                "- severity (Low, Medium, High, Critical)\n" .
                "- recommended_agency (Exact string from my list, or 'Other')\n" .
                "- letter_text (The full formal letter, ready to email)";

            $aiResponse = Http::withHeaders([
                'api-key' => $openaiKey,
                'Content-Type' => 'application/json',
            ])->post($openaiUrl, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.5,
                'response_format' => ['type' => 'json_object'],
            ]);

            $aiData = json_decode($aiResponse->json('choices.0.message.content'), true);

            // Match Agency Name to Email
            $agencyName = $aiData['recommended_agency'] ?? 'Other';
            $agencyEmail = $this->agencyDirectory[$agencyName] ?? '';

            // Update Issue
            $issue->update([
                'ai_caption' => $caption,
                'ai_tags' => $tags,
                'title' => $aiData['title'],
                'severity' => $aiData['severity'],
                'generated_letter' => $aiData['letter_text'],
                'status' => 'Drafted',
            ]);

            // Save the email/agency temporarily in session or pass to view via flash?
            // Better to pass it as a flash message for the view to pick up, or save it to DB if you added columns.
            // For now, I'll pass it to the view via the 'success' message logic or just let the view handle logic.

            // Actually, let's append the agency/email to the letter text in the DB for the view to parse?
            // No, cleanest is to just have the view use a mapping.
            // Let's pass the detected agency via session to the show route
            return redirect()->route('issues.show', ['issue' => $issue->id, 'agency' => $agencyName]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return back()->with('error', 'Analysis failed.');
        }
    }
}
