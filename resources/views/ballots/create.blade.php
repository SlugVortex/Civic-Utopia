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

            <!-- AI GENERATOR CARD -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-label-primary">
                    <h5 class="card-title text-primary mb-0"><i class="ti ti-wand me-2"></i>AI Ballot Generator</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Don't know how to write a formal bill? Just describe your idea, and the AI will draft it for you.</p>
                    <div class="input-group">
                        <input type="text" id="ai-prompt" class="form-control" placeholder="e.g., A bill for hurricane safety in Jamaica">
                        <button class="btn btn-primary" type="button" onclick="generateBallot()">
                            <i class="ti ti-sparkles me-1"></i> Generate
                        </button>
                    </div>
                </div>
            </div>

            <!-- MANUAL ENTRY CARD -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manual Entry</h5>
                    <small class="text-muted float-end">Official Records</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('ballots.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="country">Country</label>
                                <input type="text" class="form-control" id="country" name="country" placeholder="e.g. Jamaica" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="region">Region / State (Optional)</label>
                                <input type="text" class="form-control" id="region" name="region" placeholder="e.g. Kingston" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="title">Ballot Title</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="e.g., Constitutional Amendment 4" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="election_date">Election Date</label>
                            <input type="date" class="form-control" id="election_date" name="election_date" required />
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="official_text">Official Legal Text</label>
                            <textarea class="form-control" id="official_text" name="official_text" rows="8" placeholder="Paste the full legal text here..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-plus me-1"></i> Save Ballot
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateBallot() {
    const prompt = document.getElementById('ai-prompt').value;
    const btn = event.currentTarget;
    const originalHtml = btn.innerHTML;

    if (!prompt) {
        alert('Please enter an idea for the ballot question.');
        return;
    }

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';
    btn.disabled = true;

    fetch('{{ route("ballots.generate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ prompt: prompt })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) throw new Error(data.error);

        // Auto-fill the form below
        document.getElementById('title').value = data.title;
        document.getElementById('official_text').value = data.official_text;

        // Visual feedback
        document.getElementById('title').classList.add('is-valid');
        document.getElementById('official_text').classList.add('is-valid');
    })
    .catch(err => {
        alert('Failed to generate ballot text: ' + err.message);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}
</script>
@endsection
