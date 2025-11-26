@php
$containerNav = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');
@endphp

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
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-icon btn-sm btn-text-white rounded-pill p-0" onclick="toggleAiWidget()">
                <i class="ri-close-line"></i>
            </button>
        </div>
    </div>

    {{-- Chat Body (Flex grow to fill space) --}}
    <div id="ai-chat-history" class="card-body p-3 bg-body" style="flex-grow: 1; overflow-y: auto; scroll-behavior: smooth;">
        <!-- Content loaded via JS -->
    </div>

    <div class="card-footer p-2 bg-body border-top mt-auto">
        <div class="input-group input-group-merge">
            <input type="text" id="ai-widget-input" class="form-control form-control-sm" placeholder="Type a command..." onkeypress="handleAiEnter(event)">
            <button class="btn btn-primary btn-sm" type="button" onclick="sendAiCommand()">
                <i class="ri-send-plane-fill"></i>
            </button>
        </div>
    </div>

    {{-- Resize Handle --}}
    <div id="ai-widget-resize" class="resize-handle"></div>
</div>

<style>
    #ai-navigator-widget {
        position: fixed;
        width: 320px;
        height: 450px; /* Default height */
        min-width: 280px;
        min-height: 300px;
        z-index: 99999;
        border-radius: 12px;
        overflow: hidden; /* Important for resize */
        display: flex;
        flex-direction: column;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2) !important;
    }

    .resize-handle {
        width: 15px;
        height: 15px;
        background: transparent;
        position: absolute;
        right: 0;
        bottom: 0;
        cursor: se-resize;
        z-index: 100000;
    }

    /* Visual cue for resize handle */
    .resize-handle::after {
        content: '';
        position: absolute;
        right: 3px;
        bottom: 3px;
        width: 6px;
        height: 6px;
        border-right: 2px solid #ccc;
        border-bottom: 2px solid #ccc;
    }

    [data-bs-theme="dark"] #ai-chat-history { background-color: #2b2c40; }
    [data-bs-theme="dark"] .bg-label-primary { background-color: rgba(105, 108, 255, 0.16) !important; color: #696cff !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    restoreWidgetState();
    makeDraggable(document.getElementById("ai-navigator-widget"));
    makeResizable(document.getElementById("ai-navigator-widget"));
});

// --- PERSISTENCE ---
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
        historyDiv.innerHTML = `<div class="d-flex justify-content-start mb-2"><div class="bg-label-primary p-2 rounded" style="max-width: 85%; font-size: 0.85rem;"><strong>I'm ready!</strong><br>Try 'Take me to ballots' or 'Read this page'.</div></div>`;
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

// --- AI LOGIC ---
function sendAiCommand() {
    const input = document.getElementById('ai-widget-input');
    const history = document.getElementById('ai-chat-history');
    const command = input.value.trim();

    if (!command) return;

    // User Msg
    history.innerHTML += `<div class="d-flex justify-content-end mb-2"><div class="bg-primary text-white p-2 rounded text-wrap text-end" style="max-width: 85%; font-size: 0.85rem;">${command}</div></div>`;
    input.value = '';
    history.scrollTop = history.scrollHeight;
    saveChatHistory();

    // Loader
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

        // --- ACTION HANDLER ---
        if (data.action === 'clear_chat') {
            localStorage.removeItem('civicAiHistory');
            history.innerHTML = `<div class="d-flex justify-content-start mb-2"><div class="bg-label-primary p-2 rounded" style="max-width: 85%; font-size: 0.85rem;">${data.message}</div></div>`;
        }
        else if (data.action === 'redirect') {
            addBotMessage(data.message);
            console.log("AI Redirecting to:", data.target); // DEBUG LOG

            setTimeout(() => {
                // Use assign for cleaner redirect
                window.location.assign(data.target);
            }, 800);
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
        console.error(err);
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

// --- EXECUTION LOGIC (Tool Handler) ---
function executeTool(data) {
    // 1. Set Value (Dropdowns)
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
    // 2. Read All
    else if (data.tool_type === 'sequence') {
        const els = document.querySelectorAll(data.selector);
        if(els.length > 0) playSequence(Array.from(els));
        else addBotMessage("Nothing to read.");
    }
    // 3. Click Match (Text search)
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
    // 4. Click Index
    else if (data.tool_type === 'click_index') {
        const els = document.querySelectorAll(data.selector);
        const idx = parseInt(data.index) - 1;
        if(els[idx]) {
            els[idx].scrollIntoView({behavior: "smooth", block: "center"});
            setTimeout(() => els[idx].click(), 500);
        } else { addBotMessage("Item not found."); }
    }
    // 5. Simple Click
    else if (data.tool_type === 'click') {
        const el = document.querySelector(data.selector);
        if(el) {
            el.scrollIntoView({behavior: "smooth", block: "center"});
            setTimeout(() => el.click(), 500);
        } else { addBotMessage("Button not found."); }
    }
    // 6. Dropdown Click
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

// --- DRAG & RESIZE ---
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
