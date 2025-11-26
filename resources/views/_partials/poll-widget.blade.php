@if(isset($activePoll) && $activePoll)
{{-- FIXED: Removed 'border-info' and text color classes to prevent dark mode clash --}}
<div class="card mb-4 sidebar-widget" id="poll-widget-{{ $activePoll->id }}">

    {{-- FIXED: Added Collapse Button and toggle functionality --}}
    <div class="card-header pb-2 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 text-primary">
            <i class="ri-bar-chart-box-line me-2"></i>Community Poll
        </h6>
        <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle" type="button" data-bs-toggle="collapse" data-bs-target="#pollBody{{ $activePoll->id }}" aria-expanded="true">
            <i class="ri-arrow-down-s-line"></i>
        </button>
    </div>

    <div class="collapse show" id="pollBody{{ $activePoll->id }}">
        <div class="card-body pt-0">
            <p class="fw-semibold mb-3 mt-2">{{ $activePoll->question }}</p>

            @php
                $userVoted = $activePoll->votes->contains('user_id', auth()->id());
                $totalVotes = $activePoll->votes->count();
            @endphp

            @if($userVoted)
                <div class="poll-results animate__animated animate__fadeIn">
                    @foreach($activePoll->options as $option)
                        @php
                            $optionVotes = $option->votes->count();
                            $percentage = $totalVotes > 0 ? round(($optionVotes / $totalVotes) * 100) : 0;
                            $isUserChoice = $option->votes->contains('user_id', auth()->id());
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="{{ $isUserChoice ? 'fw-bold text-primary' : '' }}">
                                    {{ $option->label }}
                                    @if($isUserChoice) <i class="ri-check-line"></i> @endif
                                </span>
                                <span>{{ $percentage }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $isUserChoice ? 'bg-primary' : 'bg-secondary' }}"
                                     role="progressbar"
                                     style="width: {{ $percentage }}%"
                                     aria-valuenow="{{ $percentage }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                        </div>
                    @endforeach
                    <small class="text-muted d-block text-center mt-2">{{ $totalVotes }} votes total</small>
                </div>
            @else
                <form id="poll-form-{{ $activePoll->id }}" onsubmit="submitVote(event, {{ $activePoll->id }})">
                    <div class="d-grid gap-2 mb-3">
                        @foreach($activePoll->options as $option)
                            <input type="radio" class="btn-check" name="option_id" id="option-{{ $option->id }}" value="{{ $option->id }}" autocomplete="off" required>
                            <label class="btn btn-outline-secondary text-start btn-sm" for="option-{{ $option->id }}">{{ $option->label }}</label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Vote</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endif
