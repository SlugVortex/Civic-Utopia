@extends('layouts/layoutMaster')

@section('title', 'Upload Document')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header"><h5>Upload Legal Document</h5></div>
                <div class="card-body">
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" placeholder="e.g. Jamaica" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">PDF File (Max 20MB)</label>
                            <input type="file" name="file" class="form-control" accept="application/pdf" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Upload & Analyze</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
