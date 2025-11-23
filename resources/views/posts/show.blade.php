@php
$pageConfigs = ['myLayout' => 'vertical'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'View Post')

@section('content')
<div class="container-fluid">
    <h4 class="py-3 mb-4">
      <span class="text-muted fw-light"><a href="{{ route('dashboard') }}">Town Square</a> /</span> View Post
    </h4>

    {{-- The Main Post --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="fw-bold mb-0">{{ $post->user->name }}</p>
                <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
            </div>
            <p class="mt-2 mb-0" style="white-space: pre-wrap;">{{ $post->content }}</p>
        </div>
    </div>

    {{-- Comment Submission Form --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-4">Leave a Comment</h5>
            <form action="{{ route('comments.store', $post) }}" method="POST">
                @csrf
                <textarea name="content" rows="3" class="form-control mb-3" placeholder="Share your thoughts..."></textarea>
                @error('content')
                    <p class="text-danger text-sm mt-1">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn btn-primary">Post Comment</button>
            </form>
        </div>
    </div>

    {{-- Comments Section --}}
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">Comments ({{ $post->comments->count() }})</h5>
            <div id="comment-feed-container">
                @forelse ($post->comments as $comment)
                    <div class="p-3 mb-3 border rounded" id="comment-{{ $comment->id }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="fw-bold mb-0">{{ $comment->user->name }}</p>
                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mt-1 mb-0" style="white-space: pre-wrap;">{{ $comment->content }}</p>
                    </div>
                @empty
                    <div id="no-comments-message" class="text-center text-muted p-4">
                        <p>No comments yet. Be the first to share your thoughts!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<style>
    .comment-flash {
        animation: flash-bg 2s ease-out;
    }
    @keyframes flash-bg {
        from { background-color: #e0f2fe; }
        to { background-color: transparent; }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const commentFeedContainer = document.getElementById('comment-feed-container');
        const noCommentsMessage = document.getElementById('no-comments-message');
        const postId = {{ $post->id }};
        const privateChannelName = `posts.${postId}`;

        console.log(`[CivicUtopia] DOM loaded. Initializing Echo listener for private channel: ${privateChannelName}`);

        window.Echo.private(privateChannelName)
            .listen('CommentCreated', (event) => {
                console.log('[CivicUtopia] Received Broadcast Event: CommentCreated', event);

                if (noCommentsMessage) {
                    noCommentsMessage.style.display = 'none';
                }

                const newCommentHtml = createCommentHtml(event.comment);
                commentFeedContainer.insertAdjacentHTML('afterbegin', newCommentHtml);

                const newCommentElement = document.getElementById(`comment-${event.comment.id}`);
                if (newCommentElement) {
                    newCommentElement.classList.add('comment-flash');
                }
            });

        function createCommentHtml(comment) {
            return `
                <div class="p-3 mb-3 border rounded" id="comment-${comment.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="fw-bold mb-0">${comment.user.name}</p>
                        <small class="text-muted">just now</small>
                    </div>
                    <p class="mt-1 mb-0" style="white-space: pre-wrap;">${escapeHtml(comment.content)}</p>
                </div>
            `;
        }
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
    });
</script>
@endpush
