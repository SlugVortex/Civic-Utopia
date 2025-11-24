@php
$pageConfigs = ['myLayout' => 'vertical'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Manage Topics')

@section('content')
<h4 class="py-3 mb-4">Manage Community Topics</h4>

<div class="row">
    {{-- Create Topic Form --}}
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Create New Topic</h5>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <form action="{{ route('admin.topics.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Topic Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g., Public Safety" value="{{ old('name') }}" required>
                        @error('name')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="icon" class="form-label">RemixIcon Class</label>
                        <input type="text" class="form-control" id="icon" name="icon" placeholder="e.g., ri-government-line" value="{{ old('icon') }}" required>
                        <small class="form-text">Find icons at <a href="https://remixicon.com/" target="_blank">remixicon.com</a>.</small>
                         @error('icon')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="color" class="form-label">Color Class</label>
                        <input type="text" class="form-control" id="color" name="color" placeholder="e.g., text-primary" value="{{ old('color') }}" required>
                        <small class="form-text">Use Bootstrap text color classes like 'text-primary', 'text-success', etc.</small>
                         @error('color')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Create Topic</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Topics List --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Existing Topics</h5>
            </div>
            <div class="table-responsive text-nowrap">
                @if (session('success'))
                    <div class="alert alert-success mx-4">{{ session('success') }}</div>
                @endif
                <table class="table">
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Name</th>
                            <th>Posts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($topics as $topic)
                            <tr>
                                <td><i class="{{ $topic->icon }} {{ $topic->color }}" style="font-size: 1.5rem;"></i></td>
                                <td><strong>{{ $topic->name }}</strong></td>
                                <td><span class="badge bg-label-secondary">{{ $topic->posts_count }}</span></td>
                                <td>
                                    <form action="{{ route('admin.topics.destroy', $topic) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this topic?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-text-danger item-delete"><i class="ri-delete-bin-7-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No topics found. Create one!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
