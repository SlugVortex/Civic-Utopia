@php
$pageConfigs = ['myLayout' => 'vertical'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Digital Town Square')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Main Feed Column --}}
        <div class="col-lg-8 col-md-7">
            <h4 class="py-3 mb-4">Digital Town Square</h4>

            {{-- Post Creation Form --}}
            <div class="card mb-4 create-post-card">
                <div class="card-body p-4">
                    <form id="post-creation-form" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="d-flex gap-3">
                            <div class="avatar">
                                <svg class="rounded-circle" width="48" height="48" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#7367f0">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm0 14c-2.03 0-4.43-.82-6.14-2.88a9.947 9.947 0 0 1 12.28 0C16.43 19.18 14.03 20 12 20z"/>
                                </svg>
                            </div>
                            <div class="flex-grow-1">
                                <textarea name="content" rows="3" class="form-control border-0 p-0" placeholder="What's happening?" required style="resize: none;"></textarea>
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
                    <div class="card mb-3 post-card" id="post-{{ $post->id }}">
                        <div class="card-body p-4">
                            <div class="d-flex gap-3">
                                <div class="avatar flex-shrink-0">
                                    <svg class="rounded-circle" width="40" height="40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#7367f0">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm0 14c-2.03 0-4.43-.82-6.14-2.88a9.947 9.947 0 0 1 12.28 0C16.43 19.18 14.03 20 12 20z"/>
                                    </svg>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="fw-bold">{{ $post->user->name }}</span>
                                            <small class="text-muted ms-2">@{{ Str::of($post->user->email)->before('@') }}</small>
                                            <small class="text-muted ms-2">· {{ $post->created_at->diffForHumans() }}</small>
                                        </div>
                                        @if ($post->user_id === Auth::id())
                                            <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <p class="post-content mb-3" style="white-space: pre-wrap;">{{ $post->content }}</p>

                                    {{-- Image Carousel --}}
                                    @if($post->media && $post->media->isNotEmpty())
                                        @php
                                            $images = $post->media->where('file_type', 'image');
                                            $videos = $post->media->where('file_type', 'video');
                                        @endphp

                                        @if($images->isNotEmpty())
                                            <div class="post-carousel mb-3" data-post-id="{{ $post->id }}">
                                                <div class="carousel-container">
                                                    <div class="carousel-track">
                                                        @foreach($images as $index => $media)
                                                            <div class="carousel-slide {{ $index === 0 ? 'active' : '' }}">
                                                                <img src="{{ asset('storage/' . $media->path) }}"
                                                                     alt="Post media"
                                                                     class="carousel-image"
                                                                     data-full-image="{{ asset('storage/' . $media->path) }}">
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    @if($images->count() > 1)
                                                        <button class="carousel-btn prev-btn">
                                                            <i class="ri-arrow-left-s-line"></i>
                                                        </button>
                                                        <button class="carousel-btn next-btn">
                                                            <i class="ri-arrow-right-s-line"></i>
                                                        </button>
                                                        <div class="carousel-indicators">
                                                            @foreach($images as $index => $media)
                                                                <span class="indicator {{ $index === 0 ? 'active' : '' }}"></span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @foreach($videos as $media)
                                            <video controls class="w-100 rounded mb-3" style="max-height: 400px;">
                                                <source src="{{ asset('storage/' . $media->path) }}" type="{{ $media->mime_type }}">
                                            </video>
                                        @endforeach
                                    @endif

                                    {{-- AI Summary --}}
                                    <div class="summary-container alert alert-info" style="display: none;">
                                        <strong class="d-block mb-1"><i class="ri-sparkling-2-line me-1"></i>AI Summary</strong>
                                        <p class="mb-0 summary-content"></p>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="d-flex gap-2 pt-2">
                                        <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-light">
                                            <i class="ri-chat-3-line me-1"></i> Comment
                                        </a>
                                        <button class="btn btn-sm btn-light btn-summarize" data-post-id="{{ $post->id }}">
                                            <i class="ri-sparkling-2-line me-1"></i>
                                            <span class="button-text">Summarize</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div id="no-posts-message" class="text-center text-muted p-5">
                        <p>No posts yet. Be the first to share!</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4 col-md-5">
            <div class="sticky-sidebar">
                {{-- Live Feeds Widget --}}
                <div class="card mb-4 sidebar-widget">
                    <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#liveFeeds">
                        <h6 class="mb-0"><i class="ri-radio-line me-2"></i>Live Feeds</h6>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="collapse show" id="liveFeeds">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <i class="ri-radio-2-line text-danger me-3"></i>
                                    <div>
                                        <div class="fw-semibold">Live Feed</div>
                                        <small class="text-muted">Real-time updates</small>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <i class="ri-history-line text-secondary me-3"></i>
                                    <div>
                                        <div class="fw-semibold">Archives</div>
                                        <small class="text-muted">Past discussions</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Topics Widget --}}
                <div class="card sidebar-widget">
                    <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#topicsWidget">
                        <h6 class="mb-0"><i class="ri-hashtag me-2"></i>Topics</h6>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="collapse show" id="topicsWidget">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <i class="ri-government-line text-primary me-3"></i>
                                    <div>
                                        <div class="fw-semibold">Public Safety</div>
                                        <small class="text-muted">124 posts</small>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <i class="ri-building-line text-success me-3"></i>
                                    <div>
                                        <div class="fw-semibold">Infrastructure</div>
                                        <small class="text-muted">89 posts</small>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <i class="ri-book-line text-info me-3"></i>
                                    <div>
                                        <div class="fw-semibold">Education & Youth</div>
                                        <small class="text-muted">67 posts</small>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <i class="ri-chat-4-line text-warning me-3"></i>
                                    <div>
                                        <div class="fw-semibold">General Discussion</div>
                                        <small class="text-muted">234 posts</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Image Modal --}}
