@extends('layouts/layoutMaster')

@section('title', 'Compare Candidates')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Candidates /</span> Head-to-Head
        </h4>
        <a href="{{ route('candidates.index') }}" class="btn btn-label-secondary">Cancel</a>
    </div>

    <div class="alert alert-primary d-flex align-items-center" role="alert">
        <i class="ti ti-info-circle me-2"></i>
        <div>Select exactly <strong>two</strong> candidates to generate an AI comparison.</div>
    </div>

    <form action="{{ route('candidates.compare.analyze') }}" method="POST" id="compareForm">
        @csrf

        <!-- Sticky Action Bar -->
        <div class="card mb-4 sticky-top shadow-sm" style="top: 20px; z-index: 1020;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Select Opponents</h5>
                <div>
                    <span id="count-badge" class="badge bg-label-secondary me-2">0/2 Selected</span>
                    <button type="submit" id="compare-btn" class="btn btn-primary" disabled>
                        <i class="ti ti-scale me-1"></i> Analyze Differences
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($candidates as $candidate)
            <div class="col-md-4 col-lg-3 mb-4">
                <label class="card h-100 cursor-pointer border-2 selection-card" style="transition: all 0.2s;">
                    <div class="card-body text-center">
                        <input type="checkbox" name="selected_candidates[]" value="{{ $candidate->id }}" class="form-check-input position-absolute top-0 end-0 m-3 candidate-checkbox" style="transform: scale(1.5);">

                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-label-secondary" style="width: 80px; height: 80px;">
                            <span class="fs-2">{{ substr($candidate->party, 0, 1) }}</span>
                        </div>

                        <h6 class="card-title mb-1">{{ $candidate->name }}</h6>
                        <span class="badge bg-label-primary mb-2">{{ $candidate->party }}</span>
                        <div class="small text-muted">{{ $candidate->country }}</div>
                    </div>
                </label>
            </div>
            @endforeach
        </div>
    </form>
</div>

<script>
// Simple Logic to enforce 2 selections
const checkboxes = document.querySelectorAll('.candidate-checkbox');
const btn = document.getElementById('compare-btn');
const badge = document.getElementById('count-badge');
const form = document.getElementById('compareForm');

checkboxes.forEach(cb => {
    cb.addEventListener('change', () => {
        const checked = document.querySelectorAll('.candidate-checkbox:checked');

        // Visual Feedback
        checkboxes.forEach(box => {
            if(box.checked) {
                box.closest('.card').classList.add('border-primary');
                box.closest('.card').classList.add('bg-label-primary');
            } else {
                box.closest('.card').classList.remove('border-primary');
                box.closest('.card').classList.remove('bg-label-primary');
            }
        });

        // Enforce Limit
        if (checked.length > 2) {
            cb.checked = false;
            cb.closest('.card').classList.remove('border-primary');
            cb.closest('.card').classList.remove('bg-label-primary');
            alert("You can only compare 2 candidates at a time.");
            return;
        }

        // Update UI
        badge.innerText = `${checked.length}/2 Selected`;
        if (checked.length === 2) {
            btn.disabled = false;
            badge.classList.remove('bg-label-secondary');
            badge.classList.add('bg-success');
        } else {
            btn.disabled = true;
            badge.classList.add('bg-label-secondary');
            badge.classList.remove('bg-success');
        }
    });
});

form.addEventListener('submit', () => {
    // Show Loading State
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> AI Analyzing...';
    btn.disabled = true;
});
</script>
@endsection
