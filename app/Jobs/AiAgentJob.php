<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use App\Services\BingSearchService;
use App\Events\CommentCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;
    protected $userComment;
    protected $botName;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post, string $userComment, string $botName)
    {
        $this->post = $post;
        $this->userComment = $userComment;
        $this->botName = $botName; // e.g., "FactChecker"
    }

    /**
     * Execute the job.
     */
    public function handle(BingSearchService $bing)
    {
        Log::info("[AiAgentJob] Waking up {$this->botName} for Post ID: {$this->post->id}");

        // 1. Find the Bot User (Mapping names to emails from Seeder)
        $emailMap = [
            'FactChecker' => 'agent_factchecker@civicutopia.ai',
            'Historian' => 'agent_historian@civicutopia.ai',
            'DevilsAdvocate' => 'agent_advocate@civicutopia.ai',
            'Analyst' => 'agent_analyst@civicutopia.ai',
        ];

        $email = $emailMap[$this->botName] ?? 'agent_factchecker@civicutopia.ai';
        $botUser = User::where('email', $email)->first();

        if (!$botUser) {
            Log::error("[AiAgentJob] Bot user not found for: {$this->botName}. Run the AiAgentUserSeeder!");
            return;
        }

        // 2. Gather Context
        $context = "Original Post: \"{$this->post->content}\"\n\nUser Question/Comment: \"{$this->userComment}\"";

        // 3. Research Phase (Bing Search)
        // FactChecker, Analyst, and Historian benefit from external data.
        $searchResults = "";
        if (in_array($this->botName, ['FactChecker', 'Analyst', 'Historian'])) {
            $query = $this->generateSearchQuery();
            Log::info("[AiAgentJob] Searching Bing for: $query");

            $results = $bing->searchWeb($query, 5);
            foreach ($results as $item) {
                $searchResults .= "- " . ($item['description'] ?? '') . "\n";
            }
        }

        // 4. Generate Response (Azure OpenAI)
        $replyText = $this->generateAiResponse($context, $searchResults);

        // 5. Post Comment
        if ($replyText) {
            $comment = $this->post->comments()->create([
                'user_id' => $botUser->id,
                'content' => $replyText,
            ]);

            $comment->load('user');
            CommentCreated::dispatch($comment);

            Log::info("[AiAgentJob] {$this->botName} successfully replied.");
        }
    }

    private function generateSearchQuery()
    {
        // Simple keyword extraction
        $cleanContent = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $this->post->content), 0, 100);
        return "Jamaica " . $cleanContent . " facts statistics history news";
    }

    private function generateAiResponse($context, $searchResults)
    {
        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            // Personas
            $personas = [
                'FactChecker' => "You are a strict Fact Checker. Verify the post using the search results. Be polite but firm. Cite sources.",
                'Historian' => "You are a Jamaican Historian. Connect the topic to Jamaica's history (1960s-2020s).",
                'DevilsAdvocate' => "You are a Devil's Advocate. Offer a counter-argument or alternative perspective to foster debate. Be respectful.",
                'Analyst' => "You are a Data Analyst. Use the search results to provide numbers, percentages, or economic data.",
            ];

            $systemPrompt = $personas[$this->botName] ?? "You are a helpful AI assistant.";

            if ($searchResults) {
                $systemPrompt .= "\n\nUSE THESE REAL-TIME SEARCH RESULTS:\n" . $searchResults;
            }

            $response = Http::withHeaders([
                'api-key' => $apiKey, 'Content-Type' => 'application/json'
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $context],
                ],
                'temperature' => 0.7,
                'max_tokens' => 300,
            ]);

            return $response->json('choices.0.message.content');

        } catch (\Exception $e) {
            Log::error("[AiAgentJob] OpenAI Error: " . $e->getMessage());
            return null;
        }
    }
}
