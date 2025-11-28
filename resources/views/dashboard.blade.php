@php
$pageConfigs = ['myLayout' => 'vertical'];
$activeTopic = $activeTopic ?? null;
@endphp

@extends('layouts/layoutMaster')

@section('title', $activeTopic ? $activeTopic->name : 'Digital Town Square')

@section('content')
<style>
    /* Independent Sidebar Scrolling Logic */
    @media (min-width: 992px) {
        .sticky-column {
            position: sticky;
            top: 5rem;
            height: calc(100vh - 6rem);
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 5px;
        }
        .sticky-column::-webkit-scrollbar { width: 4px; }
        .sticky-column::-webkit-scrollbar-track { background: transparent; }
        .sticky-column::-webkit-scrollbar-thumb { background: #d3d3d3; border-radius: 4px; }
    }
</style>

<div class="container-fluid">
    <div class="row">
        {{-- Sidebar Column --}}
        <div class="col-lg-3 col-md-5 order-0 order-md-0">
            <div class="sticky-column pt-lg-2">

                @include('_partials.poll-widget')

                <div class="card mb-4 sidebar-widget border-primary">
                    <div class="card-body">
                        <h6 class="card-title text-primary"><i class="ri-robot-2-line me-2"></i>Civic Pulse AI</h6>
                        <p class="small text-muted mb-3">
                            Use your location to generate hyper-localized news reports.
                        </p>

                        <button id="btn-localize-news" class="btn btn-sm btn-primary w-100">
                            <i class="ri-map-pin-user-line me-1"></i> Generate Local Feed
                        </button>

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

                @include('_partials.suggestion-widget')

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
                    {{-- Comments hidden on dashboard --}}
                    @include('posts._post_card', ['post' => $post, 'showComments' => false])
                @empty
                    <div id="no-posts-message" class="card card-body text-center text-muted p-5">
                        <p>No posts found. Be the first to share something!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div id="image-modal-container"></div>

{{-- MODALS --}}
@include('_partials.suggestion-modal')
@include('_partials.explanation-modal')

@endsection
