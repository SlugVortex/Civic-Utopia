@extends('layouts/layoutMaster')

@section('title', 'Add Candidate')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Candidates /</span> Add New Profile
        </h4>
        <a href="{{ route('candidates.index') }}" class="btn btn-label-secondary">Cancel</a>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">

            <!-- AI RESEARCH CARD -->
            <div class="card mb-4 border-primary">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title text-primary mb-1"><i class="ti ti-world-search me-2"></i>Auto-Researcher</h5>
                        <p class="card-text text-muted small mb-0">Enter Name & Country, then click Research to auto-fill manifesto & details.</p>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="performResearch()">
                        <i class="ti ti-sparkles me-1"></i> Auto-Fill
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Candidate Details</h5>
                </div>
                <div class="card-body">
                    <!-- IMPORTANT: enctype="multipart/form-data" is required for file upload -->
                    <form action="{{ route('candidates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- PHOTO UPLOAD SECTION -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block cursor-pointer" onclick="document.getElementById('photoInput').click();">
                                <img id="photoPreview" src="https://ui-avatars.com/api/?name=Candidate&background=random&size=150" class="rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #fff;">
                                <div class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2" style="transform: translate(10%, 10%);">
                                    <i class="ti ti-camera-plus"></i>
                                </div>
                            </div>
                            <div class="form-text mt-2">Click avatar to upload a photo</div>
                            <!-- Hidden File Input -->
                            <input type="file" name="photo" id="photoInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" id="country" name="country" class="form-control" placeholder="e.g. Jamaica" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Region/Constituency (Optional)</label>
                                <input type="text" id="region" name="region" class="form-control" placeholder="e.g. St. Andrew South">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Candidate Name</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Full Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Party Affiliation</label>
                                <input type="text" id="party" name="party" class="form-control" placeholder="e.g. JLP, PNP" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Office Running For</label>
                            <input type="text" id="office" name="office" class="form-control" placeholder="e.g. Prime Minister" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Manifesto / Platform Text</label>
                            <textarea id="manifesto_text" name="manifesto_text" class="form-control" rows="10" placeholder="Paste text or use Auto-Fill..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-check me-1"></i> Save Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image Preview Logic
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('photoPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// AI Research Logic (Text Only)
function performResearch() {
    const name = document.getElementById('name').value;
    const country = document.getElementById('country').value;
    const btn = event.currentTarget;
    const originalText = btn.innerHTML;

    if (!name || !country) {
        alert('Please enter a Name and Country first.');
        return;
    }

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Researching...';
    btn.disabled = true;

    fetch('{{ route("candidates.research") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ name: name, country: country })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) throw new Error(data.error);

        // Fill Text Fields
        if(data.party) document.getElementById('party').value = data.party;
        if(data.office) document.getElementById('office').value = data.office;
        if(data.region) document.getElementById('region').value = data.region;
        if(data.manifesto_text) document.getElementById('manifesto_text').value = data.manifesto_text;

        // Update Avatar Placeholder Name
        document.getElementById('photoPreview').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random&size=150`;

        alert('Research complete! Review details and upload a photo.');
    })
    .catch(error => {
        console.error(error);
        alert('Research failed. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
@endsection