<div id="image-modal-container"></div>
@endsection

@push('scripts')
<style>
    /* Base Styles */
    .create-post-card, .post-card, .sidebar-widget {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        transition: box-shadow 0.2s;
    }
    .post-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .sticky-sidebar {
        position: sticky;
        top: 20px;
    }

    /* Carousel Styles */
    .post-carousel {
        position: relative;
        width: 100%;
        max-width: 600px;
        border-radius: 16px;
        overflow: hidden;
        background: #f3f4f6;
    }
    .carousel-container {
        position: relative;
        width: 100%;
    }
    .carousel-track {
        position: relative;
        width: 100%;
        height: 400px;
    }
    .carousel-slide {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .carousel-slide.active {
        opacity: 1;
    }
    .carousel-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .carousel-image:hover {
        transform: scale(1.02);
    }
    .carousel-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.6);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: background 0.2s;
    }
    .carousel-btn:hover {
        background: rgba(0, 0, 0, 0.8);
    }
    .prev-btn {
        left: 10px;
    }
    .next-btn {
        right: 10px;
    }
    .carousel-indicators {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
        z-index: 10;
    }
    .indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: background 0.2s;
    }
    .indicator.active {
        background: rgba(255, 255, 255, 1);
        width: 24px;
        border-radius: 4px;
    }

    /* Image Modal */
    .image-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.95);
        animation: fadeIn 0.2s;
    }
    .image-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .image-modal img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 8px;
    }
    .image-modal-close {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #fff;
        font-size: 40px;
        cursor: pointer;
        z-index: 10000;
    }
    .image-modal-close:hover {
        color: #bbb;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .post-flash {
        animation: flash-bg 2s ease-out;
    }
    @keyframes flash-bg {
        from { background-color: #dbeafe; }
        to { background-color: transparent; }
    }
    .ri-loader-4-line {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // === CAROUSEL FUNCTIONALITY ===
    document.querySelectorAll('.post-carousel').forEach(carousel => {
        const slides = carousel.querySelectorAll('.carousel-slide');
        const indicators = carousel.querySelectorAll('.indicator');
        const prevBtn = carousel.querySelector('.prev-btn');
        const nextBtn = carousel.querySelector('.next-btn');
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(ind => ind.classList.remove('active'));

            if (index >= slides.length) currentSlide = 0;
            if (index < 0) currentSlide = slides.length - 1;

            slides[currentSlide].classList.add('active');
            if (indicators[currentSlide]) {
                indicators[currentSlide].classList.add('active');
            }
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                currentSlide--;
                showSlide(currentSlide);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentSlide++;
                showSlide(currentSlide);
            });
        }

        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        // Keyboard navigation
        carousel.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                currentSlide--;
                showSlide(currentSlide);
            } else if (e.key === 'ArrowRight') {
                currentSlide++;
                showSlide(currentSlide);
            }
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
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: formData
            });
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Could not create post.');
            }
            createPostForm.reset();
            location.reload(); // Reload to show new post
        } catch (error) {
            console.error('Error:', error);
            alert(error.message);
        } finally {
            postSubmitButton.disabled = false;
            postSubmitButton.textContent = 'Post';
        }
    });

    // === SUMMARIZE FUNCTIONALITY ===
    const postFeedContainer = document.getElementById('post-feed-container');

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
