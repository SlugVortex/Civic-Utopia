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

      <!-- Mobile Toggle -->
      @if(!isset($navbarHideToggle))
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
          <i class="ri-menu-fill ri-22px"></i>
        </a>
      </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        <!-- AI NAVIGATOR TRIGGER BUTTON -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">

            <!-- The AI Button -->
            <li class="nav-item me-2 me-xl-0">
                <a class="nav-link btn btn-text-primary rounded-pill btn-icon" href="javascript:void(0);" onclick="toggleAiWidget()" title="Open Civic Guide">
                    <i class="ri-robot-2-line ri-22px text-primary"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                        <span class="visually-hidden">Online</span>
                    </span>
                </a>
            </li>

            @if($configData['hasCustomizer'] == true)
            <!-- Style Switcher -->
            <div class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class='ri-sun-line ri-22px'></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                <li>
                    <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                    <span class="align-middle"><i class='ri-sun-line ri-22px me-3'></i>Light</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                    <span class="align-middle"><i class="ri-moon-clear-line ri-22px me-3"></i>Dark</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                    <span class="align-middle"><i class="ri-computer-line ri-22px me-3"></i>System</span>
                    </a>
                </li>
                </ul>
            </div>
            @endif

          <!-- User -->
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

<!-- ================================================= -->
<!-- DRAGGABLE AI WIDGET (Overlay) -->
<!-- ================================================= -->
<div id="ai-navigator-widget" class="card shadow-lg border-0" style="display: none;">
    <!-- Draggable Header -->
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

    <!-- Chat Body -->
    <div id="ai-chat-history" class="card-body p-3 bg-body" style="height: 300px; overflow-y: auto; scroll-behavior: smooth;">
        <!-- Welcome Message -->
        <div class="d-flex justify-content-start mb-2">
            <div class="bg-label-primary p-2 rounded text-wrap" style="max-width: 85%; font-size: 0.85rem;">
                Hello! I can navigate the site for you. Try: <br>
                <em>"Take me to the ballot box"</em> or <br>
                <em>"I want to report a pothole"</em>
            </div>
        </div>
    </div>

    <!-- Input Footer -->
    <div class="card-footer p-2 bg-body border-top">
        <div class="input-group input-group-merge">
            <input type="text" id="ai-widget-input" class="form-control form-control-sm" placeholder="Where to?..." onkeypress="handleAiEnter(event)">
            <button class="btn btn-primary btn-sm" type="button" onclick="sendAiCommand()">
                <i class="ri-send-plane-fill"></i>
            </button>
        </div>
    </div>
</div>

<style>
    #ai-navigator-widget {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 320px;
        z-index: 99999;
        border-radius: 12px;
        overflow: hidden;
        transition: opacity 0.3s, transform 0.3s;
        /* Initial opacity 0 handled by display none */
    }

    [data-bs-theme="dark"] #ai-chat-history {
        background-color: #2b2c40;
    }
    [data-bs-theme="dark"] .bg-label-primary {
        background-color: rgba(105, 108, 255, 0.16) !important;
        color: #696cff !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    makeDraggable(document.getElementById("ai-navigator-widget"));
});

function toggleAiWidget() {
    const widget = document.getElementById('ai-navigator-widget');
    if (widget.style.display === 'none') {
        widget.style.display = 'block';
        widget.classList.add('animate__animated', 'animate__fadeInUp');
        document.getElementById('ai-widget-input').focus();
    } else {
        widget.style.display = 'none';
    }
}

function handleAiEnter(e) {
    if (e.key === 'Enter') sendAiCommand();
}

function sendAiCommand() {
    const input = document.getElementById('ai-widget-input');
    const history = document.getElementById('ai-chat-history');
    const command = input.value.trim();

    if (!command) return;

    // 1. Add User Message
    history.innerHTML += `
        <div class="d-flex justify-content-end mb-2">
            <div class="bg-primary text-white p-2 rounded text-wrap text-end" style="max-width: 85%; font-size: 0.85rem;">
                ${command}
            </div>
        </div>
    `;
    input.value = '';
    history.scrollTop = history.scrollHeight;

    // 2. Add Thinking Bubble
    const loadingId = 'ai-loading-' + Date.now();
    history.innerHTML += `
        <div id="${loadingId}" class="d-flex justify-content-start mb-2">
            <div class="bg-label-secondary p-2 rounded" style="max-width: 85%; font-size: 0.85rem;">
                <i class="ri-loader-4-line ri-spin"></i> Thinking...
            </div>
        </div>
    `;
    history.scrollTop = history.scrollHeight;

    // 3. Call Backend
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

        if (data.action === 'redirect') {
            history.innerHTML += `
                <div class="d-flex justify-content-start mb-2">
                    <div class="bg-success text-white p-2 rounded" style="max-width: 85%; font-size: 0.85rem;">
                        <i class="ri-rocket-line me-1"></i> Navigating...
                    </div>
                </div>
            `;
            setTimeout(() => {
                window.location.href = data.target;
            }, 800);
        } else {
            history.innerHTML += `
                <div class="d-flex justify-content-start mb-2">
                    <div class="bg-label-primary p-2 rounded" style="max-width: 85%; font-size: 0.85rem;">
                        ${data.target}
                    </div>
                </div>
            `;
        }
        history.scrollTop = history.scrollHeight;
    })
    .catch(err => {
        document.getElementById(loadingId).remove();
        history.innerHTML += `<div class="text-danger small">Error connecting to AI.</div>`;
    });
}

// --- DRAG LOGIC ---
function makeDraggable(elmnt) {
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    const header = document.getElementById("ai-widget-header");

    if (header) {
        header.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        // get the mouse cursor position at startup:
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;

        // Change cursor
        header.style.cursor = 'grabbing';
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        // calculate the new cursor position:
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // set the element's new position:
        elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
        elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
        // Remove bottom/right fix to allow free movement
        elmnt.style.bottom = 'auto';
        elmnt.style.right = 'auto';
    }

    function closeDragElement() {
        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.onmousemove = null;
        header.style.cursor = 'grab';
    }
}
</script>
