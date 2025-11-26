<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Topic;
use App\Models\Poll;
use App\Models\Suggestion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the Admin Command Center.
     */
    public function index()
    {
        // 1. Basic Stats
        $totalUsers = User::count();
        $totalPosts = Post::count();
        $postsToday = Post::whereDate('created_at', Carbon::today())->count();

        // 2. Chart Data: Posts over last 7 days
        $chartData = $this->getPostActivityChart();

        // 3. Chart Data: Posts per Topic
        $topicDistribution = Topic::withCount('posts')->get()->map(function($t) {
            return ['name' => $t->name, 'count' => $t->posts_count];
        });

        // 4. Admin Features
        $activePolls = Poll::with('options.votes')->where('is_active', true)->get();
        $pendingSuggestions = Suggestion::with('user', 'votes')->where('status', 'pending')->latest()->get();

        // 5. AI Insights (Cached for 1 hour to save API credits)
        $aiAnalysis = Cache::remember('admin_ai_analysis', 3600, function () {
            return $this->performAiAnalysis();
        });

        Log::info('[AdminDashboard] Dashboard loaded by user: ' . auth()->id());

        return view('admin.dashboard.index', compact(
            'totalUsers',
            'totalPosts',
            'postsToday',
            'chartData',
            'topicDistribution',
            'activePolls',
            'pendingSuggestions',
            'aiAnalysis'
        ));
    }

    /**
     * Create a new poll.
     */
    public function storePoll(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:100'
        ]);

        $poll = Poll::create([
            'question' => $request->question,
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        foreach ($request->options as $optionLabel) {
            if(!empty($optionLabel)) {
                $poll->options()->create(['label' => $optionLabel]);
            }
        }

        Log::info('[AdminDashboard] New poll created: ' . $poll->id);

        return redirect()->back()->with('success', 'Poll created successfully.');
    }

    /**
     * Approve or Reject a user suggestion.
     */
    public function updateSuggestion(Request $request, Suggestion $suggestion)
    {
        $request->validate(['status' => 'required|in:approved,rejected']);
        $suggestion->update(['status' => $request->status]);

        Log::info('[AdminDashboard] Suggestion ' . $suggestion->id . ' updated to ' . $request->status);

        return redirect()->back()->with('success', 'Suggestion status updated.');
    }

    /**
     * Helper for Chart.js data
     */
    private function getPostActivityChart()
    {
        $days = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $days[] = Carbon::now()->subDays($i)->format('M d');
            $counts[] = Post::whereDate('created_at', $date)->count();
        }

        return ['labels' => $days, 'data' => $counts];
    }

    /**
     * Uses Azure OpenAI to analyze recent posts for trends.
     */
    private function performAiAnalysis()
    {
        // Fetch last 50 posts for context
        $recentPosts = Post::latest()->take(50)->pluck('content')->join("\n---\n");

        if (empty($recentPosts)) {
            return [
                'sentiment' => 'Neutral',
                'concerns' => ['Not enough data yet.'],
                'summary' => 'Waiting for user activity.'
            ];
        }

        $endpoint = config('services.azure.openai.endpoint');
        $apiKey = config('services.azure.openai.api_key');
        $deployment = config('services.azure.openai.deployment');
        $apiVersion = config('services.azure.openai.api_version');

        $url = "{$endpoint}/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

        try {
            Log::info('[AdminDashboard] Contacting Azure OpenAI for analysis...');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an AI Analyst for a Civic Engagement Platform. Analyze the following user posts. Return a JSON object with: 1. "sentiment" (Positive, Negative, Neutral, or Heated), 2. "concerns" (Array of top 3 recurring topics/complaints), 3. "summary" (A 2 sentence summary of the community vibe).'],
                    ['role' => 'user', 'content' => $recentPosts],
                ],
                'max_tokens' => 300,
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object'] // Force JSON
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                return json_decode($content, true);
            } else {
                Log::error('[AdminDashboard] AI Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('[AdminDashboard] AI Analysis failed: ' . $e->getMessage());
        }

        return [
            'sentiment' => 'Unknown',
            'concerns' => ['AI Service Unavailable'],
            'summary' => 'Could not generate analysis at this time.'
        ];
    }
}
