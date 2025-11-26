@extends('layouts/layoutMaster')

@section('title', 'Candidate Compass')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">CivicUtopia /</span> Candidate Compass
        </h4>
        <div>
            {{-- ID ADDED: btn-compare-candidates --}}
            <a href="{{ route('candidates.compare.select') }}" class="btn btn-outline-primary me-2" id="btn-compare-candidates">
                <i class="ti ti-scale me-1"></i> Compare
            </a>

            {{-- ID ADDED: btn-add-candidate --}}
            <a href="{{ route('candidates.create') }}" class="btn btn-primary" id="btn-add-candidate">
                <i class="ti ti-plus me-1"></i> Add Candidate
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('candidates.index') }}" class="row gx-3 gy-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search name or party..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="country">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-label-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('candidates.index') }}" class="btn btn-label-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @forelse($candidates as $candidate)
        <div class="col-md-6 col-lg-4 mb-4">
            {{-- CLASS ADDED: candidate-card (Target for AI) --}}
            <div class="card h-100 candidate-card">
                <div class="card-body text-center">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-label-secondary" style="width: 100px; height: 100px; overflow:hidden;">
                        @if($candidate->photo_url)
                            <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @elseif(Str::contains($candidate->party, ['JLP', 'Republican']))
                            <i class="ti ti-bell fs-1 text-success"></i>
                        @elseif(Str::contains($candidate->party, ['PNP', 'Democrat']))
                            <i class="ti ti-user fs-1 text-warning"></i>
                        @else
                            <i class="ti ti-flag fs-1 text-primary"></i>
                        @endif
                    </div>
                    <h5 class="card-title">{{ $candidate->name }}</h5>
                    <div class="mb-2">
                        <span class="badge bg-label-primary">{{ $candidate->country }}</span>
                        <span class="badge bg-label-secondary">{{ $candidate->party }}</span>
                    </div>
                    <p class="text-muted small">{{ $candidate->office }}</p>

                    <p class="small text-start bg-light p-2 rounded text-muted">
                        <i class="ti ti-robot me-1"></i>
                        {{ Str::limit($candidate->ai_summary ?? 'No analysis yet.', 80) }}
                    </p>

                    {{-- CLASS ADDED: btn-view-stances --}}
                    <a href="{{ route('candidates.show', $candidate->id) }}" class="btn btn-primary w-100 btn-view-stances">View Stances</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="ti ti-users fs-1 text-muted mb-3"></i>
            <h5>No candidates found.</h5>
            <p>Try adjusting filters or add a new candidate.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
