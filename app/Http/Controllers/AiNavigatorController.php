<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiNavigatorController extends Controller
{
    public function navigate(Request $request)
    {
        $request->validate(['command' => 'required|string|max:200']);
        $command = $request->command;

        // Define the Site Map (The "Tools" the agent knows about)
        $siteMap = [
            '/dashboard' => "Town Square, Feed, Social, Posts, Discussion, Home",
            '/ballots' => "Ballot Box, Laws, Voting, Referendum, Bills",
            '/ballots/create' => "Add Ballot, Create Law",
            '/candidates' => "Candidate Compass, Politicians, Leaders, Election, Profile",
            '/candidates/compare' => "Compare Candidates, Head to Head, Versus",
            '/issues' => "Civic Lens, Report Issue, Pothole, Garbage, Complaint",
            '/documents' => "Legal Library, Documents, PDFs, Legislation, Research",
            '/interview' => "Political Interview, Simulation, Talk to Politician",
        ];

        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $systemMessage = "You are a navigation assistant for the 'CivicUtopia' app.
            Map the user's request to one of the following URL paths based on the keywords provided.

            Site Map:
            " . json_encode($siteMap) . "

            If the request is 'help' or 'what can I do', return specific text listing features.

            Output valid JSON: { \"action\": \"redirect\" or \"message\", \"target\": \"/url\" or \"The text message to show\" }";

            $response = Http::withHeaders([
                'api-key' => $apiKey, 'Content-Type' => 'application/json'
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $command],
                ],
                'temperature' => 0.1, // Low temp for precision
                'response_format' => ['type' => 'json_object'],
            ]);

            $aiData = json_decode($response->json('choices.0.message.content'), true);

            return response()->json($aiData);

        } catch (\Exception $e) {
            Log::error("AI Navigator Error: " . $e->getMessage());
            return response()->json(['action' => 'message', 'target' => 'Sorry, I got lost. Try clicking the menu instead.']);
        }
    }
}
