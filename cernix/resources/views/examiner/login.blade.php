@extends('layouts.portal')

@section('title', 'Examiner Login')

@section('content')
<style>
    /* ── Page ──────────────────────────────────────────── */
    body { background: #f2f2f0; }
    .lp {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px 20px;
    }

    /* ── Card ──────────────────────────────────────────── */
    .lp-card {
        background: #fff;
        border: 1px solid #e2e2e0;
        border-radius: 16px;
        padding: 36px 32px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 1px 4px rgba(0,0,0,.05), 0 8px 28px rgba(0,0,0,.05);
        animation: fadeUp .35s ease both;
    }

    /* ── Header mark ───────────────────────────────────── */
    .lp-mark {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #1a1a1a;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
    }
    .lp-title {
        font-size: 22px;
        font-weight: 700;
        color: #111;
        margin: 0 0 5px;
        letter-spacing: -.025em;
    }
    .lp-sub {
        font-size: 13px;
        color: #6b6b6b;
        margin: 0 0 28px;
        line-height: 1.55;
    }

    /* ── Fields ────────────────────────────────────────── */
    .lp-field { margin-bottom: 14px; }
    .lp-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #444;
        letter-spacing: .02em;
        text-transform: uppercase;
        margin-bottom: 7px;
    }
    .lp-input {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid #d6d6d4;
        border-radius: 10px;
        font-size: 14px;
        background: #fff;
        color: #111;
        transition: border-color .15s, box-shadow .15s;
        outline: none;
    }
    .lp-input:hover:not(:focus) { border-color: #b0b0ae; }
    .lp-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
    .lp-pw-wrap { position: relative; }
    .lp-pw-toggle {
        position: absolute;
        right: 4px;
        top: 4px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        border-radius: 8px;
        background: none;
        border: none;
        cursor: pointer;
        transition: color .15s;
    }
    .lp-pw-toggle:hover { color: #6b6b6b; }

    /* ── Error ─────────────────────────────────────────── */
    .lp-error {
        display: none;
        padding: 10px 13px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 9px;
        font-size: 13px;
        color: #b91c1c;
        margin-bottom: 14px;
        align-items: center;
        gap: 8px;
    }
    .lp-error.show { display: flex; }

    /* ── Submit ────────────────────────────────────────── */
    .lp-btn {
        width: 100%;
        padding: 12px;
        background: #1a1a1a;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background .15s, transform .15s;
        margin-top: 4px;
    }
    .lp-btn:hover { background: #2a2a2a; }
    .lp-btn:active { transform: scale(.99); }
    .lp-btn:disabled { opacity: .55; cursor: not-allowed; }

    /* ── Footer note ───────────────────────────────────── */
    .lp-note {
        margin-top: 20px;
        font-size: 11px;
        color: #a8a8a6;
        text-align: center;
        line-height: 1.55;
    }

    /* ── Back button ───────────────────────────────────── */
    .lp-back {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 10;
        width: 36px;
        height: 36px;
        background: #fff;
        border: 1px solid #e2e2e0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #444;
        text-decoration: none;
        transition: background .15s, border-color .15s;
    }
    .lp-back:hover { background: #f5f5f4; border-color: #c8c8c6; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: none; }
    }
</style>

<a href="/" class="lp-back" aria-label="Back to home">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M15 18l-6-6 6-6"/>
    </svg>
</a>

<div class="lp">
    <div class="lp-card">
        <div class="lp-mark">
            <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <h1 class="lp-title">Examiner access</h1>
        <p class="lp-sub">Sign in to start verifying student QR passes at your assigned hall.</p>

        <form id="login-form" novalidate>
            <div class="lp-field">
                <label class="lp-label" for="username">Username</label>
                <input id="username" class="lp-input" type="text"
                       placeholder="examiner1" autocomplete="username" required>
            </div>

            <div class="lp-field">
                <label class="lp-label" for="password">Password</label>
                <div class="lp-pw-wrap">
                    <input id="password" class="lp-input" type="password"
                           placeholder="••••••••••" autocomplete="current-password"
                           style="padding-right:46px" required>
                    <button type="button" class="lp-pw-toggle" id="toggle-pw" aria-label="Toggle password">
                        <svg id="eye-show" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eye-hide" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="lp-error" id="login-error">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span id="login-error-text"></span>
            </div>

            <button type="submit" class="lp-btn" id="login-btn">
                <svg id="login-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                </svg>
                <span id="login-label">Sign in</span>
                <span id="login-dots" class="dots" style="display:none"><span></span><span></span><span></span></span>
            </button>
        </form>

        <p class="lp-note">Sessions expire after 4 hours · All scan activity is logged</p>
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

    label.textContent = 'Signing in…';
    icon.style.display = 'none';
    dots.style.display = 'inline-flex';
    btn.disabled = true;
    err.classList.remove('show');

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
        err.classList.add('show');
    } finally {
        label.textContent = 'Sign in';
        icon.style.display = '';
        dots.style.display = 'none';
        btn.disabled = false;
    }
});
</script>
@endpush
