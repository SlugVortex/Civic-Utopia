<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PoliticalInterviewService;
use App\Services\AzureSpeechService;
use Illuminate\Support\Facades\Log;

class PoliticalInterviewController extends Controller
{
    public function index()
    {
        return view('pages.political-interview');
    }

    public function chat(Request $request, PoliticalInterviewService $agent)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'array',
            'age_level' => 'required|integer|min:1|max:100',
            'lat' => 'nullable|numeric',
            'lon' => 'nullable|numeric',
        ]);

        $response = $agent->processInteraction(
            $request->input('history', []),
            $request->input('message'),
            $request->input('lat'),
            $request->input('lon'),
            $request->input('age_level')
        );

        return response()->json([
            'response' => $response
        ]);
    }

    public function speech(Request $request, AzureSpeechService $speechService)
    {
        $request->validate(['text' => 'required|string']);

        $audioData = $speechService->textToSpeech($request->input('text'));

        if (!$audioData) {
            return response()->json(['error' => 'Speech generation failed'], 500);
        }

        return response($audioData, 200)
            ->header('Content-Type', 'audio/mpeg');
    }
}
