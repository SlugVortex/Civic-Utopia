<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Comment;
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
    protected $triggerComment;
    protected $botName;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post, Comment $triggerComment, string $botName)
    {
        $this->post = $post;
        $this->triggerComment = $triggerComment;
        $this->botName = $botName;
    }

    /**
     * Execute the job.
     */
    public function handle(BingSearchService $bing)
    {
        Log::info("[AiAgentJob] Waking up {$this->botName} for Post ID: {$this->post->id}");

        // 1. Find the Bot User
        $emailMap = [
            'FactChecker' => 'agent_factchecker@civicutopia.ai',
            'Historian' => 'agent_historian@civicutopia.ai',
            'DevilsAdvocate' => 'agent_advocate@civicutopia.ai',
            'Analyst' => 'agent_analyst@civicutopia.ai',
        ];

        $email = $emailMap[$this->botName] ?? 'agent_factchecker@civicutopia.ai';
        $botUser = User::where('email', $email)->first();

        if (!$botUser) {
            Log::error("[AiAgentJob] Bot user not found for: {$this->botName}.");
            return;
        }

        // 2. Gather Context
        $userQuestion = $this->triggerComment->content;
        // Clean up question to remove the trigger tag for search purposes
        $cleanQuestion = str_ireplace("@{$this->botName}", '', $userQuestion);

        $context = "Original Post: \"{$this->post->content}\"\n\nUser Question: \"{$cleanQuestion}\"";

        // 3. Research Phase
        $searchResults = "";
        if (in_array($this->botName, ['FactChecker', 'Analyst', 'Historian'])) {
            $query = $this->generateSearchQuery($cleanQuestion);
            Log::info("[AiAgentJob] Searching Bing for: $query");

            $results = $bing->searchWeb($query, 5);
            foreach ($results as $item) {
                $searchResults .= "- " . ($item['description'] ?? '') . "\n";
            }
        }

        // 4. Generate Response
        $aiResponse = $this->generateAiResponse($context, $searchResults);

        // 5. Post Comment (With Reply Context)
        if ($aiResponse) {
            // Create a quote string to visually reply to the user
            // Limit the quote length
            $quoteText = mb_substr($userQuestion, 0, 80) . (mb_strlen($userQuestion) > 80 ? '...' : '');
            // Format: > **User**: Question \n\n Answer
            $finalContent = "> **{$this->triggerComment->user->name}**: {$quoteText}\n\n{$aiResponse}";

            $comment = $this->post->comments()->create([
                'user_id' => $botUser->id,
                'content' => $finalContent,
            ]);

            $comment->load('user');
            CommentCreated::dispatch($comment);

            Log::info("[AiAgentJob] {$this->botName} replied.");
        }
    }

    private function generateSearchQuery($text)
    {
        // Basic keyword extraction: take first 10 words + "Jamaica"
        $words = explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', $text));
        $keywords = implode(' ', array_slice($words, 0, 10));
        return "Jamaica " . $keywords . " facts statistics history";
    }

    private function generateAiResponse($context, $searchResults)
    {
        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $personas = [
                'FactChecker' => "You are a strict Fact Checker. Verify the user's claim using the search results. Be polite but firm.",
                'Historian' => "You are a Jamaican Historian. Connect the topic to Jamaica's history (1960s-2020s).",
                'DevilsAdvocate' => "You are a Devil's Advocate. Offer a counter-argument or alternative perspective. Be respectful.",
                'Analyst' => "You are a Data Analyst. Use the search results to provide numbers and statistics.",
            ];

            $systemPrompt = $personas[$this->botName] ?? "You are a helpful AI assistant.";

            if ($searchResults) {
                $systemPrompt .= "\n\nUSE THESE SEARCH RESULTS:\n" . $searchResults;
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
