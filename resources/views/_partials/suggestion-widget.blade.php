<div class="card mb-4 sidebar-widget">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 card-title"><i class="ri-lightbulb-line me-2"></i>Suggestions</h6>
        {{-- Button triggers modal, but modal HTML is now in dashboard.blade.php --}}
        <button class="btn btn-xs btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#suggestionModal">
            <i class="ri-add-line"></i> Add
        </button>
    </div>

    <div class="list-group list-group-flush">
        @if(isset($suggestions) && $suggestions->isNotEmpty())
            @foreach($suggestions as $suggestion)
                @php
                    $hasUpvoted = $suggestion->votes->contains('id', auth()->id());
                @endphp
                <div class="list-group-item px-3 py-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1 pe-2">
                            <div class="fw-semibold small text-truncate" style="max-width: 180px;">{{ $suggestion->title }}</div>
                            <small class="text-muted" style="font-size: 0.7rem;">by {{ $suggestion->user->name }}</small>
                        </div>

                        <button class="btn btn-sm btn-icon {{ $hasUpvoted ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill"
                                onclick="toggleSuggestionVote(this, {{ $suggestion->id }})"
                                style="width: 32px; height: 32px;">
                            <span class="d-flex flex-column align-items-center justify-content-center" style="line-height: 1;">
                                <i class="ri-arrow-up-s-fill" style="font-size: 1rem;"></i>
                                <span class="vote-count small" style="font-size: 0.6rem;">{{ $suggestion->votes_count }}</span>
                            </span>
                        </button>
                    </div>
                </div>
            @endforeach
        @else
            <div class="p-3 text-center text-muted small">
                No active suggestions. Be the first!
            </div>
        @endif
    </div>
    <div class="card-footer py-2 text-center">
        <small class="text-muted">Top approved ideas</small>
    </div>
</div>
{{-- MOVED MODAL TO dashboard.blade.php TO FIX Z-INDEX --}}
