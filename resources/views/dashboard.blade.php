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
                    <!-- ... (Post Creation Form remains the same) ... -->
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
                                        <p class="mt-2 mb-0 post-content" style="white-space: pre-wrap;">{{ $post->content }}</p>

                                        {{-- UPDATED: Added Summarize button --}}
                                        <div class="mt-3">
                                            <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-outline-secondary">View Comments</a>
                                            <button class="btn btn-sm btn-outline-primary btn-summarize" data-post-id="{{ $post->id }}">
                                                <i class="ri-sparkling-2-line me-1"></i> Summarize
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

<!-- Modal for displaying the summary -->
<div class="modal fade" id="summaryModal" tabindex="-1" aria-labelledby="summaryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="summaryModalLabel">AI Summary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="summary-content">Loading...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
{{-- ... (The style and script for real-time posts remain the same) ... --}}
<style>
    /* ... existing styles ... */
</style>
<script>
    // ... (existing script for real-time posts) ...

    // --- NEW JAVASCRIPT FOR AI SUMMARIZER ---
    document.addEventListener('DOMContentLoaded', function () {
        const summaryModal = new bootstrap.Modal(document.getElementById('summaryModal'));
        const summaryContent = document.getElementById('summary-content');

        // Use event delegation to handle clicks on buttons that might not exist yet
        document.getElementById('post-feed-container').addEventListener('click', function (event) {
            if (event.target.classList.contains('btn-summarize')) {
                handleSummarizeClick(event.target);
            }
        });

        async function handleSummarizeClick(button) {
            const postId = button.dataset.postId;
            const postElement = document.getElementById(`post-${postId}`);
            const postContent = postElement.querySelector('.post-content').innerText;

            // Show the modal and set loading state
            summaryContent.textContent = 'Generating summary, please wait...';
            summaryModal.show();
            button.disabled = true;

            try {
                const response = await fetch(`/posts/${postId}/summarize`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ content: postContent })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                }

                const data = await response.json();
                summaryContent.textContent = data.summary;

            } catch (error) {
                console.error('Error fetching summary:', error);
                summaryContent.textContent = 'Sorry, we could not generate a summary at this time.';
            } finally {
                button.disabled = false; // Re-enable the button
            }
        }
    });
</script>
@endpush
