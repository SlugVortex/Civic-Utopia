@extends('layouts/layoutMaster')

@section('title', $candidate->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y position-relative">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Candidates /</span> {{ $candidate->country }}
            </h4>
            @if($candidate->region) <small class="text-muted">{{ $candidate->region }}</small> @endif
        </div>
        <a href="{{ route('candidates.index') }}" class="btn btn-label-secondary">Back</a>
    </div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                         <span class="badge bg-primary p-2 fs-5">{{ $candidate->party }}</span>
                    </div>
                    <h2 class="mb-1">{{ $candidate->name }}</h2>
                    <p class="text-muted">{{ $candidate->office }}</p>
                    <hr>
                    <div class="text-start">
                        <small class="text-uppercase text-muted fw-bold">AI Summary</small>
                        <p class="mt-2">{{ $candidate->ai_summary ?? 'Pending Analysis...' }}</p>
                    </div>

                    @if(!$candidate->ai_summary)
                    <form action="{{ route('candidates.analyze', $candidate->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-primary w-100 mt-3"><i class="ti ti-wand me-1"></i> Analyze Manifesto</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stances Grid -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-label-primary text-primary">
                    <h5 class="mb-0 text-primary"><i class="ti ti-list-check me-2"></i>Key Stances</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row">
                        @if($candidate->stances)
                            @foreach($candidate->stances as $issue => $stance)
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded h-100 shadow-sm">
                                    <h6 class="text-dark fw-bold mb-2 text-uppercase small">{{ $issue }}</h6>
                                    <p class="mb-0 text-muted">{{ $stance }}</p>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-5">
                                <p class="text-muted">No specific stances extracted yet. Click "Analyze" on the left.</p>
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
        <button class="btn btn-primary rounded-pill shadow-lg p-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#aiChatModal">
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
function handleEnter(e) { if (e.key === 'Enter') sendQuestion(); }

function sendQuestion() {
    const input = document.getElementById('chat-input');
    const history = document.getElementById('chat-history');
    const question = input.value.trim();
    if(!question) return;

    // UI: User Message
    history.innerHTML += `<div class="d-flex justify-content-end mb-2"><div class="bg-primary text-white rounded p-2 small" style="max-width: 80%">${question}</div></div>`;
    input.value = '';

    // UI: Loading
    const loadingId = 'loading-' + Date.now();
    history.innerHTML += `<div id="${loadingId}" class="d-flex justify-content-start mb-2"><div class="bg-label-secondary rounded p-2 small text-muted">Checking manifesto...</div></div>`;
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
