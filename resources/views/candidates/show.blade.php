@extends('layouts/layoutMaster')

@section('title', $candidate->name)

@section('content')
@include('_partials.ai-disclaimer')
<div class="container-xxl flex-grow-1 container-p-y position-relative">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Candidates /</span> {{ $candidate->country }}
            </h4>
            @if($candidate->region) <small class="text-muted">{{ $candidate->region }}</small> @endif
        </div>
        <div>
            <a href="{{ route('candidates.edit', $candidate->id) }}" class="btn btn-outline-secondary me-2"><i class="ti ti-pencil"></i> Edit</a>
            <a href="{{ route('candidates.index') }}" class="btn btn-label-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <!-- LEFT: Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <!-- Photo -->
                    <div class="mx-auto mb-3">
                        <img src="{{ $candidate->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($candidate->name).'&background=random&size=150' }}" class="rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>

                    <h2 class="mb-1">{{ $candidate->name }}</h2>
                    <p class="text-muted mb-2">{{ $candidate->office }}</p>
                    <span class="badge bg-primary p-2 fs-6 mb-4">{{ $candidate->party }}</span>

                    <hr>

                    <!-- Summary Section -->
                    <div class="text-start">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-uppercase text-muted fw-bold">AI Summary</small>
                            {{-- ID ADDED: btn-read-summary (For AI Navigator) --}}
                            <button id="btn-read-summary" class="btn btn-xs btn-icon btn-label-primary rounded-pill" onclick="toggleAudio('txt-summary', this)">
                                <i class="ti ti-volume"></i>
                            </button>
                        </div>
                        <p class="small" id="txt-summary">{{ $candidate->ai_summary ?? 'Pending Analysis...' }}</p>
                    </div>

                    @if(!$candidate->ai_summary)
                    <form action="{{ route('candidates.analyze', $candidate->id) }}" method="POST">
                        @csrf
                        {{-- ID ADDED: btn-analyze-profile (For AI Navigator) --}}
                        <button id="btn-analyze-profile" class="btn btn-outline-primary w-100 mt-3"><i class="ti ti-wand me-1"></i> Initial Analysis</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- RIGHT: Stances & Tools -->
        <div class="col-md-8">

            <!-- TOOLBAR: Language -->
            <div class="card mb-3 bg-label-secondary">
                <div class="card-body py-2 d-flex align-items-center justify-content-between">
                    <span class="fw-bold"><i class="ti ti-world me-2"></i>Translate Profile:</span>
                    {{-- ID ADDED: langSelector (For AI Navigator 'set_value') --}}
                    <select class="form-select w-px-200" id="langSelector" onchange="translateProfile(this.value)">
                        <option value="English">English</option>
                        <option value="Jamaican Patois">Patois</option>
                        <option value="Spanish">Spanish</option>
                        <option value="French">French</option>
                        <option value="Chinese">Chinese</option>
                    </select>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-label-primary text-primary">
                    <h5 class="mb-0 text-primary"><i class="ti ti-list-check me-2"></i>Key Stances</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row" id="stances-container">
                        @if($candidate->stances)
                            @foreach($candidate->stances as $issue => $stance)
                            {{-- CLASS: stance-card (For AI Navigator 'click_match') --}}
                            <div class="col-md-6 mb-4 stance-card" data-key="{{ $issue }}">
                                <div class="p-3 border rounded h-100 shadow-sm position-relative">

                                    <!-- Header: Title + Research Button -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="text-dark fw-bold text-uppercase small mb-0 issue-title">{{ $issue }}</h6>
                                        {{-- CLASS: btn-research-stance (For AI Navigator) --}}
                                        <button class="btn btn-xs btn-outline-primary btn-research-stance" onclick="researchStance('{{ $issue }}', this)" title="Search Web & Update">
                                            <i class="ti ti-world-search"></i> Research
                                        </button>
                                    </div>

                                    <!-- Text Body -->
                                    <p class="mb-3 text-muted small stance-text" id="stance-{{ Str::slug($issue) }}">
                                        {{ $stance }}
                                    </p>

                                    <!-- Audio Button (Bottom Right) -->
                                    <div class="text-end">
                                        {{-- CLASS: btn-read-stance (For AI Navigator) --}}
                                        <button class="btn btn-xs btn-icon btn-label-secondary rounded-pill btn-read-stance" onclick="toggleAudio('stance-{{ Str::slug($issue) }}', this)">
                                            <i class="ti ti-volume"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-5">
                                <p class="text-muted">No specific stances extracted yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Raw Manifesto Toggle -->
            <div class="accordion" id="manifestoAcc">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rawText">
                            View Full Manifesto Source
                        </button>
                    </h2>
                    <div id="rawText" class="accordion-collapse collapse" data-bs-parent="#manifestoAcc">
                        <div class="accordion-body small text-muted">
                            {{ $candidate->manifesto_text }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI CHAT FLOATING BUTTON -->
    <div style="position: fixed; bottom: 30px; right: 30px; z-index: 9999;">
        {{-- ID ADDED: btn-ask-candidate (For AI Navigator) --}}
        <button id="btn-ask-candidate" class="btn btn-primary rounded-pill shadow-lg p-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#aiChatModal">
            <i class="ti ti-message-chatbot fs-2"></i>
            <span class="fw-bold d-none d-md-inline">Ask {{ explode(' ', $candidate->name)[0] }}'s Bot</span>
        </button>
    </div>
</div>

<!-- AI Chat Modal -->
<div class="modal fade" id="aiChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-label-primary">
                <h5 class="modal-title"><i class="ti ti-robot me-2"></i> Chat with {{ $candidate->name }}'s AI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="chat-history" class="mb-3" style="max-height: 300px; overflow-y: auto;">
                    <div class="text-center text-muted small my-2">
                        Ask about their platform. E.g., "What is your plan for crime?"
                    </div>
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

<script>
// --- AUDIO LOGIC (Shared) ---
let currentAudio = null;
let currentBtn = null;

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
    const iconEl = btnElement.querySelector('i');
    const originalIconClass = iconEl.className;
    btnElement.dataset.icon = originalIconClass;

    btnElement.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 0.8rem; height: 0.8rem;"></span>';
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
        alert('Audio generation failed.');
        stopCurrentAudio();
    });
}

