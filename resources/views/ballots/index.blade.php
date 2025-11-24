@extends('layouts/layoutMaster')

@section('title', 'Ballot Box')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">CivicUtopia /</span> Ballot Box
        </h4>
        <a href="{{ route('ballots.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add New Ballot
        </a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('ballots.index') }}" class="row gx-3 gy-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search ballots..." value="{{ request('search') }}">
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
                    <a href="{{ route('ballots.index') }}" class="btn btn-label-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @forelse($ballots as $ballot)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-label-primary">{{ $ballot->election_date ? $ballot->election_date->format('M d, Y') : 'Upcoming' }}</span>
                        @if($ballot->country)
                            <span class="badge bg-label-info ms-1">{{ $ballot->country }}</span>
                        @endif
                    </div>
                    @if($ballot->summary_patois)
                        <i class="ti ti-check text-success" title="Decoded"></i>
                    @endif
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $ballot->title }}</h5>
                    <p class="card-text text-muted">
                        {{ Str::limit($ballot->official_text, 100) }}
                    </p>
                    @if($ballot->region)
                        <small class="text-muted"><i class="ti ti-map-pin me-1"></i>{{ $ballot->region }}</small>
                    @endif
                </div>
                <div class="card-footer border-top">
                    <a href="{{ route('ballots.show', $ballot->id) }}" class="btn btn-primary w-100">
                        <i class="ti ti-eye me-1"></i> View Decoder
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="text-muted mb-3">
                <i class="ti ti-folder-off fs-1"></i>
            </div>
            <h5>No ballot questions found.</h5>
            <p class="text-muted">Try adjusting your search filters.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
