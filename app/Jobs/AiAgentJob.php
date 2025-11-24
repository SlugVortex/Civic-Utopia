<?php

namespace App\Jobs;

use App\Events\CommentCreated;
use App\Models\Post;
use App\Models\User;
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

    protected Post $post;
    protected string $userQuestion;
    protected User $historianUser;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post, string $userQuestion)
    {
        $this->post = $post;
        // Clean up the mention from the question
        $this->userQuestion = trim(str_ireplace('@Historian', '', $userQuestion));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("[AiAgentJob] Starting for Post ID: {$this->post->id}. Question: '{$this->userQuestion}'");

        try {
            // Find our bot user, or fail gracefully
            $this->historianUser = User::where('email', 'historian@civicutopia.bot')->firstOrFail();

            // === 1. RETRIEVE: Query Azure AI Search ===
            $contextSnippets = $this->retrieveContextFromSearch();
            if (empty($contextSnippets)) {
                $this->postBotComment("I couldn't find any relevant information in our documents to answer that question.");
                return;
            }

            // === 2. AUGMENT: Build the Prompt ===
            $prompt = $this->buildAugmentedPrompt($contextSnippets);

            // === 3. GENERATE: Query Azure OpenAI ===
            $aiAnswer = $this->generateAnswerWithAI($prompt);

            // === 4. RESPOND: Post the Answer as a Comment ===
            $this->postBotComment($aiAnswer);

        } catch (\Exception $e) {
            Log::error("[AiAgentJob] FAILED for Post ID: {$this->post->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Optionally, post an error message to the user
            if (isset($this->historianUser)) {
                $this->postBotComment("Sorry, I encountered an error while trying to find an answer. Please try again later.");
            }
        }
    }

    /**
     * Searches the Azure AI index for relevant context.
     * @return array
     */
    private function retrieveContextFromSearch(): array
    {
        $searchEndpoint = env('AZURE_AI_SEARCH_ENDPOINT');
        $searchApiKey = env('AZURE_AI_SEARCH_API_KEY');
        $searchIndexName = env('AZURE_AI_SEARCH_INDEX_NAME');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'api-key' => $searchApiKey,
        ])->post("{$searchEndpoint}/indexes/{$searchIndexName}/docs/search?api-version=2021-04-30-Preview", [
            'search' => $this->userQuestion,
            'select' => 'content',
            'top' => 3, // Get the top 3 most relevant snippets
        ]);

        $response->throw(); // Throw an exception if the request fails

        $results = $response->json('value');
        Log::info("[AiAgentJob] Retrieved " . count($results) . " context snippets from AI Search.");

        return array_column($results, 'content');
    }

    /**
     * Builds the final prompt for the language model.
     * @param array $contextSnippets
     * @return string
     */
    private function buildAugmentedPrompt(array $contextSnippets): string
    {
        $contextString = implode("\n\n---\n\n", $contextSnippets);

        return <<<PROMPT
You are 'The Historian', an AI assistant on the CivicUtopia platform. Your purpose is to answer user questions based *only* on the provided context from official documents. Do not use any outside knowledge. If the context does not contain the answer, say that you couldn't find the information in the available documents.

**Context from Documents:**
{$contextString}

**User's Question:**
{$this->userQuestion}

**Answer:**
PROMPT;
    }

    /**
     * Sends the prompt to Azure OpenAI and gets a generated answer.
     * @param string $prompt
     * @return string
     */
    private function generateAnswerWithAI(string $prompt): string
    {
        $apiKey = env('AZURE_AI_API_KEY');
        $apiEndpoint = env('AZURE_AI_PROJECT_ENDPOINT');
        $deploymentName = env('AZURE_AI_MODEL_DEPLOYMENT_NAME');
        $apiVersion = env('AZURE_API_VERSION');
        $requestUrl = "{$apiEndpoint}openai/deployments/{$deploymentName}/chat/completions?api-version={$apiVersion}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'api-key' => $apiKey,
        ])->post($requestUrl, [
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 300, // Limit the response length
            'temperature' => 0.2, // Make the response more deterministic and factual
        ]);

        $response->throw();

        $answer = $response->json('choices.0.message.content');
        Log::info("[AiAgentJob] Generated AI answer successfully.");

        return trim($answer);
    }

    /**
     * Creates and broadcasts the bot's comment.
     * @param string $content
     */
    private function postBotComment(string $content): void
    {
        $comment = $this->post->comments()->create([
            'user_id' => $this->historianUser->id,
            'content' => $content,
        ]);
        $comment->load('user'); // Eager load the user for the broadcast payload

        // Broadcast the AI's comment so it appears in real-time
        CommentCreated::dispatch($comment);
        Log::info("[AiAgentJob] Dispatched CommentCreated event for the bot's response.");
    }
}
