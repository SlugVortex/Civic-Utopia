{{-- Use the same layout as the other template pages for consistency --}}
@extends('layouts/layoutMaster')

@section('title', 'Digital Town Square')

@section('content')
<div class="container-fluid">
    <h4 class="py-3 mb-4">
        Digital Town Square
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    <!-- Post Creation Form -->
                    <div class="mb-6 card p-4 shadow-sm">
                        <h5 class="card-title mb-4">Create a New Post</h5>
                        <form action="{{ route('posts.store') }}" method="POST">
                            @csrf
                            <textarea
                                name="content"
                                rows="3"
                                class="form-control mb-3"
                                placeholder="What's on your mind, {{ Auth::user()->name }}?"
                                required
                            ></textarea>
                            @error('content')
                                <p class="text-danger text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary">
                                    Post
                                </button>
                            </div>
                        </form>
                    </div>

                    <hr class="my-4">

                    <!-- Real-time Post Feed Container -->
                    <h5 class="mb-4">Community Feed</h5>
                    <div id="post-feed-container" class="space-y-4">
                        @forelse ($posts as $post)
                            <div class="card p-4 mb-3 border" id="post-{{ $post->id }}">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="fw-bold mb-0">{{ $post->user->name }}</p>
                                            <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mt-2 mb-0" style="white-space: pre-wrap;">{{ $post->content }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div id="no-posts-message" class="text-center text-muted p-5">
                                <p>No posts yet. Be the first to start a conversation!</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    /* A simple animation to highlight new posts */
    .post-flash {
        animation: flash-bg 2s ease-out;
    }

    @keyframes flash-bg {
        from { background-color: #e0f2fe; }
        to { background-color: transparent; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const postFeedContainer = document.getElementById('post-feed-container');
        const noPostsMessage = document.getElementById('no-posts-message');

        console.log('[CivicUtopia] DOM loaded. Initializing Echo listener for posts channel.');

        window.Echo.channel('posts')
            .listen('PostCreated', (event) => {
                console.log('[CivicUtopia] Received Broadcast Event: PostCreated', event);

                if (noPostsMessage) {
                    noPostsMessage.style.display = 'none';
                }

                const newPostHtml = createPostHtml(event.post);
                postFeedContainer.insertAdjacentHTML('afterbegin', newPostHtml);

                const newPostElement = document.getElementById(`post-${event.post.id}`);
                if (newPostElement) {
                    newPostElement.classList.add('post-flash');
                }
            });

        function createPostHtml(post) {
            return `
                <div class="card p-4 mb-3 border" id="post-${post.id}">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="fw-bold mb-0">${post.user.name}</p>
                                <small class="text-muted">just now</small>
                            </div>
                            <p class="mt-2 mb-0" style="white-space: pre-wrap;">${escapeHtml(post.content)}</p>
                        </div>
                    </div>
                </div>
            `;
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>
@endpush
