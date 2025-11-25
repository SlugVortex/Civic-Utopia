@extends('layouts/layoutMaster')

@section('title', 'Legal Library')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><span class="text-muted fw-light">CivicUtopia /</span> Legal Library</h4>
        <a href="{{ route('documents.create') }}" class="btn btn-primary"><i class="ti ti-upload me-1"></i> Upload Document</a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('documents.index') }}" class="row gx-3 align-items-center">
                <div class="col-md-4"><input type="text" class="form-control" name="search" placeholder="Search bills..." value="{{ request('search') }}"></div>
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="Bill">Bills</option>
                        <option value="Policy">Policies</option>
                        <option value="Report">Reports</option>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Search</button></div>
            </form>
        </div>
    </div>

    <!-- Grid -->
    <div class="row">
        @forelse($documents as $doc)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-label-primary">{{ $doc->type }}</span>
                        <small class="text-muted">{{ $doc->country }}</small>
                    </div>
                    <h5 class="card-title">{{ $doc->title }}</h5>
                    <p class="small text-muted mb-3">
                        {{ Str::limit($doc->summary_plain, 100) }}
                    </p>
                    <a href="{{ route('documents.show', $doc->id) }}" class="btn btn-outline-primary w-100">Read & Discuss</a>
                </div>
                <div class="card-footer border-top d-flex justify-content-between text-muted small">
                    <span><i class="ti ti-calendar me-1"></i> {{ $doc->created_at->format('M d') }}</span>
                    <span><i class="ti ti-message me-1"></i> {{ $doc->annotations()->count() }} Notes</span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5"><p class="text-muted">No documents found.</p></div>
        @endforelse
    </div>
</div>
@endsection
