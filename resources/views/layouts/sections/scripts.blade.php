<!-- BEGIN: Core JS-->
@vite([
  'resources/assets/vendor/js/helpers.js',
  'resources/assets/js/config.js',
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/js/bootstrap.js',
  'resources/assets/vendor/libs/node-waves/node-waves.js',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  'resources/assets/vendor/libs/hammer/hammer.js',
  'resources/assets/vendor/libs/typeahead-js/typeahead.js',
  'resources/assets/vendor/js/menu.js',
  'resources/assets/js/main.js',
  'resources/js/app.js'
])

@stack('pricing-script')
@yield('page-script')

<!-- ========================================== -->
<!-- GLOBAL LOGIC: CHAT, AI, BUTTONS, SAFETY -->
<!-- ========================================== -->
<style>
    /* --- FIXED FULLSCREEN CHAT --- */
    .chat-fullscreen {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 200000 !important; /* Force top */
        margin: 0 !important;
        border-radius: 0 !important;
        display: flex;
        flex-direction: column;
        background-color: var(--bs-body-bg);
    }

    .chat-fullscreen .card-body {
        height: 100%;
        display: flex;
        flex-direction: column;
        padding: 0 !important; /* Remove padding from body wrapper */
        overflow: hidden;
    }

    .chat-fullscreen .post-header {
        padding: 1rem;
        border-bottom: 1px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        flex-shrink: 0;
    }

    /* Hide irrelevant parts in fullscreen */
    .chat-fullscreen .post-content,
    .chat-fullscreen .post-carousel,
    .chat-fullscreen .post-actions,
    .chat-fullscreen .summary-container {
        display: none !important;
    }

    /* Force Show toggle button */
    .chat-fullscreen .toggle-post-details {
        display: inline-flex !important;
        margin: 0 1rem;
    }

    /* The Chat Area */
    .chat-fullscreen .chat-section {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        margin: 0 !important;
        padding: 0 !important;
        min-height: 0; /* Key for flex scrolling */
    }

    .chat-fullscreen .comments-list {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1rem;
        max-height: none !important; /* Override inline style */
    }

    /* The Input Area (Sticky Bottom) */
    .chat-fullscreen .comment-form-wrapper {
        flex-shrink: 0;
        padding: 1rem;
        background: var(--bs-body-bg); /* Ensure opaque background */
        border-top: 1px solid var(--bs-border-color);
        position: relative; /* Ensure z-index context */
        z-index: 200001;
    }

    /* --- TOAST NOTIFICATIONS --- */
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 200002;
    }

    /* --- MARKDOWN QUOTES --- */
    .comment-text blockquote {
        border-left: 4px solid var(--bs-primary);
        background-color: rgba(105, 108, 255, 0.1);
        margin: 0 0 8px 0;
        padding: 8px 12px;
        border-radius: 0 8px 8px 0;
        font-size: 0.85em;
        color: var(--bs-body-color);
        cursor: pointer;
    }

    /* --- AUTOCOMPLETE --- */
    #bot-autocomplete-dropdown {
        border-radius: 8px;
        border: 1px solid var(--bs-border-color);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        background-color: var(--bs-paper-bg, #fff);
        color: var(--bs-body-color);
    }
    [data-bs-theme="dark"] #bot-autocomplete-dropdown {
        background-color: #2b2c40;
        border-color: #444564;
    }

    /* --- LOADING PULSE --- */
    .animate__pulse { animation: pulse 1.5s infinite; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
</style>

<div id="toast-container"></div>

<script>
// --- GLOBAL HELPERS ---
window.civicAudio = null;
let globalAudioBtn = null;
let currentUserName = "{{ auth()->check() ? auth()->user()->name : 'Guest' }}";
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

// --- 1. INITIALIZATION & EVENT DELEGATION ---
document.addEventListener('DOMContentLoaded', function() {
    restoreWidgetState();
    makeDraggable(document.getElementById("ai-navigator-widget"));
    makeResizable(document.getElementById("ai-navigator-widget"));
    setupAutocomplete();

    // AUTO-RESUME MIC IF IT WAS ON
    if (localStorage.getItem('civicMicState') === 'active') {
        setTimeout(() => toggleRealTimeMic(), 500);
    }

    // THEME FIX
    const themeDropdownItems = document.querySelectorAll('[data-theme]');
    themeDropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const theme = this.getAttribute('data-theme');
            setTheme(theme);
        });
    });

    // Apply Saved Theme on Load
    const savedTheme = localStorage.getItem('templateCustomizer-vertical-menu-template--Theme') || 'light';
    let appliedTheme = savedTheme;
    if(savedTheme === 'system') {
         appliedTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    applyThemeToDom(appliedTheme);

    // Scroll all comment lists to bottom on load
    document.querySelectorAll('.comments-list').forEach(list => {
        list.scrollTop = list.scrollHeight;
    });
});

