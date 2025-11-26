@php
$containerNav = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');
@endphp

<!-- INCLUDE AZURE SPEECH SDK -->
<script src="https://cdn.jsdelivr.net/npm/microsoft-cognitiveservices-speech-sdk@latest/distrib/browser/microsoft.cognitiveservices.speech.sdk.bundle-min.js"></script>

<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
  <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{$containerNav}}">
      @endif

      <!--  Brand -->
      @if(isset($navbarFull))
      <div class="navbar-brand app-brand demo d-none d-xl-flex">
        <a href="{{url('/')}}" class="app-brand-link">
          <span class="app-brand-logo demo">
            @include('_partials.macros',["height"=>20])
          </span>
          <span class="app-brand-text demo menu-text fw-semibold ms-2">{{config('variables.templateName')}}</span>
        </a>
      </div>
      @endif

      @if(!isset($navbarHideToggle))
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
          <i class="ri-menu-fill ri-22px"></i>
        </a>
      </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- AI TRIGGER -->
            <li class="nav-item me-2 me-xl-0">
                <a class="nav-link btn btn-text-primary rounded-pill btn-icon" href="javascript:void(0);" onclick="toggleAiWidget()" title="Open Civic Guide">
                    <i class="ri-robot-2-line ri-22px text-primary"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                        <span class="visually-hidden">Online</span>
                    </span>
                </a>
            </li>

            @if($configData['hasCustomizer'] == true)
            <div class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class='ri-sun-line ri-22px'></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                <li><a class="dropdown-item" href="javascript:void(0);" data-theme="light"><span class="align-middle"><i class='ri-sun-line ri-22px me-3'></i>Light</span></a></li>
                <li><a class="dropdown-item" href="javascript:void(0);" data-theme="dark"><span class="align-middle"><i class="ri-moon-clear-line ri-22px me-3"></i>Dark</span></a></li>
                <li><a class="dropdown-item" href="javascript:void(0);" data-theme="system"><span class="align-middle"><i class="ri-computer-line ri-22px me-3"></i>System</span></a></li>
                </ul>
            </div>
            @endif

          <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
              <div class="avatar avatar-online">
                <img src="{{ Auth::user()->profile_photo_url ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="{{ Route::has('profile.edit') ? route('profile.edit') : 'javascript:void(0);' }}">
                  <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-online">
                        <img src="{{ Auth::user()->profile_photo_url ?? asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <span class="fw-medium d-block">{{ Auth::user()->name ?? 'User' }}</span>
                      <small class="text-muted">Member</small>
                    </div>
                  </div>
                </a>
              </li>
              <li><div class="dropdown-divider"></div></li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class='ri-logout-box-r-line me-2 ri-20px'></i>
                        <span class="align-middle">Log Out</span>
                    </a>
                </form>
              </li>
            </ul>
          </li>
        </ul>
      </div>

      @if(!isset($navbarDetached))
    </div>
    @endif
</nav>

<!-- AI WIDGET -->
<div id="ai-navigator-widget" class="card shadow-lg border-0" style="display: none;">
    <div id="ai-widget-header" class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3" style="cursor: grab;">
        <div class="d-flex align-items-center">
            <i class="ri-robot-2-line me-2"></i>
            <span class="fw-bold small">Civic Guide</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Live Status Text -->
            <span id="mic-status" class="badge bg-white text-primary small d-none">Listening...</span>
            <button type="button" class="btn btn-icon btn-sm btn-text-white rounded-pill p-0" onclick="toggleAiWidget()">
                <i class="ri-close-line"></i>
            </button>
        </div>
    </div>

    <div id="ai-chat-history" class="card-body p-3 bg-body" style="flex-grow: 1; overflow-y: auto; scroll-behavior: smooth;">
        <!-- Content loaded via JS -->
    </div>

    <div class="card-footer p-2 bg-body border-top mt-auto">
        <div class="input-group input-group-merge">
            <button class="btn btn-outline-primary border-end-0" type="button" id="ai-mic-btn" onclick="toggleRealTimeMic()">
                <i class="ri-mic-line"></i>
            </button>

            <input type="text" id="ai-widget-input" class="form-control form-control-sm border-start-0" placeholder="Type or click mic..." onkeypress="handleAiEnter(event)">

            <button class="btn btn-primary btn-sm" type="button" onclick="sendAiCommand()">
                <i class="ri-send-plane-fill"></i>
            </button>
        </div>
    </div>

    <div id="ai-widget-resize" class="resize-handle"></div>
</div>

