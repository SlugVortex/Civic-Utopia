@extends('layouts/layoutMaster')

@section('title', 'Civic Values Interview')

@section('content')
<div class="container-xxl flex-grow-1" style="padding-top:-180px;">
      <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Civic /</span> Values Interview</h4>

    <div class="row">
        <!-- Settings Column -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Interview Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="ageSlider" class="form-label">Communication Style (Age/Complexity)</label>
                        <input type="range" class="form-range" min="10" max="90" step="10" id="ageSlider" value="30">
                        <div class="d-flex justify-content-between">
                            <small>Simple</small>
                            <small>Standard</small>
                            <small>Complex</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                         <input class="form-check-input" type="checkbox" id="locationSwitch" checked>
                            <label class="form-check-label" for="locationSwitch">Enable Location Context</label>
                        </div>
                        <small class="text-muted" id="locationStatus">Location off</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="voiceSwitch" checked>
                            <label class="form-check-label" for="voiceSwitch">Auto-Play Voice Response</label>
                        </div>
                    </div>

                  <hr>
                    <div class="alert alert-warning d-flex align-items-start" role="alert">
                        <i class="ri-error-warning-line me-2" style="font-size: 1.3rem;"></i>
                        <div>
                            <strong>Disclaimer:</strong> This AI agent is designed to help you explore political values neutrally.
                            Responses are monitored by a scrutinizer system to ensure balanced, non-partisan education.
                            No voting recommendations are provided.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Column -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 me-2"><i class="ri-chat-voice-line me-2"></i>Interviewer Agent</h5>
                        <span class="badge bg-label-primary" id="statusBadge">Ready</span>
                    </div>
                    <!-- Audio Visualizer Placeholder -->
                    <div id="audioVisualizer" style="display:none; height: 20px; width: 100px; background: url('{{ asset('assets/img/illustrations/misc-bg-light.png') }}'); opacity: 0.5;"></div>
                </div>

                 <div class="card-body overflow-auto" id="chatHistory" style="height: 350px; background-color: #f8f9fa;">
                    {{-- Initial Greeting --}}
                    <div class="d-flex justify-content-start mb-3" style=" margin-top:30px">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded-circle bg-primary" ><i class="ri-robot-line"></i></span>
                        </div>
                        <div class="p-3 bg-white rounded shadow-sm text-dark" style="max-width: 80%; margin-top:20px">
                            Hello. I am here to help you explore your political values neutrally. To begin, what issues matter most to you in your community right now?
                        </div>
                    </div>
                </div>

                <div class="card-footer border-top">
                    <form id="chatForm" class="d-flex gap-2 align-items-center">
                        {{-- Microphone Button --}}
                        <button type="button" id="micBtn" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="ri-mic-line" style="font-size: 1.2rem;"></i>
                        </button>

                        <input type="text" id="userInput" class="form-control" placeholder="Type or click mic to speak..." autocomplete="off">
                        <button type="submit" class="btn btn-primary"><i class="ri-send-plane-fill"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden Audio Player --}}