// --- THEME HELPER ---
function setTheme(theme) {
    let appliedTheme = theme;
    if(theme === 'system') {
            appliedTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    localStorage.setItem('templateCustomizer-vertical-menu-template--Theme', theme);
    document.cookie = `admin-mode=${theme}; path=/; max-age=31536000`;
    document.cookie = `front-mode=${theme}; path=/; max-age=31536000`;
    applyThemeToDom(appliedTheme);
}

function applyThemeToDom(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    document.documentElement.setAttribute('data-theme', theme);

    if (theme === 'dark') {
        document.documentElement.classList.add('dark-style');
        document.documentElement.classList.remove('light-style');
    } else {
        document.documentElement.classList.add('light-style');
        document.documentElement.classList.remove('dark-style');
    }

    const menu = document.getElementById('layout-menu');
    if(menu) menu.setAttribute('data-bs-theme', theme);

    const toggleLink = document.querySelector('.dropdown-style-switcher .dropdown-toggle i');
    if(toggleLink) {
        if(theme === 'light') toggleLink.className = 'ri-sun-line ri-22px';
        else if(theme === 'dark') toggleLink.className = 'ri-moon-clear-line ri-22px';
        else toggleLink.className = 'ri-computer-line ri-22px';
    }
}


// --- 2. BUTTON HANDLERS (Delegated) ---
document.addEventListener('click', async function(e) {

    // LIKE BUTTON
    const likeBtn = e.target.closest('.btn-like');
    if (likeBtn) {
        const postId = likeBtn.dataset.postId;
        const countSpan = likeBtn.querySelector('.like-count');
        const icon = likeBtn.querySelector('i');

        try {
            const res = await fetch(`/posts/${postId}/like`, {
                method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            likeBtn.classList.toggle('liked', data.action === 'liked');
            icon.className = data.action === 'liked' ? 'ri-heart-fill me-1 text-danger' : 'ri-heart-line me-1';
            countSpan.innerText = data.likes_count || '';
        } catch(err) { console.error(err); }
        return;
    }

    // SHARE BUTTON
    const shareBtn = e.target.closest('.btn-share');
    if(shareBtn) {
        const url = shareBtn.dataset.url;
        navigator.clipboard.writeText(url);
        const orig = shareBtn.innerHTML;
        shareBtn.innerHTML = '<i class="ri-check-line me-1"></i> Copied';
        setTimeout(() => shareBtn.innerHTML = orig, 2000);
        return;
    }

    // SUMMARIZE BUTTON
    const sumBtn = e.target.closest('.btn-summarize');
    if(sumBtn) {
        const postId = sumBtn.dataset.postId;
        const card = document.getElementById(`post-${postId}`);
        const container = card.querySelector('.summary-container');
        const contentP = container.querySelector('.summary-content');

        if(container.style.display === 'block') {
            container.style.display = 'none';
            return;
        }

        sumBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Generating...';
        sumBtn.disabled = true;

        try {
            const res = await fetch(`/posts/${postId}/summarize`, {
                method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
            });
            const data = await res.json();
            if(contentP) contentP.innerText = data.summary;
            else container.innerText = data.summary;
            container.style.display = 'block';
        } catch(err) {
            alert('Summary failed.');
        } finally {
            sumBtn.innerHTML = '<i class="ri-sparkling-2-line me-1"></i> Summarize';
            sumBtn.disabled = false;
        }
        return;
    }

    // EXPLAIN BUTTON (ELI5)
    const explainBtn = e.target.closest('.btn-explain');
    if(explainBtn) {
        const postId = explainBtn.dataset.postId;
        const modalEl = document.getElementById('explanation-modal');
        if(!modalEl) return;

        const modal = new bootstrap.Modal(modalEl);
        const content = document.getElementById('explanation-content');

        content.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
        modal.show();

        try {
            const res = await fetch(`/posts/${postId}/explain`, {
                method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            content.innerText = data.explanation;
        } catch(err) {
            content.innerText = 'Sorry, could not explain this post.';
        }
    }

    // LOCAL FEED BUTTON
    const locBtn = e.target.closest('#btn-localize-news');
    if(locBtn) {
        if(!navigator.geolocation) return alert('No GPS support.');

        locBtn.disabled = true;
        locBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Locating...';

        navigator.geolocation.getCurrentPosition(async (pos) => {
            try {
                const res = await fetch('{{ route("news.fetch") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ lat: pos.coords.latitude, lon: pos.coords.longitude })
                });
                const data = await res.json();

                if(data.status === 'success') {
                    // Wait for queue processing then reload (simulated delay)
                    setTimeout(() => window.location.reload(), 5000);
                }
            } catch(e) {
                console.error('News Error:', e);
                alert('Failed to fetch local news.');
                locBtn.disabled = false;
                locBtn.innerHTML = 'Generate Local Feed';
            }
        }, () => {
            alert('GPS Permission Denied');
            locBtn.disabled = false;
            locBtn.innerHTML = 'Generate Local Feed';
        });
    }

    // CLICK EVENT DELEGATION (For Quote Clicking)
    const blockquote = e.target.closest('.comment-text blockquote');
    if (blockquote) {
        const text = blockquote.innerText;
        const parts = text.split(':');
        if(parts.length > 1) {
            const searchStr = parts[1].trim().substring(0, 15);
            const postCard = blockquote.closest('.post-card');
            const comments = postCard.querySelectorAll('.comment-text');

            for (let comment of comments) {
                if (comment === blockquote.parentElement) continue;
                if (comment.innerText.includes(searchStr)) {
                    comment.closest('.comment-item').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const bubble = comment.closest('.comment-bubble');
                    bubble.classList.add('highlight-message');
                    setTimeout(() => bubble.classList.remove('highlight-message'), 2000);
                    break;
                }
            }
        }
    }
});

// --- 3. REAL-TIME CHAT LOGIC ---
document.addEventListener('DOMContentLoaded', function() {

    // Real-time Pusher
    if (window.Echo) {
        window.Echo.channel('comments').listen('CommentCreated', (e) => {
            const postCard = document.getElementById(`post-${e.post_id}`);
            if (!postCard) return;

            const list = postCard.querySelector('.comments-list');
            const countBtn = postCard.querySelector('.comment-count-btn');

            // Remove Ghost
            const loader = document.getElementById(`typing-${e.post_id}`);
            if (loader) loader.remove();

            // Check duplicate
            if(list.lastElementChild?.innerText.includes(e.content)) return;

            // Safety Check Visuals
            let contentHtml = e.content;
            if(e.content.includes('[Content Flagged')) {
                contentHtml = `<span class="text-danger fst-italic"><i class="ri-alert-line"></i> ${e.content}</span>`;
            }

            const html = `
                <div class="d-flex mb-3 comment-item animate__animated animate__fadeIn">
                    <div class="flex-shrink-0"><img src="${e.user.avatar_url}" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;"></div>
                    <div class="flex-grow-1 ms-2">
                        <div class="comment-bubble px-3 py-2 rounded-3 position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small mb-0">${e.user.name}</span>
                                <small class="text-muted" style="font-size: 0.7rem">Just now</small>
                            </div>
                            <div class="comment-text small mb-0 text-break" id="comment-${e.id}">${contentHtml}</div>
                            <div class="comment-actions">
                                <button class="btn btn-xs btn-icon rounded-pill" onclick="initReply(${e.post_id}, '${e.user.name}', \`${e.content.replace(/`/g, '\\`')}\`)"><i class="ri-reply-line"></i></button>
                                <button class="btn btn-xs btn-icon rounded-pill" onclick="toggleGlobalAudio('comment-${e.id}', this)"><i class="ri-volume-up-line"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', html);
            list.scrollTop = list.scrollHeight;

            if(countBtn) {
                let num = parseInt(countBtn.innerText.replace(/\D/g,'')) || 0;
                countBtn.innerHTML = `<i class="ri-chat-3-line me-1"></i> ${num + 1}`;
            }

            // Notification
            if (e.content.includes(`@${currentUserName}`)) showToast('Mention', `<strong>${e.user.name}</strong> mentioned you.`);
        });
    }

    // Comment Submission
    document.body.addEventListener('submit', async function(e) {
        if (e.target.matches('.comment-form')) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const textarea = form.querySelector('textarea');
            const list = form.closest('.card').querySelector('.comments-list');
            const content = textarea.value;
            const replyContext = form.querySelector('input[name="reply_to_context"]').value;

            if(!content.trim()) return;

            const fullContent = replyContext ? `${replyContext}\n\n${content}` : content;
            const postId = form.action.split('/').slice(-1)[0];

            // Optimistic UI
            const tempId = Date.now();
            const userAvatar = form.querySelector('.user-avatar-img').src;

            const displayContent = fullContent.replace(/> (.*?)(\n|$)/g, '<blockquote>$1</blockquote>').replace(/\n/g, '<br>');

            const tempHtml = `
                <div class="d-flex mb-3 comment-item" id="temp-${tempId}" style="opacity: 0.5;">
                    <div class="flex-shrink-0"><img src="${userAvatar}" class="rounded-circle" style="width: 36px; height: 36px;"></div>
                    <div class="flex-grow-1 ms-2">
                        <div class="comment-bubble px-3 py-2 rounded-3">
                            <div class="fw-semibold small">You <small class="text-muted">Sending...</small></div>
                            <div class="comment-text small">${displayContent}</div>
                        </div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', tempHtml);
            list.scrollTop = list.scrollHeight;

            // Check Bot
            const bots = ['FactChecker', 'Historian', 'DevilsAdvocate', 'Analyst'];
            let botName = bots.find(b => content.includes('@'+b));
            if(botName) {
                list.insertAdjacentHTML('beforeend', `
                    <div id="typing-${postId}" class="d-flex mb-3 comment-item animate__pulse">
                        <div class="flex-shrink-0"><div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px;"><i class="ri-robot-line"></i></div></div>
                        <div class="flex-grow-1 ms-2"><div class="comment-bubble px-3 py-2 rounded-3"><small class="fst-italic"><i class="ri-loader-4-line ri-spin"></i> ${botName} is investigating...</small></div></div>
                    </div>
                `);
            }

            // Reset Form
            textarea.value = '';
            form.querySelector('input[name="reply_to_context"]').value = '';
            form.parentElement.querySelector('.reply-preview-container').style.display = 'none';
            textarea.style.height = 'auto';
            btn.disabled = true;

            // AJAX Send
            try {
                const fd = new FormData();
                fd.append('content', fullContent);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: fd
                });

                const data = await res.json();
                if(!res.ok) throw new Error('Failed');

                if(data.comment.is_flagged) {
                    document.getElementById(`temp-${tempId}`).querySelector('.comment-text').innerHTML = `<span class="text-danger"><i class="ri-alarm-warning-line"></i> ${data.comment.content}</span>`;
                    showToast('Safety Alert', 'Your comment was flagged by AI.');
                } else {
                    document.getElementById(`temp-${tempId}`).style.opacity = 1;
                    document.getElementById(`temp-${tempId}`).querySelector('small').innerText = 'Just now';
                }

            } catch(err) {
                document.getElementById(`temp-${tempId}`).remove();
                alert('Failed to post.');
            } finally {
                btn.disabled = false;
            }
        }
    });

    // Enter Key to Submit
    document.body.addEventListener('keydown', (e) => {
        if(e.target.matches('textarea[name="content"]') && e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            e.target.closest('form').querySelector('button[type="submit"]').click();
        }
    });
});