<style>
    #ai-navigator-widget {
        position: fixed;
        width: 320px;
        height: 450px;
        min-width: 280px;
        min-height: 300px;
        z-index: 99999;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2) !important;
    }
    .resize-handle {
        width: 15px; height: 15px; background: transparent;
        position: absolute; right: 0; bottom: 0; cursor: se-resize; z-index: 100000;
    }
    .resize-handle::after {
        content: ''; position: absolute; right: 3px; bottom: 3px; width: 6px; height: 6px;
        border-right: 2px solid #ccc; border-bottom: 2px solid #ccc;
    }
    [data-bs-theme="dark"] #ai-chat-history { background-color: #2b2c40; }
    [data-bs-theme="dark"] .bg-label-primary { background-color: rgba(105, 108, 255, 0.16) !important; color: #696cff !important; }

    /* Pulse Animation for Mic */
    .mic-active {
        animation: pulse-red 1.5s infinite;
        background-color: #ff3e1d !important;
        color: white !important;
        border-color: #ff3e1d !important;
    }
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(255, 62, 29, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(255, 62, 29, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 62, 29, 0); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    restoreWidgetState();
    makeDraggable(document.getElementById("ai-navigator-widget"));
    makeResizable(document.getElementById("ai-navigator-widget"));

    // AUTO-RESUME MIC IF IT WAS ON
    if (localStorage.getItem('civicMicState') === 'active') {
        // Slight delay to ensure page load
        setTimeout(() => toggleRealTimeMic(), 500);
    }
});

// --- REAL-TIME SPEECH LOGIC ---
let recognizer;
let isListening = false;
let silenceTimer;

// CONFIG: Silence Timeout (5 Seconds)
const SILENCE_TIMEOUT_MS = 5000;
const MAGIC_WORDS = ["shazam", "boom", "send", "go", "submit"];

async function toggleRealTimeMic() {
    const btn = document.getElementById('ai-mic-btn');
    const status = document.getElementById('mic-status');
    const input = document.getElementById('ai-widget-input');

    if (isListening) {
        // Manual Stop
        stopRecognition();
        return;
    }

    try {
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';

        // 1. Get Token
        const tokenRes = await fetch('{{ route("ai.voice_token") }}');
        const tokenData = await tokenRes.json();

        if(tokenData.error) throw new Error(tokenData.error);

        // 2. Configure SDK
        const speechConfig = SpeechSDK.SpeechConfig.fromAuthorizationToken(tokenData.token, tokenData.region);
        speechConfig.speechRecognitionLanguage = "en-US";
        const audioConfig = SpeechSDK.AudioConfig.fromDefaultMicrophoneInput();

        recognizer = new SpeechSDK.SpeechRecognizer(speechConfig, audioConfig);

        // 3. Event: Recognizing (Updates text while speaking)
        recognizer.recognizing = (s, e) => {
            input.value = e.result.text;
            status.innerText = "Listening...";
            resetSilenceTimer(); // Speaking -> Reset timer
        };

        // 4. Event: Recognized (Finished a sentence)
        recognizer.recognized = (s, e) => {
            if (e.result.reason === SpeechSDK.ResultReason.RecognizedSpeech) {
                let text = e.result.text.toLowerCase().replace(/[.,\/#!$%\^&\*;:{}=\-_`~()]/g,"");
                input.value = e.result.text;

                if (MAGIC_WORDS.some(word => text.includes(word))) {
                    // Magic Word -> Send immediately but KEEP LISTENING (Continuous)
                    // Or stop? User asked to "turn off mic" logic implies we should stay on until silence.
                    // Let's send but keep mic active.
                    sendAiCommand();
                    input.value = ''; // Clear for next command
                }

                // Reset silence timer
                resetSilenceTimer();
            }
        };

        // 5. Start
        recognizer.startContinuousRecognitionAsync();
        isListening = true;
        localStorage.setItem('civicMicState', 'active'); // PERSIST STATE

        // UI Updates
        btn.innerHTML = '<i class="ri-mic-line"></i>';
        btn.classList.add('mic-active');
        status.classList.remove('d-none');
        input.placeholder = "Speak now...";

        // Start the silence countdown immediately in case they don't speak
        resetSilenceTimer();

    } catch (err) {
        console.error(err);
        // Only alert if user explicitly clicked, not on auto-resume
        if(localStorage.getItem('civicMicState') !== 'active') {
             alert("Microphone error: " + err.message);
        }
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
    localStorage.setItem('civicMicState', 'inactive'); // CLEAR STATE
    clearTimeout(silenceTimer);

    // UI Reset
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
        console.log("Silence detected (5s). Sending & Stopping.");

        const input = document.getElementById('ai-widget-input');
        // Only send if there is text
        if(input.value.trim().length > 0) {
            sendAiCommand();
        }
        stopRecognition(); // Auto-stop after silence

    }, SILENCE_TIMEOUT_MS);
}

// --- STANDARD AI LOGIC (Unchanged) ---
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

    // Note: Don't stop recording here anymore to allow continuous flow until silence

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
