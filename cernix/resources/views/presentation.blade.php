<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CERNIX — Defense Presentation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #141413;
            --bg-2: #1b1b19;
            --bg-3: #242422;
            --ink: #f5f0e8;
            --ink-2: #cdc6b8;
            --ink-3: #8a857a;
            --line: rgba(245,240,232,.09);
            --line-2: rgba(245,240,232,.14);
            --accent: #c96442;
            --accent-2: #d4714e;
            --accent-wash: rgba(201,100,66,.12);
            --emerald: #7dbd9a;
            --red: #d97566;
            --amber: #d9a066;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; width: 100%; overflow: hidden; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #0e0e0d;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            font-feature-settings: "ss01", "cv11";
            letter-spacing: -0.005em;
        }

        .presentation-wrap {
            width: 100%;
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .slides-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .slide {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 80px 60px;
            text-align: center;
            opacity: 0;
            transition: opacity 0.8s cubic-bezier(0.2, 0.8, 0.3, 1);
            background: linear-gradient(180deg, #0e0e0d 0%, #1a1a18 100%),
                        radial-gradient(circle at 50% -50%, rgba(201, 100, 66, 0.15), transparent 60%);
            overflow-y: auto;
            overflow-x: hidden;
        }

        .slide.active {
            opacity: 1;
            z-index: 10;
        }

        .slide h1, .slide h2 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            letter-spacing: -0.02em;
            margin: 0 0 24px;
            max-width: 900px;
        }

        .slide h1 {
            font-size: 64px;
            line-height: 1.1;
            color: var(--ink);
        }

        .slide h2 {
            font-size: 48px;
            line-height: 1.15;
            color: var(--ink);
        }

        .slide p, .slide .subtitle {
            font-size: 18px;
            color: var(--ink-2);
            line-height: 1.8;
            max-width: 700px;
            margin: 16px auto;
        }

        .slide .subtitle {
            font-size: 16px;
            color: var(--ink-3);
        }

        .slide-accent {
            display: inline-block;
            color: var(--accent);
            font-weight: 600;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            width: 100%;
            max-width: 1000px;
            margin: 40px auto;
            align-items: center;
        }

        .grid-item h3 {
            font-family: 'Fraunces', serif;
            font-size: 24px;
            font-weight: 600;
            color: var(--ink);
            margin: 0 0 20px;
            text-align: left;
        }

        .grid-item ul {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .grid-item li {
            padding: 12px 0 12px 28px;
            position: relative;
            font-size: 16px;
            color: var(--ink-2);
            line-height: 1.6;
        }

        .grid-item li::before {
            content: "▸";
            position: absolute;
            left: 0;
            color: var(--accent);
            font-weight: 600;
            font-size: 20px;
        }

        .visual-box {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            background: var(--bg-2);
            border-radius: 12px;
            min-height: 300px;
            border: 1px solid var(--line);
        }

        .visual-box svg {
            width: 100%;
            height: 100%;
            max-width: 250px;
            max-height: 250px;
            stroke: var(--accent);
        }

        /* Credits grid */
        .credits-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            width: 100%;
            max-width: 900px;
            margin: 40px auto;
        }

        .credit-item {
            padding: 20px;
            background: var(--bg-2);
            border-radius: 10px;
            border: 1px solid var(--line);
        }

        .credit-item.leader {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, rgba(201, 100, 66, 0.15), rgba(201, 100, 66, 0.08));
            border: 1.5px solid var(--accent);
        }

        .credit-item .name {
            font-size: 16px;
            font-weight: 700;
            color: var(--ink);
            margin: 0;
        }

        .credit-item.leader .name::before {
            content: "★ ";
            color: var(--accent);
            font-size: 18px;
            margin-right: 4px;
        }

        .credit-item .role {
            font-size: 12px;
            color: var(--ink-3);
            margin: 6px 0 0;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .credit-item.leader .role {
            color: var(--accent);
            font-weight: 600;
        }

        .credit-item .id {
            font-size: 11px;
            color: var(--ink-4);
            margin-top: 8px;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.05em;
        }

        /* Controls */
        .controls {
            position: fixed;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            z-index: 100;
            align-items: center;
        }

        .progress {
            font-size: 13px;
            color: var(--ink-3);
            font-weight: 600;
            min-width: 90px;
            text-align: center;
            font-family: 'JetBrains Mono', monospace;
        }

        .nav-btn {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: var(--bg-2);
            border: 1px solid var(--line-2);
            color: var(--ink-2);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-btn:hover:not(:disabled) {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 8px 24px rgba(201, 100, 66, 0.3);
        }

        .nav-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Workflow steps */
        .workflow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 800px;
            margin: 40px auto;
            width: 100%;
        }

        .step {
            text-align: center;
            flex: 1;
        }

        .step-num {
            font-size: 32px;
            font-weight: 700;
            color: var(--accent);
            font-family: 'JetBrains Mono', monospace;
            margin-bottom: 8px;
        }

        .step-label {
            font-size: 14px;
            color: var(--ink-2);
            line-height: 1.4;
        }

        .step-line {
            flex-grow: 1;
            height: 2px;
            background: var(--line-2);
            margin: 0 12px;
        }

        .step:last-child .step-line {
            display: none;
        }

        @media (max-width: 768px) {
            .slide {
                padding: 60px 32px;
            }

            .slide h1 {
                font-size: 40px;
            }

            .slide h2 {
                font-size: 32px;
            }

            .slide p {
                font-size: 15px;
            }

            .grid-2 {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .credits-grid {
                grid-template-columns: 1fr;
            }

            .credit-item.leader {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>
    <div class="presentation-wrap">
        <div class="slides-container">
            <!-- Slide 1: Opening -->
            <div class="slide active">
                <h1>CERNIX</h1>
                <p class="subtitle">A Secure Examination Verification System Using QR Code Technology</p>
                <p style="margin-top: 80px; font-size: 16px; color: var(--ink-3);">
                    Federal University of Technology, Minna<br>
                    Final-Year Project Defense<br>
                    <span style="display: block; margin-top: 24px;">April 2024</span>
                </p>
            </div>

            <!-- Slide 2: Problem -->
            <div class="slide">
                <h2>The Problem</h2>
                <div class="grid-2">
                    <div class="grid-item">
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
                    <div class="visual-box">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Slide 3: System Overview -->
            <div class="slide">
                <h2>System Overview</h2>
                <div class="grid-2">
                    <div class="visual-box">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="2" y="2" width="20" height="20" rx="2"/><path d="M12 2v20M2 12h20"/><circle cx="6" cy="6" r="1" fill="currentColor"/><circle cx="18" cy="6" r="1" fill="currentColor"/><circle cx="6" cy="18" r="1" fill="currentColor"/><circle cx="18" cy="18" r="1" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="grid-item">
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
                <div class="workflow">
                    <div class="step"><div class="step-num">1</div><div class="step-label">Student<br>Registers</div></div>
                    <div class="step-line"></div>
                    <div class="step"><div class="step-num">2</div><div class="step-label">Payment<br>Verified</div></div>
                    <div class="step-line"></div>
                    <div class="step"><div class="step-num">3</div><div class="step-label">QR<br>Generated</div></div>
                    <div class="step-line"></div>
                    <div class="step"><div class="step-num">4</div><div class="step-label">Examiner<br>Scans</div></div>
                    <div class="step-line"></div>
                    <div class="step"><div class="step-num" style="color: var(--emerald);">5</div><div class="step-label">Admitted</div></div>
                </div>
            </div>

            <!-- Slide 5: Security -->
            <div class="slide">
                <h2>Security Architecture</h2>
                <div class="grid-2">
                    <div class="grid-item">
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
                    <div class="grid-item">
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
                <div class="grid-2">
                    <div class="grid-item">
                        <h3>Student Portal</h3>
                        <ul>
                            <li>Self-registration</li>
                            <li>QR generation</li>
                            <li>One-time QR access</li>
                            <li>Registration history</li>
                        </ul>
                    </div>
                    <div class="grid-item">
                        <h3>Examiner Scanner</h3>
                        <ul>
                            <li>QR code scanning</li>
                            <li>Verification decision</li>
                            <li>Scan history view</li>
                            <li>Session management</li>
                        </ul>
                    </div>
                    <div class="grid-item">
                        <h3>Admin Dashboard</h3>
                        <ul>
                            <li>Session management</li>
                            <li>Audit log review</li>
                            <li>Statistics & analytics</li>
                            <li>System configuration</li>
                        </ul>
                    </div>
                    <div class="grid-item">
                        <h3>Supervisor</h3>
                        <ul>
                            <li>Project oversight</li>
                            <li>Quality assurance</li>
                            <li>Final approval</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Slide 7: Technical Stack -->
            <div class="slide">
                <h2>Technical Stack</h2>
                <div class="grid-2">
                    <div class="grid-item">
                        <h3>Backend</h3>
                        <ul>
                            <li>Laravel 11 framework</li>
                            <li>Blade templating</li>
                            <li>OpenSSL cryptography</li>
                            <li>SQLite database</li>
                            <li>Remita API integration</li>
                        </ul>
                    </div>
                    <div class="grid-item">
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

            <!-- Slide 8: Impact -->
            <div class="slide">
                <h2>Real-World Impact</h2>
                <div class="grid-2">
                    <div class="visual-box">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="grid-item">
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
                    <div class="credit-item" style="grid-column: 1 / -1; background: var(--bg-2); border: 1px solid var(--line); margin-top: 16px;">
                        <p style="font-size: 13px; color: var(--ink-3); margin: 0 0 6px;">Supervised by</p>
                        <p style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0;">DR. OGBEIDE</p>
                        <p class="role">Project Supervisor</p>
                    </div>
                </div>
            </div>

            <!-- Slide 10: Closing -->
            <div class="slide">
                <h1>CERNIX</h1>
                <p class="subtitle" style="font-size: 24px; color: var(--accent); font-weight: 600; margin-top: 60px;">
                    Secure · Fast · Verifiable
                </p>
                <p style="margin-top: 80px; font-size: 18px; color: var(--ink-2);">
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

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') document.getElementById('next-btn').click();
            else if (e.key === 'ArrowLeft') document.getElementById('prev-btn').click();
        });

        let touchStartX = 0;
        let touchEndX = 0;
        document.querySelector('.slides-container').addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        document.querySelector('.slides-container').addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            if (touchStartX - touchEndX > 50) document.getElementById('next-btn').click();
            else if (touchEndX - touchStartX > 50) document.getElementById('prev-btn').click();
        });

        showSlide(0);
    </script>
</body>
</html>