// --- HELPER FUNCTIONS ---

function toggleChatFullscreen(postId) {
    const card = document.getElementById(`post-${postId}`);
    const icon = card.querySelector('.btn-fullscreen i');

    if(card.classList.contains('chat-fullscreen')) {
        card.classList.remove('chat-fullscreen');
        card.classList.remove('show-post-details');
        document.body.style.overflow = '';
        icon.className = 'ri-fullscreen-line';
    } else {
        card.classList.add('chat-fullscreen');
        document.body.style.overflow = 'hidden';
        icon.className = 'ri-fullscreen-exit-line';

        setTimeout(() => {
            const list = card.querySelector('.comments-list');
            list.scrollTop = list.scrollHeight;
        }, 100);
    }
}

function togglePostDetails(postId) {
    const card = document.getElementById(`post-${postId}`);
    card.classList.toggle('show-post-details');
    const btn = card.querySelector('.toggle-post-details span');
    btn.innerText = card.classList.contains('show-post-details') ? 'Hide Original Post' : 'Show Original Post';
}

function initReply(postId, user, content) {
    const card = document.getElementById(`post-${postId}`);
    const form = card.querySelector('.comment-form');
    const container = form.parentElement.querySelector('.reply-preview-container');
    const input = form.querySelector('input[name="reply_to_context"]');

    const cleanContent = content.replace(/[*_>]/g, '').substring(0, 60) + '...';

    input.value = `> **${user}**: ${content}`;
    container.style.display = 'block';
    container.innerHTML = `
        <div class="reply-context">
            <span><i class="ri-reply-fill"></i> Replying to <strong>${user}</strong>: ${cleanContent}</span>
            <button type="button" class="btn-close" onclick="this.parentElement.parentElement.style.display='none'; this.closest('form').querySelector('input[name=reply_to_context]').value='';"></button>
        </div>
    `;
    form.querySelector('textarea').focus();
}

