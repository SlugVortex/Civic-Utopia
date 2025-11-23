<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        try {
            $post = $request->user()->posts()->create($request->validated());

            // Load the user relationship explicitly before broadcasting
            $post->load('user');

            Log::info('[CivicUtopia] New post created by User ID: ' . $request->user()->id . '. Firing PostCreated event.');

            // Fire the event to broadcast the new post
            PostCreated::dispatch($post);

            return redirect()->back()->with('status', 'Post created successfully!');
        } catch (\Exception $e) {
            Log::error('[CivicUtopia] Failed to create post.', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'There was an error creating your post.');
        }
    }
}
