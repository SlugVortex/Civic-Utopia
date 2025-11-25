@extends('layouts/layoutMaster')

@section('title', 'Report Issue')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Civic Lens /</span> New Report
        </h4>
        <a href="{{ route('issues.index') }}" class="btn btn-label-secondary">Cancel</a>
    </div>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Snap & Report</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('issues.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <div class="input-group">
                                <input type="text" name="location" id="locationInput" class="form-control" placeholder="e.g. 25 Hope Road, Kingston" required>
                                <button class="btn btn-outline-primary" type="button" id="getLocationBtn" onclick="getLocation()">
                                    <i class="ti ti-current-location me-1"></i> Get My Location
                                </button>
                            </div>
                            <div class="form-text">Where is this problem?</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Photo Evidence</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Short Description (Optional)</label>
                            <textarea name="user_description" class="form-control" rows="3" placeholder="e.g. Huge pothole damaging cars..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-camera me-1"></i> Upload Photo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getLocation() {
    const btn = document.getElementById('getLocationBtn');
    const input = document.getElementById('locationInput');

    if (!navigator.geolocation) {
        alert("Geolocation is not supported by your browser");
        return;
    }

    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Locating...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(success, error);

    function success(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        // Use OpenStreetMap Nominatim for Reverse Geocoding (Free, No Key Needed)
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        .then(response => response.json())
        .then(data => {
            if(data.display_name) {
                // Simplify address (take first 3 parts)
                const parts = data.display_name.split(',').slice(0, 3).join(',');
                input.value = parts;
            } else {
                input.value = `${lat}, ${lng}`;
            }
            btn.innerHTML = '<i class="ti ti-check me-1"></i> Found';
            btn.className = "btn btn-success";
        })
        .catch(err => {
            console.error(err);
            input.value = `${lat}, ${lng}`;
            btn.innerHTML = '<i class="ti ti-map-pin me-1"></i> Coordinates Set';
            btn.disabled = false;
        });
    }

    function error() {
        alert("Unable to retrieve your location");
        btn.innerHTML = '<i class="ti ti-current-location me-1"></i> Retry';
        btn.disabled = false;
    }
}
</script>
@endsection
