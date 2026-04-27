@extends('layouts.portal')

@section('title', 'Examiner Login')

@section('content')
<style>
    /* ── Layout ─────────────────────────────────────────────────────────── */
    .login-page {
        min-height: 100vh;
        display: flex;
        background: var(--bg);
    }

    /* ── Left panel (desktop only) ────────────────────────────────────────── */
    .login-left {
        display: none;
    }
    .login-left-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 60px 48px;
    }
    .login-left-logo {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 40px;
    }
    .login-left-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: var(--blue);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-left-icon svg { width: 28px; height: 28px; color: #fff; }
    .login-left b {
        display: block;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -.01em;
    }
    .login-left span {
        display: block;
        font-size: 12px;
        color: rgba(255,255,255,.6);
        margin-top: 2px;
        letter-spacing: .05em;
    }
    .login-left h2 {
        font-size: 32px;
        font-weight: 800;
        letter-spacing: -.02em;
        margin: 0 0 12px;
        line-height: 1.1;
    }
    .login-left p {
        font-size: 15px;
        color: rgba(255,255,255,.75);
        margin: 0 0 32px;
        line-height: 1.6;
    }
    .login-features {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .login-feature {
        display: flex;
        gap: 12px;
    }
    .login-feature-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(255,255,255,.08);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .login-feature-icon svg { width: 16px; height: 16px; color: var(--blue); stroke-width: 2.5; }
    .login-feature-text b { display: block; font-size: 13px; font-weight: 600; margin-bottom: 2px; }
    .login-feature-text span { display: block; font-size: 12px; color: rgba(255,255,255,.55); }

    /* ── Right panel (form side) ─────────────────────────────────────────── */
    .login-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 20px;
        position: relative;
    }
    .login-right::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(var(--line) 1px, transparent 1px),
            linear-gradient(90deg, var(--line) 1px, transparent 1px);
        background-size: 40px 40px;
        opacity: .3;
        pointer-events: none;
        mask: radial-gradient(circle at 50% 50%, #000 30%, transparent 80%);
        -webkit-mask: radial-gradient(circle at 50% 50%, #000 30%, transparent 80%);
    }

    .login-card {
        position: relative;
        padding: 32px 28px;
        background: var(--bg-2);
        border: 1px solid var(--line);
        border-radius: 22px;
        box-shadow: var(--shadow-lg);
        max-width: 420px;
        width: 100%;
        animation: fadeUp .4s cubic-bezier(.2,.8,.3,1) both;
        transition: box-shadow .3s;
    }
    .login-card:focus-within {
        box-shadow: 0 30px 70px -20px rgba(15,32,80,.2), 0 0 0 1px rgba(45,108,255,.1);
    }
    .login-card .badge {
        display: inline-flex;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(45,108,255,.12);
        color: var(--blue);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 16px;
    }
    .login-card h2 { font-size: 24px; font-weight: 700; margin: 0 0 4px; letter-spacing: -.02em; }
    .login-card .sub { font-size: 13px; color: var(--ink-3); margin: 0 0 24px; }

    .show-pw-btn {
        position: absolute;
        right: 4px;
        top: 4px;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--ink-3);
        border-radius: 10px;
    }
    .show-pw-btn:hover { background: var(--bg); }

    .divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 20px 0 16px;
    }
    .divider::before, .divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--line);
    }
    .divider span {
        font-size: 11px;
        color: var(--ink-4);
        letter-spacing: .1em;
    }

    .sec-note {
        display: flex;
        gap: 8px;
        padding: 10px 12px;
        background: var(--bg);
        border-radius: 10px;
        font-size: 11px;
        color: var(--ink-3);
        line-height: 1.5;
        border: 1px solid var(--line);
    }

    /* ── Mobile and back button ──────────────────────────────────────────── */
    .login-back {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 10;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-2);
        border: 1px solid var(--line);
        color: var(--ink-2);
        text-decoration: none;
        transition: all .15s;
    }
    .login-back:hover {
        background: var(--bg);
        border-color: var(--ink-4);
    }

    /* ── Desktop layout ──────────────────────────────────────────────────── */
    @media (min-width: 768px) {
        .login-page {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
        }
        .login-left {
            display: flex;
            flex-direction: column;
            width: 480px;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
            position: relative;
            overflow: hidden;
            color: #fff;
        }
        .login-left::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(45deg, rgba(255,255,255,.03) 0 10px, transparent 10px 20px);
        }
        .login-left-content {
            position: relative;
            z-index: 1;
        }
        .login-right {
            background: var(--bg);
        }
        .login-right::before {
            background-image:
                linear-gradient(var(--line) 1px, transparent 1px),
                linear-gradient(90deg, var(--line) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: .25;
            mask: radial-gradient(circle at 50% 50%, #000 20%, transparent 70%);
            -webkit-mask: radial-gradient(circle at 50% 50%, #000 20%, transparent 70%);
        }
    }

    @media (max-width: 767px) {
        .login-back {
            display: flex;
        }
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>

<div class="login-page">

    <!-- Left panel (desktop only) -->
    <div class="login-left">
        <div class="login-left-content">
            <div class="login-left-logo">
                <div class="login-left-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div>
                    <b>CERNIX</b>
                    <span>Exam Verification</span>
                </div>
            </div>

            <h2>Secure Exam Hall Access</h2>
            <p>Verify student QR tokens and manage exam room entry with cryptographic authentication.</p>

            <div class="login-features">
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="12" width="18" height="9" rx="2"/><path d="M7 12V7a2 2 0 012-2h6a2 2 0 012 2v5"/></svg>
                    </div>
                    <div class="login-feature-text">
                        <b>QR Verification</b>
                        <span>Real-time token scanning and validation</span>
                    </div>
                </div>

                <div class="login-feature">
                    <div class="login-feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    </div>
                    <div class="login-feature-text">
                        <b>AES-256-GCM Encryption</b>
                        <span>Military-grade payload protection</span>
                    </div>
                </div>

                <div class="login-feature">
                    <div class="login-feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                    <div class="login-feature-text">
                        <b>HMAC-SHA256 Signing</b>
                        <span>Cryptographic integrity verification</span>
                    </div>
                </div>

                <div class="login-feature">
                    <div class="login-feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2-12H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V4a2 2 0 00-2-2z"/></svg>
                    </div>
                    <div class="login-feature-text">
                        <b>Audit Logging</b>
                        <span>Full cryptographic activity trail</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right panel (form side) -->
    <div class="login-right">
        <a href="/" class="login-back" aria-label="Back">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
        </a>

        <div class="login-card">
            <span class="badge">Examiner Access</span>
            <h2>Sign in to begin</h2>
            <p class="sub">Verify QR tokens at your assigned exam hall.</p>

            <form id="login-form">
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" type="text" class="input" placeholder="examiner1" autocomplete="username" required>
                </div>

                <div class="field" style="position:relative">
                    <label for="password">Password</label>
                    <div style="position:relative">
                        <input id="password" type="password" class="input" placeholder="••••••••••" autocomplete="current-password" style="padding-right:52px" required>
                        <button type="button" class="show-pw-btn" id="toggle-pw" aria-label="Show password">
                            <svg id="eye-open" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eye-closed" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none">
                                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="login-error" class="error-box" style="display:none;margin-bottom:16px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span id="login-error-text"></span>
                </div>

                <button type="submit" id="login-btn" class="btn btn-primary btn-block" style="margin-top:4px">
                    <svg id="login-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span id="login-label">Sign in to Scanner</span>
                    <span id="login-dots" class="dots" style="display:none"><span></span><span></span><span></span></span>
                </button>
            </form>

            <div class="divider"><span>SECURE ACCESS</span></div>

            <div class="sec-note">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Sessions expire after 4 hours of inactivity. All scans are cryptographically logged and tied to your examiner ID.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('toggle-pw').addEventListener('click', () => {
    const pw = document.getElementById('password');
    const open = document.getElementById('eye-open');
    const closed = document.getElementById('eye-closed');
    if (pw.type === 'password') {
        pw.type = 'text';
        open.style.display = 'none';
        closed.style.display = '';
    } else {
        pw.type = 'password';
        open.style.display = '';
        closed.style.display = 'none';
    }
});

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('login-btn');
    const label = document.getElementById('login-label');
    const icon = document.getElementById('login-icon');
    const dots = document.getElementById('login-dots');
    const err = document.getElementById('login-error');

    label.textContent = 'Signing in';
    icon.style.display = 'none';
    dots.style.display = 'inline-flex';
    btn.disabled = true;
    err.style.display = 'none';

    try {
        const resp = await fetch('/examiner/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                username: document.getElementById('username').value.trim(),
                password: document.getElementById('password').value,
            }),
        });
        const data = await resp.json();
        if (!resp.ok || data.status === 'error') {
            throw new Error(data.message || 'Invalid credentials.');
        }
        window.location.href = '/examiner/dashboard';

    } catch (ex) {
        document.getElementById('login-error-text').textContent = ex.message;
        err.style.display = 'flex';
    } finally {
        label.textContent = 'Sign in to Scanner';
        icon.style.display = '';
        dots.style.display = 'none';
        btn.disabled = false;
    }
});
</script>
@endpush
