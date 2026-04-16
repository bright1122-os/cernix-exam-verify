@extends('layouts.app')

@section('title', 'Examiner Dashboard')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Examiner Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Point the camera at a student's QR code to verify exam access</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Camera panel -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Camera</h2>

            <div id="camera-placeholder" class="flex flex-col items-center justify-center bg-gray-100 rounded-lg h-60 text-gray-400">
                <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.89L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                </svg>
                <p class="text-sm">Camera not started</p>
                <button onclick="startCamera()" id="start-scan-btn"
                    class="mt-3 px-4 py-2 bg-[#0f2050] text-white text-sm rounded-lg hover:bg-[#1a3370] transition">
                    Start Scan
                </button>
            </div>

            <div id="camera-active" class="hidden">
                <div class="relative rounded-lg overflow-hidden bg-black">
                    <video id="qr-video" class="w-full h-60 object-cover" autoplay playsinline muted></video>
                    <canvas id="qr-canvas" class="hidden"></canvas>
                    <!-- Scanning overlay -->
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-40 h-40 border-2 border-white/70 rounded-lg">
                            <div class="absolute top-0 left-0 w-5 h-5 border-t-2 border-l-2 border-blue-400 rounded-tl"></div>
                            <div class="absolute top-0 right-0 w-5 h-5 border-t-2 border-r-2 border-blue-400 rounded-tr"></div>
                            <div class="absolute bottom-0 left-0 w-5 h-5 border-b-2 border-l-2 border-blue-400 rounded-bl"></div>
                            <div class="absolute bottom-0 right-0 w-5 h-5 border-b-2 border-r-2 border-blue-400 rounded-br"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3">
                    <p id="scan-status" class="text-xs text-gray-500">Scanning…</p>
                    <button onclick="stopCamera()" class="text-xs text-red-500 hover:underline">Stop Scan</button>
                </div>
            </div>

            <!-- Manual QR Input (fallback) -->
            <div class="mt-4 border-t pt-4">
                <p class="text-xs text-gray-500 mb-2 font-medium">Or paste QR JSON manually:</p>
                <textarea id="manual-qr" rows="3" placeholder='{"token_id":"...","encrypted_payload":"...","hmac_signature":"...","session_id":1}'
                    class="w-full text-xs border border-gray-300 rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-[#0f2050]"></textarea>
                <button onclick="verifyManual()"
                    class="mt-2 w-full bg-gray-800 text-white text-sm rounded-lg px-4 py-2 hover:bg-gray-700 transition">
                    Verify Manually
                </button>
            </div>
        </div>

        <!-- Result panel -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Verification Result</h2>

            <!-- Idle state -->
            <div id="result-idle" class="flex flex-col items-center justify-center h-60 text-gray-300">
                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm">Awaiting QR scan</p>
            </div>

            <!-- APPROVED -->
            <div id="result-approved" class="hidden">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-green-600 text-xl font-bold">✓</span>
                        <span class="text-green-800 font-bold text-lg">APPROVED</span>
                    </div>
                    <p class="text-xs text-green-600">Student identity verified — access granted</p>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div id="student-photo-container" class="w-14 h-14 bg-gray-200 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center text-gray-400">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                            </svg>
                        </div>
                        <div>
                            <p id="res-student-name" class="font-semibold text-gray-900"></p>
                            <p id="res-student-matric" class="text-xs text-gray-500"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 rounded p-2">
                            <p class="text-gray-400">Token ID</p>
                            <p id="res-token-id" class="font-mono text-gray-700 truncate"></p>
                        </div>
                        <div class="bg-gray-50 rounded p-2">
                            <p class="text-gray-400">Timestamp</p>
                            <p id="res-timestamp" class="text-gray-700"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REJECTED -->
            <div id="result-rejected" class="hidden">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-red-600 text-xl font-bold">✗</span>
                        <span class="text-red-800 font-bold text-lg">REJECTED</span>
                    </div>
                    <p class="text-xs text-red-600">QR code is invalid, tampered, or from an inactive session</p>
                </div>
            </div>

            <!-- DUPLICATE -->
            <div id="result-duplicate" class="hidden">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-yellow-600 text-xl font-bold">!</span>
                        <span class="text-yellow-800 font-bold text-lg">DUPLICATE</span>
                    </div>
                    <p class="text-xs text-yellow-700">This QR code has already been used. Entry denied.</p>
                </div>
            </div>

            <!-- Verifying spinner -->
            <div id="result-loading" class="hidden flex flex-col items-center justify-center h-20 text-gray-400">
                <svg class="animate-spin w-8 h-8" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <p class="text-sm mt-2">Verifying…</p>
            </div>

            <button id="scan-again-btn" onclick="resetResult()"
                class="hidden mt-4 w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg px-4 py-2 transition">
                Reset Scan
            </button>
        </div>

    </div>
