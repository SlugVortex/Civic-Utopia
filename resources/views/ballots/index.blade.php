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

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="text-white mb-2">🗳️ Ballot Decoder</h3>
                            <p class="mb-0">
                                Official ballot questions translated into plain English and Patois by AI.
                                Understand before you vote.
                            </p>
                        </div>
                        <i class="ti ti-files fs-1 text-white opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($ballots as $ballot)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="badge bg-label-primary">{{ $ballot->election_date ? $ballot->election_date->format('M d, Y') : 'Upcoming' }}</span>
                    @if($ballot->summary_patois)
                        <span class="badge bg-success" title="AI Analysis Complete"><i class="ti ti-check"></i> Decoded</span>
                    @else
                        <span class="badge bg-secondary" title="Pending Analysis">Raw Text</span>
                    @endif
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $ballot->title }}</h5>
                    <p class="card-text text-muted">
                        {{ Str::limit($ballot->official_text, 120) }}
                    </p>
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
            <p class="text-muted">There are no upcoming elections or referendums loaded in the system yet.</p>
            <a href="{{ route('ballots.create') }}" class="btn btn-outline-primary mt-2">Add the first one</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