function toggleGlobalAudio(id, btn) {
    const el = document.getElementById(id);
    if(!el) return;
    const text = el.innerText;

    if (window.civicAudio && !window.civicAudio.paused && window.globalAudioBtn === btn) {
        window.civicAudio.pause();
        window.globalAudioBtn.innerHTML = '<i class="ri-volume-up-line"></i>';
        window.civicAudio = null;
        window.globalAudioBtn = null;
        return;
    }
    if(window.civicAudio) {
        window.civicAudio.pause();
        if(window.globalAudioBtn) window.globalAudioBtn.innerHTML = '<i class="ri-volume-up-line"></i>';
    }

    window.globalAudioBtn = btn;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';

    fetch('{{ route("speech.generate") }}', {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ text: text })
    })
    .then(r => r.json())
    .then(data => {
        window.civicAudio = new Audio("data:audio/mp3;base64," + data.audio);
        window.civicAudio.play();
        btn.innerHTML = '<i class="ri-stop-circle-line text-danger"></i>';
        window.civicAudio.onended = () => {
            btn.innerHTML = '<i class="ri-volume-up-line"></i>';
            window.civicAudio = null;
            window.globalAudioBtn = null;
            document.dispatchEvent(new Event('civic-audio-ended'));
        };
    })
    .catch(() => { btn.innerHTML = '<i class="ri-volume-up-line"></i>'; alert('Audio failed'); });
}

