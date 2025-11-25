<div class="card mb-3 post-card" id="post-{{ $post->id }}">
    <div class="card-body p-4">
        <div class="d-flex gap-3">
            {{-- Avatar Logic: Strictly checks for Agent Email to force the GIF --}}
            <div class="avatar flex-shrink-0">
                @if($post->user->email === 'agent@civicutopia.ai')
                    {{-- Force Agent Avatar --}}
                    <img src="{{ asset('assets/img/logo-sm.gif') }}" alt="Agent AI" class="rounded-circle">
                @else
                    {{-- Standard User Avatar --}}
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
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-text-secondary btn-read-aloud p-0 me-2" data-post-id="{{ $post->id }}" title="Read post aloud">
                            <i class="ri-volume-up-line"></i>
                        </button>

                        @if (Auth::check() && $post->user_id === Auth::id())
                          <div class="dropdown">
                            <button class="btn p-0" type="button" id="postAction-{{ $post->id }}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i class="ri-more-2-line"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="postAction-{{ $post->id }}">
                                {{-- NEW "Explain" Button --}}
      <button class="dropdown-item btn-explain" data-post-id="{{ $post->id }}">
          <i class="ri-rocket-line me-2"></i>Explain Like I'm 5
      </button>

      <div class="dropdown-divider"></div>

      <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Delete Post</button>
      </form>
                            </div>
                          </div>
                        @endif
                    </div>
                </div>

                <div class="post-content mb-3 text-break">
                    {!! Str::markdown($post->content) !!}
                </div>

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
                                            {{-- FIX: Reverted to 'path' to match your database column --}}
                                            <img src="{{ asset('storage/' . $media->path) }}" alt="Post media" class="carousel-image" data-full-image="{{ asset('storage/' . $media->path) }}">
                                        </div>
                                    @endforeach
                                </div>

                                @if($images->count() > 1)
                                    <button class="carousel-btn prev-btn"><i class="ri-arrow-left-s-line"></i></button>
                                    <button class="carousel-btn next-btn"><i class="ri-arrow-right-s-line"></i></button>
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
                            {{-- FIX: Reverted to 'path' --}}
                            <source src="{{ asset('storage/' . $media->path) }}" type="{{ $media->mime_type }}">
                        </video>
                    @endforeach
                @endif

                <div class="summary-container alert alert-info" style="display: none;">
                    <strong class="d-block mb-1"><i class="ri-sparkling-2-line me-1"></i>AI Summary</strong>
                    <p class="mb-0 summary-content"></p>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 post-actions">
                    <div class="d-flex gap-2">
                        <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-text-secondary">
                            <i class="ri-chat-3-line me-1"></i> {{ $post->comments->count() }}
                        </a>
                        <button class="btn btn-sm btn-text-secondary btn-like {{ $post->is_liked ? 'liked' : '' }}" data-post-id="{{ $post->id }}">
                            <i class="ri-heart-{{ $post->is_liked ? 'fill' : 'line' }} me-1"></i>
                            <span class="like-count">{{ $post->likers->count() > 0 ? $post->likers->count() : '' }}</span>
                        </button>
                        <button class="btn btn-sm btn-text-secondary btn-share" data-url="{{ route('posts.show', $post) }}">
                            <i class="ri-share-forward-line me-1"></i> Share
                        </button>
                        <button class="btn btn-sm btn-text-secondary btn-summarize" data-post-id="{{ $post->id }}">
                            <i class="ri-sparkling-2-line me-1"></i>
                            <span class="button-text">Summarize</span>
                        </button>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-text-secondary btn-bookmark {{ $post->is_bookmarked ? 'active' : '' }}" data-post-id="{{ $post->id }}">
                            <i class="ri-bookmark-{{ $post->is_bookmarked ? 'fill' : 'line' }} me-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
