<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use App\Models\Media;
use App\Services\NewsAgentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
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
        set_time_limit(900);

        Log::info("[CivicNewsJob] 🚀 Starting hyper-local fetch for User {$this->requestingUserId} at ({$this->lat}, {$this->lon})");

        $aiUser = User::firstOrCreate(
            ['email' => 'agent@civicutopia.ai'],
            [
                'name' => 'Civic AI Agent',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $newsItems = $agent->searchNews($this->lat, $this->lon);

        if (empty($newsItems)) {
            Log::warning("[CivicNewsJob] ⚠️ No localized news found for coordinates");
            return;
        }

        Log::info("[CivicNewsJob] 📰 Processing " . count($newsItems) . " news items");

        $count = 0;
        $maxPosts = 3;

        // Get existing titles from last 24 hours only
        $existingTitles = Post::where('user_id', $aiUser->id)
            ->where('created_at', '>', now()->subHours(24))
            ->pluck('content')
            ->map(function($content) {
                // Extract title between ** markers
                if (preg_match('/\*\*(.*?)\*\*/', $content, $matches)) {
                    return trim($matches[1]);
                }
                return null;
            })
            ->filter()
            ->toArray();

        Log::info("[CivicNewsJob] 📋 Found " . count($existingTitles) . " posts in last 24 hours");

        foreach ($newsItems as $item) {
            if ($count >= $maxPosts) {
                Log::info("[CivicNewsJob] ✋ Reached limit of {$maxPosts} posts");
                break;
            }

            $title = $item['name'] ?? $item['title'] ?? null;
            if (!$title || strlen($title) < 10) continue;

            // SIMPLE CHECK: Skip if exact title exists
            if (in_array($title, $existingTitles)) {
                Log::info("[CivicNewsJob] ♻️ Skipping exact duplicate: " . Str::limit($title, 40));
                continue;
            }

            // AI Analysis
            Log::info("[CivicNewsJob] 🧠 Analyzing: " . Str::limit($title, 40));
            $analysis = $agent->analyzeNewsContent($title);

            $aiSummary = is_array($analysis['summary']) ?
                implode(' ', $analysis['summary']) : $analysis['summary'];

            $visualKeywords = is_array($analysis['keywords']) ?
                implode(' ', $analysis['keywords']) : $analysis['keywords'];

            $url = $item['url'] ?? '#';
            $source = $item['provider'][0]['name'] ?? $item['source'] ?? 'News Source';

            $content = "📰 **{$title}**\n\n{$aiSummary}\n\n_📍 Local to your area • Source: {$source}_\n\n[Read Full Article →]($url)";

            // Image generation
            $imageFilename = null;

            if (config('services.azure_dalle.endpoint')) {
                Log::info("[CivicNewsJob] 🎨 Attempting DALL-E for: " . Str::limit($visualKeywords, 30));
                $imageFilename = $agent->generateImage("News photo: " . $visualKeywords);

                if ($imageFilename) {
                    Log::info("[CivicNewsJob] ✅ DALL-E succeeded!");
                }
            }

            if (!$imageFilename) {
                Log::info("[CivicNewsJob] 🔍 Searching free sources for: " . Str::limit($visualKeywords, 30));
                $imageFilename = $agent->findCivicImage($visualKeywords, $item);
            }

            // Create post
            $post = Post::create([
                'user_id' => $aiUser->id,
                'content' => $content,
            ]);

            if ($imageFilename) {
                Media::create([
                    'user_id' => $aiUser->id,
                    'post_id' => $post->id,
                    'path' => 'post_media/' . $imageFilename,
                    'file_type' => 'image',
                    'mime_type' => 'image/jpeg',
                    'disk' => 'public'
                ]);
                $postNum = $count + 1;
                Log::info("[CivicNewsJob] ✅ Post #{$postNum} created WITH image");
            } else {
                $postNum = $count + 1;
                Log::warning("[CivicNewsJob] ⚠️ Post #{$postNum} created WITHOUT image");
            }

            // Add to existing titles array so we don't create duplicates in this run
            $existingTitles[] = $title;

            $count++;

            if ($count < $maxPosts) {
                sleep(1);
            }
        }

        Log::info("[CivicNewsJob] 🎉 Completed! Created {$count} localized posts");
    }

    public function failed(\Throwable $exception)
    {
        Log::error("[CivicNewsJob] ❌ Job failed for User {$this->requestingUserId}: " . $exception->getMessage());
    }
}