</div>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
@endpush

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let videoStream = null;
let scanLoop    = null;
let lastScanned = null;

async function startCamera() {
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' }
        });
        const video = document.getElementById('qr-video');
        video.srcObject = videoStream;
        document.getElementById('camera-placeholder').classList.add('hidden');
        document.getElementById('camera-active').classList.remove('hidden');
        scanLoop = requestAnimationFrame(scanFrame);
    } catch (err) {
        alert('Camera access denied or unavailable: ' + err.message);
    }
}

function stopCamera() {
    if (videoStream) {
        videoStream.getTracks().forEach(t => t.stop());
        videoStream = null;
    }
    if (scanLoop) cancelAnimationFrame(scanLoop);
    document.getElementById('camera-active').classList.add('hidden');
    document.getElementById('camera-placeholder').classList.remove('hidden');
}

function scanFrame() {
    const video  = document.getElementById('qr-video');
    const canvas = document.getElementById('qr-canvas');

    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: 'dontInvert',
        });

        if (code && code.data !== lastScanned) {
            lastScanned = code.data;
            document.getElementById('scan-status').textContent = 'QR detected — verifying…';
            verifyQrData(code.data);
            return; // pause scanning until reset
        }
    }
    scanLoop = requestAnimationFrame(scanFrame);
}

function verifyManual() {
    const raw = document.getElementById('manual-qr').value.trim();
    if (!raw) return;
    verifyQrData(raw);
}

async function verifyQrData(rawJson) {
    let qrData;
    try {
        qrData = JSON.parse(rawJson);
    } catch {
        showResult('rejected');
        return;
    }

    showResult('loading');

    try {
        const resp = await fetch('/examiner/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ qr_data: qrData }),
        });

        const data = await resp.json();

        if (data.status === 'APPROVED' && data.student) {
            document.getElementById('res-student-name').textContent   = data.student.full_name ?? '';
            document.getElementById('res-student-matric').textContent = data.student.matric_no ?? '';
            document.getElementById('res-token-id').textContent       = data.token_id ?? '';
            document.getElementById('res-timestamp').textContent      = formatTs(data.timestamp);
            showResult('approved');
        } else if (data.status === 'DUPLICATE') {
            showResult('duplicate');
        } else {
            showResult('rejected');
        }

    } catch {
        showResult('rejected');
    }
}

function showResult(type) {
    const panels = ['idle', 'approved', 'rejected', 'duplicate', 'loading'];
    panels.forEach(p => document.getElementById('result-' + p).classList.add('hidden'));
    document.getElementById('result-' + type).classList.remove('hidden');
    const showAgain = ['approved', 'rejected', 'duplicate'].includes(type);
    document.getElementById('scan-again-btn').classList.toggle('hidden', !showAgain);
}

function resetResult() {
    lastScanned = null;
    showResult('idle');
    document.getElementById('manual-qr').value = '';
    document.getElementById('scan-status').textContent = 'Scanning…';
    if (videoStream) scanLoop = requestAnimationFrame(scanFrame);
}

function formatTs(ts) {
    if (!ts) return '';
    try { return new Date(ts).toLocaleTimeString(); } catch { return ts; }
}
</script>
@endpush
