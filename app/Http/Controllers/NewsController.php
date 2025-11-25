<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\FetchCivicNewsJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    /**
     * Trigger hyper-localized news fetch
     */
    public function fetchLocal(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ]);

        $lat = $request->input('lat');
        $lon = $request->input('lon');
        $userId = Auth::id();

        Log::info("[NewsController] 📍 User $userId requesting hyper-local news at ($lat, $lon)");

        // Dispatch to queue (async processing)
        // IMPORTANT: Make sure you're running: php artisan queue:work
        FetchCivicNewsJob::dispatch($lat, $lon, $userId);

        return response()->json([
            'status' => 'success',
            'message' => '🤖 Our AI Agents are finding hyper-local news for your area and creating stunning visualizations. This takes 30-90 seconds on mobile data. Refresh in a moment!',
            'location' => [
                'lat' => $lat,
                'lon' => $lon
            ],
            'estimated_wait' => '60-90 seconds'
        ]);
    }
}
