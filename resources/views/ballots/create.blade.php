@extends('layouts/layoutMaster')

@section('title', 'Add New Ballot')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Ballot Box /</span> Add New Question
        </h4>
        <a href="{{ route('ballots.index') }}" class="btn btn-label-secondary">Cancel</a>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">New Ballot Question</h5>
                    <small class="text-muted float-end">Official Records</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('ballots.store') }}" method="POST">
                        @csrf

                        <!-- Location Section -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="country">Country</label>
                                <input type="text" class="form-control" id="country" name="country" placeholder="e.g. Jamaica" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="region">Region / Parish / State (Optional)</label>
                                <input type="text" class="form-control" id="region" name="region" placeholder="e.g. Kingston" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="title">Ballot Title / Question Name</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="e.g., Referendum on Constitutional Amendment 4" required />
                            <div class="form-text">Give it a clear, recognizable name.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="election_date">Election Date</label>
                            <input type="date" class="form-control" id="election_date" name="election_date" required />
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="official_text">Official Legal Text (The Bill)</label>
                            <textarea class="form-control" id="official_text" name="official_text" rows="10" placeholder="Paste the full legal text of the bill or question here..." required></textarea>
                            <div class="form-text">Paste the full, raw text. We will use AI to decode this later.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-plus me-1"></i> Save & Analyze
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
