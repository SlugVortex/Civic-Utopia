@php
$pageConfigs = ['myLayout' => 'vertical'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Civic Document Search')

@section('content')
<h4 class="py-3 mb-4">
    Search Civic Documents
</h4>

<div class="row">
    <div class="col-md-12">
        {{-- Display Error Messages --}}
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Search Form --}}
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('search.perform') }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" class="form-control" name="query" placeholder="Ask a question about the documents..." value="{{ $query ?? '' }}" required>
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Search Results --}}
        @if (isset($query) && $query)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Results for "{{ $query }}"</h5>
            </div>
            <div class="card-body">
                @forelse ($results as $result)
                    <div class="mb-4 border-bottom pb-3">
                        <p class="mb-2">
                            <strong class="text-primary"><i class="ri-file-text-line me-1"></i>Source Document:</strong>
                            {{-- Display the sourcefile, with a fallback if it's missing --}}
                            {{ $result['sourcefile'] ?? 'Unknown' }}
                        </p>
                        <p class="text-muted">{{ Str::limit($result['content'], 400) }}</p>
                    </div>
                @empty
                    <p>No results found for your query.</p>
                @endforelse
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
