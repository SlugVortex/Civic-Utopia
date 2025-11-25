@extends('layouts/layoutMaster')

@section('title', 'Issue Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Report /</span> {{ $issue->title }}
        </h4>
        <a href="{{ route('issues.index') }}" class="btn btn-label-secondary">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- LEFT: Image & Analysis -->
        <div class="col-md-5 mb-4">
            <div class="card h-100">
                <img src="{{ asset('storage/' . $issue->image_path) }}" class="card-img-top" alt="Issue Evidence" style="max-height: 400px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title">Visual Evidence</h5>
                    <p class="text-muted mb-2"><i class="ti ti-map-pin me-1"></i> {{ $issue->location }}</p>

                    @if($issue->ai_caption)
                        <div class="alert alert-primary d-flex align-items-center" role="alert">
                            <i class="ti ti-eye me-2"></i>
                            <div><strong>AI Sees:</strong> "{{ $issue->ai_caption }}"</div>
                        </div>
                        <div class="mb-3">
                            @foreach($issue->ai_tags ?? [] as $tag)
                                <span class="badge bg-label-secondary me-1 mb-1">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning">AI has not analyzed this photo yet.</div>
                        <form action="{{ route('issues.analyze', $issue->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-primary w-100 pulse-button">
                                <i class="ti ti-wand me-1"></i> Analyze & Draft Letter
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- RIGHT: The Generated Letter -->
        <div class="col-md-7 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Formal Complaint Letter</h5>
                        @if(request('agency'))
                             <small class="text-muted">Target Agency: <strong>{{ request('agency') }}</strong></small>
                        @endif
                    </div>
                    <div>
                        @if($issue->generated_letter)
                        <button class="btn btn-label-secondary btn-sm rounded-pill btn-icon" onclick="toggleAudio('letter-content', this)">
                            <i class="ti ti-volume"></i>
                        </button>
                        @endif
                        @if($issue->severity)
                            <span class="badge ms-2 {{ $issue->severity == 'Critical' || $issue->severity == 'High' ? 'bg-danger' : 'bg-warning' }}">
                                {{ $issue->severity }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($issue->generated_letter)
                        <div id="letter-content" class="bg-light p-4 rounded border font-monospace mb-3" style="white-space: pre-wrap; font-size: 0.9rem;">{{ $issue->generated_letter }}</div>

                        <!-- EMAIL LOGIC -->
                        @php
                            // Agency Directory (Matches Controller)
                            $directory = [
                                'National Works Agency (Roads)' => 'commsmanager@nwa.gov.jm',
                                'National Water Commission (Water)' => 'pr@nwc.com.jm',
                                'NSWMA (Garbage)' => 'complaints@nswma.gov.jm',
                                'JPS (Electricity)' => 'customer@jpsco.com',
                                'KSAMC (Kingston Corp)' => 'customerservice@ksamc.gov.jm',
                                'Police (JCF)' => 'contact@jcf.gov.jm',
                            ];

                            $targetEmail = '';
                            $targetAgency = request('agency') ?? 'Agency';

                            if (array_key_exists($targetAgency, $directory)) {
                                $targetEmail = $directory[$targetAgency];
                            }

                            $mailtoLink = "mailto:$targetEmail?subject=" . rawurlencode("Formal Complaint: " . $issue->title) . "&body=" . rawurlencode($issue->generated_letter);
                        @endphp

                        <div class="d-flex gap-2">
                            <a href="{{ $mailtoLink }}" class="btn btn-primary">
                                <i class="ti ti-mail me-1"></i> Email {{ $targetEmail ? 'to ' . $targetAgency : 'Agency' }}
                            </a>
                            <button class="btn btn-label-secondary" onclick="window.print()">
                                <i class="ti ti-printer me-1"></i> Print
                            </button>
                        </div>
                        @if(!$targetEmail)
                            <div class="form-text mt-2 text-warning"><i class="ti ti-alert-triangle me-1"></i> AI couldn't auto-detect email. Please enter recipient manually in your mail app.</div>
                        @endif

                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-file-description fs-1 text-muted opacity-25 mb-3"></i>
                            <h5 class="text-muted">No Letter Generated Yet</h5>
                            <button class="btn btn-outline-primary" disabled>Analyze First</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// --- AUDIO LOGIC (Copied & Simplified) ---
let currentAudio = null;
let currentBtn = null;
let originalIcon = '<i class="ti ti-volume"></i>';

function toggleAudio(elementId, btnElement) {
    const element = document.getElementById(elementId);
    if (!element) return;
    const text = element.innerText;

    if (currentAudio && currentBtn === btnElement) {
        stopCurrentAudio();
        return;
    }
    if (currentAudio) stopCurrentAudio();

    currentBtn = btnElement;
    originalIcon = btnElement.innerHTML;
    btnElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btnElement.disabled = true;

    fetch('{{ route("speech.generate") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ text: text })
    })
    .then(res => res.json())
    .then(data => {
        if(data.error) throw new Error(data.error);
        const audioSrc = "data:audio/mp3;base64," + data.audio;
        currentAudio = new Audio(audioSrc);
        currentAudio.play();
        btnElement.innerHTML = '<i class="ti ti-player-stop"></i>';
        btnElement.disabled = false;
        currentAudio.onended = () => stopCurrentAudio();
    })
    .catch(err => {
        alert('Speech generation failed.');
        stopCurrentAudio();
    });
}

function stopCurrentAudio() {
    if (currentAudio) { currentAudio.pause(); currentAudio = null; }
    if (currentBtn) { currentBtn.innerHTML = '<i class="ti ti-volume"></i>'; currentBtn.disabled = false; currentBtn = null; }
}
</script>
@endsection
