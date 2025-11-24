@extends('layouts/layoutMaster')

@section('title', $ballot->title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y position-relative">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Ballot /</span> {{ $ballot->country }}
            </h4>
            @if($ballot->region) <small class="text-muted">{{ $ballot->region }}</small> @endif
        </div>
        <a href="{{ route('ballots.index') }}" class="btn btn-label-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
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
                    <p>This ballot has not been decoded yet.</p>
                    <form action="{{ route('ballots.analyze', $ballot->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-wand me-2"></i> Decode with AI Agent
                        </button>
                    </form>
                </div>
            @else
                <!-- TRANSLATION TOOLBAR -->
                <div class="card mb-3 bg-label-secondary">
                    <div class="card-body py-2 d-flex align-items-center justify-content-between">
                        <span class="fw-bold"><i class="ti ti-world me-2"></i>Translate this page:</span>
                        <select class="form-select w-px-200" id="languageSelector" onchange="translatePage(this.value)">
                            <option value="English" selected>English (Original)</option>
                            <option value="Jamaican Patois">Jamaican Patois</option>
                            <option value="Spanish">Spanish</option>
                            <option value="French">French</option>
                            <option value="Chinese">Chinese (Simplified)</option>
                            <option value="Hindi">Hindi</option>
                            <option value="Russian">Russian</option>
                            <option value="Arabic">Arabic</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Breakdown Section (ELI5) -->
                <div class="card mb-4 border-primary shadow-sm">
                    <div class="card-header bg-label-primary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary" id="lbl-breakdown">
                            <i class="ti ti-language me-2"></i>
                            {{ $ballot->country == 'Jamaica' ? 'Patois Breakdown' : 'Local Dialect Breakdown' }}
                        </h5>
                        <button class="btn btn-primary btn-sm rounded-pill" onclick="playAudio(document.getElementById('txt-breakdown').innerText, this)">
                            <i class="ti ti-volume me-1"></i> Listen
                        </button>
                    </div>
                    <div class="card-body pt-3">
                        <p class="fs-5 fst-italic mb-0 text-dark" id="txt-breakdown">
                            "{{ $ballot->summary_patois }}"
                        </p>
                    </div>
                </div>

                <!-- Standard Summary (Hidden if translated to avoid redundancy, or kept as reference) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Formal Summary</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0" id="txt-summary">{{ $ballot->summary_plain }}</p>
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
                                <p class="mb-0 text-dark" id="txt-yes">{{ $ballot->yes_vote_meaning }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0 text-white"><i class="ti ti-x me-2"></i> If you vote NO</h6>
                            </div>
                            <div class="card-body bg-label-danger">
                                <p class="mb-0 text-dark" id="txt-no">{{ $ballot->no_vote_meaning }}</p>
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
                                <ul class="list-group list-group-flush" id="list-pros">
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
                                <ul class="list-group list-group-flush" id="list-cons">
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

        <!-- RIGHT COLUMN: Meta Data -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Election Date</h6>
                    <h4 class="mb-4 text-primary">{{ $ballot->election_date->format('F j, Y') }}</h4>

                    <h6 class="text-muted text-uppercase small fw-bold">Official Question Title</h6>
                    <p class="mb-4">{{ $ballot->title }}</p>

                    <h6 class="text-muted text-uppercase small fw-bold">Official Legal Text (Word-for-Word)</h6>
                    <div class="alert alert-secondary p-3 mb-0 small" style="max-height: 300px; overflow-y: auto;" id="txt-official">
                        {{ $ballot->official_text }}
                    </div>
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
// Translation Logic
function translatePage(language) {
    const selector = document.getElementById('languageSelector');
    selector.disabled = true;
    document.body.style.cursor = 'wait';

    // Show loading state on main fields
    document.getElementById('txt-official').style.opacity = '0.5';
    document.getElementById('txt-breakdown').style.opacity = '0.5';

    fetch('{{ route("ballots.translate", $ballot->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ language: language })
    })
    .then(res => res.json())
    .then(data => {
        if(data.error) throw new Error(data.error);

        // Update Official Text
        document.getElementById('txt-official').innerText = data.official_text;

        // Update Breakdown (ELI5)
        document.getElementById('lbl-breakdown').innerHTML = `<i class="ti ti-language me-2"></i> ${data.breakdown_label}`;
        document.getElementById('txt-breakdown').innerText = data.breakdown_text;

        // Update Standard Fields
        document.getElementById('txt-yes').innerText = data.yes_vote_meaning;
        document.getElementById('txt-no').innerText = data.no_vote_meaning;

        // Update Lists
        const prosList = document.getElementById('list-pros');
        prosList.innerHTML = '';
        data.pros.forEach(item => {
            prosList.innerHTML += `<li class="list-group-item px-0"><i class="ti ti-arrow-right text-success me-2"></i> ${item}</li>`;
        });

        const consList = document.getElementById('list-cons');
        consList.innerHTML = '';
        data.cons.forEach(item => {
            consList.innerHTML += `<li class="list-group-item px-0"><i class="ti ti-arrow-right text-danger me-2"></i> ${item}</li>`;
        });

        // Restore UI
        document.getElementById('txt-official').style.opacity = '1';
        document.getElementById('txt-breakdown').style.opacity = '1';
        selector.disabled = false;
        document.body.style.cursor = 'default';
    })
    .catch(err => {
        console.error(err);
        alert('Translation failed. Please try again.');
        selector.disabled = false;
        document.body.style.cursor = 'default';
        document.getElementById('txt-official').style.opacity = '1';
    });
}

// Audio Playback
function playAudio(text, btnElement) {
    if (!text) return alert("No text available.");
    const originalContent = btnElement.innerHTML;
    btnElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
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
        new Audio(audioSrc).play();
        btnElement.innerHTML = originalContent;
        btnElement.disabled = false;
    })
    .catch(error => {
        alert('Could not generate audio.');
        btnElement.innerHTML = originalContent;
        btnElement.disabled = false;
    });
}

// Chat Bot
function handleEnter(e) { if (e.key === 'Enter') sendQuestion(); }

function sendQuestion() {
    const input = document.getElementById('chat-input');
    const history = document.getElementById('chat-history');
    const question = input.value.trim();
    if(!question) return;

    history.innerHTML += `<div class="d-flex justify-content-end mb-2"><div class="bg-primary text-white rounded p-2 small" style="max-width: 80%">${question}</div></div>`;
    input.value = '';

    const loadingId = 'loading-' + Date.now();
    history.innerHTML += `<div id="${loadingId}" class="d-flex justify-content-start mb-2"><div class="bg-label-secondary rounded p-2 small text-muted">Thinking...</div></div>`;
    history.scrollTop = history.scrollHeight;

    fetch('{{ route("ballots.ask", $ballot->id) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ question: question })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById(loadingId).remove();
        history.innerHTML += `<div class="d-flex justify-content-start mb-2"><div class="bg-label-secondary rounded p-2 small" style="max-width: 80%">${data.answer || "No answer found."}</div></div>`;
        history.scrollTop = history.scrollHeight;
    })
    .catch(err => {
        document.getElementById(loadingId).remove();
        history.innerHTML += `<div class="text-danger small">Error connecting to AI.</div>`;
    });
}
</script>
@endsection