function showToast(title, msg) {
    const t = document.getElementById('toast-container');
    const d = document.createElement('div');
    d.className = 'toast show align-items-center text-white bg-primary border-0 mb-2';
    d.innerHTML = `<div class="d-flex"><div class="toast-body"><strong>${title}</strong>: ${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    t.appendChild(d);
    setTimeout(() => d.remove(), 4000);
}

function setupAutocomplete() {
    const bots = [
        { name: 'FactChecker', icon: 'ri-checkbox-circle-line text-success', desc: 'Verify claims' },
        { name: 'Historian', icon: 'ri-book-open-line text-warning', desc: 'Context' },
        { name: 'DevilsAdvocate', icon: 'ri-fire-line text-danger', desc: 'Debate' },
        { name: 'Analyst', icon: 'ri-bar-chart-line text-info', desc: 'Stats' }
    ];

    let dropdown = document.getElementById('bot-autocomplete-dropdown');
    if(!dropdown) {
        dropdown = document.createElement('div');
        dropdown.id = 'bot-autocomplete-dropdown';
        dropdown.className = 'list-group position-absolute shadow';
        dropdown.style.display = 'none';
        dropdown.style.zIndex = '200005';
        document.body.appendChild(dropdown);
    }

    document.body.addEventListener('keyup', function(e) {
        if(e.target.matches('.comment-textarea')) {
            const val = e.target.value;
            const lastAt = val.lastIndexOf('@');
            if(lastAt > -1 && !val.substring(lastAt).includes(' ')) {
                const query = val.substring(lastAt + 1).toLowerCase();
                const rect = e.target.getBoundingClientRect();
                dropdown.style.top = (window.scrollY + rect.top - (bots.length * 50)) + 'px';
                dropdown.style.left = (window.scrollX + rect.left) + 'px';
                dropdown.innerHTML = bots.filter(b=>b.name.toLowerCase().startsWith(query)).map(b => `
                    <button class="list-group-item list-group-item-action" onclick="insertBot('${b.name}')">
                        <i class="${b.icon} me-2"></i> ${b.name} <small class="text-muted ms-2">${b.desc}</small>
                    </button>
                `).join('');
                dropdown.style.display = 'block';
                dropdown.currentInput = e.target;
            } else {
                dropdown.style.display = 'none';
            }
        }
    });

    window.insertBot = function(name) {
        const input = dropdown.currentInput;
        const val = input.value;
        const lastAt = val.lastIndexOf('@');
        input.value = val.substring(0, lastAt) + '@' + name + ' ';
        dropdown.style.display = 'none';
        input.focus();
    };

    document.addEventListener('click', (e) => {
        if(e.target.closest('#bot-autocomplete-dropdown')) return;
        dropdown.style.display = 'none';
    });
}

// --- REAL-TIME SPEECH LOGIC ---
let recognizer;
let isListening = false;
let silenceTimer;
const SILENCE_TIMEOUT_MS = 5000;
const MAGIC_WORDS = ["shazam", "boom", "send", "go", "submit"];

async function toggleRealTimeMic(autoStart = false) {
    const btn = document.getElementById('ai-mic-btn');
    const status = document.getElementById('mic-status');
    const input = document.getElementById('ai-widget-input');

    if (isListening && !autoStart) {
        stopRecognition();
        return;
    }

    try {
        if(btn) btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';

        const tokenRes = await fetch('{{ route("ai.voice_token") }}');
        const tokenData = await tokenRes.json();

        if(tokenData.error) throw new Error(tokenData.error);

        const speechConfig = SpeechSDK.SpeechConfig.fromAuthorizationToken(tokenData.token, tokenData.region);
        speechConfig.speechRecognitionLanguage = "en-US";
        const audioConfig = SpeechSDK.AudioConfig.fromDefaultMicrophoneInput();

        recognizer = new SpeechSDK.SpeechRecognizer(speechConfig, audioConfig);

        recognizer.recognizing = (s, e) => {
            if(input) input.value = e.result.text;
            if(status) status.innerText = "Listening...";
            resetSilenceTimer();
        };

        recognizer.recognized = (s, e) => {
            if (e.result.reason === SpeechSDK.ResultReason.RecognizedSpeech) {
                let text = e.result.text.toLowerCase().replace(/[.,\/#!$%\^&\*;:{}=\-_`~()]/g,"");
                if(input) input.value = e.result.text;

                if (MAGIC_WORDS.some(word => text.includes(word))) {
                    sendAiCommand();
                    if(input) input.value = '';
                }
                resetSilenceTimer();
            }
        };

        recognizer.startContinuousRecognitionAsync();
        isListening = true;
        localStorage.setItem('civicMicState', 'active');

        if(btn) {
            btn.innerHTML = '<i class="ri-mic-line"></i>';
            btn.classList.add('mic-active');
        }
        if(status) status.classList.remove('d-none');
        if(input) input.placeholder = "Speak now...";

        resetSilenceTimer();

    } catch (err) {
        console.error(err);
        if(!autoStart) alert("Microphone error: " + err.message);
        stopRecognition();
    }
}

