@php
$pageConfigs = ['myLayout' => 'vertical'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Document Management')

@section('content')
<h4 class="py-3 mb-4">
    Upload and Process Civic Documents
</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Upload New Document</h5>
            </div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="document" class="form-label">Select PDF Document</label>
                        <input class="form-control" type="file" id="document" name="document" accept=".pdf" required>
                        @error('document')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Upload to Azure</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
