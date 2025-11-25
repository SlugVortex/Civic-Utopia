<!-- BEGIN: Core JS-->
<!-- Core JS -->
@vite([
  'resources/assets/vendor/js/helpers.js'
])

{{-- Required for theme config --}}
@vite([
  'resources/assets/js/config.js'
])

<!-- END: Core JS-->
<!-- BEGIN: Vendor JS-->
@vite([
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/js/bootstrap.js',
  'resources/assets/vendor/libs/node-waves/node-waves.js',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  'resources/assets/vendor/libs/hammer/hammer.js',
  'resources/assets/vendor/libs/typeahead-js/typeahead.js',
  'resources/assets/vendor/js/menu.js'
])

<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])
<!-- END: Theme JS-->

<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->

<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<!-- app JS -->
@vite(['resources/js/app.js'])
<!-- END: app JS-->

<!-- ========================================== -->
<!-- GLOBAL AI COUNCIL & CHAT LOGIC -->
<!-- ========================================== -->
<style>
    /* ============================================ */
    /* FULLSCREEN CHAT STYLES */
    /* ============================================ */
    .chat-fullscreen {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        border-radius: 0 !important;
        margin: 0 !important;
        display: flex;
        flex-direction: column;
        background-color: var(--bs-body-bg);
        box-shadow: none !important;
    }

    .chat-fullscreen .card-body {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1.5rem !important;
        overflow: hidden;
    }

    /* Collapse original post content in fullscreen by default */
    .chat-fullscreen .post-content,
    .chat-fullscreen .post-carousel,
    .chat-fullscreen .post-actions,
    .chat-fullscreen .summary-container {
        display: none;
    }

    /* Show toggle button only in fullscreen */
    .chat-fullscreen .toggle-post-details {
        display: inline-flex !important;
    }

    /* When post details are shown */
    .chat-fullscreen.show-post-details .post-content,
    .chat-fullscreen.show-post-details .post-carousel,
    .chat-fullscreen.show-post-details .post-actions {
        display: block;
    }

    .chat-fullscreen .post-header {
        flex-shrink: 0;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--bs-border-color);
        margin-bottom: 1rem;
    }

    .chat-fullscreen .comments-section {
        border: none !important;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        margin-top: 0 !important;
        padding-top: 0 !important;
        min-height: 0; /* Important for flex overflow */
    }

    .chat-fullscreen .comments-list {
        flex-grow: 1;
        max-height: none !important;
        overflow-y: auto;
        margin-bottom: 1rem;
        padding-right: 8px;
    }

    .chat-fullscreen .comment-form-wrapper {
        margin-top: auto;
        flex-shrink: 0;
        background: var(--bs-body-bg);
        padding-top: 1rem;
        border-top: 1px solid var(--bs-border-color);
    }

    /* ============================================ */
    /* COMMENT STYLES */
    /* ============================================ */
    .comment-item {
        transition: all 0.2s ease;
    }

    .comment-bubble {
        background-color: var(--bs-gray-100);
        border: 1px solid var(--bs-border-color);
        transition: all 0.2s ease;
        position: relative;
    }

    .comment-bubble:hover {
        background-color: var(--bs-gray-200);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .comment-text {
        color: var(--bs-body-color);
        line-height: 1.5;
    }

    .comment-text p {
        margin-bottom: 0.5rem;
    }

    .comment-text p:last-child {
        margin-bottom: 0;
    }

    /* Comment Action Buttons */
    .comment-actions {
        position: absolute;
        bottom: 4px;
        right: 4px;
        display: flex;
        gap: 4px;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .comment-bubble:hover .comment-actions {
        opacity: 1;
    }

    .comment-actions .btn {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        width: 26px;
        height: 26px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .comment-actions .btn:hover {
        background: var(--bs-primary);
        color: white;
        border-color: var(--bs-primary);
    }

    .comment-actions .btn i {
        font-size: 0.85rem;
    }

    /* ============================================ */
    /* REPLY CONTEXT BOX */
    /* ============================================ */
    .reply-context {
        border-left: 3px solid var(--bs-primary);
        background-color: var(--bs-gray-100);
        font-size: 0.85rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        animation: slideDown 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .reply-context i {
        color: var(--bs-primary);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ============================================ */
    /* COMMENT INPUT */
    /* ============================================ */
    .comment-textarea {
        border: 1px solid var(--bs-border-color);
        background-color: var(--bs-body-bg);
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }

    .comment-textarea:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.1);
        background-color: var(--bs-body-bg);
    }

    .comment-input-group {
        padding: 0;
    }

    .send-btn {
        transition: all 0.2s ease;
    }

    .send-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
    }

    .send-btn:active {
        transform: scale(0.95);
    }

    /* ============================================ */
    /* BOT AUTOCOMPLETE DROPDOWN */
    /* ============================================ */
    #bot-autocomplete-dropdown {
        border-radius: 12px;
        border: 1px solid var(--bs-border-color);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        overflow: hidden;
        animation: fadeInUp 0.2s ease;
        background-color: var(--bs-body-bg) !important;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #bot-autocomplete-dropdown .list-group-item {
        border: none;
        border-bottom: 1px solid var(--bs-border-color);
        padding: 0.85rem 1rem;
        background-color: var(--bs-body-bg);
        color: var(--bs-body-color);
        transition: all 0.15s ease;
    }

    #bot-autocomplete-dropdown .list-group-item:last-child {
        border-bottom: none;
    }

    #bot-autocomplete-dropdown .list-group-item:hover {
        background-color: var(--bs-primary);
        color: white;
        transform: translateX(4px);
    }

    #bot-autocomplete-dropdown .list-group-item:hover i {
        color: white !important;
    }

    /* ============================================ */
    /* SCROLLBAR STYLING */
    /* ============================================ */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: var(--bs-gray-200);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: var(--bs-gray-400);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: var(--bs-gray-500);
    }

    /* ============================================ */
    /* DARK MODE SUPPORT */
    /* ============================================ */
    [data-bs-theme="dark"] .comment-bubble {
        background-color: #3a3b55;
        border-color: #4a4b65;
    }

    [data-bs-theme="dark"] .comment-bubble:hover {
        background-color: #4a4b65;
    }

    [data-bs-theme="dark"] .reply-context {
        background-color: #3a3b55;
        border-left-color: #696cff;
    }

    [data-bs-theme="dark"] .comment-textarea {
        background-color: #2b2c40;
        border-color: #4a4b65;
        color: #cfcfe4;
    }

    [data-bs-theme="dark"] .comment-textarea:focus {
        background-color: #2b2c40;
        border-color: #696cff;
    }

    [data-bs-theme="dark"] .comment-actions .btn {
        background: #2b2c40;
        border-color: #4a4b65;
        color: #cfcfe4;
    }

    [data-bs-theme="dark"] #bot-autocomplete-dropdown {
        background-color: #2b2c40 !important;
        border-color: #4a4b65;
    }

    [data-bs-theme="dark"] #bot-autocomplete-dropdown .list-group-item {
        background-color: #2b2c40;
        color: #cfcfe4;
        border-color: #4a4b65;
    }

    [data-bs-theme="dark"] #bot-autocomplete-dropdown .list-group-item:hover {
        background-color: #696cff;
        color: white;
    }

    /* ============================================ */
    /* RESPONSIVE ADJUSTMENTS */
    /* ============================================ */
    @media (max-width: 768px) {
        .chat-fullscreen .card-body {
            padding: 1rem !important;
        }

        .comment-bubble {
            padding: 0.75rem 1rem;
        }

        .comment-actions .btn {
            width: 28px;
            height: 28px;
        }
    }

    /* ============================================ */
    /* ANIMATION CLASSES */
    /* ============================================ */
    .animate__animated {
        animation-duration: 0.5s;
    }

    .animate__fadeIn {
        animation-name: fadeIn;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .animate__pulse {
        animation-name: pulse;
        animation-iteration-count: infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>

<script>
// --- GLOBAL STATE ---
let globalAudio = null;
let globalAudioBtn = null;
let currentUserName = "{{ auth()->check() ? auth()->user()->name : 'Guest' }}";

document.addEventListener('DOMContentLoaded', function() {

    // --- 1. REAL-TIME LISTENER (Pusher) ---
    if (window.Echo) {
        window.Echo.channel('comments')
            .listen('CommentCreated', (e) => {
                const postCard = document.getElementById(`post-${e.post_id}`);
                if (!postCard) return;

                const list = postCard.querySelector('.comments-list');
                const countBtn = postCard.querySelector('.comment-count-btn');

                // Remove Ghost Loader
                const loader = document.getElementById(`typing-${e.post_id}`);
                if (loader) loader.remove();

                // Build HTML
                const html = `
                    <div class="d-flex mb-3 comment-item animate__animated animate__fadeIn">
                        <div class="flex-shrink-0">
                             <img src="${e.user.avatar_url}" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="comment-bubble px-3 py-2 rounded-3 position-relative">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark small mb-0">${e.user.name}</span>
                                    <small class="text-muted" style="font-size: 0.7rem">Just now</small>
                                </div>
                                <div class="comment-text small mb-0 text-break" id="comment-text-${e.id}">${e.content}</div>

                                <div class="comment-actions">
                                    <button class="btn btn-xs btn-icon rounded-pill"
                                            onclick="initReply(${e.post_id}, '${e.user.name}', \`${e.content.replace(/`/g, '\\`')}\`)" title="Reply">
                                        <i class="ri-reply-line"></i>
                                    </button>
                                    <button class="btn btn-xs btn-icon rounded-pill"
                                            onclick="toggleGlobalAudio('comment-text-${e.id}', this)" title="Read aloud">
                                        <i class="ri-volume-up-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                if (list) {
                    list.insertAdjacentHTML('beforeend', html);
                    list.scrollTop = list.scrollHeight;
                }

                // Update Count
                if(countBtn) {
                    let num = parseInt(countBtn.innerText.replace(/\D/g,'')) || 0;
                    countBtn.innerHTML = `<i class="ri-chat-3-line me-1"></i> ${num + 1}`;
                }

                // NOTIFICATION: Did they mention me?
                if (e.content.includes(`@${currentUserName}`)) {
                    showToast(`New mention from ${e.user.name}`, e.content);
                }
            });
    }

    // --- 2. COMMENT SUBMISSION (AJAX + Reply Logic) ---
    document.body.addEventListener('submit', async function(e) {
        if (e.target.matches('.comment-form')) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const textarea = form.querySelector('textarea');
            const postCard = form.closest('.card');
            const list = postCard.querySelector('.comments-list');
            const postId = postCard.id.replace('post-', '');

            // Check for Reply Context
            const replyContext = form.querySelector('input[name="reply_to_context"]');
            let content = textarea.value;

            // If replying, prepend the quote in Markdown format
            if (replyContext && replyContext.value) {
                content = `${replyContext.value}\n\n${content}`;
            }

            if(!content.trim()) return;

            // 1. Optimistic Append
            const userAvatar = form.querySelector('.user-avatar-img').src;
            const tempId = Date.now();
            const userHtml = `
                <div class="d-flex mb-3 comment-item" id="temp-${tempId}" style="opacity: 0.6;">
                    <div class="flex-shrink-0"><img src="${userAvatar}" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;"></div>
                    <div class="flex-grow-1 ms-2">
                        <div class="comment-bubble px-3 py-2 rounded-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark small mb-0">You</span>
                                <small class="text-muted" style="font-size: 0.7rem">Sending...</small>
                            </div>
                            <div class="comment-text small mb-0 text-break">${content.replace(/\n/g, '<br>')}</div>
                        </div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', userHtml);
            list.scrollTop = list.scrollHeight;

            // 2. Check for Bot Summon
            const bots = ['FactChecker', 'Historian', 'DevilsAdvocate', 'Analyst'];
            let botName = null;
            bots.forEach(b => { if(content.toLowerCase().includes('@'+b.toLowerCase())) botName = b; });

            if(botName) {
                const loadingHtml = `
                    <div id="typing-${postId}" class="d-flex mb-3 comment-item animate__animated animate__pulse animate__infinite">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px;">
                                <i class="ri-robot-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="comment-bubble px-3 py-2 rounded-3">
                                <span class="text-muted small fst-italic">
                                    <i class="ri-loader-4-line ri-spin me-1"></i> ${botName} is investigating...
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                list.insertAdjacentHTML('beforeend', loadingHtml);
                list.scrollTop = list.scrollHeight;
            }

            // 3. Reset Form & Reply State
            textarea.value = '';
            textarea.style.height = 'auto';
            cancelReply(postId);
            btn.disabled = true;

            // 4. Send Request
            try {
                const formData = new FormData();
                formData.append('content', content);

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if(!response.ok) throw new Error('Failed');
                const tempEl = document.getElementById(`temp-${tempId}`);
                if(tempEl) {
                    tempEl.style.opacity = 1;
                    tempEl.querySelector('small').innerText = "Just now";
                }

            } catch (err) {
                console.error(err);
                showToast('Error', 'Failed to send comment');
                const tempEl = document.getElementById(`temp-${tempId}`);
                if(tempEl) tempEl.remove();
            } finally {
                btn.disabled = false;
            }
        }
    });

    // --- 3. AUTOCOMPLETE LOGIC ---
    const bots = [
        { id: 'FactChecker', name: 'FactChecker', desc: 'Verifies claims & sources', icon: 'ri-checkbox-circle-line text-success' },
        { id: 'Historian', name: 'Historian', desc: 'Provides historical context', icon: 'ri-book-open-line text-warning' },
        { id: 'DevilsAdvocate', name: 'DevilsAdvocate', desc: 'Challenges assumptions', icon: 'ri-fire-line text-danger' },
        { id: 'Analyst', name: 'Analyst', desc: 'Data & statistical analysis', icon: 'ri-bar-chart-line text-info' }
    ];

    let dropdown = document.getElementById('bot-autocomplete-dropdown');
    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.id = 'bot-autocomplete-dropdown';
        dropdown.className = 'list-group position-absolute';
        dropdown.style.display = 'none';
        dropdown.style.zIndex = '10000';
        dropdown.style.width = '280px';
        document.body.appendChild(dropdown);
    }

    let activeInput = null;

    document.body.addEventListener('keyup', function(e) {
        if (e.target.matches('textarea[name="content"]')) {
            activeInput = e.target;
            const val = activeInput.value;
            const cursorPos = activeInput.selectionStart;
            const lastAt = val.lastIndexOf('@', cursorPos - 1);

            if (lastAt !== -1) {
                const query = val.substring(lastAt + 1, cursorPos);
                if (query.includes(' ')) {
                    dropdown.style.display = 'none';
                    return;
                }
                showSuggestions(query, activeInput, lastAt);
            } else {
                dropdown.style.display = 'none';
            }
        }
    });

    function showSuggestions(query, input, atIndex) {
        const rect = input.getBoundingClientRect();
        dropdown.style.top = (window.scrollY + rect.top - 10) + 'px';
        dropdown.style.left = (window.scrollX + rect.left) + 'px';
        dropdown.innerHTML = '';

        const matches = bots.filter(b => b.name.toLowerCase().startsWith(query.toLowerCase()));

        if (matches.length === 0) {
            dropdown.style.display = 'none';
            return;
        }

        matches.forEach(bot => {
            const item = document.createElement('button');
            item.className = 'list-group-item list-group-item-action d-flex align-items-center';
            item.innerHTML = `
                <i class="${bot.icon} fs-4 me-3"></i>
                <div>
                    <div class="fw-bold">@${bot.name}</div>
                    <small class="text-muted">${bot.desc}</small>
                </div>
            `;
            item.onclick = function(e) {
                e.preventDefault();
                const before = input.value.substring(0, atIndex);
                const after = input.value.substring(input.selectionStart);
                input.value = before + '@' + bot.name + ' ' + after;
                dropdown.style.display = 'none';
                input.focus();
                autoGrowTextarea(input);
            };
            dropdown.appendChild(item);
        });

        dropdown.style.display = 'block';
    }

    document.addEventListener('click', (e) => {
        if (e.target !== dropdown && e.target !== activeInput) {
            dropdown.style.display = 'none';
        }
    });
});

// --- HELPER FUNCTIONS (Global Scope) ---

// Auto-grow textarea
function autoGrowTextarea(element) {
    element.style.height = 'auto';
    element.style.height = Math.min(element.scrollHeight, 120) + 'px';
}

// Toggle Full Screen Chat
function toggleChatFullscreen(postId) {
    const card = document.getElementById(`post-${postId}`);
    const icon = card.querySelector('.btn-fullscreen i');

    if (card.classList.contains('chat-fullscreen')) {
        card.classList.remove('chat-fullscreen');
        card.classList.remove('show-post-details');
        document.body.style.overflow = '';
        icon.className = 'ri-fullscreen-line';
    } else {
        card.classList.add('chat-fullscreen');
        document.body.style.overflow = 'hidden';
        icon.className = 'ri-fullscreen-exit-line';

        // Scroll to bottom of comments
        setTimeout(() => {
            const list = card.querySelector('.comments-list');
            if(list) list.scrollTop = list.scrollHeight;
        }, 100);
    }
}

// Toggle Post Details in Fullscreen
function togglePostDetails(postId) {
    const card = document.getElementById(`post-${postId}`);
    const btn = card.querySelector('.toggle-post-details');
    const btnText = btn.querySelector('span');
    const btnIcon = btn.querySelector('i');

    if (card.classList.contains('show-post-details')) {
        card.classList.remove('show-post-details');
        btnText.textContent = 'Show Original Post';
        btnIcon.className = 'ri-eye-line me-1';
    } else {
        card.classList.add('show-post-details');
        btnText.textContent = 'Hide Original Post';
        btnIcon.className = 'ri-eye-off-line me-1';
    }
}

// Initialize Reply
function initReply(postId, userName, content) {
    const card = document.getElementById(`post-${postId}`);
    const form = card.querySelector('.comment-form');
    const replyContainer = form.parentElement.querySelector('.reply-preview-container');
    const replyInput = form.querySelector('input[name="reply_to_context"]');
    const textarea = form.querySelector('textarea');

    // Truncate content for display
    const snippet = content.length > 80 ? content.substring(0, 80) + '...' : content;

    // Set hidden input value (Markdown Quote)
    replyInput.value = `> **${userName}**: ${snippet}`;

    // Show visual UI
    replyContainer.innerHTML = `
        <div class="reply-context">
            <div>
                <i class="ri-reply-fill me-2"></i>
                <span>Replying to <strong>${userName}</strong>: "${snippet}"</span>
            </div>
            <button type="button" class="btn-close" onclick="cancelReply(${postId})" aria-label="Cancel reply"></button>
        </div>
    `;
    replyContainer.style.display = 'block';
    textarea.focus();
}

// Cancel Reply
function cancelReply(postId) {
    const card = document.getElementById(`post-${postId}`);
    const form = card.querySelector('.comment-form');
    const replyContainer = form.parentElement.querySelector('.reply-preview-container');
    const replyInput = form.querySelector('input[name="reply_to_context"]');

    replyInput.value = '';
    replyContainer.innerHTML = '';
    replyContainer.style.display = 'none';
}

// Audio Player
function toggleGlobalAudio(elementId, btn) {
    const el = document.getElementById(elementId);
    if(!el) return;
    const text = el.innerText;

    if (globalAudio && !globalAudio.paused && globalAudioBtn === btn) {
        globalAudio.pause();
        globalAudioBtn.innerHTML = '<i class="ri-volume-up-line"></i>';
        globalAudio = null;
        globalAudioBtn = null;
        return;
    }

    if(globalAudio) {
        globalAudio.pause();
        if(globalAudioBtn) globalAudioBtn.innerHTML = '<i class="ri-volume-up-line"></i>';
    }

    globalAudioBtn = btn;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';

    fetch('{{ route("speech.generate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ text: text })
    })
    .then(res => res.json())
    .then(data => {
        const src = "data:audio/mp3;base64," + data.audio;
        globalAudio = new Audio(src);
        globalAudio.play();
        btn.innerHTML = '<i class="ri-stop-circle-line text-danger"></i>';
        globalAudio.onended = () => {
            btn.innerHTML = '<i class="ri-volume-up-line"></i>';
            globalAudio = null;
            globalAudioBtn = null;
        };
    })
    .catch(() => {
        btn.innerHTML = '<i class="ri-volume-up-line"></i>';
        showToast('Error', 'Audio playback failed');
        globalAudio = null;
        globalAudioBtn = null;
    });
}

// Toast Notification
function showToast(title, message) {
    const div = document.createElement('div');
    div.className = 'toast show position-fixed top-0 end-0 m-3';
    div.style.zIndex = '10001';
    div.style.minWidth = '300px';
    div.innerHTML = `
        <div class="toast-header bg-primary text-white">
            <i class="ri-notification-3-line me-2"></i>
            <strong class="me-auto">${title}</strong>
            <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
        <div class="toast-body bg-white text-dark">${message}</div>
    `;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 5000);
}
</script>