function stopCurrentAudio() {
    if (currentAudio) { currentAudio.pause(); currentAudio = null; }
    if (currentBtn) {
        const iconClass = currentBtn.dataset.icon || 'ti ti-volume';
        currentBtn.innerHTML = `<i class="${iconClass}"></i>`;
        currentBtn.disabled = false;
        currentBtn = null;
    }
}

// --- RESEARCH LOGIC ---
function researchStance(topic, btn) {
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;

    fetch('{{ route("candidates.researchStance", $candidate->id) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ topic: topic })
    })
    .then(res => res.json())
    .then(data => {
        if(data.error) throw new Error(data.error);

        // Find text container via closest card logic
        const card = btn.closest('.stance-card');
        const p = card.querySelector('.stance-text');
        if(p) {
            p.innerText = data.stance;
            p.classList.add('bg-label-success', 'p-1', 'rounded');
            setTimeout(() => p.classList.remove('bg-label-success', 'p-1', 'rounded'), 2000);
        }
        alert('Research updated! Chat Bot is now aware of this new info.');
    })
    .catch(err => {
        alert('Research failed: ' + err.message);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

// --- TRANSLATION LOGIC (UPDATED) ---
function translateProfile(lang) {
    const selector = document.getElementById('langSelector');
    selector.disabled = true;
    document.body.style.cursor = 'wait';

    fetch('{{ route("candidates.translate", $candidate->id) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ language: lang })
    })
    .then(res => res.json())
    .then(data => {
        if(data.error) throw new Error(data.error);

        // Update Summary
        document.getElementById('txt-summary').innerText = data.ai_summary;

        // Update Stances using data-key to handle styling/case mismatch
        const stanceCards = document.querySelectorAll('.stance-card');
        stanceCards.forEach(card => {
            const key = card.dataset.key;
            const textEl = card.querySelector('.stance-text');

            if(data.stances && data.stances[key]) {
                textEl.innerText = data.stances[key];
            }
        });

        selector.disabled = false;
        document.body.style.cursor = 'default';
        stopCurrentAudio();
    })
    .catch(err => {
        console.error(err);
        alert('Translation failed.');
        selector.disabled = false;
        document.body.style.cursor = 'default';
    });
}


// --- CHAT LOGIC ---
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

    fetch('{{ route("candidates.ask", $candidate->id) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ question: question })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById(loadingId).remove();
        const answer = data.answer || "I couldn't find that in my platform.";
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
