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
            {{-- Added pt-lg-4 for top padding on large screens --}}
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
            {{-- Adjusted heading style for better alignment and appearance --}}
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

                                {{-- TOPIC SELECTOR --}}
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

            {{-- Post Feed --}}
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

{{-- Image Modal --}}
<div id="image-modal-container"></div>
@endsection


@push('scripts')
{{-- The Javascript for social actions is now included here --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const postFeedContainer = document.getElementById('post-feed-container');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // === DELEGATED EVENT LISTENERS for social actions ===
    postFeedContainer.addEventListener('click', function (event) {
        const likeButton = event.target.closest('.btn-like');
        const bookmarkButton = event.target.closest('.btn-bookmark');
        const shareButton = event.target.closest('.btn-share');
        const summarizeButton = event.target.closest('.btn-summarize');

        if (likeButton) {
            handleLikeClick(likeButton);
        } else if (bookmarkButton) {
            handleBookmarkClick(bookmarkButton);
        } else if (shareButton) {
            handleShareClick(shareButton);
        } else if (summarizeButton) {
            handleSummarizeClick(summarizeButton);
        }
    });

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


    // === CAROUSEL FUNCTIONALITY ===
    document.querySelectorAll('.post-carousel').forEach(carousel => {
        const slides = carousel.querySelectorAll('.carousel-slide');
        const indicators = carousel.querySelectorAll('.indicator');
        const prevBtn = carousel.querySelector('.prev-btn');
        const nextBtn = carousel.querySelector('.next-btn');
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (indicators[i]) indicators[i].classList.remove('active');
            });

            currentSlide = index;
            if (index >= slides.length) currentSlide = 0;
            if (index < 0) currentSlide = slides.length - 1;

            if (slides[currentSlide]) {
                slides[currentSlide].classList.add('active');
            }
            if (indicators[currentSlide]) {
                indicators[currentSlide].classList.add('active');
            }
        }

        if (prevBtn && nextBtn) { // Only add listeners if buttons exist
            prevBtn.addEventListener('click', () => showSlide(currentSlide - 1));
            nextBtn.addEventListener('click', () => showSlide(currentSlide + 1));
        }

        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => showSlide(index));
        });
    });

    // === IMAGE MODAL ===
    const modalContainer = document.getElementById('image-modal-container');
    modalContainer.innerHTML = `
        <div class="image-modal">
            <span class="image-modal-close">&times;</span>
            <img src="" alt="Expanded image">
        </div>
    `;
    const modal = modalContainer.querySelector('.image-modal');
    const modalImg = modal.querySelector('img');
    const closeBtn = modal.querySelector('.image-modal-close');

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('carousel-image')) {
            modalImg.src = e.target.dataset.fullImage;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    });
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.classList.contains('active')) closeModal(); });

    // === POST FORM SUBMISSION ===
    const createPostForm = document.querySelector('#post-creation-form');
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
            console.error('Error:', error);
            alert(error.message);
        } finally {
            postSubmitButton.disabled = false;
            postSubmitButton.textContent = 'Post';
        }
    });

    // === SUMMARIZE FUNCTIONALITY ===
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
});
</script>
@endpush
