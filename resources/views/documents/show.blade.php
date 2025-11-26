@extends('layouts/layoutMaster')

@section('title', $document->title)

@section('content')
<style>
    /* === CUSTOM STYLES FOR DOCUMENT VIEWER === */

    /* Smooth height transitions */
    .document-viewer-container {
        height: calc(100vh - 180px);
        min-height: 600px;
    }

    /* PDF Viewer Enhancements */
    .pdf-viewer-card {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid var(--bs-border-color);
        transition: box-shadow 0.3s ease;
    }

    .pdf-viewer-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }

    /* Right Panel Styling */
    .tools-panel-card {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid var(--bs-border-color);
    }

    /* Tab Navigation Enhancement */
    .nav-tabs .nav-link {
        font-weight: 500;
        font-size: 0.9rem;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }

    .nav-tabs .nav-link:hover {
        background-color: rgba(105, 108, 255, 0.08);
    }

    .nav-tabs .nav-link.active {
        font-weight: 600;
    }

    /* Tab Content Area */
    .tab-content-wrapper {
        height: calc(100% - 50px);
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Custom Scrollbar */
    .tab-content-wrapper::-webkit-scrollbar {
        width: 6px;
    }

    .tab-content-wrapper::-webkit-scrollbar-track {
        background: var(--bs-border-color);
        border-radius: 10px;
    }

    .tab-content-wrapper::-webkit-scrollbar-thumb {
        background: rgba(105, 108, 255, 0.5);
        border-radius: 10px;
    }

    .tab-content-wrapper::-webkit-scrollbar-thumb:hover {
        background: rgba(105, 108, 255, 0.8);
    }

    /* Summary Tab Styling */
    .summary-section {
        padding: 1.25rem;
    }

    .summary-section h6 {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        margin-bottom: 0.75rem;
    }

    .summary-plain-text {
        line-height: 1.7;
        font-size: 0.95rem;
        color: var(--bs-body-color);
    }

    .summary-eli5-box {
        background: linear-gradient(135deg, rgba(105, 108, 255, 0.1) 0%, rgba(105, 108, 255, 0.05) 100%);
        border-left: 4px solid var(--bs-primary);
        padding: 1.25rem;
        border-radius: 0.5rem;
        font-size: 0.95rem;
        line-height: 1.7;
    }

    /* Chat Tab Styling */
    .chat-container {
        padding: 1rem;
    }

    #chat-box {
        background: linear-gradient(to bottom, var(--bs-body-bg) 0%, rgba(105, 108, 255, 0.02) 100%);
        border: 1px solid var(--bs-border-color) !important;
        box-shadow: inset 0 2px 8px rgba(0,0,0,0.04);
    }

    .chat-message-user {
        background: linear-gradient(135deg, var(--bs-primary) 0%, rgba(105, 108, 255, 0.85) 100%);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 1rem 1rem 0.25rem 1rem;
        max-width: 85%;
        font-size: 0.9rem;
        line-height: 1.5;
        word-wrap: break-word;
        box-shadow: 0 2px 6px rgba(105, 108, 255, 0.3);
    }

    .chat-message-ai {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        padding: 0.75rem 1rem;
        border-radius: 1rem 1rem 1rem 0.25rem;
        max-width: 85%;
        font-size: 0.9rem;
        line-height: 1.6;
        word-wrap: break-word;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }

    .chat-message-loading {
        background: var(--bs-body-bg);
        border: 1px dashed var(--bs-border-color);
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        font-style: italic;
        color: var(--bs-secondary);
    }

    .chat-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--bs-secondary);
    }

    .chat-empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Chat Input Styling */
    .chat-input-group {
        gap: 0.5rem;
    }

    .chat-input-group input {
        border-radius: 1.5rem;
        padding: 0.75rem 1.25rem;
        border: 2px solid var(--bs-border-color);
        transition: all 0.2s ease;
    }

    .chat-input-group input:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15);
    }

    .chat-input-group button {
        border-radius: 50%;
        width: 42px;
        height: 42px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(105, 108, 255, 0.3);
    }

    /* Notes Tab Styling */
    .notes-container {
        padding: 1rem;
    }

    .note-form-card {
        background: linear-gradient(135deg, rgba(105, 108, 255, 0.05) 0%, rgba(105, 108, 255, 0.02) 100%);
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .note-item {
        padding: 1rem;
        border-left: 3px solid var(--bs-primary);
        background: var(--bs-body-bg);
        margin-bottom: 1rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .note-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transform: translateX(4px);
    }

    .note-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .note-author {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--bs-primary);
    }

    .note-reference-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
    }

    .note-content {
        font-size: 0.9rem;
        line-height: 1.6;
        color: var(--bs-body-color);
        margin-bottom: 0.5rem;
    }

    .note-timestamp {
        font-size: 0.75rem;
        color: var(--bs-secondary);
    }

    /* Header Bar Enhancements */
    .document-header {
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, rgba(105, 108, 255, 0.03) 100%);
        border: 1px solid var(--bs-border-color);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .document-title {
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .document-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .document-meta-item {
        display: inline-flex;
        align-items: center;
        font-size: 0.85rem;
        color: var(--bs-secondary);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .status-badge i {
        font-size: 1rem;
        margin-right: 0.35rem;
    }

    /* Button Enhancements */
    .btn-regenerate {
        font-size: 0.8rem;
        padding: 0.4rem 0.85rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-regenerate:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(105, 108, 255, 0.2);
    }

    /* Alert Styling */
    .alert-publish-warning {
        background: linear-gradient(135deg, rgba(255, 159, 67, 0.1) 0%, rgba(255, 159, 67, 0.05) 100%);
        border-left: 4px solid #ff9f43;
        border-radius: 0.5rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .document-viewer-container {
            height: auto;
            min-height: 400px;
        }

        .col-md-7, .col-md-5 {
            height: 500px !important;
            margin-bottom: 1rem;
        }

        .document-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }

        .chat-message-user,
        .chat-message-ai {
            max-width: 95%;
        }
    }

    /* Dark Mode Adjustments */
    [data-bs-theme="dark"] .pdf-viewer-card,
    [data-bs-theme="dark"] .tools-panel-card {
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }

    [data-bs-theme="dark"] .summary-eli5-box {
        background: linear-gradient(135deg, rgba(105, 108, 255, 0.15) 0%, rgba(105, 108, 255, 0.08) 100%);
    }

    [data-bs-theme="dark"] #chat-box {
        background: linear-gradient(to bottom, rgba(255,255,255,0.02) 0%, rgba(105, 108, 255, 0.03) 100%);
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- HEADER BAR -->
    <div class="document-header d-flex justify-content-between align-items-center mb-4 p-3 rounded-3">
        <div class="d-flex align-items-center overflow-hidden flex-grow-1">
            <a href="{{ route('documents.index') }}"
               class="btn btn-icon btn-label-secondary me-3 flex-shrink-0"
               data-bs-toggle="tooltip"
               title="Back to Documents">
                <i class="ti ti-arrow-left"></i>
            </a>
            <div class="overflow-hidden">
                <h5 class="document-title mb-1 text-truncate" style="max-width: 500px;">
                    {{ $document->title }}
                </h5>
                <div class="document-meta">
                    <span class="document-meta-item">
                        <i class="ti ti-map-pin me-1"></i>{{ $document->country }}
                    </span>
                    <span class="text-muted">•</span>
                    <span class="document-meta-item">
                        <i class="ti ti-file-text me-1"></i>{{ $document->type }}
                    </span>
                    <span class="text-muted">•</span>
                    @if($document->is_public)
                        <span class="status-badge bg-label-success text-success">
                            <i class="ti ti-world"></i>Public
                        </span>
                    @else
                        <span class="status-badge bg-label-warning text-warning">
                            <i class="ti ti-lock"></i>Private Draft
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <!-- PUBLISH TOGGLE BUTTON -->
            <form action="{{ route('documents.publish', $document->id) }}" method="POST">
                @csrf
                @if($document->is_public)
                    <button type="submit"
                            class="btn btn-label-warning"
                            data-bs-toggle="tooltip"
                            title="Remove from public feed">
                        <i class="ti ti-eye-off me-1"></i> Unpublish
                    </button>
                @else
                    <button type="submit"
                            class="btn btn-success"
                            data-bs-toggle="tooltip"
                            title="Make visible to everyone">
                        <i class="ti ti-world-upload me-1"></i> Publish to Feed
                    </button>
                @endif
            </form>
        </div>
    </div>

    <!-- SUCCESS MESSAGE -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="ti ti-check me-2 fs-5"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- MAIN CONTENT -->
    <div class="row document-viewer-container">
        <!-- LEFT: PDF Viewer -->
        <div class="col-md-7 h-100 mb-3 mb-md-0">
            <div class="card pdf-viewer-card h-100">
                <div class="card-body p-0">
                    <iframe
                        src="{{ asset('storage/' . $document->file_path) }}"
                        width="100%"
                        height="100%"
                        style="border: none; border-radius: 0.5rem;"
                        title="{{ $document->title }}">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- RIGHT: AI & Tools Panel -->
        <div class="col-md-5 h-100">
            <div class="card tools-panel-card h-100">
                <!-- Tab Navigation -->
                <div class="card-header p-0 border-bottom">
                    <ul class="nav nav-tabs nav-fill border-0" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tab-summary"
                                    type="button"
                                    role="tab">
                                <i class="ti ti-file-description me-1"></i>
                                <span class="d-none d-sm-inline">Summary</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tab-chat"
                                    type="button"
                                    role="tab">
                                <i class="ti ti-message-chatbot me-1"></i>
                                <span class="d-none d-sm-inline">AI Chat</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tab-notes"
                                    type="button"
                                    role="tab">
                                <i class="ti ti-notes me-1"></i>
                                <span class="d-none d-sm-inline">Notes</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Tab Content -->
                <div class="card-body p-0">
                    <div class="tab-content tab-content-wrapper">

                        <!-- TAB 1: Summary -->
                        <div class="tab-pane fade show active" id="tab-summary" role="tabpanel">
                            <div class="summary-section">
                                <!-- Regenerate Button -->
                                <div class="d-flex justify-content-end mb-3">
                                    <form action="{{ route('documents.regenerate', $document->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure? This will re-scan the PDF and regenerate all summaries.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary btn-regenerate">
                                            <i class="ti ti-refresh me-1"></i> Regenerate Analysis
                                        </button>
                                    </form>
                                </div>

                                <!-- Plain English Summary -->
                                <div class="mb-4">
                                    <h6 class="text-uppercase text-muted fw-bold mb-3">
                                        <i class="ti ti-article me-1"></i> Plain English Summary
                                    </h6>
                                    <p class="summary-plain-text">
                                        {{ $document->summary_plain ?? 'No summary generated yet. Click "Regenerate Analysis" above to create one.' }}
                                    </p>
                                </div>

                                <hr class="my-4">

                                <!-- ELI5 Explanation -->
                                <div>
                                    <h6 class="text-uppercase text-primary fw-bold mb-3">
                                        <i class="ti ti-bulb me-1"></i> Explain Like I'm 5
                                    </h6>
                                    <div class="summary-eli5-box">
                                        {{ $document->summary_eli5 ?? 'No simple explanation yet. Click "Regenerate Analysis" above to create one.' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: AI Chat -->
                        <div class="tab-pane fade" id="tab-chat" role="tabpanel">
                            <div class="chat-container">
                                <!-- Clear Chat Button -->
                                <div class="d-flex justify-content-end mb-3">
                                    <button class="btn btn-sm btn-label-secondary" onclick="clearChat()">
                                        <i class="ti ti-eraser me-1"></i> Clear Chat
                                    </button>
                                </div>

                                <!-- Chat Messages Area -->
                                <div id="chat-box"
                                     class="mb-3 rounded-3 p-3"
                                     style="height: 400px; overflow-y: auto;">
                                    <div class="chat-empty-state">
                                        <i class="ti ti-message-chatbot"></i>
                                        <p class="mb-0 text-center">
                                            <strong>Ask questions about this document</strong><br>
                                            <small>The AI has read the entire document and can answer your questions.</small>
                                        </p>
                                    </div>
                                </div>

                                <!-- Chat Input -->
                                <div class="chat-input-group d-flex">
                                    <input type="text"
                                           id="chat-input"
                                           class="form-control flex-grow-1"
                                           placeholder="Ask about this document..."
                                           onkeypress="handleEnter(event)">
                                    <button class="btn btn-primary" onclick="sendDocChat()">
                                        <i class="ti ti-send"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: Notes (Annotations) -->
                        <div class="tab-pane fade" id="tab-notes" role="tabpanel">
                            <div class="notes-container">
                                @if($document->is_public)
                                    <!-- Add Note Form -->
                                    <div class="note-form-card">
                                        <h6 class="fw-bold mb-3">
                                            <i class="ti ti-pencil me-1"></i> Add Public Note
                                        </h6>
                                        <form action="{{ route('documents.annotate', $document->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Reference (Optional)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="ti ti-bookmark"></i>
                                                    </span>
                                                    <input type="text"
                                                           name="section"
                                                           class="form-control"
                                                           placeholder="e.g., Page 1, Section 3">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Your Note</label>
                                                <textarea name="note"
                                                          class="form-control"
                                                          rows="3"
                                                          placeholder="Share your thoughts, questions, or insights..."
                                                          required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="ti ti-send me-1"></i> Post Note
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <!-- Publish Warning -->
                                    <div class="alert alert-publish-warning">
                                        <div class="d-flex align-items-start">
                                            <i class="ti ti-lock fs-4 me-2"></i>
                                            <div>
                                                <strong>Document Not Published</strong>
                                                <p class="mb-0 small mt-1">
                                                    Publish this document to allow public notes and collaboration.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Notes List -->
                                <div class="notes-list mt-4">
                                    @forelse($document->annotations as $note)
                                        <div class="note-item">
                                            <div class="note-header">
                                                <span class="note-author">
                                                    <i class="ti ti-user-circle me-1"></i>{{ $note->user->name }}
                                                </span>
                                                @if($note->section_reference)
                                                    <span class="badge bg-label-secondary note-reference-badge">
                                                        {{ $note->section_reference }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="note-content mb-2">{{ $note->note }}</p>
                                            <span class="note-timestamp">
                                                <i class="ti ti-clock me-1"></i>{{ $note->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-5">
                                            <i class="ti ti-notes fs-1 mb-2 opacity-50"></i>
                                            <p class="mb-0">No notes yet. Be the first to add one!</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHAT JAVASCRIPT -->
<script>
// Handle Enter key in chat input
function handleEnter(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendDocChat();
    }
}

// Send chat message
function sendDocChat() {
    const input = document.getElementById('chat-input');
    const box = document.getElementById('chat-box');
    const question = input.value.trim();

    if (!question) return;

    // Remove empty state if present
    const emptyState = box.querySelector('.chat-empty-state');
    if (emptyState) {
        box.innerHTML = '';
    }

    // Add user message
    const userMsgDiv = document.createElement('div');
    userMsgDiv.className = 'd-flex justify-content-end mb-3';
    userMsgDiv.innerHTML = `<div class="chat-message-user">${escapeHtml(question)}</div>`;
    box.appendChild(userMsgDiv);

    // Clear input and scroll
    input.value = '';
    box.scrollTop = box.scrollHeight;

    // Add loading message
    const loadingId = 'loading-' + Date.now();
    const loadingDiv = document.createElement('div');
    loadingDiv.id = loadingId;
    loadingDiv.className = 'd-flex justify-content-start mb-3';
    loadingDiv.innerHTML = `
        <div class="chat-message-loading">
            <i class="ti ti-loader-2 ti-spin me-2"></i>Reading document...
        </div>
    `;
    box.appendChild(loadingDiv);
    box.scrollTop = box.scrollHeight;

    // Send request
    fetch('{{ route("documents.chat", $document->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ question: question })
    })
    .then(res => res.json())
    .then(data => {
        // Remove loading message
        document.getElementById(loadingId).remove();

        // Add AI response
        const aiMsgDiv = document.createElement('div');
        aiMsgDiv.className = 'd-flex justify-content-start mb-3';
        aiMsgDiv.innerHTML = `<div class="chat-message-ai">${escapeHtml(data.answer)}</div>`;
        box.appendChild(aiMsgDiv);

        box.scrollTop = box.scrollHeight;
    })
    .catch(error => {
        console.error('Chat error:', error);
        document.getElementById(loadingId).remove();

        const errorDiv = document.createElement('div');
        errorDiv.className = 'd-flex justify-content-start mb-3';
        errorDiv.innerHTML = `
            <div class="chat-message-ai text-danger">
                <i class="ti ti-alert-circle me-1"></i>
                Sorry, I couldn't process that question. Please try again.
            </div>
        `;
        box.appendChild(errorDiv);
        box.scrollTop = box.scrollHeight;
    });
}

// Clear chat history
function clearChat() {
    if (confirm('Are you sure you want to clear the chat history?')) {
        const box = document.getElementById('chat-box');
        box.innerHTML = `
            <div class="chat-empty-state">
                <i class="ti ti-message-chatbot"></i>
                <p class="mb-0 text-center">
                    <strong>Ask questions about this document</strong><br>
                    <small>The AI has read the entire document and can answer your questions.</small>
                </p>
            </div>
        `;
    }
}

// Escape HTML for security
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
