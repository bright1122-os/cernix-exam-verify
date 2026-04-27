@extends('layouts.portal')

@section('title', 'Examiner Login')

@section('content')
<style>
    /* ── Layout ────────────────────────────────────────────── */
    .login-page {
        min-height: 100vh;
        display: flex;
        background: var(--bg);
    }

    /* ── Left branding panel (desktop only) ─────────────────── */
    .login-left { display: none; }

    @media (min-width: 768px) {
        .login-left {
            display: flex;
            flex-direction: column;
            width: 460px;
            flex-shrink: 0;
            background: linear-gradient(160deg, #0f2050 0%, #0a1635 55%, #061022 100%);
            position: relative;
            overflow: hidden;
            color: #fff;
        }
        .login-left::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(45,108,255,.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(16,185,129,.06) 0%, transparent 50%);
        }
        .login-left::after {
            content: "";
            position: absolute;
            bottom: -60px;
            right: -60px;
            width: 280px;
            height: 280px;
            border: 1px solid rgba(255,255,255,.04);
            border-radius: 50%;
            box-shadow: inset 0 0 0 40px rgba(255,255,255,.02), inset 0 0 0 80px rgba(255,255,255,.015);
        }
    }

    .login-left-body {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
        padding: 52px 44px;
        position: relative;
        z-index: 1;
    }

    .login-left-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 52px;
    }
    .login-left-shield {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.18);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .login-left-brand b {
        display: block;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -.01em;
    }
    .login-left-brand span {
        display: block;
        font-size: 11px;
        color: rgba(255,255,255,.5);
        margin-top: 1px;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .login-left-headline {
        font-size: 30px;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -.02em;
        margin: 0 0 12px;
    }
    .login-left-sub {
        font-size: 14px;
        color: rgba(255,255,255,.6);
        line-height: 1.6;
        margin: 0 0 36px;
    }

    .login-features {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .login-feature {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .lf-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .lf-icon.blue   { background: rgba(45,108,255,.15);  color: var(--blue-2); }
    .lf-icon.green  { background: rgba(16,185,129,.15);  color: var(--emerald-2); }
    .lf-icon.amber  { background: rgba(245,158,11,.15);  color: var(--amber-2); }
    .lf-icon.muted  { background: rgba(255,255,255,.07); color: rgba(255,255,255,.5); }
    .lf-icon svg { width: 17px; height: 17px; stroke-width: 2; }
    .lf-text b    { display: block; font-size: 13px; font-weight: 600; line-height: 1.3; }
    .lf-text span { font-size: 12px; color: rgba(255,255,255,.45); line-height: 1.4; }

    .login-left-footer {
        margin-top: 48px;
        padding-top: 24px;
        border-top: 1px solid rgba(255,255,255,.07);
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 11px;
        color: rgba(255,255,255,.3);
        letter-spacing: .05em;
    }

    /* ── Right panel (form side) ─────────────────────────────── */
    .login-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 20px;
        position: relative;
    }
    .login-right-pattern {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(var(--line) 1px, transparent 1px),
            linear-gradient(90deg, var(--line) 1px, transparent 1px);
        background-size: 40px 40px;
        opacity: .35;
        pointer-events: none;
        mask: radial-gradient(circle at 50% 50%, #000 25%, transparent 75%);
        -webkit-mask: radial-gradient(circle at 50% 50%, #000 25%, transparent 75%);
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
    }
    .login-card:focus-within {
        box-shadow: 0 30px 70px -20px rgba(15,32,80,.2), 0 0 0 1px rgba(45,108,255,.1);
    }
    .login-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        border-radius: 999px;
        background: rgba(45,108,255,.1);
        color: var(--blue);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 16px;
    }
    .login-card h2 {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 4px;
        letter-spacing: -.02em;
        color: var(--ink);
    }
    .login-card .sub {
        font-size: 13px;
        color: var(--ink-3);
        margin: 0 0 24px;
        line-height: 1.5;
    }

    .pw-wrap { position: relative; }
    .pw-toggle {
        position: absolute;
        right: 4px;
        top: 4px;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--ink-4);
        border-radius: 10px;
        transition: background .15s, color .15s;
    }
    .pw-toggle:hover { background: var(--bg); color: var(--ink-2); }

    .sec-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 20px 0 16px;
    }
    .sec-divider::before,
    .sec-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--line);
    }
    .sec-divider span {
        font-size: 10px;
        color: var(--ink-4);
        letter-spacing: .12em;
        text-transform: uppercase;
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

    /* ── Back button ─────────────────────────────────────────── */
    .login-back {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 20;
        width: 38px;
        height: 38px;
        border-radius: 11px;
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
        border-color: var(--ink-4);
        background: var(--bg);
        transform: translateX(-1px);
    }
    @media (min-width: 768px) {
        .login-back { display: none; }
    }
</style>

<div class="login-page">

    <!-- Left branding panel (desktop only) -->
    <div class="login-left">
        <div class="login-left-body">
            <div>
                <div class="login-left-brand">
                    <div class="login-left-shield">
                        <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <div>
                        <b>CERNIX</b>
                        <span>Exam Verification</span>
                    </div>
                </div>

                <h2 class="login-left-headline">Secure Exam<br>Hall Access</h2>
                <p class="login-left-sub">Verify student identities and QR tokens in real time with cryptographic confidence.</p>

                <div class="login-features">
                    <div class="login-feature">
                        <div class="lf-icon blue">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h.01M14 17h.01M17 14h.01M17 17h.01M20 17h.01M20 20h.01M17 20h.01M14 20h.01M20 14h.01"/></svg>
                        </div>
                        <div class="lf-text">
                            <b>One-scan QR Verification</b>
                            <span>Real-time token validation via camera</span>
                        </div>
                    </div>
                    <div class="login-feature">
                        <div class="lf-icon green">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        </div>
                        <div class="lf-text">
                            <b>AES-256-GCM Encryption</b>
                            <span>Military-grade payload protection</span>
                        </div>
                    </div>
                    <div class="login-feature">
                        <div class="lf-icon amber">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="lf-text">
                            <b>HMAC-SHA256 Signing</b>
                            <span>Tamper-proof cryptographic integrity</span>
                        </div>
                    </div>
                    <div class="login-feature">
                        <div class="lf-icon muted">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="lf-text">
                            <b>Full Audit Logging</b>
                            <span>Every scan tied to your examiner ID</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="login-left-footer">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                Session-bound · Cryptographic audit · 4-hour timeout
            </div>
        </div>
    </div>

    <!-- Right panel (form) -->
    <div class="login-right">
        <div class="login-right-pattern"></div>
        <a href="/" class="login-back" aria-label="Back to home">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
        </a>

        <div class="login-card">
            <div class="login-badge">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Examiner Access
            </div>
            <h2>Sign in to scan</h2>
            <p class="sub">Authenticate to begin verifying student QR tokens at your hall.</p>

            <form id="login-form">
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" type="text" class="input" placeholder="examiner1"
                           autocomplete="username" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="pw-wrap">
                        <input id="password" type="password" class="input" placeholder="••••••••••"
                               autocomplete="current-password" style="padding-right:52px" required>
                        <button type="button" class="pw-toggle" id="toggle-pw" aria-label="Toggle password">
                            <svg id="eye-show" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eye-hide" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none">
                                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="login-error" class="error-box" style="display:none;margin-bottom:16px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <span id="login-error-text"></span>
                </div>

                <button type="submit" id="login-btn" class="btn btn-primary btn-block" style="margin-top:4px">
                    <svg id="login-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span id="login-label">Sign in to Scanner</span>
                    <span id="login-dots" class="dots" style="display:none"><span></span><span></span><span></span></span>
                </button>
            </form>

            <div class="sec-divider"><span>Secure Access</span></div>

            <div class="sec-note">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                Sessions expire after 4 hours of inactivity. All scan events are cryptographically logged to your examiner ID.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('toggle-pw').addEventListener('click', () => {
    const pw = document.getElementById('password');
    const show = document.getElementById('eye-show');
    const hide = document.getElementById('eye-hide');
    if (pw.type === 'password') {
        pw.type = 'text'; show.style.display = 'none'; hide.style.display = '';
    } else {
        pw.type = 'password'; show.style.display = ''; hide.style.display = 'none';
    }
});

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn   = document.getElementById('login-btn');
    const label = document.getElementById('login-label');
    const icon  = document.getElementById('login-icon');
    const dots  = document.getElementById('login-dots');
    const err   = document.getElementById('login-error');

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
        if (!resp.ok || data.status === 'error') throw new Error(data.message || 'Invalid credentials.');
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
