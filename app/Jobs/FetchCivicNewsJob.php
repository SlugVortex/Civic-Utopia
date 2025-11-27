<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Media;
use App\Services\NewsAgentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchCivicNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900;
    public $tries = 2;

    protected $lat;
    protected $lon;
    protected $requestingUserId;

    public function __construct($lat, $lon, $requestingUserId)
    {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->requestingUserId = $requestingUserId;
    }

    public function handle(NewsAgentService $agent)
    {
        Log::info("[CivicNewsJob] 🚀 Starting job for User ID: {$this->requestingUserId}");

        // 1. Fetch News
        $newsItems = $agent->searchNews($this->lat, $this->lon);

        if (empty($newsItems)) {
            Log::warning("[CivicNewsJob] ⚠️ No news found.");
            return;
        }

        // 2. Get Existing Titles
        $existingTitles = Post::where('user_id', $this->requestingUserId)
            ->where('created_at', '>', now()->subHours(24))
            ->get()
            ->map(function($post) {
                if (preg_match('/\*\*(.*?)\*\*/', $post->content, $matches)) {
                    return $matches[1];
                }
                return null;
            })
            ->filter()
            ->toArray();

        $count = 0;
        $maxPosts = 3;

        foreach ($newsItems as $item) {
            if ($count >= $maxPosts) break;

            $title = $item['name'] ?? $item['title'] ?? null;
            if (!$title) continue;

            if (in_array($title, $existingTitles)) {
                Log::info("[CivicNewsJob] Skipping duplicate: $title");
                continue;
            }

            // 3. Analyze
            Log::info("[CivicNewsJob] 🧠 Analyzing: " . Str::limit($title, 50));
            $analysis = $agent->analyzeNewsContent($title);

            $aiSummary = $analysis['summary'];
            $rawKeywords = $analysis['keywords'];

            // --- FIX FOR ARRAY ERROR ---
            $visualKeywords = "News about " . $title; // Default

            if (is_string($rawKeywords)) {
                $visualKeywords = $rawKeywords;
            } elseif (is_array($rawKeywords)) {
                $visualKeywords = implode(' ', $rawKeywords);
            }
            // Ensure it's clean
            $visualKeywords = strip_tags($visualKeywords);
            // ---------------------------

            $url = $item['url'] ?? '#';
            $source = $item['provider'][0]['name'] ?? 'Local Source';

            // 4. Content
            $content = "📰 **{$title}**\n\n{$aiSummary}\n\n_📍 Local Update • Source: {$source}_\n\n[Read Full Article]($url)";

            // 5. Find Image
            Log::info("[CivicNewsJob] 🎨 Finding image for: " . Str::limit($visualKeywords, 30));
            $imageFilename = $agent->findCivicImage($visualKeywords, $item);

            // 6. Save
            $post = Post::create([
                'user_id' => $this->requestingUserId,
                'content' => $content,
                'is_private' => true,
                'is_flagged' => false,
            ]);

            if ($imageFilename) {
                Media::create([
                    'user_id' => $this->requestingUserId,
                    'post_id' => $post->id,
                    'path' => 'post_media/' . $imageFilename,
                    'file_type' => 'image',
                    'mime_type' => 'image/jpeg',
                    'disk' => 'public'
                ]);
            }

            $existingTitles[] = $title;
            $count++;

            if ($count < $maxPosts) sleep(2);
        }

        Log::info("[CivicNewsJob] 🎉 Finished! Created {$count} private posts.");
    }

    public function failed(\Throwable $exception)
    {
        Log::error("[CivicNewsJob] ❌ Failed: " . $exception->getMessage());
    }
}