function stopRecognition() {
    if (recognizer) {
        recognizer.stopContinuousRecognitionAsync();
        recognizer.close();
        recognizer = undefined;
    }
    isListening = false;
    localStorage.setItem('civicMicState', 'inactive');
    clearTimeout(silenceTimer);

    const btn = document.getElementById('ai-mic-btn');
    const status = document.getElementById('mic-status');
    const input = document.getElementById('ai-widget-input');

    if(btn) {
        btn.classList.remove('mic-active');
        btn.innerHTML = '<i class="ri-mic-line"></i>';
    }
    if(status) status.classList.add('d-none');
    if(input) input.placeholder = "Type or click mic...";
}

function resetSilenceTimer() {
    clearTimeout(silenceTimer);
    silenceTimer = setTimeout(() => {
        const input = document.getElementById('ai-widget-input');
        if(input && input.value.trim().length > 0) {
            sendAiCommand();
        }
        stopRecognition();
    }, SILENCE_TIMEOUT_MS);
}

// --- REST OF AI LOGIC ---
function restoreWidgetState() {
    const widget = document.getElementById('ai-navigator-widget');
    const historyDiv = document.getElementById('ai-chat-history');
    const savedState = localStorage.getItem('civicAiState');
    const savedPos = localStorage.getItem('civicAiPos');
    const savedHistory = localStorage.getItem('civicAiHistory');

    if (savedPos) {
        const config = JSON.parse(savedPos);
        widget.style.top = config.top;
        widget.style.left = config.left;
        if (config.width) widget.style.width = config.width;
        if (config.height) widget.style.height = config.height;
        widget.style.bottom = 'auto';
        widget.style.right = 'auto';
    } else {
        widget.style.bottom = '30px';
        widget.style.right = '30px';
    }

    if (savedState === 'open') widget.style.display = 'flex';
    else widget.style.display = 'none';

    if (savedHistory) {
        historyDiv.innerHTML = savedHistory;
        historyDiv.scrollTop = historyDiv.scrollHeight;
    } else {
        historyDiv.innerHTML = `<div class="d-flex justify-content-start mb-2"><div class="bg-label-primary p-2 rounded" style="max-width: 85%; font-size: 0.85rem;"><strong>I'm ready!</strong><br>Click the Mic or type to start navigating.</div></div>`;
    }
}

