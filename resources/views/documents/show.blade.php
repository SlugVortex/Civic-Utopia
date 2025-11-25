@extends('layouts/layoutMaster')

@section('title', $document->title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" style="height: 85vh;">

    <!-- HEADER BAR -->
    <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-3 rounded shadow-sm">
        <div class="d-flex align-items-center overflow-hidden">
            <a href="{{ route('documents.index') }}" class="btn btn-icon btn-label-secondary me-3"><i class="ti ti-arrow-left"></i></a>
            <div>
                <h5 class="mb-0 text-truncate" style="max-width: 400px;">
                    {{ $document->title }}
                </h5>
                <small class="text-muted">
                    {{ $document->country }} • {{ $document->type }} •
                    @if($document->is_public)
                        <span class="text-success fw-bold"><i class="ti ti-world me-1"></i>Public</span>
                    @else
                        <span class="text-warning fw-bold"><i class="ti ti-lock me-1"></i>Private Draft</span>
                    @endif
                </small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- PUBLISH TOGGLE BUTTON -->
            <form action="{{ route('documents.publish', $document->id) }}" method="POST">
                @csrf
                @if($document->is_public)
                    <button type="submit" class="btn btn-label-warning">
                        <i class="ti ti-eye-off me-1"></i> Unpublish
                    </button>
                @else
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-world-upload me-1"></i> Publish to Feed
                    </button>
                @endif
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row" style="height: calc(100% - 80px);">
        <!-- LEFT: PDF Viewer -->
        <div class="col-md-7 h-100">
            <div class="card h-100">
                <iframe src="{{ asset('storage/' . $document->file_path) }}" width="100%" height="100%" style="border:none; border-radius: 8px;"></iframe>
            </div>
        </div>

        <!-- RIGHT: AI & Tools -->
        <div class="col-md-5 h-100">
            <div class="card h-100">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs nav-fill" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-summary">Summary</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-chat">AI Chat</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-notes">Notes</button></li>
                    </ul>
                </div>
                <div class="card-body tab-content overflow-auto" style="height: calc(100% - 50px);">

                    <!-- TAB 1: Summary -->
                    <div class="tab-pane fade show active" id="tab-summary">
                        <!-- REGENERATE BUTTON -->
                        <div class="d-flex justify-content-end mb-3">
                            <form action="{{ route('documents.regenerate', $document->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will re-scan the PDF.');">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-primary">
                                    <i class="ti ti-refresh me-1"></i> Regenerate Analysis
                                </button>
                            </form>
                        </div>

                        <h6 class="text-uppercase text-muted small fw-bold">Plain English</h6>
                        <p class="text-dark">{{ $document->summary_plain ?? 'No summary yet.' }}</p>
                        <hr>
                        <h6 class="text-uppercase text-primary small fw-bold">Explain Like I'm 5</h6>
                        <div class="bg-label-primary p-3 rounded">
                            {{ $document->summary_eli5 ?? 'No explanation yet.' }}
                        </div>
                    </div>

                    <!-- TAB 2: Chat -->
                    <div class="tab-pane fade" id="tab-chat">
                         <div class="d-flex justify-content-end mb-2">
                             <button class="btn btn-xs btn-label-secondary" onclick="clearChat()">
                                <i class="ti ti-eraser me-1"></i> Clear Chat
                            </button>
                        </div>
                        <div id="chat-box" class="mb-3 border rounded p-3 bg-light" style="height: 300px; overflow-y: auto;">
                            <div class="text-center text-muted small mt-5">
                                <i class="ti ti-message-chatbot fs-1 mb-2"></i><br>
                                Ask questions about this document.
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" id="chat-input" class="form-control" placeholder="Ask AI..." onkeypress="handleEnter(event)">
                            <button class="btn btn-primary" onclick="sendDocChat()"><i class="ti ti-send"></i></button>
                        </div>
                    </div>

                    <!-- TAB 3: Notes (Annotations) -->
                    <div class="tab-pane fade" id="tab-notes">
                        @if($document->is_public)
                            <form action="{{ route('documents.annotate', $document->id) }}" method="POST" class="mb-4">
                                @csrf
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Ref</span>
                                    <input type="text" name="section" class="form-control" placeholder="Pg 1">
                                </div>
                                <textarea name="note" class="form-control mb-2" rows="2" placeholder="Add a public note..." required></textarea>
                                <button class="btn btn-sm btn-primary w-100">Post Note</button>
                            </form>
                        @else
                            <div class="alert alert-warning small">
                                <i class="ti ti-lock me-1"></i> Publish this document to allow public notes.
                            </div>
                        @endif

                        <div class="list-group list-group-flush">
                            @foreach($document->annotations as $note)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <small class="fw-bold">{{ $note->user->name }}</small>
                                    @if($note->section_reference) <span class="badge bg-label-secondary">{{ $note->section_reference }}</span> @endif
                                </div>
                                <p class="mb-0 small text-muted">{{ $note->note }}</p>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $note->created_at->diffForHumans() }}</small>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS REMAIN UNCHANGED -->
<script>
function handleEnter(e) { if (e.key === 'Enter') sendDocChat(); }
function sendDocChat() {
    const input = document.getElementById('chat-input');
    const box = document.getElementById('chat-box');
    const q = input.value.trim();
    if(!q) return;

    if(box.querySelector('.text-center')) { box.innerHTML = ''; }

    box.innerHTML += `<div class="d-flex justify-content-end mb-2"><div class="bg-primary text-white rounded p-2 small" style="max-width: 80%">${q}</div></div>`;
    input.value = '';
    box.scrollTop = box.scrollHeight;

    const loadingId = 'loading-' + Date.now();
    box.innerHTML += `<div id="${loadingId}" class="d-flex justify-content-start mb-2"><div class="bg-white border rounded p-2 small text-muted">Reading document...</div></div>`;
    box.scrollTop = box.scrollHeight;

    fetch('{{ route("documents.chat", $document->id) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ question: q })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById(loadingId).remove();
        box.innerHTML += `<div class="d-flex justify-content-start mb-2"><div class="bg-white border rounded p-2 small" style="max-width: 80%">${data.answer}</div></div>`;
        box.scrollTop = box.scrollHeight;
    });
}
function clearChat() {
    if(confirm('Clear chat history?')) {
        document.getElementById('chat-box').innerHTML = '<div class="text-center text-muted small mt-5"><i class="ti ti-message-chatbot fs-1 mb-2"></i><br>Ask questions about this document.</div>';
    }
}
</script>
@endsection
