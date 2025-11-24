<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TopicController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Topic $topic)
    {
        Log::info('[TopicController] Showing posts for topic: ' . $topic->name);

        // Load posts for this specific topic, along with all their relations
        $posts = $topic->posts()->with('user', 'comments', 'media', 'topics')->latest()->get();

        // We also need all topics for the sidebar
        $topics = Topic::withCount('posts')->orderBy('name')->get();

        // We can reuse the dashboard view
        return view('dashboard', [
            'posts' => $posts,
            'topics' => $topics,
            'activeTopic' => $topic, // Pass the currently active topic to the view
        ]);
    }
}
