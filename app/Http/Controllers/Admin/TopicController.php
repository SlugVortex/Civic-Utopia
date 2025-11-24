<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $topics = Topic::withCount('posts')->orderBy('name')->get();
        Log::info('[TopicController] Displaying all topics for admin.');
        return view('admin.topics.index', compact('topics'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:topics,name|max:255',
            'icon' => 'required|string|max:255',
            'color' => 'required|string|max:255',
        ]);

        try {
            $topic = Topic::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'icon' => $validated['icon'],
                'color' => $validated['color'],
            ]);

            Log::info('[TopicController] New topic created successfully.', ['topic_id' => $topic->id, 'name' => $topic->name]);

            return redirect()->route('admin.topics.index')->with('success', 'Topic created successfully!');
        } catch (\Exception $e) {
            Log::error('[TopicController] Failed to create topic.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'There was an error creating the topic.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Topic $topic)
    {
        try {
            $topicName = $topic->name;
            $topic->posts()->detach(); // Detach from all posts
            $topic->delete();

            Log::info('[TopicController] Topic deleted successfully.', ['topic_name' => $topicName]);

            return redirect()->route('admin.topics.index')->with('success', 'Topic deleted successfully!');
        } catch (\Exception $e) {
            Log::error('[TopicController] Failed to delete topic.', [
                'topic_id' => $topic->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'There was an error deleting the topic.');
        }
    }
}
