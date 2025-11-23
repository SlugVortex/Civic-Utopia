<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Display the search page.
     */
    public function index(Request $request)
    {
        return view('search.index', ['results' => [], 'query' => '']);
    }

    /**
     * Perform a search against the Azure AI Search index.
     */
    public function search(Request $request)
    {
        $request->validate(['query' => 'required|string|max:200']);
        $query = $request->input('query');
        Log::info('[SearchController] Performing search for: ' . $query);

        try {
            $searchEndpoint = env('AZURE_AI_SEARCH_ENDPOINT');
            $searchApiKey = env('AZURE_AI_SEARCH_API_KEY');
            $searchIndexName = env('AZURE_AI_SEARCH_INDEX_NAME');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $searchApiKey,
            ])->post("{$searchEndpoint}/indexes/{$searchIndexName}/docs/search?api-version=2021-04-30-Preview", [
                'search' => $query,
                'select' => 'content, sourcefile', // We now ask for the sourcefile
                'top' => 5,
            ]);

            if ($response->failed()) {
                $response->throw();
            }

            $results = $response->json('value');
            Log::info('[SearchController] Search successful.', ['result_count' => count($results)]);

            return view('search.index', ['results' => $results, 'query' => $query]);

        } catch (\Exception $e) {
            Log::error('[SearchController] Search request failed.', ['error' => $e->getMessage()]);
            return back()->with('error', 'The search could not be completed. Error: ' . $e->getMessage());
        }
    }
}
