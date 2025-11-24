@extends('layouts/layoutMaster')

@section('title', $ballot->title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y position-relative">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Ballot /</span> Decoder
        </h4>
        <a href="{{ route('ballots.index') }}" class="btn btn-label-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to List
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- LEFT COLUMN: AI Analysis -->
        <div class="col-lg-8 mb-4">

            @if(!$ballot->summary_patois)
                <!-- State: Not Analyzed Yet -->
                <div class="card mb-4 text-center p-5">
                    <i class="ti ti-robot fs-1 text-primary mb-3"></i>
                    <h3>AI Analysis Needed</h3>
                    <p>This ballot has not been decoded yet. Click below to ask Azure AI to explain it.</p>
                    <form action="{{ route('ballots.analyze', $ballot->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-wand me-2"></i> Decode with AI Agent
                        </button>
                    </form>
                </div>
            @else
                <!-- State: Analyzed -->

                <!-- Patois Section -->
                <div class="card mb-4 border-primary shadow-sm">
                    <div class="card-header bg-label-primary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary"><i class="ti ti-language me-2"></i> Jamaican Patois Breakdown</h5>
                        <button class="btn btn-primary btn-sm rounded-pill" onclick="playAudio('{{ addslashes($ballot->summary_patois) }}', this)">
                            <i class="ti ti-volume me-1"></i> Listen
                        </button>
                    </div>
                    <div class="card-body pt-3">
                        <p class="fs-5 fst-italic mb-0 text-dark">
                            "{{ $ballot->summary_patois }}"
                        </p>
                    </div>
                </div>

                <!-- Plain English Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Plain English Summary</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $ballot->summary_plain }}</p>
                    </div>
                </div>

                <!-- Yes/No Implications -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100 border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0 text-white"><i class="ti ti-check me-2"></i> If you vote YES</h6>
                            </div>
                            <div class="card-body bg-label-success">
                                <p class="mb-0 text-dark">{{ $ballot->yes_vote_meaning }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0 text-white"><i class="ti ti-x me-2"></i> If you vote NO</h6>
                            </div>
                            <div class="card-body bg-label-danger">
                                <p class="mb-0 text-dark">{{ $ballot->no_vote_meaning }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pros & Cons -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Arguments For</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    @foreach($ballot->pros ?? [] as $pro)
                                        <li class="list-group-item px-0"><i class="ti ti-arrow-right text-success me-2"></i> {{ $pro }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Arguments Against</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    @foreach($ballot->cons ?? [] as $con)
                                        <li class="list-group-item px-0"><i class="ti ti-arrow-right text-danger me-2"></i> {{ $con }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <!-- RIGHT COLUMN: Official Meta Data -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Election Date</h6>
                    <h4 class="mb-4 text-primary">{{ $ballot->election_date->format('F j, Y') }}</h4>

                    <h6 class="text-muted text-uppercase small fw-bold">Official Question Title</h6>
                    <p class="mb-4">{{ $ballot->title }}</p>

                    <h6 class="text-muted text-uppercase small fw-bold">Original Legal Text</h6>
                    <div class="alert alert-secondary p-3 mb-0 small" style="max-height: 300px; overflow-y: auto;">
                        {{ $ballot->official_text }}
                    </div>
                </div>
            </div>

            <!-- Context Tool -->
            <div class="card bg-label-info border-info">
                <div class="card-body">
                    <h5 class="card-title text-info"><i class="ti ti-info-circle me-2"></i>Note</h5>
                    <p class="card-text small mb-0">
                        This information is generated by AI to help you understand the ballot. It is not an official government statement. Always read the full bill if you are unsure.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- AI CHAT FLOATING BUTTON -->
    <div style="position: fixed; bottom: 30px; right: 30px; z-index: 9999;">
        <button class="btn btn-primary rounded-pill shadow-lg p-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#aiChatModal">
            <i class="ti ti-message-chatbot fs-2"></i>
            <span class="fw-bold d-none d-md-inline">Ask the Ballot Bot</span>
        </button>
    </div>
</div>

<!-- AI Chat Modal -->
<div class="modal fade" id="aiChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-label-primary">
                <h5 class="modal-title" id="exampleModalLabel"><i class="ti ti-robot me-2"></i> Ask about this Ballot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="chat-history" class="mb-3" style="max-height: 300px; overflow-y: auto;">
                    <div class="text-center text-muted small my-2">Ask questions like: "Will this raise taxes?" or "Who does this affect?"</div>
                </div>
                <div class="input-group">
                    <input type="text" id="chat-input" class="form-control" placeholder="Type your question..." onkeypress="handleEnter(event)">
                    <button class="btn btn-primary" type="button" onclick="sendQuestion()">
                        <i class="ti ti-send"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
// Audio Playback
function playAudio(text, btnElement) {
    if (!text) {
        alert("No text available to read.");
        return;
    }
    const originalContent = btnElement.innerHTML;
    btnElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
    btnElement.disabled = true;

    fetch('{{ route("speech.generate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ text: text })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) throw new Error(data.error);
        const audioSrc = "data:audio/mp3;base64," + data.audio;
        const audio = new Audio(audioSrc);
        audio.play();
        btnElement.innerHTML = originalContent;
        btnElement.disabled = false;
    })
    .catch(error => {
        console.error('Error generating speech:', error);
        alert('Could not generate audio. Check API keys.');
        btnElement.innerHTML = originalContent;
        btnElement.disabled = false;
    });
}

// Chat Bot Logic
function handleEnter(e) {
    if (e.key === 'Enter') sendQuestion();
}

function sendQuestion() {
    const input = document.getElementById('chat-input');
    const history = document.getElementById('chat-history');
    const question = input.value.trim();
    if(!question) return;

    // Append User Message
    history.innerHTML += `<div class="d-flex justify-content-end mb-2"><div class="bg-primary text-white rounded p-2 small" style="max-width: 80%">${question}</div></div>`;
    input.value = '';

    // Loading Indicator
    const loadingId = 'loading-' + Date.now();
    history.innerHTML += `<div id="${loadingId}" class="d-flex justify-content-start mb-2"><div class="bg-label-secondary rounded p-2 small text-muted">Thinking...</div></div>`;
    history.scrollTop = history.scrollHeight;

    fetch('{{ route("ballots.ask", $ballot->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ question: question })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById(loadingId).remove();
        const answer = data.answer || "I couldn't find an answer.";
        history.innerHTML += `<div class="d-flex justify-content-start mb-2"><div class="bg-label-secondary rounded p-2 small" style="max-width: 80%">${answer}</div></div>`;
        history.scrollTop = history.scrollHeight;
    })
    .catch(err => {
        document.getElementById(loadingId).remove();
        history.innerHTML += `<div class="text-danger small">Error connecting to AI.</div>`;
    });
}
</script>
@endsection
