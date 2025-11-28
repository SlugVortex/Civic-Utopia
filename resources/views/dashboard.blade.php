@php
$pageConfigs = ['myLayout' => 'vertical'];
$activeTopic = $activeTopic ?? null;
@endphp

@extends('layouts/layoutMaster')

@section('title', $activeTopic ? $activeTopic->name : 'Digital Town Square')

@section('content')
<style>
    /* FIXED: Independent Sidebar Scrolling Logic */
    @media (min-width: 992px) {
        .sticky-column {
            position: sticky;
            top: 5rem; /* Adjust based on navbar height */
            height: calc(100vh - 6rem); /* Full height minus header */
            overflow-y: auto; /* Scroll independently */
            overflow-x: hidden;
            padding-right: 5px; /* Space for scrollbar */
        }

        /* Custom scrollbar for sidebar */
        .sticky-column::-webkit-scrollbar { width: 4px; }
        .sticky-column::-webkit-scrollbar-track { background: transparent; }
        .sticky-column::-webkit-scrollbar-thumb { background: #d3d3d3; border-radius: 4px; }
        .sticky-column:hover::-webkit-scrollbar-thumb { background: #b0b0b0; }
    }
</style>

<div class="container-fluid">
    <div class="row">
        {{-- Sidebar Column --}}
        <div class="col-lg-3 col-md-5 order-0 order-md-0">
            <div class="sticky-column pt-lg-2">

                {{-- 1. Poll Widget --}}
                @include('_partials.poll-widget')

                {{-- 2. AI News Agent Widget --}}
                <div class="card mb-4 sidebar-widget border-primary">
                    <div class="card-body">
                        <h6 class="card-title text-primary"><i class="ri-robot-2-line me-2"></i>Civic Pulse AI</h6>
                        <p class="small text-muted mb-3">
                            Use your location to generate hyper-localized, illustrated news reports.
                        </p>

                        <button id="btn-localize-news" class="btn btn-sm btn-primary w-100">
                            <i class="ri-map-pin-user-line me-1"></i> Generate Local Feed
                        </button>

                        {{-- Progress Bar Container --}}
                        <div id="ai-progress-container" class="mt-3" style="display: none;">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold text-primary">AI Status</span>
                                <span id="ai-progress-percent" class="small text-muted">0%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div id="ai-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div id="ai-progress-text" class="small text-center text-muted mt-2">Initializing...</div>
                        </div>
                    </div>
                </div>

                {{-- 3. Suggestions Widget --}}
                @include('_partials.suggestion-widget')

                {{-- 4. Live Feeds Widget --}}
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

                {{-- 5. Topics Widget --}}
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

{{-- Suggestion Modal (Placed here to avoid Z-Index clipping in sidebar) --}}
<div class="modal fade" id="suggestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1060;">
        <form class="modal-content" action="{{ route('suggestions.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Make a Suggestion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-primary py-2 small mb-3">
                    <i class="ri-information-line me-1"></i> Suggestions must be approved by admin before appearing here.
                </div>

                @if($errors->has('msg'))
                    <div class="alert alert-danger">
                        {{ $errors->first('msg') }}
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Add dark mode support" required maxlength="150">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description (Optional)</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Explain your idea..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Idea</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // --- 1. LOCAL NEWS AGENT LOGIC (FIXED AUTO-REFRESH) ---
    const localizeBtn = document.getElementById('btn-localize-news');
    const progressContainer = document.getElementById('ai-progress-container');
    const progressBar = document.getElementById('ai-progress-bar');
    const progressText = document.getElementById('ai-progress-text');
    const progressPercent = document.getElementById('ai-progress-percent');

    function updateProgress(width, text) {
        progressBar.style.width = width + '%';
        progressPercent.textContent = width + '%';
        progressText.innerHTML = text;
    }

    if(localizeBtn) {
        localizeBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                localizeBtn.classList.remove('btn-primary');
                localizeBtn.classList.add('btn-danger');
                localizeBtn.innerHTML = '<i class="ri-error-warning-line"></i> Geolocation Not Supported';
                return;
            }

            localizeBtn.disabled = true;
            const originalBtnText = localizeBtn.innerHTML;
            localizeBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Working...';

            progressContainer.style.display = 'block';
            updateProgress(15, '<i class="ri-map-pin-user-line"></i> Acquiring GPS location...');

            navigator.geolocation.getCurrentPosition(function(position) {
                updateProgress(50, '<i class="ri-broadcast-line"></i> Contacting News Agents...');

                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                // Fetch Backend Job
                fetch('{{ route("news.fetch") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ lat: lat, lon: lon })
                })
                .then(response => response.json())
                .then(data => {
                    // Job Started successfully
                    updateProgress(90, '<i class="ri-quill-pen-line"></i> Generating summaries & images... (~15s)');
                    progressBar.classList.add('progress-bar-striped', 'progress-bar-animated');

                    // Wait 15 seconds for Azure AI to finish, then AUTO RELOAD
                    setTimeout(() => {
                        updateProgress(100, '<i class="ri-check-double-line"></i> Done! Reloading...');
                        progressBar.classList.remove('progress-bar-animated'); // Stop animation

                        // Force Refresh after 1 second delay so user sees "Done"
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }, 25000);
                })
                .catch(err => {
                    console.error(err);
                    progressBar.classList.remove('bg-primary');
                    progressBar.classList.add('bg-danger');
                    updateProgress(100, 'Connection failed.');
                    localizeBtn.disabled = false;
                    localizeBtn.innerHTML = originalBtnText;
                });
            }, function(error) {
                // GPS Error
                progressBar.classList.remove('bg-primary');
                progressBar.classList.add('bg-danger');
                updateProgress(100, 'GPS Access Denied.');
                localizeBtn.disabled = false;
                localizeBtn.innerHTML = originalBtnText;
            });
        });
    }

    // --- 2. POLL VOTING LOGIC ---
    window.submitVote = async function(event, pollId) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Voting...';

        try {
            const response = await fetch(`/polls/${pollId}/vote`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Vote failed');
            }
            // Auto refresh to show results
            window.location.reload();
        } catch (error) {
            console.error(error);
            // Removed Alert: Just reset button
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Vote';
        }
    }

    // --- 3. SUGGESTION LOGIC ---
    window.toggleSuggestionVote = async function(btn, suggestionId) {
        if(btn.disabled) return;
        btn.disabled = true;
        const countSpan = btn.querySelector('.vote-count');

        try {
            const response = await fetch(`/suggestions/${suggestionId}/vote`, {
                method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
            });
            if (!response.ok) throw new Error('Network error');

            const data = await response.json();
            countSpan.textContent = data.count;

            if (data.action === 'voted') {
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-primary');
            } else {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-secondary');
            }
        } catch (error) {
            console.error('Error voting:', error);
        } finally {
            btn.disabled = false;
        }
    }

    // --- 4. POST CREATION FORM ---
    const createPostForm = document.querySelector('#post-creation-form');
    if(createPostForm) {
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
                location.reload();
            } catch (error) {
                console.error(error.message);
            } finally {
                postSubmitButton.disabled = false;
                postSubmitButton.textContent = 'Post';
            }
        });
    }

    // --- 5. MEDIA & MODAL LOGIC (Standard) ---
    const mediaUploadInput = document.getElementById('media-upload');
    const mediaPreviewContainer = document.getElementById('media-preview');
    if(mediaUploadInput) {
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

    const modalContainer = document.getElementById('image-modal-container');
    if(modalContainer) {
        modalContainer.innerHTML = `<div class="image-modal"><span class="image-modal-close">&times;</span><img src="" alt="Expanded image"></div>`;
        const modal = modalContainer.querySelector('.image-modal');
        const modalImg = modal.querySelector('img');

        window.closeModal = function() { modal.classList.remove('active'); document.body.style.overflow = ''; }
        window.openImageModal = function(trigger) { modalImg.src = trigger.dataset.fullImage; modal.classList.add('active'); document.body.style.overflow = 'hidden'; }

        document.body.addEventListener('click', function(e) {
            if (e.target.closest('.image-modal-close') || e.target === modal) closeModal();
            if (e.target.closest('.carousel-image')) openImageModal(e.target.closest('.carousel-image'));
        });
    }

    // --- 6. POST INTERACTIONS (Like, Bookmark, Audio, Summarize) ---
    document.body.addEventListener('click', function (event) {
        const likeButton = event.target.closest('.btn-like');
        const bookmarkButton = event.target.closest('.btn-bookmark');
        const shareButton = event.target.closest('.btn-share');
        const summarizeButton = event.target.closest('.btn-summarize');
        const readAloudButton = event.target.closest('.btn-read-aloud');

        if (likeButton) handleLikeClick(likeButton);
        else if (bookmarkButton) handleBookmarkClick(bookmarkButton);
        else if (shareButton) handleShareClick(shareButton);
        else if (summarizeButton) handleSummarizeClick(summarizeButton);
        else if (readAloudButton) handleReadAloudClick(readAloudButton);
    });

    async function handleLikeClick(button) {
        const postId = button.dataset.postId;
        const countSpan = button.querySelector('.like-count');
        const icon = button.querySelector('i');
        try {
            const response = await fetch(`/posts/${postId}/like`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
            const data = await response.json();
            countSpan.textContent = data.likes_count > 0 ? data.likes_count : '';
            button.classList.toggle('liked', data.action === 'liked');
            icon.className = data.action === 'liked' ? 'ri-heart-fill me-1' : 'ri-heart-line me-1';
        } catch (error) { console.error(error); }
    }

    async function handleBookmarkClick(button) {
        const postId = button.dataset.postId;
        const icon = button.querySelector('i');
        try {
            const response = await fetch(`/posts/${postId}/bookmark`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
            const data = await response.json();
            button.classList.toggle('active', data.action === 'bookmarked');
            icon.className = data.action === 'bookmarked' ? 'ri-bookmark-fill me-1' : 'ri-bookmark-line me-1';
        } catch (error) { console.error(error); }
    }

    function handleShareClick(button) {
        const url = button.dataset.url;
        navigator.clipboard.writeText(url).then(() => {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="ri-check-line me-1"></i> Copied!';
            setTimeout(() => { button.innerHTML = originalText; }, 2000);
        });
    }

    async function handleSummarizeClick(button) {
        const postId = button.dataset.postId;
        const summaryContainer = document.getElementById(`post-${postId}`).querySelector('.summary-container');
        const summaryContent = summaryContainer.querySelector('.summary-content');

        if (summaryContainer.style.display === 'block') { summaryContainer.style.display = 'none'; return; }

        button.disabled = true;
        try {
            const response = await fetch(`/posts/${postId}/summarize`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
            const data = await response.json();
            summaryContent.textContent = data.summary;
            summaryContainer.style.display = 'block';
        } catch (error) { summaryContent.textContent = 'Summary failed.'; summaryContainer.style.display = 'block'; }
        finally { button.disabled = false; }
    }

    let currentAudio = null;
    let lastPlayedButton = null;
    async function handleReadAloudClick(button) {
        const postId = button.dataset.postId;
        const icon = button.querySelector('i');
        if (currentAudio && !currentAudio.paused) {
            currentAudio.pause();
            currentAudio.currentTime = 0;
            if(lastPlayedButton) lastPlayedButton.querySelector('i').className = 'ri-volume-up-line';
            if(lastPlayedButton === button) { currentAudio = null; lastPlayedButton = null; return; }
        }
        icon.className = 'ri-loader-4-line';
        lastPlayedButton = button;
        try {
            const response = await fetch('{{ route("speech.generate") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ post_id: postId })
            });
            const data = await response.json();
            currentAudio = new Audio(`data:audio/mp3;base64,${data.audio}`);
            icon.className = 'ri-stop-circle-line';
            currentAudio.play();
            currentAudio.onended = () => { icon.className = 'ri-volume-up-line'; };
        } catch (e) { icon.className = 'ri-volume-up-line'; }
    }
});
</script>
@endpush
