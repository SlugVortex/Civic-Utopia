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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Candidate Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('candidates.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" placeholder="e.g. Jamaica" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Region/Constituency (Optional)</label>
                                <input type="text" name="region" class="form-control" placeholder="e.g. St. Andrew South">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Candidate Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Party Affiliation</label>
                                <input type="text" name="party" class="form-control" placeholder="e.g. JLP, PNP, Democrat" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Office Running For</label>
                            <input type="text" name="office" class="form-control" placeholder="e.g. Prime Minister, MP, Senator" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Manifesto / Platform Text</label>
                            <textarea name="manifesto_text" class="form-control" rows="10" placeholder="Paste the candidate's speech, website text, or manifesto here. AI will analyze this." required></textarea>
                            <div class="form-text">The AI needs this text to generate stances and answer user questions.</div>
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
@endsection
