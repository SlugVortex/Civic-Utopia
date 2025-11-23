@php
$pageConfigs = ['myLayout' => 'vertical'];
@endphp

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
                    {{-- Post Creation Form --}}
                    <div class="mb-6 card p-4 shadow-sm">
                        <h5 class="card-title mb-4">Create a New Post</h5>
                        <form action="{{ route('posts.store') }}" method="POST">
                            @csrf
                            <textarea name="content" rows="3" class="form-control mb-3" placeholder="What's on your mind, {{ Auth::user()->name }}?" required></textarea>
                            @error('content')<p class="text-danger text-sm mt-1">{{ $message }}</p>@enderror
                            <div class="mt-2"><button type="submit" class="btn btn-primary">Post</button></div>
                        </form>
                    </div>

                    <hr class="my-4">

                    {{-- Post Feed --}}
                    <h5 class="mb-4">Community Feed</h5>
                    <div id="post-feed-container" class="space-y-4">
                        @forelse ($posts as $post)
                            {{-- 1. ADDED position-relative to the card --}}
                            <div class="card p-4 mb-3 border position-relative" id="post-{{ $post->id }}">

                                {{-- 2. MOVED DELETE BUTTON to the top right --}}
                                @if ($post->user_id === Auth::id())
                                    <form class="delete-post-form" action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                @endif

                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="fw-bold mb-0">{{ $post->user->name }}</p>
                                            <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mt-2 mb-0 post-content" style="white-space: pre-wrap;">{{ $post->content }}</p>

                                        <div class="summary-container alert alert-primary mt-3" style="display: none;">
                                            <strong class="d-block mb-1"><i class="ri-sparkling-2-line me-1"></i>AI Summary</strong>
                                            <p class="mb-0 summary-content"></p>
                                        </div>

                                        {{-- 3. REMOVED DELETE BUTTON from this action group --}}
                                        <div class="mt-3 d-flex align-items-center">
                                            <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-outline-secondary me-2">View Comments</a>
                                            <button class="btn btn-sm btn-outline-primary btn-summarize me-2" data-post-id="{{ $post->id }}">
                                                <i class="ri-sparkling-2-line me-1"></i>
                                                <span class="button-text">Summarize</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div id="no-posts-message" class="text-center text-muted p-5"><p>No posts yet.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- This section contains ALL necessary styles and scripts for the dashboard --}}
<style>
    /* Animation for new posts/comments */
    .post-flash {
        animation: flash-bg 2s ease-out;
    }
    @keyframes flash-bg {
        from { background-color: #e0f2fe; }
        to { background-color: transparent; }
    }

    /* 4. NEW CSS for the delete button positioning */
    .delete-post-form {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // --- SCRIPT FOR REAL-TIME POSTS ---
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
            });

        // 5. UPDATED JAVASCRIPT TEMPLATE to match new structure
        function createPostHtml(post) {
            const postUrl = `{{ url('/posts') }}/${post.id}`;
            const postDestroyUrl = `{{ url('/posts') }}/${post.id}`;
            const currentUserId = {{ Auth::id() }};

            let deleteForm = '';
            if (post.user_id === currentUserId) {
                deleteForm = `
                    <form class="delete-post-form" action="${postDestroyUrl}" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </form>
                `;
            }

            return `
                <div class="card p-4 mb-3 border" id="post-${post.id}">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="fw-bold mb-0">${post.user.name}</p>
                                <small class="text-muted">just now</small>
                            </div>
                            <p class="mt-2 mb-0 post-content" style="white-space: pre-wrap;">${escapeHtml(post.content)}</p>
                            <div class="summary-container alert alert-primary mt-3" style="display: none;">
                                <strong class="d-block mb-1"><i class="ri-sparkling-2-line me-1"></i>AI Summary</strong>
                                <p class="mb-0 summary-content"></p>
                            </div>
                            <div class="mt-3 d-flex align-items-center">
                                <a href="${postUrl}" class="btn btn-sm btn-outline-secondary me-2">
                                    View Comments
                                </a>
                                <button class="btn btn-sm btn-outline-primary btn-summarize me-2" data-post-id="${post.id}">
                                    <i class="ri-sparkling-2-line me-1"></i>
                                    <span class="button-text">Summarize</span>
                                </button>
                                ${deleteForm}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }


        // --- SCRIPT FOR AI SUMMARIZER & DYNAMIC BUTTONS ---
        postFeedContainer.addEventListener('click', function (event) {
            const button = event.target.closest('.btn-summarize');
            if (button) {
                handleSummarizeClick(button);
            }
        });

        async function handleSummarizeClick(button) {
            const postId = button.dataset.postId;
            const postElement = document.getElementById(`post-${postId}`);
            const summaryContainer = postElement.querySelector('.summary-container');
            const summaryContent = summaryContainer.querySelector('.summary-content');
            const buttonText = button.querySelector('.button-text');

            if (summaryContainer.style.display === 'block') {
                summaryContainer.style.display = 'none';
                buttonText.textContent = 'Summarize';
                return;
            }

            button.disabled = true;
            buttonText.textContent = 'Generating...';

            try {
                const response = await fetch(`/posts/${postId}/summarize`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                });

                if (!response.ok) { throw new Error('Network error.'); }

                const data = await response.json();
                summaryContent.textContent = data.summary;
                summaryContainer.style.display = 'block';
                buttonText.textContent = 'Hide Summary';

            } catch (error) {
                console.error('Error fetching summary:', error);
                summaryContent.textContent = 'Could not generate a summary.';
                summaryContainer.style.display = 'block';
                buttonText.textContent = 'Summarize';
            } finally {
                button.disabled = false;
            }
        }
    });
</script>
@endpush
