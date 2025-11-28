<div class="card mb-3 post-card transition-all" id="post-{{ $post->id }}">
    <div class="card-body p-4 d-flex flex-column h-100">

        {{-- HEADER --}}
        <div class="d-flex gap-3 flex-shrink-0 post-header">
            <div class="avatar flex-shrink-0">
                @if(Str::startsWith($post->user->email, 'agent'))
                    <img src="{{ $post->user->profile_photo_url ?? asset('assets/img/logo-sm.gif') }}" alt="Agent AI" class="rounded-circle">
                @else
                    <img src="{{ $post->user->profile_photo_url ?? asset('assets/img/avatars/1.png') }}" alt="User Avatar" class="rounded-circle">
                @endif
            </div>

            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="fw-bold">{{ $post->user->name }}</span>
                        <small class="text-muted ms-2">
                            {{ '@' . ($post->user->email ? Str::of($post->user->email)->before('@') : 'user') }}
                        </small>
                        <small class="text-muted ms-2">· {{ $post->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-icon btn-text-secondary btn-fullscreen" onclick="toggleChatFullscreen({{ $post->id }})" title="Full Screen Chat">
                            <i class="ri-fullscreen-line"></i>
                        </button>
                        <button class="btn btn-sm btn-text-secondary btn-read-aloud p-0" data-post-id="{{ $post->id }}" title="Read post aloud">
                            <i class="ri-volume-up-line"></i>
                        </button>
                        @if (Auth::check() && $post->user_id === Auth::id())
                          <div class="dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown"><i class="ri-more-2-line"></i></button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <button class="dropdown-item btn-explain" data-post-id="{{ $post->id }}"><i class="ri-rocket-line me-2"></i>Explain Like I'm 5</button>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Delete Post</button>
                                </form>
                            </div>
                          </div>
                        @endif
                    </div>
                </div>

                {{-- BODY CONTENT --}}
                <div class="post-content mb-3 text-break">
                    {!! Str::markdown($post->content) !!}
                </div>

                {{-- MEDIA --}}
                 @if($post->media && $post->media->isNotEmpty())
                    @php $images = $post->media->where('file_type', 'image'); $videos = $post->media->where('file_type', 'video'); @endphp
                    @if($images->isNotEmpty())
                        <div class="post-carousel mb-3" data-post-id="{{ $post->id }}">
                            <div class="carousel-container">
                                <div class="carousel-track">
                                    @foreach($images as $index => $media)
                                        <div class="carousel-slide {{ $index === 0 ? 'active' : '' }}">
                                            <img src="{{ asset('storage/' . $media->path) }}" alt="Post media" class="carousel-image" data-full-image="{{ asset('storage/' . $media->path) }}">
                                        </div>
                                    @endforeach
                                </div>
                                @if($images->count() > 1)
                                    <button class="carousel-btn prev-btn"><i class="ri-arrow-left-s-line"></i></button>
                                    <button class="carousel-btn next-btn"><i class="ri-arrow-right-s-line"></i></button>
                                    <div class="carousel-indicators">
                                        @foreach($images as $index => $media) <span class="indicator {{ $index === 0 ? 'active' : '' }}"></span> @endforeach
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

                <div class="summary-container alert alert-info" style="display: none;">
                    <strong class="d-block mb-1"><i class="ri-sparkling-2-line me-1"></i>AI Summary</strong>
                    <p class="mb-0 summary-content"></p>
                </div>

                {{-- ACTIONS --}}
                <div class="d-flex justify-content-between align-items-center pt-2 post-actions border-bottom pb-3">
                    <div class="d-flex gap-2">
                        @if(!($showComments ?? false))
                            <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-text-secondary comment-count-btn">
                                <i class="ri-chat-3-line me-1"></i> {{ $post->comments->count() }}
                            </a>
                        @else
                            <button class="btn btn-sm btn-text-secondary comment-count-btn" disabled>
                                <i class="ri-chat-3-line me-1"></i> {{ $post->comments->count() }}
                            </button>
                        @endif
                        <button class="btn btn-sm btn-text-secondary btn-like {{ $post->is_liked ? 'liked' : '' }}" data-post-id="{{ $post->id }}">
                            <i class="ri-heart-{{ $post->is_liked ? 'fill' : 'line' }} me-1"></i>
                            <span class="like-count">{{ $post->likers->count() > 0 ? $post->likers->count() : '' }}</span>
                        </button>
                        <button class="btn btn-sm btn-text-secondary btn-share" data-url="{{ route('posts.show', $post) }}">
                            <i class="ri-share-forward-line me-1"></i> Share
                        </button>
                        <button class="btn btn-sm btn-text-secondary btn-summarize" data-post-id="{{ $post->id }}">
                            <i class="ri-sparkling-2-line me-1"></i> Summarize
                        </button>
                    </div>
                </div>

                {{-- CHAT SECTION (Visible ONLY if $showComments is true) --}}
                @if($showComments ?? false)
                <div class="chat-section d-flex flex-column flex-grow-1 mt-3">

                    {{-- Comments Container --}}
                    <div class="comments-list mb-3 custom-scrollbar flex-grow-1" id="comments-container-{{ $post->id }}" style="max-height: 350px; overflow-y: auto; padding-right: 5px;">
                        @foreach($post->comments as $comment)
                            <div class="d-flex mb-3 comment-item animate__animated animate__fadeIn" id="comment-{{ $comment->id }}">
                                <div class="flex-shrink-0">
                                     <img src="{{ $comment->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($comment->user->name).'&background=random' }}" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <div class="comment-bubble px-3 py-2 rounded-3 position-relative">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold text-dark small mb-0">{{ $comment->user->name }}</span>
                                            <small class="text-muted" style="font-size: 0.7rem">{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>

                                        <div class="comment-text small mb-0 text-break" id="comment-text-{{ $comment->id }}">
                                            {!! Str::markdown($comment->content) !!}
                                        </div>

                                        <div class="comment-actions">
                                            <button class="btn btn-xs btn-icon rounded-pill"
                                                    onclick="initReply({{ $post->id }}, '{{ $comment->user->name }}', `{{ addslashes($comment->content) }}`)"
                                                    title="Reply">
                                                <i class="ri-reply-line"></i>
                                            </button>
                                            <button class="btn btn-xs btn-icon rounded-pill"
                                                    onclick="toggleGlobalAudio('comment-text-{{ $comment->id }}', this)"
                                                    title="Read aloud">
                                                <i class="ri-volume-up-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Input Area --}}
                    <div class="comment-form-wrapper mt-auto pt-2">
                        <div class="reply-preview-container mb-2" style="display:none;"></div>

                        {{-- FORM WITH 'comment-form' CLASS --}}
                        <form action="{{ route('comments.store', $post->id) }}" method="POST" class="comment-form">
                            @csrf
                            <input type="hidden" name="reply_to_context" value="">

                            <div class="d-flex gap-2 align-items-end comment-input-group">
                                <img src="{{ auth()->user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=random' }}" class="rounded-circle user-avatar-img" style="width: 36px; height: 36px; object-fit: cover;">

                                <div class="flex-grow-1 position-relative">
                                    {{-- TEXTAREA WITH 'comment-textarea' CLASS --}}
                                    <textarea name="content" class="form-control comment-textarea rounded-pill px-4 py-2"
                                              rows="1" placeholder="Type @ to mention bots..." required
                                              style="resize:none; overflow-y:hidden; min-height: 42px; max-height: 120px;"
                                              oninput="autoGrowTextarea(this)"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary rounded-circle send-btn d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; flex-shrink: 0;">
                                    <i class="ri-send-plane-fill"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
