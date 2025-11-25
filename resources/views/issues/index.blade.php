@extends('layouts/layoutMaster')

@section('title', 'Civic Lens')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">CivicUtopia /</span> Civic Lens
        </h4>
        <a href="{{ route('issues.create') }}" class="btn btn-primary">
            <i class="ti ti-camera me-1"></i> Report Issue
        </a>
    </div>

    <div class="row">
        @forelse($issues as $issue)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="position-relative">
                    <img src="{{ asset('storage/' . $issue->image_path) }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                    @if($issue->severity)
                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">{{ $issue->severity }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $issue->title ?? 'New Report' }}</h5>
                    <p class="text-muted small mb-2"><i class="ti ti-map-pin me-1"></i> {{ $issue->location }}</p>
                    <p class="card-text">
                        {{ Str::limit($issue->ai_caption ?? $issue->user_description ?? 'No description yet.', 80) }}
                    </p>
                    <a href="{{ route('issues.show', $issue->id) }}" class="btn btn-outline-primary w-100">View Status</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="ti ti-camera fs-1 text-muted mb-3"></i>
            <h5>No issues reported.</h5>
            <p>Community looks good! Or maybe you should report something?</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