<audio id="agentAudio" style="display: none;"></audio>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatForm = document.getElementById('chatForm');
    const userInput = document.getElementById('userInput');
    const chatHistory = document.getElementById('chatHistory');
    const statusBadge = document.getElementById('statusBadge');
    const ageSlider = document.getElementById('ageSlider');
    const locationSwitch = document.getElementById('locationSwitch');
    const locationStatus = document.getElementById('locationStatus');
    const voiceSwitch = document.getElementById('voiceSwitch');
    const micBtn = document.getElementById('micBtn');
    const audioPlayer = document.getElementById('agentAudio');

    let conversationHistory = [
        {"role": "assistant", "content": "Hello. I am here to help you explore your political values neutrally. To begin, what issues matter most to you in your community right now?"}
    ];

    let currentLat = null;
    let currentLon = null;


        // AUTO-REQUEST LOCATION IF ENABLED
    if (locationSwitch.checked) {
        locationStatus.textContent = "Acquiring...";
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    currentLat = pos.coords.latitude;
                    currentLon = pos.coords.longitude;
                    locationStatus.textContent = `Lat: ${currentLat.toFixed(2)}, Lon: ${currentLon.toFixed(2)}`;
                    locationStatus.classList.add('text-success');
                },
                (err) => {
                    locationStatus.textContent = "Permission denied.";
                    locationSwitch.checked = false;
                }
            );
        }
    }
    // --- SPEECH RECOGNITION SETUP ---
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognition = null;

    if (SpeechRecognition) {
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.lang = 'en-US';
        recognition.interimResults = false;

        recognition.onstart = function() {
            micBtn.classList.remove('btn-outline-secondary');
            micBtn.classList.add('btn-danger', 'pulse-animation'); // Add pulse class via CSS or just red
            statusBadge.textContent = "Listening...";
            statusBadge.className = "badge bg-label-danger";
            userInput.placeholder = "Listening...";
        };

        recognition.onend = function() {
            micBtn.classList.remove('btn-danger', 'pulse-animation');
            micBtn.classList.add('btn-outline-secondary');
            statusBadge.textContent = "Processing...";
            statusBadge.className = "badge bg-label-warning";
            userInput.placeholder = "Type or click mic to speak...";

            // Auto-submit if we have text
            if (userInput.value.trim().length > 0) {
                chatForm.dispatchEvent(new Event('submit'));
            } else {
                statusBadge.textContent = "Ready";
                statusBadge.className = "badge bg-label-primary";
            }
        };

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            userInput.value = transcript;
        };

        micBtn.addEventListener('click', () => {
            // Stop audio if playing
            if (!audioPlayer.paused) {
                audioPlayer.pause();
                audioPlayer.currentTime = 0;
            }
            try {
                recognition.start();
            } catch (e) {
                // Already started
                recognition.stop();
            }
        });
    } else {
        micBtn.style.display = 'none';
        console.warn("Web Speech API not supported in this browser.");
    }

    // --- GEOLOCATION ---
    locationSwitch.addEventListener('change', function() {
        if (this.checked) {
            locationStatus.textContent = "Acquiring...";
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        currentLat = pos.coords.latitude;
                        currentLon = pos.coords.longitude;
                        locationStatus.textContent = `Lat: ${currentLat.toFixed(2)}, Lon: ${currentLon.toFixed(2)}`;
                        locationStatus.classList.add('text-success');
                    },
                    (err) => {
                        locationStatus.textContent = "Permission denied.";
                        this.checked = false;
                    }
                );
            } else {
                locationStatus.textContent = "Not supported.";
                this.checked = false;
            }
        } else {
            currentLat = null;
            currentLon = null;
            locationStatus.textContent = "Location off";
            locationStatus.classList.remove('text-success');
        }
    });

    // --- CHAT LOGIC ---
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const message = userInput.value.trim();
        if (!message) return;

        // 1. UI Updates
        appendMessage('user', message);
        userInput.value = '';
        conversationHistory.push({"role": "user", "content": message});

        statusBadge.textContent = "Thinking...";
        statusBadge.className = "badge bg-label-info";
        userInput.disabled = true;

        try {
            // 2. Text Request
            const response = await fetch('{{ route("interview.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: message,
                    history: conversationHistory,
                    age_level: ageSlider.value,
                    lat: currentLat,
                    lon: currentLon
                })
            });

            if (!response.ok) throw new Error("Chat API Error");

            const data = await response.json();
            const botReply = data.response;

            appendMessage('assistant', botReply);
            conversationHistory.push({"role": "assistant", "content": botReply});

            // 3. Audio Request (if enabled)
            if(voiceSwitch.checked) {
                statusBadge.textContent = "Generating Voice...";
                await playAudio(botReply); // Wait for audio to start
            } else {
                resetUI();
            }

        } catch (error) {
            console.error(error);
            appendMessage('assistant', "I encountered an issue connecting. Please try again.");
            statusBadge.textContent = "Error";
            statusBadge.className = "badge bg-label-danger";
            userInput.disabled = false;
        }
    });

    function appendMessage(role, text) {
        const isUser = role === 'user';
        const align = isUser ? 'justify-content-end' : 'justify-content-start';
        const bgColor = isUser ? 'bg-primary text-white' : 'bg-white text-dark';
        const avatar = isUser ? '' : `
            <div class="avatar avatar-sm me-2">
                <span class="avatar-initial rounded-circle bg-primary"><i class="ri-robot-line"></i></span>
            </div>`;

        const html = `
            <div class="d-flex ${align} mb-3">
                ${avatar}
                <div class="p-3 rounded shadow-sm ${bgColor}" style="max-width: 80%;">
                    ${text}
                </div>
            </div>
        `;

        chatHistory.insertAdjacentHTML('beforeend', html);
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }

    async function playAudio(text) {
        try {
            const response = await fetch('{{ route("interview.speech") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ text: text })
            });

            if (!response.ok) {
                console.error("TTS Server Error", response.status);
                throw new Error('TTS Failed');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);

            audioPlayer.src = url;
            audioPlayer.onended = () => resetUI();

            statusBadge.textContent = "Speaking...";
            statusBadge.className = "badge bg-label-success";

            await audioPlayer.play();

        } catch (e) {
            console.error(e);
            statusBadge.textContent = "Voice Unavailable";
            setTimeout(resetUI, 2000);
        }
    }

    function resetUI() {
        statusBadge.textContent = "Ready";
        statusBadge.className = "badge bg-label-primary";
        userInput.disabled = false;
        userInput.focus();
    }
});
</script>

<style>
.pulse-animation {
    animation: pulse-red 2s infinite;
}
@keyframes pulse-red {
    0% { box-shadow: 0 0 0 0 rgba(255, 82, 82, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(255, 82, 82, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 82, 82, 0); }
}
</style>
@endpush