function saveChatHistory() {
    const historyDiv = document.getElementById('ai-chat-history');
    localStorage.setItem('civicAiHistory', historyDiv.innerHTML);
}

function saveWidgetState(el) {
    localStorage.setItem('civicAiPos', JSON.stringify({
        top: el.style.top,
        left: el.style.left,
        width: el.style.width,
        height: el.style.height
    }));
}

function toggleAiWidget() {
    const widget = document.getElementById('ai-navigator-widget');
    if (widget.style.display === 'none') {
        widget.style.display = 'flex';
        widget.classList.add('animate__animated', 'animate__fadeInUp');
        localStorage.setItem('civicAiState', 'open');
        setTimeout(() => document.getElementById('ai-widget-input').focus(), 100);
    } else {
        widget.style.display = 'none';
        localStorage.setItem('civicAiState', 'closed');
    }
}

function handleAiEnter(e) { if (e.key === 'Enter') sendAiCommand(); }

function sendAiCommand() {
    const input = document.getElementById('ai-widget-input');
    const history = document.getElementById('ai-chat-history');
    const command = input.value.trim();

    if (!command) return;

    history.innerHTML += `<div class="d-flex justify-content-end mb-2"><div class="bg-primary text-white p-2 rounded text-wrap text-end" style="max-width: 85%; font-size: 0.85rem;">${command}</div></div>`;
    input.value = '';
    history.scrollTop = history.scrollHeight;
    saveChatHistory();

    const loadingId = 'ai-loading-' + Date.now();
    history.innerHTML += `<div id="${loadingId}" class="d-flex justify-content-start mb-2"><div class="bg-label-secondary p-2 rounded" style="max-width: 85%; font-size: 0.85rem;"><i class="ri-loader-4-line ri-spin"></i> Thinking...</div></div>`;
    history.scrollTop = history.scrollHeight;

    fetch('{{ route("ai.navigate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ command: command })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById(loadingId).remove();

        if (data.action === 'clear_chat') {
            localStorage.removeItem('civicAiHistory');
            history.innerHTML = `<div class="d-flex justify-content-start mb-2"><div class="bg-label-primary p-2 rounded" style="max-width: 85%; font-size: 0.85rem;">${data.message}</div></div>`;
        }
        else if (data.action === 'redirect') {
            addBotMessage(data.message);
            setTimeout(() => { window.location.assign(data.target); }, 800);
        }
        else if (data.action === 'tool') {
            addBotMessage(data.message);
            executeTool(data);
        }
        else {
            addBotMessage(data.message);
        }
        saveChatHistory();
    })
    .catch(err => {
        document.getElementById(loadingId)?.remove();
        addBotMessage("Error connecting to AI.");
    });
}

