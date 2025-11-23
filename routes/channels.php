<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Post;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('posts', function () {
    return true; // Public channel for new posts
});

// --- NEW CHANNEL AUTHORIZATION ---
// Authorize users to listen on a specific post's channel.
// We'll just check if the user is authenticated.
Broadcast::channel('posts.{post}', function (User $user, Post $post) {
    return Auth::check();
});
