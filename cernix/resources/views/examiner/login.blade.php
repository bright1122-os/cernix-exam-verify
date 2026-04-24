@extends('layouts.cernix')

@section('title', 'Examiner Login')

@push('styles')
<style>
.login-page{
  min-height:100vh;background:var(--bg);position:relative;
  display:flex;flex-direction:column;justify-content:flex-start;
}

/* Background */
.login-bg{
  position:fixed;inset:0;
  background:
    radial-gradient(circle at 30% 20%,rgba(45,108,255,.12),transparent 50%),
    radial-gradient(circle at 70% 80%,rgba(15,32,80,.1),transparent 50%),
    var(--bg);
  z-index:0;
}
.login-bg .pattern{
  position:absolute;inset:0;
  background-image:linear-gradient(var(--line) 1px,transparent 1px),linear-gradient(90deg,var(--line) 1px,transparent 1px);
  background-size:40px 40px;opacity:.4;
  mask:radial-gradient(circle at 50% 50%,#000 20%,transparent 70%);
  -webkit-mask:radial-gradient(circle at 50% 50%,#000 20%,transparent 70%);
}

/* Back button */
.login-back{
  position:relative;z-index:10;
  padding:20px 20px 0;
}
.login-back a{
  width:40px;height:40px;display:flex;align-items:center;justify-content:center;
  background:var(--bg-2);border:1px solid var(--line);border-radius:12px;
  transition:transform .15s;
}
.login-back a:hover{transform:translateX(-2px)}
.login-back a:active{transform:scale(.95)}

/* Login card */
.login-card{
  position:relative;z-index:1;margin:40px 20px 40px;padding:28px 24px;
  background:var(--bg-2);border:1px solid var(--line);border-radius:22px;
  box-shadow:var(--shadow-lg);
  animation:fadeUp .4s ease both;
}
.login-card .badge{
  display:inline-flex;padding:6px 12px;border-radius:999px;
  background:rgba(45,108,255,.12);color:var(--blue);
  font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;
  margin-bottom:14px;
}
.login-card h2{font-size:24px;font-weight:700;margin:0 0 4px;letter-spacing:-.02em}
.login-card .sub{font-size:13px;color:var(--ink-3);margin:0 0 22px}

/* Eye toggle */
.pw-wrap{position:relative}
.pw-wrap .input{padding-right:48px}
.eye-btn{
  position:absolute;right:4px;top:4px;width:42px;height:42px;
  display:flex;align-items:center;justify-content:center;
  color:var(--ink-3);border-radius:8px;transition:color .15s;
}
.eye-btn:hover{color:var(--ink)}

/* Wide screen */
@media(min-width:540px){
  .login-back{padding:24px 24px 0}
  .login-card{max-width:420px;margin-left:auto;margin-right:auto}
}
</style>
@endpush

@section('content')
<div class="login-page">
  <div class="login-bg"><div class="pattern"></div></div>

  <div class="login-back">
    <a href="/">
      <svg class="i" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    </a>
  </div>

  <div class="login-card">
    <span class="badge">Examiner Access</span>
    <h2>Sign in to begin</h2>
    <p class="sub">Verify QR tokens at your assigned exam hall.</p>

    <form id="login-form">
      @csrf

      <div class="field" id="field-user">
        <label for="username">Username</label>
        <input class="input" id="username" type="text" placeholder="examiner1"
          autocomplete="username" required>
      </div>

      <div class="field" id="field-pass">
        <label for="password">Password</label>
        <div class="pw-wrap">
          <input class="input" id="password" type="password" placeholder="••••••••••"
            autocomplete="current-password" required style="padding-right:48px">
          <button type="button" class="eye-btn" id="eye-btn" onclick="toggleEye()">
            <svg class="i" id="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>

      <div class="error-box fade-up" id="error-box" style="display:none">
        <svg class="i i-sm" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
        <div><span id="error-msg">Invalid credentials.</span></div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="submit-btn" style="margin-top:8px">
        <span id="submit-label" style="display:flex;align-items:center;gap:8px">
          <svg class="i" viewBox="0 0 24 24"><path d="M12 2l8 3v7c0 5-3.5 9-8 10-4.5-1-8-5-8-10V5l8-3z"/><path d="M9 12l2 2 4-4"/></svg>
          Sign in to Scanner
        </span>
        <span id="submit-loading" style="display:none;align-items:center;gap:8px">
          <span class="spin" style="width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;display:inline-block"></span>
          Signing in…
        </span>
      </button>

      <div style="display:flex;align-items:center;gap:8px;margin:18px 0 14px">
        <div style="flex:1;height:1px;background:var(--line)"></div>
        <span style="font-size:11px;color:var(--ink-4);letter-spacing:.1em">SECURE ACCESS</span>
        <div style="flex:1;height:1px;background:var(--line)"></div>
      </div>

      <div style="display:flex;gap:8px;padding:10px;background:var(--bg);border-radius:10px;font-size:11px;color:var(--ink-3);line-height:1.5">
        <svg class="i i-sm" style="flex-shrink:0;margin-top:1px" viewBox="0 0 24 24"><path d="M12 2l8 3v7c0 5-3.5 9-8 10-4.5-1-8-5-8-10V5l8-3z"/><path d="M9 12l2 2 4-4"/></svg>
        <div>Sessions expire after 4 hours of inactivity. All scans are cryptographically logged and tied to your examiner ID.</div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let eyeOpen = false;

function toggleEye() {
  eyeOpen = !eyeOpen;
  const pw = document.getElementById('password');
  pw.type = eyeOpen ? 'text' : 'password';
  document.getElementById('eye-icon').innerHTML = eyeOpen
    ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
    : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}

document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();

  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;
  const errBox   = document.getElementById('error-box');

  errBox.style.display = 'none';
  document.getElementById('field-user').classList.remove('err');
  document.getElementById('field-pass').classList.remove('err');

  if (!username) {
    document.getElementById('field-user').classList.add('err');
    document.getElementById('error-msg').textContent = 'Username is required.';
    errBox.style.display = 'flex';
    return;
  }
  if (!password) {
    document.getElementById('field-pass').classList.add('err');
    document.getElementById('error-msg').textContent = 'Password is required.';
    errBox.style.display = 'flex';
    return;
  }

  setLoading(true);

  try {
    const resp = await fetch('/examiner/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ username, password }),
    });

    const data = await resp.json();

    if (!resp.ok || !data.success) {
      throw new Error(data.message || 'Invalid credentials. Please try again.');
    }

    window.location.href = '/examiner/dashboard';

  } catch (err) {
    document.getElementById('error-msg').textContent = err.message;
    errBox.style.display = 'flex';
    document.getElementById('field-user').classList.add('err');
    document.getElementById('field-pass').classList.add('err');
  } finally {
    setLoading(false);
  }
});

function setLoading(on) {
  const btn     = document.getElementById('submit-btn');
  const label   = document.getElementById('submit-label');
  const loading = document.getElementById('submit-loading');
  btn.disabled          = on;
  label.style.display   = on ? 'none' : 'flex';
  loading.style.display = on ? 'flex' : 'none';
}
</script>
@endpush