function addBotMessage(msg) {
    const history = document.getElementById('ai-chat-history');
    history.innerHTML += `<div class="d-flex justify-content-start mb-2"><div class="bg-label-primary p-2 rounded" style="max-width: 85%; font-size: 0.85rem;">${msg}</div></div>`;
    history.scrollTop = history.scrollHeight;
    saveChatHistory();
}

function executeTool(data) {
    if (data.tool_type === 'set_value') {
        const el = document.querySelector(data.selector);
        if(el) {
            el.scrollIntoView({behavior: "smooth", block: "center"});
            el.style.border = "2px solid #696cff";
            setTimeout(() => {
                el.value = data.value;
                el.dispatchEvent(new Event('change'));
                el.style.border = "";
            }, 800);
        } else { addBotMessage("Element not found."); }
    }
    else if (data.tool_type === 'sequence') {
        const els = document.querySelectorAll(data.selector);
        if(els.length > 0) playSequence(Array.from(els));
        else addBotMessage("Nothing to read.");
    }
    else if (data.tool_type === 'click_match') {
        const containers = document.querySelectorAll(data.container_selector);
        let found = false;
        containers.forEach(container => {
            if(container.innerText.toLowerCase().includes(data.target_text.toLowerCase())) {
                const btn = container.querySelector(data.trigger_selector);
                if(btn) {
                    btn.scrollIntoView({behavior: "smooth", block: "center"});
                    container.style.border = "2px solid #696cff";
                    setTimeout(() => { container.style.border = ""; btn.click(); }, 1000);
                    found = true;
                }
            }
        });
        if(!found) addBotMessage("Could not find '" + data.target_text + "'.");
    }
    else if (data.tool_type === 'click_index') {
        const els = document.querySelectorAll(data.selector);
        const idx = parseInt(data.index) - 1;
        if(els[idx]) {
            els[idx].scrollIntoView({behavior: "smooth", block: "center"});
            setTimeout(() => els[idx].click(), 500);
        } else { addBotMessage("Item not found."); }
    }
    else if (data.tool_type === 'click') {
        const el = document.querySelector(data.selector);
        if(el) {
            el.scrollIntoView({behavior: "smooth", block: "center"});
            setTimeout(() => el.click(), 500);
        } else { addBotMessage("Button not found."); }
    }
    else if (data.tool_type === 'click_last_dropdown') {
        const els = document.querySelectorAll(data.selector);
        if(els.length > 0) {
            const el = els[0];
            const dropdown = el.closest('.dropdown');
            if(dropdown) {
                const toggle = dropdown.querySelector('[data-bs-toggle="dropdown"]');
                if(toggle) toggle.click();
            }
            setTimeout(() => el.click(), 300);
        } else { addBotMessage("Button not found."); }
    }
}

function playSequence(elements) {
    if(elements.length === 0) return;
    const currentBtn = elements.shift();
    currentBtn.scrollIntoView({behavior: "smooth", block: "center"});
    currentBtn.click();

    const onEnd = () => {
        document.removeEventListener('civic-audio-ended', onEnd);
        setTimeout(() => playSequence(elements), 1000);
    };
    document.addEventListener('civic-audio-ended', onEnd);
}

function makeDraggable(elmnt) {
    let pos1=0, pos2=0, pos3=0, pos4=0;
    const header = document.getElementById("ai-widget-header");
    if(header) header.onmousedown = dragMouseDown;

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
        header.style.cursor = 'grabbing';
    }
    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
        elmnt.style.bottom = 'auto';
        elmnt.style.right = 'auto';
    }
    function closeDragElement() {
        document.onmouseup = null;
        document.onmousemove = null;
        header.style.cursor = 'grab';
        saveWidgetState(elmnt);
    }
}

function makeResizable(elmnt) {
    const resizer = document.getElementById("ai-widget-resize");
    if(!resizer) return;
    resizer.addEventListener('mousedown', initResize, false);
    function initResize(e) {
        window.addEventListener('mousemove', Resize, false);
        window.addEventListener('mouseup', stopResize, false);
    }
    function Resize(e) {
        elmnt.style.width = (e.clientX - elmnt.offsetLeft) + 'px';
        elmnt.style.height = (e.clientY - elmnt.offsetTop) + 'px';
    }
    function stopResize(e) {
        window.removeEventListener('mousemove', Resize, false);
        window.removeEventListener('mouseup', stopResize, false);
        saveWidgetState(elmnt);
    }
}
</script>
