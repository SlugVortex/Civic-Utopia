<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // <-- IMPORTANT
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// Must implement ShouldBroadcast for Pusher to work
class CommentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;

    /**
     * Create a new event instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the channels the event should broadcast on.
     * We broadcast on a public channel named 'comments' for simplicity.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('comments'),
        ];
    }

    /**
     * Data to send to the client.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
            'content' => $this->comment->content,
            'created_at_human' => $this->comment->created_at->diffForHumans(),
            'user' => [
                'id' => $this->comment->user->id,
                'name' => $this->comment->user->name,
                // Use UI Avatars if no photo exists, or your DB column
                'avatar_url' => $this->comment->user->profile_photo_path ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->comment->user->name) . '&background=random',
            ]
        ];
    }
}
