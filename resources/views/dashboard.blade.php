@php
$pageConfigs = ['myLayout' => 'vertical'];
$activeTopic = $activeTopic ?? null; // Set default null if not passed
@endphp

@extends('layouts/layoutMaster')

@section('title', $activeTopic ? $activeTopic->name : 'Digital Town Square')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar Column --}}
        <div class="col-lg-3 col-md-5 order-0 order-md-0">
            <div class="sticky-sidebar pt-lg-4">
                {{-- Live Feeds Widget --}}
                <div class="card mb-4 sidebar-widget">
                    <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#liveFeeds">
                        <h6 class="mb-0 card-title"><i class="ri-radio-line me-2"></i>Live Feeds</h6>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="collapse show" id="liveFeeds">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action {{ !$activeTopic ? 'active' : '' }}">
                                <div class="d-flex align-items-center">
                                    <i class="ri-radio-2-line text-danger me-3"></i>
                                    <div>
                                        <div class="fw-semibold">All Posts</div>
                                        <small class="text-muted">Real-time updates</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Topics Widget --}}
                <div class="card sidebar-widget">
                    <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#topicsWidget">
                        <h6 class="mb-0 card-title"><i class="ri-hashtag me-2"></i>Topics</h6>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="collapse show" id="topicsWidget">
                        <div class="list-group list-group-flush">
                            @forelse ($topics as $topic)
                                <a href="{{ route('topics.show', $topic->slug) }}" class="list-group-item list-group-item-action {{ $activeTopic && $activeTopic->id === $topic->id ? 'active' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <i class="{{ $topic->icon }} {{ $topic->color }} me-3" style="font-size: 1.25rem;"></i>
                                        <div>
                                            <div class="fw-semibold">{{ $topic->name }}</div>
                                            <small class="text-muted">{{ $topic->posts_count }} {{ Str::plural('post', $topic->posts_count) }}</small>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="list-group-item">
                                    <small class="text-muted">No topics created yet.</small>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Main Feed Column --}}
        <div class="col-lg-9 col-md-7 order-1 order-md-1">
            <h4 class="py-lg-4 py-3 mb-4 text-body-secondary">{{ $activeTopic ? 'Topic: ' . $activeTopic->name : 'Digital Town Square' }}</h4>

            {{-- Post Creation Form --}}
            <div class="card mb-4 create-post-card">
                <div class="card-body p-4">
                    <form id="post-creation-form" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="d-flex gap-3">
                            <div class="avatar">
                                <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                            </div>
                            <div class="flex-grow-1">
                                <textarea name="content" rows="3" class="form-control border-0 p-0 shadow-none" placeholder="What's happening in your community?" required></textarea>

                                <div class="mt-3">
                                    <label for="topic_id" class="form-label">Select a Topic (Optional)</label>
                                    <select class="form-select form-select-sm" name="topic_id" id="topic_id">
                                        <option value="">General Discussion</option>
                                        @foreach($topics as $topic)
                                            <option value="{{ $topic->id }}" {{ $activeTopic && $activeTopic->id === $topic->id ? 'selected' : '' }}>
                                                {{ $topic->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div id="media-preview" class="mt-3"></div>

                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                    <label for="media-upload" class="btn btn-sm btn-outline-primary mb-0">
                                        <i class="ri-image-line me-1"></i> Media
                                    </label>
                                    <input type="file" id="media-upload" name="media[]" class="d-none" multiple accept="image/*,video/*">
                                    <button type="submit" class="btn btn-primary px-4">Post</button>
                                </div>
                            </div>
                        </div>
                        @error('content')<p class="text-danger text-sm mt-2 mb-0">{{ $message }}</p>@enderror
                    </form>
                </div>
            </div>

            <div id="post-feed-container">
                @forelse ($posts as $post)
                    @include('posts._post_card', ['post' => $post])
                @empty
                    <div id="no-posts-message" class="card card-body text-center text-muted p-5">
                        <p>No posts found in this topic. Be the first to share something!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div id="image-modal-container"></div>
{{--! NEW: Explanation Modal --}}
<div class="modal fade" id="explanation-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-rocket-line me-2"></i>Explain Like I'm 5</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="explanation-content">Generating explanation...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const postFeedContainer = document.getElementById('post-feed-container');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentAudio = null;
    let lastPlayedButton = null;

    const explainModalEl = document.getElementById('explanation-modal');
    const explainModal = new bootstrap.Modal(explainModalEl);
    const explanationContent = document.getElementById('explanation-content');

    document.body.addEventListener('click', function (event) {
        const likeButton = event.target.closest('.btn-like');
        const bookmarkButton = event.target.closest('.btn-bookmark');
        const shareButton = event.target.closest('.btn-share');
        const summarizeButton = event.target.closest('.btn-summarize');
        const readAloudButton = event.target.closest('.btn-read-aloud');
        const explainButton = event.target.closest('.btn-explain');
        const imageModalTrigger = event.target.closest('.carousel-image');
        const modalClose = event.target.closest('.image-modal-close');
        const modalBackdrop = event.target.closest('.image-modal');

        if (likeButton) handleLikeClick(likeButton);
        else if (bookmarkButton) handleBookmarkClick(bookmarkButton);
        else if (shareButton) handleShareClick(shareButton);
        else if (summarizeButton) handleSummarizeClick(summarizeButton);
        else if (readAloudButton) handleReadAloudClick(readAloudButton);
        else if (explainButton) handleExplainClick(explainButton);
        else if (imageModalTrigger) openImageModal(imageModalTrigger);
        else if (modalClose) closeModal();
        else if (modalBackdrop && event.target === modalBackdrop) closeModal();
    });

    const mediaUploadInput = document.getElementById('media-upload');
    const mediaPreviewContainer = document.getElementById('media-preview');

    if (mediaUploadInput) {
        mediaUploadInput.addEventListener('change', function() {
            mediaPreviewContainer.innerHTML = '';
            if (this.files.length > 0) {
                const fileList = document.createElement('ul');
                fileList.className = 'list-unstyled mb-0 small text-muted';
                Array.from(this.files).forEach(file => {
                    const listItem = document.createElement('li');
                    listItem.textContent = `📎 ${file.name}`;
                    fileList.appendChild(listItem);
                });
                mediaPreviewContainer.appendChild(fileList);
            }
        });
    }

    async function handleExplainClick(button) {
        const postId = button.dataset.postId;
        explanationContent.innerHTML = '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        explainModal.show();
        try {
            const response = await fetch(`/posts/${postId}/explain`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
            });
            if (!response.ok) throw new Error('Explanation failed');
            const data = await response.json();
            explanationContent.textContent = data.explanation;
        } catch (error) {
            console.error('Error explaining post:', error);
            explanationContent.textContent = 'Sorry, something went wrong while trying to explain this.';
        }
    }

    async function handleReadAloudClick(button) {
        const postId = button.dataset.postId;
        const icon = button.querySelector('i');

        if (currentAudio && !currentAudio.paused && lastPlayedButton === button) {
            currentAudio.pause();
            currentAudio.currentTime = 0;
            icon.className = 'ri-volume-up-line';
            currentAudio = null;
            lastPlayedButton = null;
            return;
        }

        if (currentAudio && !currentAudio.paused) {
            currentAudio.pause();
            currentAudio.currentTime = 0;
            if (lastPlayedButton) {
                lastPlayedButton.querySelector('i').className = 'ri-volume-up-line';
            }
        }

        icon.className = 'ri-loader-4-line';
        lastPlayedButton = button;

        try {
            const response = await fetch('{{ route("speech.generate") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ post_id: postId })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Speech generation failed.');
            }
            const data = await response.json();
            if (!data.audio) throw new Error('No audio data received.');

            const audioSrc = `data:audio/mp3;base64,${data.audio}`;
            currentAudio = new Audio(audioSrc);
            icon.className = 'ri-stop-circle-line';

            currentAudio.play().catch(e => {
                console.error("Audio playback failed:", e);
                alert("Audio playback was blocked by the browser.");
                icon.className = 'ri-volume-up-line';
            });

            currentAudio.onended = () => {
                icon.className = 'ri-volume-up-line';
                currentAudio = null;
                lastPlayedButton = null;
            };
        } catch (error) {
            console.error('[Speech] Error:', error);
            icon.className = 'ri-volume-up-line';
            alert('Could not generate audio for this post.');
            currentAudio = null;
            lastPlayedButton = null;
        }
    }

    async function handleLikeClick(button) {
        const postId = button.dataset.postId;
        const countSpan = button.querySelector('.like-count');
        const icon = button.querySelector('i');

        try {
            const response = await fetch(`/posts/${postId}/like`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            if (!response.ok) throw new Error('Like failed');
            const data = await response.json();

            countSpan.textContent = data.likes_count > 0 ? data.likes_count : '';
            button.classList.toggle('liked', data.action === 'liked');
            icon.className = data.action === 'liked' ? 'ri-heart-fill me-1' : 'ri-heart-line me-1';
        } catch (error) {
            console.error('Error liking post:', error);
        }
    }

    async function handleBookmarkClick(button) {
        const postId = button.dataset.postId;
        const icon = button.querySelector('i');
        button.disabled = true;

        try {
            const response = await fetch(`/posts/${postId}/bookmark`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            if (!response.ok) throw new Error('Bookmark failed');
            const data = await response.json();
            button.classList.toggle('active', data.action === 'bookmarked');
            icon.className = data.action === 'bookmarked' ? 'ri-bookmark-fill me-1' : 'ri-bookmark-line me-1';
        } catch (error) {
            console.error('Error bookmarking post:', error);
        } finally {
            button.disabled = false;
        }
    }

    function handleShareClick(button) {
        const url = button.dataset.url;
        navigator.clipboard.writeText(url).then(() => {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="ri-check-line me-1"></i> Copied!';
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        }).catch(err => {
            console.error('Could not copy text: ', err);
            alert('Failed to copy link.');
        });
    }

    async function handleSummarizeClick(button) {
        const postId = button.dataset.postId;
        const postElement = document.getElementById(`post-${postId}`);
        const summaryContainer = postElement.querySelector('.summary-container');
        const summaryContent = summaryContainer.querySelector('.summary-content');
        const buttonText = button.querySelector('.button-text');
        const buttonIcon = button.querySelector('i');

        if (summaryContainer.style.display === 'block') {
            summaryContainer.style.display = 'none';
            buttonText.textContent = 'Summarize';
            return;
        }

        button.disabled = true;
        buttonText.textContent = 'Generating...';
        buttonIcon.className = 'ri-loader-4-line me-1';

        try {
            const response = await fetch(`/posts/${postId}/summarize`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
            if (!response.ok) throw new Error('Network error.');
            const data = await response.json();

            summaryContent.textContent = data.summary;
            summaryContainer.style.display = 'block';
            buttonText.textContent = 'Hide Summary';
        } catch (error) {
            console.error('Error:', error);
            summaryContent.textContent = 'Could not generate a summary.';
            summaryContainer.style.display = 'block';
            buttonText.textContent = 'Summarize';
        } finally {
            button.disabled = false;
            buttonIcon.className = 'ri-sparkling-2-line me-1';
        }
    }

    const modalContainer = document.getElementById('image-modal-container');
    modalContainer.innerHTML = `<div class="image-modal"><span class="image-modal-close">&times;</span><img src="" alt="Expanded image"></div>`;
    const modal = modalContainer.querySelector('.image-modal');
    const modalImg = modal.querySelector('img');
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
    function openImageModal(trigger) {
        modalImg.src = trigger.dataset.fullImage;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.classList.contains('active')) closeModal(); });

    const createPostForm = document.querySelector('#post-creation-form');
    if (createPostForm) {
        const postSubmitButton = createPostForm.querySelector('button[type="submit"]');
        createPostForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            postSubmitButton.disabled = true;
            postSubmitButton.textContent = 'Posting...';
            const formData = new FormData(createPostForm);
            try {
                const response = await fetch('{{ route("posts.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: formData
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Could not create post.');
                }
                createPostForm.reset();
                if (mediaPreviewContainer) mediaPreviewContainer.innerHTML = '';
                location.reload();
            } catch (error) {
                console.error('Error:', error);
                alert(error.message);
            } finally {
                postSubmitButton.disabled = false;
                postSubmitButton.textContent = 'Post';
            }
        });
    }
});
</script>
@endpush
