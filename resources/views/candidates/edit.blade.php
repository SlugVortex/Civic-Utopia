@extends('layouts/layoutMaster')

@section('title', 'Edit Candidate')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Candidates /</span> Edit Profile
        </h4>
        <a href="{{ route('candidates.show', $candidate->id) }}" class="btn btn-label-secondary">Cancel</a>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('candidates.update', $candidate->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block cursor-pointer" onclick="document.getElementById('photoInput').click();">
                                <img id="photoPreview" src="{{ $candidate->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($candidate->name).'&background=random&size=150' }}" class="rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #fff;">
                                <div class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2" style="transform: translate(10%, 10%);">
                                    <i class="ti ti-camera"></i>
                                </div>
                            </div>
                            <div class="form-text mt-2">Tap to change photo</div>
                            <input type="file" name="photo" id="photoInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Candidate Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $candidate->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Party</label>
                                <input type="text" name="party" class="form-control" value="{{ $candidate->party }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Office</label>
                            <input type="text" name="office" class="form-control" value="{{ $candidate->office }}" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="{{ $candidate->country }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Region</label>
                                <input type="text" name="region" class="form-control" value="{{ $candidate->region }}">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Manifesto Text</label>
                            <textarea name="manifesto_text" class="form-control" rows="10" required>{{ $candidate->manifesto_text }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) { document.getElementById('photoPreview').src = e.target.result; }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
