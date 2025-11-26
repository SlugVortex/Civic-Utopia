<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiNavigatorController extends Controller
{
    public function navigate(Request $request)
    {
        $request->validate(['command' => 'required|string|max:500']);
        $command = $request->command;

        // 1. Define Site Map (Navigation Targets)
        $siteMap = [
            '/dashboard' => "Town Square, Feed, Social, Posts, Discussion, Home",
            '/ballots' => "Ballot Box, Laws, Voting, Referendum, Bills, Legislation",
            '/ballots/create' => "Add Ballot, Create Law, Upload Bill",
            '/candidates' => "Candidate Compass, Politicians, Leaders, Election, Profiles",
            '/candidates/compare' => "Compare Candidates, Head to Head, Versus",
            '/issues' => "Civic Lens, Report Issue, Pothole, Garbage, Complaint, Camera",
            '/documents' => "Legal Library, Documents, PDFs, Research, Archives",
            '/interview' => "Political Interview, Simulation, Talk to Politician",
        ];

        // 2. Define Page Tools (Interactive Actions)
        $toolsInstructions = "
        GLOBAL TOOLS:
        - 'read all' / 'read posts' -> { \"action\": \"tool\", \"tool_type\": \"sequence\", \"selector\": \".btn-read-aloud\", \"message\": \"Reading items...\" }
        - 'clear chat' -> { \"action\": \"clear_chat\", \"message\": \"Chat cleared.\" }

        DASHBOARD TOOLS:
        - 'summarize [X]' -> { \"action\": \"tool\", \"tool_type\": \"click_match\", \"container_selector\": \".post-card\", \"target_text\": \"[X]\", \"trigger_selector\": \".btn-summarize\", \"message\": \"Summarizing...\" }
        - 'explain [X]' -> { \"action\": \"tool\", \"tool_type\": \"click_match\", \"container_selector\": \".post-card\", \"target_text\": \"[X]\", \"trigger_selector\": \".btn-explain\", \"message\": \"Explaining...\" }
        - 'local news' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-localize-news\", \"message\": \"Generating local news...\" }

        BALLOT LIST TOOLS:
        - 'open [X]' / 'view [X]' -> { \"action\": \"tool\", \"tool_type\": \"click_match\", \"container_selector\": \".ballot-card\", \"target_text\": \"[X]\", \"trigger_selector\": \".btn-view-decoder\", \"message\": \"Opening [X]...\" }

        BALLOT DETAIL TOOLS:
        - 'read patois' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-audio-patois\", \"message\": \"Playing Patois...\" }
        - 'read summary' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-audio-summary\", \"message\": \"Reading summary...\" }
        - 'vote yes' / 'read yes' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-audio-yes\", \"message\": \"Reading Yes implications...\" }
        - 'vote no' / 'read no' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-audio-no\", \"message\": \"Reading No implications...\" }
        - 'read official' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-audio-official\", \"message\": \"Reading legal text...\" }
        - 'translate to [Lang]' -> { \"action\": \"tool\", \"tool_type\": \"set_value\", \"selector\": \"#languageSelector\", \"value\": \"[Lang]\", \"message\": \"Translating...\" }
        - 'ask bot' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-ask-bot\", \"message\": \"Opening chat...\" }

        CANDIDATE LIST TOOLS:
        - 'view [Name]' / 'open [Name]' -> { \"action\": \"tool\", \"tool_type\": \"click_match\", \"container_selector\": \".candidate-card\", \"target_text\": \"[Name]\", \"trigger_selector\": \".btn-view-stances\", \"message\": \"Opening profile for [Name]...\" }
        - 'add candidate' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-add-candidate\", \"message\": \"Opening creation form...\" }
        - 'compare' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-compare-candidates\", \"message\": \"Go to comparison tool...\" }

        CANDIDATE PROFILE TOOLS:
        - 'research [Topic]' / 'deep dive [Topic]' -> { \"action\": \"tool\", \"tool_type\": \"click_match\", \"container_selector\": \".stance-card\", \"target_text\": \"[Topic]\", \"trigger_selector\": \".btn-research-stance\", \"message\": \"Researching [Topic]...\" }
        - 'read [Topic]' -> { \"action\": \"tool\", \"tool_type\": \"click_match\", \"container_selector\": \".stance-card\", \"target_text\": \"[Topic]\", \"trigger_selector\": \".btn-read-stance\", \"message\": \"Reading stance on [Topic]...\" }
        - 'read summary' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-read-summary\", \"message\": \"Reading summary...\" }
        - 'analyze' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-analyze-profile\", \"message\": \"Running initial analysis...\" }
        - 'translate to [Lang]' -> { \"action\": \"tool\", \"tool_type\": \"set_value\", \"selector\": \"#langSelector\", \"value\": \"[Lang]\", \"message\": \"Translating profile...\" }
        - 'chat' / 'talk to candidate' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#btn-ask-candidate\", \"message\": \"Opening chat...\" }

        COMPARE PAGE TOOLS:
        - 'select [Name]' -> { \"action\": \"tool\", \"tool_type\": \"click_match\", \"container_selector\": \".selection-card\", \"target_text\": \"[Name]\", \"trigger_selector\": \".candidate-checkbox\", \"message\": \"Selecting [Name]...\" }
        - 'run analysis' / 'compare now' -> { \"action\": \"tool\", \"tool_type\": \"click\", \"selector\": \"#compare-btn\", \"message\": \"Comparing...\" }
        ";

        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $systemMessage = "You are the 'Civic Guide' AI. You control the website UI.
            IMPORTANT: You must output your response in valid JSON format.

            SITE MAP: " . json_encode($siteMap) . "
            TOOLS: " . $toolsInstructions . "

            INSTRUCTIONS:
            1. If the user wants to GO somewhere, check the SITE MAP.
               Return: { \"action\": \"redirect\", \"target\": \"/exact/url\", \"message\": \"Navigating...\" }
            2. If the user wants to DO something (read, click, translate), check the TOOLS list.
               Return: { \"action\": \"tool\", \"tool_type\": \"...\", \"selector\": \"...\", \"message\": \"...\" }
               If it's a 'click_match', fill in 'container_selector', 'target_text', and 'trigger_selector'.
            3. If general chat, return: { \"action\": \"message\", \"message\": \"...\" }
            ";

            $response = Http::timeout(30)->withHeaders([
                'api-key' => $apiKey, 'Content-Type' => 'application/json'
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $command],
                ],
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->failed()) {
                Log::error("AI Navigator Azure Error: Status " . $response->status() . " - " . $response->body());
                return response()->json(['action' => 'message', 'message' => 'My brain is unreachable right now.']);
            }

            $aiData = json_decode($response->json('choices.0.message.content'), true);

            if (!$aiData) {
                return response()->json(['action' => 'message', 'message' => 'I got confused.']);
            }

            // Failsafe for redirect URL formatting
            if (isset($aiData['action']) && $aiData['action'] === 'redirect') {
                if (!str_starts_with($aiData['target'], '/')) {
                    $aiData['target'] = '/' . $aiData['target'];
                }
            }

            return response()->json($aiData);

        } catch (\Exception $e) {
            Log::error("AI Navigator System Error: " . $e->getMessage());
            return response()->json(['action' => 'message', 'message' => 'System error.']);
        }
    }
}
