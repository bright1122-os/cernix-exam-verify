@extends('layouts.portal')

@section('title', 'Examiner Login')

@section('content')
<style>
    .login-bg {
        position: fixed; inset: 0;
        background:
            radial-gradient(circle at 30% 20%, rgba(45,108,255,.12), transparent 50%),
            radial-gradient(circle at 70% 80%, rgba(15,32,80,.1), transparent 50%),
            var(--bg);
    }
    .login-bg .pattern {
        position: absolute; inset: 0;
        background-image:
            linear-gradient(var(--line) 1px, transparent 1px),
            linear-gradient(90deg, var(--line) 1px, transparent 1px);
        background-size: 40px 40px; opacity: .4;
        mask: radial-gradient(circle at 50% 50%, #000 20%, transparent 70%);
        -webkit-mask: radial-gradient(circle at 50% 50%, #000 20%, transparent 70%);
    }
    .login-card {
        position: relative; margin: 0 auto; padding: 32px 28px;
        background: var(--bg-2); border: 1px solid var(--line); border-radius: 22px;
        box-shadow: var(--shadow-lg); max-width: 420px; width: calc(100% - 40px);
        animation: fadeUp .35s ease both;
    }
    .login-card .badge {
        display: inline-flex; padding: 6px 12px; border-radius: 999px;
        background: rgba(45,108,255,.12); color: var(--blue);
        font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 16px;
    }
    .login-card h2 { font-size: 24px; font-weight: 700; margin: 0 0 4px; letter-spacing: -.02em; }
    .login-card .sub { font-size: 13px; color: var(--ink-3); margin: 0 0 24px; }

    .show-pw-btn {
        position: absolute; right: 4px; top: 4px;
        width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;
        color: var(--ink-3); border-radius: 10px;
    }
    .show-pw-btn:hover { background: var(--bg); }

    .divider { display: flex; align-items: center; gap: 10px; margin: 20px 0 16px; }
    .divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: var(--line); }
    .divider span { font-size: 11px; color: var(--ink-4); letter-spacing: .1em; }

    .sec-note {
        display: flex; gap: 8px; padding: 10px 12px; background: var(--bg);
        border-radius: 10px; font-size: 11px; color: var(--ink-3); line-height: 1.5;
    }
</style>

<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; position:relative; padding:20px;">
    <div class="login-bg"><div class="pattern"></div></div>

    <!-- Back link -->
    <a href="/" style="position:fixed;top:20px;left:20px;z-index:10;
        width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;
        background:var(--bg-2);border:1px solid var(--line);color:var(--ink-2);" aria-label="Back">
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
@endsection

@push('scripts')
<script>
// Password toggle
document.getElementById('toggle-pw').addEventListener('click', () => {
    const pw = document.getElementById('password');
    const open = document.getElementById('eye-open');
    const closed = document.getElementById('eye-closed');
    if (pw.type === 'password') {
        pw.type = 'text'; open.style.display = 'none'; closed.style.display = '';
    } else {
        pw.type = 'password'; open.style.display = ''; closed.style.display = 'none';
    }
});

// Login form — calls /examiner/login (web session), then redirects to dashboard
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn   = document.getElementById('login-btn');
    const label = document.getElementById('login-label');
    const icon  = document.getElementById('login-icon');
    const dots  = document.getElementById('login-dots');
    const err   = document.getElementById('login-error');

    label.textContent = 'Signing in';
    icon.style.display = 'none'; dots.style.display = 'inline-flex';
    btn.disabled = true; err.style.display = 'none';

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
        label.textContent  = 'Sign in to Scanner';
        icon.style.display = ''; dots.style.display = 'none';
        btn.disabled = false;
    }
});
</script>
@endpush
