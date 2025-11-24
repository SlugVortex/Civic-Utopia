@php
$pageConfigs = ['myLayout' => 'vertical'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Post by ' . $post->user->name)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            {{-- Back Button --}}
            <div class="mb-3">
                {{--! FIX: Changed url()->previous() to a direct route() call --}}
                <a href="{{ route('dashboard') }}" class="btn btn-light"><i class="ri-arrow-left-s-line me-1"></i> Back to Feed</a>
            </div>

            {{-- Main Post Card --}}
            @include('posts._post_card', ['post' => $post])

            {{-- Comment Form --}}
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h5 class="mb-3">Post a Comment</h5>
                    <form action="{{ route('comments.store', $post) }}" method="POST">
                        @csrf
                        <div class="d-flex gap-3">
                            <div class="avatar flex-shrink-0">
                               <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                            </div>
                            <div class="flex-grow-1">
                                <textarea name="content" class="form-control" rows="3" placeholder="Join the discussion... (try typing @Historian!)" required></textarea>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary">Post Comment</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Comments Feed --}}
            <div id="comment-feed">
                @forelse ($post->comments as $comment)
                    <div class="card mb-3 post-card" id="comment-{{ $comment->id }}">
                        <div class="card-body p-4">
                            <div class="d-flex gap-3">
                                <div class="avatar flex-shrink-0">
                                     <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="fw-bold">{{ $comment->user->name }}</span>
                                            <small class="text-muted ms-2">· {{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    <p class="post-content mb-0" style="white-space: pre-wrap;">{{ $comment->content }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted p-4">
                        <p>No comments yet. Be the first to reply!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
