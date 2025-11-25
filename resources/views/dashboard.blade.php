@php
$pageConfigs = ['myLayout' => 'vertical'];
$activeTopic = $activeTopic ?? null;
@endphp

@extends('layouts/layoutMaster')

@section('title', $activeTopic ? $activeTopic->name : 'Digital Town Square')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar Column --}}
        <div class="col-lg-3 col-md-5 order-0 order-md-0">
            <div class="sticky-sidebar pt-lg-4">

                {{-- AI News Agent Widget --}}
                <div class="card mb-4 sidebar-widget border-primary">
                    <div class="card-body">
                        <h6 class="card-title text-primary"><i class="ri-robot-2-line me-2"></i>Civic Pulse AI</h6>
                        <p class="small text-muted mb-3">
                            Use your location to generate hyper-localized, illustrated news reports.
                        </p>

                        <button id="btn-localize-news" class="btn btn-sm btn-primary w-100">
                            <i class="ri-map-pin-user-line me-1"></i> Generate Local Feed
                        </button>

                        {{-- Progress Bar Container (Hidden initially) --}}
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
{{-- Explanation Modal --}}
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
    // ==========================================
    // 1. REAL-TIME COMMENT LISTENER (PUSHER)
    // ==========================================
    // Ensure Echo is initialized (Laravel default bootstrap.js usually does this)
    // If not, you might need: window.Echo = new Echo({...});

    if (window.Echo) {
        window.Echo.channel('comments')
            .listen('CommentCreated', (e) => {
                console.log('New comment received:', e);

                // 1. Find the post container
                const postCard = document.getElementById(`post-${e.post_id}`);
                if (!postCard) return; // Comment is for a post not currently on screen

                // 2. Find the comments list
                const commentsList = postCard.querySelector('.comments-list'); // Ensure your _post_card.blade.php has this class on the container

                // 3. Remove "Typing" indicator if this is the bot reply
                const loadingId = `typing-${e.post_id}`;
                const loadingEl = document.getElementById(loadingId);
                if (loadingEl) loadingEl.remove();

                // 4. Build the HTML (Matches your design)
                const newCommentHtml = `
                    <div class="d-flex mb-3 animate__animated animate__fadeIn">
                        <div class="flex-shrink-0">
                            <img src="${e.user.avatar_url}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="bg-light p-3 rounded">
                                <div class="fw-bold text-dark mb-1">${e.user.name}</div>
                                <div class="text-muted small">${e.content}</div>
                            </div>
                            <small class="text-muted ms-1">Just now</small>
                        </div>
                    </div>
                `;

                // 5. Append
                if (commentsList) {
                    commentsList.insertAdjacentHTML('beforeend', newCommentHtml);
                }
            });
    }

    // ==========================================
    // 2. BOT AUTOCOMPLETE & LOADING UI
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {

        // The Bots Config
        const bots = [
            { id: 'FactChecker', name: 'FactChecker', desc: 'Verifies claims', icon: 'ri-checkbox-circle-line text-success' },
            { id: 'Historian', name: 'Historian', desc: 'Provides context', icon: 'ri-book-open-line text-warning' },
            { id: 'DevilsAdvocate', name: 'DevilsAdvocate', desc: 'Argues logic', icon: 'ri-fire-line text-danger' },
            { id: 'Analyst', name: 'Analyst', desc: 'Data & Stats', icon: 'ri-bar-chart-line text-info' }
        ];

        // Create the dropdown element (Hidden initially)
        const dropdown = document.createElement('div');
        dropdown.id = 'bot-autocomplete-dropdown';
        dropdown.className = 'list-group position-absolute shadow-lg';
        dropdown.style.display = 'none';
        dropdown.style.zIndex = '1000';
        dropdown.style.width = '250px';
        document.body.appendChild(dropdown);

        let activeInput = null;

        // Listen for typing in ANY comment box
        document.body.addEventListener('keyup', function(e) {
            if (e.target.matches('textarea[name="content"]')) {
                activeInput = e.target;
                const val = activeInput.value;
                const cursorPos = activeInput.selectionStart;

                // Check if the last character typed or word being typed starts with @
                const lastAt = val.lastIndexOf('@', cursorPos - 1);

                if (lastAt !== -1) {
                    const query = val.substring(lastAt + 1, cursorPos);
                    // If query contains space, close menu (assuming they finished the name or are typing a sentence)
                    if (query.includes(' ')) {
                        dropdown.style.display = 'none';
                        return;
                    }
                    showSuggestions(query, activeInput, lastAt);
                } else {
                    dropdown.style.display = 'none';
                }
            }
        });

        function showSuggestions(query, input, atIndex) {
            const rect = input.getBoundingClientRect();
            dropdown.style.top = (window.scrollY + rect.bottom) + 'px';
            dropdown.style.left = (window.scrollX + rect.left) + 'px';
            dropdown.innerHTML = '';

            const matches = bots.filter(b => b.name.toLowerCase().startsWith(query.toLowerCase()));

            if (matches.length === 0) {
                dropdown.style.display = 'none';
                return;
            }

            matches.forEach(bot => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action d-flex align-items-center';
                item.innerHTML = `
                    <i class="${bot.icon} fs-4 me-2"></i>
                    <div>
                        <div class="fw-bold">@${bot.name}</div>
                        <small class="text-muted">${bot.desc}</small>
                    </div>
                `;
                item.onclick = function() {
                    const before = input.value.substring(0, atIndex);
                    const after = input.value.substring(input.selectionStart);
                    input.value = before + '@' + bot.name + ' ' + after;
                    dropdown.style.display = 'none';
                    input.focus();
                };
                dropdown.appendChild(item);
            });

            dropdown.style.display = 'block';
        }

        // Hide dropdown on click elsewhere
        document.addEventListener('click', (e) => {
            if (e.target !== dropdown && e.target !== activeInput) {
                dropdown.style.display = 'none';
            }
        });

        // Detect Submit to show "Loading..." state
        document.body.addEventListener('submit', function(e) {
            if (e.target.matches('.comment-form')) { // Ensure your form has class 'comment-form'
                const input = e.target.querySelector('textarea');
                const val = input.value;

                // Check if a bot was summoned
                const summonedBot = bots.find(b => val.includes('@' + b.name));

                if (summonedBot) {
                    // Find the container to append the "Ghost" comment
                    const postCard = e.target.closest('.card'); // Assuming post is in a card
                    const commentsList = postCard.querySelector('.comments-list');
                    const postId = postCard.id.replace('post-', '');

                    if (commentsList) {
                        const loadingHtml = `
                            <div id="typing-${postId}" class="d-flex mb-3 animate__animated animate__pulse animate__infinite">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px;">
                                        <i class="ri-robot-line"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="bg-light p-3 rounded">
                                        <span class="text-muted small fst-italic">
                                            <i class="ri-loader-4-line ri-spin me-1"></i>
                                            ${summonedBot.name} is investigating...
                                        </span>
                                    </div>
                                </div>
                            </div>
                        `;
                        commentsList.insertAdjacentHTML('beforeend', loadingHtml);
                    }
                }
            }
        });
    });
</script>
@endpush
