@extends('layouts.portal')

@section('title', 'CERNIX Presentation')

@section('content')
<style>
    body { overflow: hidden; }

    .presentation-wrap {
        width: 100%; height: 100vh; display: flex;
        background: var(--bg); position: relative;
    }

    /* Slide container */
    .slides-container {
        width: 100%; height: 100%; position: relative; overflow: hidden;
    }

    .slide {
        width: 100%; height: 100%; position: absolute; inset: 0;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        padding: 60px 40px; text-align: center; opacity: 0; transition: opacity .6s ease;
        background: var(--bg);
    }

    .slide.active {
        opacity: 1; z-index: 10;
        animation: slideIn .6s cubic-bezier(.2,.8,.3,1) forwards;
    }

    @keyframes slideIn {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    /* Typewriter effect */
    .slide h1, .slide h2 {
        margin: 0 0 20px; overflow: hidden; position: relative;
    }

    .slide.active h1, .slide.active h2 {
        animation: typewriter .8s steps(40, end) forwards, blink-caret .7s step-end infinite;
    }

    @keyframes typewriter {
        0% { width: 0; }
        100% { width: 100%; }
    }

    @keyframes blink-caret {
        0%, 49% { border-right-color: rgba(45,108,255,.5); }
        50%, 100% { border-right-color: transparent; }
    }

    .slide h1 {
        font-size: 48px; font-weight: 700; letter-spacing: -.02em;
        color: var(--navy); max-width: 900px;
        border-right: 3px solid rgba(45,108,255,.5);
    }

    .slide h2 {
        font-size: 36px; font-weight: 700; letter-spacing: -.02em;
        color: var(--navy); max-width: 800px;
        border-right: 3px solid rgba(45,108,255,.5);
    }

    .slide p, .slide .subtitle {
        font-size: 18px; color: var(--ink-2); line-height: 1.8;
        max-width: 700px; margin: 20px auto;
    }

    .slide .subtitle {
        font-size: 16px; color: var(--ink-3); margin-top: 8px;
    }

    .slide.active .subtitle {
        animation: fadeIn .8s .3s ease both;
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    /* Content grid for multi-column slides */
    .slide-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 40px;
        width: 100%; max-width: 1000px; margin: 20px auto;
        align-items: center;
    }

    .slide-grid-item h3 {
        font-size: 20px; font-weight: 700; color: var(--navy); margin: 0 0 12px;
        text-align: left;
    }

    .slide-grid-item ul {
        list-style: none; padding: 0; margin: 0; text-align: left;
    }

    .slide-grid-item li {
        padding: 8px 0 8px 28px; position: relative;
        font-size: 15px; color: var(--ink-2); line-height: 1.6;
    }

    .slide-grid-item li::before {
        content: "▸"; position: absolute; left: 8px;
        color: var(--blue); font-weight: 600; font-size: 18px;
    }

    .slide-visual {
        display: flex; align-items: center; justify-content: center;
        padding: 40px; background: rgba(45,108,255,.08);
        border-radius: 16px; min-height: 300px;
    }

    .slide-visual svg {
        width: 100%; height: 100%; max-width: 300px; max-height: 300px;
    }

    /* Team credits layout */
    .credits-grid {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 24px; width: 100%; max-width: 900px; margin: 30px auto 0;
        text-align: center;
    }

    .credit-item {
        padding: 20px; background: var(--bg-2); border-radius: 12px;
        border: 1px solid var(--line);
    }

    .credit-item.leader {
        grid-column: 1 / -1; background: linear-gradient(135deg, rgba(45,108,255,.12), rgba(16,185,129,.08));
        border: 1.5px solid rgba(45,108,255,.3);
    }

    .credit-item .name {
        font-size: 16px; font-weight: 700; color: var(--navy); margin: 0;
    }

    .credit-item.leader .name::before {
        content: "★ "; color: var(--blue); font-size: 18px; margin-right: 4px;
    }

    .credit-item .role {
        font-size: 12px; color: var(--ink-3); margin: 4px 0 0; letter-spacing: .08em;
        text-transform: uppercase;
    }

    .credit-item.leader .role {
        color: var(--blue); font-weight: 600;
    }

    .credit-item .id {
        font-size: 11px; color: var(--ink-4); margin-top: 6px;
        font-family: 'JetBrains Mono', monospace; letter-spacing: .05em;
    }

    /* Controls */
    .controls {
        position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
        display: flex; gap: 16px; z-index: 20; align-items: center;
    }

    .progress {
        font-size: 13px; color: var(--ink-3); font-weight: 600;
        min-width: 80px; text-align: center; letter-spacing: .06em;
    }

    .nav-btn {
        width: 44px; height: 44px; border-radius: 12px;
        background: var(--bg-2); border: 1px solid var(--line);
        color: var(--ink-2); cursor: pointer; display: flex;
        align-items: center; justify-content: center; transition: all .2s;
    }

    .nav-btn:hover {
        background: var(--blue); color: #fff; border-color: var(--blue);
        box-shadow: 0 8px 24px rgba(45,108,255,.2);
    }

    .nav-btn:disabled {
        opacity: .5; cursor: not-allowed;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .slide {
            padding: 40px 24px;
        }

        .slide h1 {
            font-size: 32px;
        }

        .slide h2 {
            font-size: 26px;
        }

        .slide p {
            font-size: 16px;
        }

        .slide-grid {
            grid-template-columns: 1fr; gap: 30px;
        }

        .slide-visual {
            min-height: 200px;
        }

        .credits-grid {
            grid-template-columns: 1fr;
        }

        .credit-item.leader {
            grid-column: 1;
        }
    }
</style>

<div class="presentation-wrap">
    <div class="slides-container">
        <!-- Slide 1: Opening -->
        <div class="slide active">
            <h1>CERNIX</h1>
            <p class="subtitle">A Secure Examination Verification System Using QR Code Technology</p>
            <p style="margin-top: 60px; font-size: 16px; color: var(--ink-3); font-weight: 500;">
                Federal University of Technology, Minna<br>
                Final-Year Project Defense<br>
                <span style="margin-top: 20px; display: block;">April 2024</span>
            </p>
        </div>

        <!-- Slide 2: Problem Statement -->
        <div class="slide">
            <h2>The Problem</h2>
            <div class="slide-grid">
                <div class="slide-grid-item">
                    <h3>Current Challenges</h3>
                    <ul>
                        <li>Manual verification at faculty building</li>
                        <li>Bottleneck during exam admission</li>
                        <li>Document fraud & forgery</li>
                        <li>Impersonation risks</li>
                        <li>Cognitive fatigue & errors</li>
                        <li>Lack of audit trails</li>
                    </ul>
                </div>
                <div class="slide-visual">
                    <svg width="200" height="200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 6v6l4 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Slide 3: System Overview -->
        <div class="slide">
            <h2>System Overview</h2>
            <div class="slide-grid">
                <div class="slide-visual">
                    <svg width="220" height="220" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="2" y="2" width="20" height="20" rx="2"/>
                        <path d="M12 2v20M2 12h20"/>
                        <circle cx="6" cy="6" r="1" fill="currentColor"/>
                        <circle cx="18" cy="6" r="1" fill="currentColor"/>
                        <circle cx="6" cy="18" r="1" fill="currentColor"/>
                        <circle cx="18" cy="18" r="1" fill="currentColor"/>
                    </svg>
                </div>
                <div class="slide-grid-item">
                    <h3>Core Components</h3>
                    <ul>
                        <li>Student registration portal</li>
                        <li>Payment verification</li>
                        <li>QR code generation</li>
                        <li>Examiner scanner dashboard</li>
                        <li>Verification & admission</li>
                        <li>Cryptographic audit log</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Slide 4: Workflow -->
        <div class="slide">
            <h2>Examination Workflow</h2>
            <div style="max-width: 800px; margin: 30px auto 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <div style="text-align: center; flex: 1;">
                        <div style="font-size: 28px; color: var(--blue); font-weight: 700; margin-bottom: 8px;">1</div>
                        <div style="font-size: 13px; color: var(--ink-2);">Student<br>Registers</div>
                    </div>
                    <div style="flex-grow: 1; height: 2px; background: var(--line); margin: 0 12px;"></div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-size: 28px; color: var(--blue); font-weight: 700; margin-bottom: 8px;">2</div>
                        <div style="font-size: 13px; color: var(--ink-2);">Payment<br>Verified</div>
                    </div>
                    <div style="flex-grow: 1; height: 2px; background: var(--line); margin: 0 12px;"></div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-size: 28px; color: var(--blue); font-weight: 700; margin-bottom: 8px;">3</div>
                        <div style="font-size: 13px; color: var(--ink-2);">QR<br>Generated</div>
                    </div>
                    <div style="flex-grow: 1; height: 2px; background: var(--line); margin: 0 12px;"></div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-size: 28px; color: var(--blue); font-weight: 700; margin-bottom: 8px;">4</div>
                        <div style="font-size: 13px; color: var(--ink-2);">Examiner<br>Scans</div>
                    </div>
                    <div style="flex-grow: 1; height: 2px; background: var(--line); margin: 0 12px;"></div>
                    <div style="text-align: center; flex: 1;">
                        <div style="font-size: 28px; color: var(--emerald); font-weight: 700; margin-bottom: 8px;">5</div>
                        <div style="font-size: 13px; color: var(--ink-2);">Admitted</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 5: Security Architecture -->
        <div class="slide">
            <h2>Security Architecture</h2>
            <div class="slide-grid">
                <div class="slide-grid-item">
                    <h3>Cryptography</h3>
                    <ul>
                        <li>AES-256-GCM encryption</li>
                        <li>HMAC-SHA256 signing</li>
                        <li>Per-session session keys</li>
                        <li>One-time token consumption</li>
                        <li>Atomic transactions</li>
                        <li>Append-only audit log</li>
                    </ul>
                </div>
                <div class="slide-grid-item">
                    <h3>Access Control</h3>
                    <ul>
                        <li>Role-based permissions</li>
                        <li>Session-based auth</li>
                        <li>CSRF protection</li>
                        <li>Rate limiting</li>
                        <li>4-hour session timeout</li>
                        <li>Examiner ID binding</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Slide 6: User Roles -->
        <div class="slide">
            <h2>User Roles & Permissions</h2>
            <div class="slide-grid">
                <div class="slide-grid-item">
                    <h3>Student Portal</h3>
                    <ul>
                        <li>Self-registration</li>
                        <li>QR generation</li>
                        <li>One-time QR access</li>
                        <li>Registration history</li>
                    </ul>
                </div>
                <div class="slide-grid-item">
                    <h3>Examiner Scanner</h3>
                    <ul>
                        <li>QR code scanning</li>
                        <li>Verification decision</li>
                        <li>Scan history view</li>
                        <li>Session management</li>
                    </ul>
                </div>
                <div class="slide-grid-item">
                    <h3>Admin Dashboard</h3>
                    <ul>
                        <li>Session management</li>
                        <li>Audit log review</li>
                        <li>Statistics & analytics</li>
                        <li>System configuration</li>
                    </ul>
                </div>
                <div class="slide-grid-item">
                    <h3>Supervisor</h3>
                    <ul>
                        <li>Project oversight</li>
                        <li>Quality assurance</li>
                        <li>Final approval</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Slide 7: Technical Architecture -->
        <div class="slide">
            <h2>Technical Stack</h2>
            <div class="slide-grid">
                <div class="slide-grid-item">
                    <h3>Backend</h3>
                    <ul>
                        <li>Laravel 11 framework</li>
                        <li>Blade templating</li>
                        <li>OpenSSL cryptography</li>
                        <li>SQLite database</li>
                        <li>Remita API integration</li>
                    </ul>
                </div>
                <div class="slide-grid-item">
                    <h3>Frontend</h3>
                    <ul>
                        <li>Responsive HTML/CSS</li>
                        <li>Vanilla JavaScript</li>
                        <li>jsQR library</li>
                        <li>Mobile swipe gestures</li>
                        <li>Touch event handling</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Slide 8: Real-World Impact -->
        <div class="slide">
            <h2>Real-World Impact</h2>
            <div class="slide-grid">
                <div class="slide-visual">
                    <svg width="240" height="240" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                    </svg>
                </div>
                <div class="slide-grid-item">
                    <h3>Benefits</h3>
                    <ul>
                        <li>Eliminates manual process</li>
                        <li>Reduces faculty congestion</li>
                        <li>Prevents impersonation</li>
                        <li>Ensures authenticity</li>
                        <li>Provides audit trail</li>
                        <li>Improves admission speed</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Slide 9: Team & Credits -->
        <div class="slide">
            <h2>Development Team</h2>
            <div class="credits-grid">
                <div class="credit-item leader">
                    <p class="name">AGWUNOBI SOMTOCHUKWU BRIGHT</p>
                    <p class="role">Group Leader & Lead Developer</p>
                    <p class="id">ID: 220404008</p>
                </div>
                <div class="credit-item">
                    <p class="name">OLATUNJI JUBRIL TEMITOPE</p>
                    <p class="role">Member</p>
                </div>
                <div class="credit-item">
                    <p class="name">ADEBOWALE KOLAWOLE JOSHUA</p>
                    <p class="role">Member</p>
                </div>
                <div class="credit-item">
                    <p class="name">UBONG VICTORY PEACE</p>
                    <p class="role">Member</p>
                </div>
                <div class="credit-item">
                    <p class="name">OLUWATOMIWA OLUMOFE</p>
                    <p class="role">Member</p>
                </div>
                <div class="credit-item">
                    <p class="name">OJEKUNLE BOLUWATIFE</p>
                    <p class="role">Member</p>
                </div>
                <div class="credit-item" style="grid-column: 1 / -1; background: var(--bg-2); border: 1px solid var(--line); margin-top: 20px;">
                    <p class="name" style="font-size: 14px;">Supervised by</p>
                    <p style="font-size: 16px; font-weight: 700; color: var(--navy); margin: 4px 0;">DR. OGBEIDE</p>
                    <p class="role">Project Supervisor</p>
                </div>
            </div>
        </div>

        <!-- Slide 10: Closing -->
        <div class="slide">
            <h1>CERNIX</h1>
            <p class="subtitle" style="font-size: 24px; color: var(--blue); font-weight: 600; margin-top: 40px;">
                Secure · Fast · Verifiable
            </p>
            <p style="margin-top: 60px; font-size: 18px; color: var(--ink-2);">
                The Future of Examination Admission
            </p>
            <p style="margin-top: 80px; font-size: 14px; color: var(--ink-3);">
                Thank you
            </p>
        </div>
    </div>

    <!-- Controls -->
    <div class="controls">
        <button class="nav-btn" id="prev-btn" aria-label="Previous slide">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
        <div class="progress"><span id="current-slide">1</span> / 10</div>
        <button class="nav-btn" id="next-btn" aria-label="Next slide">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const totalSlides = slides.length;

function showSlide(n) {
    slides.forEach(slide => slide.classList.remove('active'));

    if (n >= totalSlides) currentSlide = totalSlides - 1;
    if (n < 0) currentSlide = 0;

    slides[currentSlide].classList.add('active');
    document.getElementById('current-slide').textContent = currentSlide + 1;

    document.getElementById('prev-btn').disabled = currentSlide === 0;
    document.getElementById('next-btn').disabled = currentSlide === totalSlides - 1;
}

document.getElementById('next-btn').addEventListener('click', () => {
    if (currentSlide < totalSlides - 1) {
        currentSlide++;
        showSlide(currentSlide);
    }
});

document.getElementById('prev-btn').addEventListener('click', () => {
    if (currentSlide > 0) {
        currentSlide--;
        showSlide(currentSlide);
    }
});

// Arrow key navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') {
        document.getElementById('next-btn').click();
    } else if (e.key === 'ArrowLeft') {
        document.getElementById('prev-btn').click();
    }
});

// Touch swipe navigation
let touchStartX = 0;
let touchEndX = 0;

document.querySelector('.slides-container').addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
});

document.querySelector('.slides-container').addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    if (touchStartX - touchEndX > 50) {
        document.getElementById('next-btn').click();
    } else if (touchEndX - touchStartX > 50) {
        document.getElementById('prev-btn').click();
    }
}

// Initialize
showSlide(0);
</script>
@endpush
