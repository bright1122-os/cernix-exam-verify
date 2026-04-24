@extends('layouts.cernix')

@section('title', 'Exam Registration')

@push('styles')
<style>
.register-page{min-height:100vh;background:var(--bg);display:flex;flex-direction:column}

/* Topbar */
.topbar{
  position:sticky;top:0;z-index:30;background:var(--bg);
  padding:16px 20px 12px;display:flex;align-items:center;gap:12px;
  border-bottom:1px solid var(--line);
}
.topbar .back{
  width:40px;height:40px;display:flex;align-items:center;justify-content:center;
  background:var(--bg-2);border-radius:12px;border:1px solid var(--line);
  transition:transform .15s;flex-shrink:0;
}
.topbar .back:hover{transform:translateX(-2px)}
.topbar .back:active{transform:scale(.95)}
.topbar h1{font-size:17px;font-weight:600;margin:0;flex:1;text-align:center;margin-right:40px}

/* Progress */
.progress-dots{display:flex;gap:6px;padding:12px 20px 0}
.progress-dots i{height:3px;flex:1;background:var(--line);border-radius:2px;max-width:80px}
.progress-dots i.on{background:var(--accent)}

/* Session pill */
.session-pill{
  margin:16px 20px 0;padding:14px 16px;
  background:linear-gradient(135deg,rgba(15,32,80,.04),rgba(45,108,255,.06));
  border:1px solid var(--line);border-radius:14px;
  display:flex;justify-content:space-between;align-items:center;
}
.session-pill .left b{display:block;font-size:13px;font-weight:600}
.session-pill .left span{font-size:11px;color:var(--ink-3);letter-spacing:.06em;text-transform:uppercase}
.session-pill .fee{font-size:20px;font-weight:700;font-family:'JetBrains Mono',monospace}

/* Form body */
.form-body{padding:20px 20px 40px;max-width:560px;width:100%}

/* === QR Success === */
.success-page{min-height:100vh;background:var(--bg);display:flex;flex-direction:column}

.success-header{
  padding:48px 20px 24px;
  background:linear-gradient(180deg,var(--navy) 0%,var(--navy-3) 100%);
  color:#fff;position:relative;overflow:hidden;
}
.success-header::before{
  content:"";position:absolute;inset:0;
  background:radial-gradient(circle at 10% 20%,rgba(91,141,255,.2),transparent 40%),
             radial-gradient(circle at 90% 80%,rgba(16,185,129,.15),transparent 40%);
}
.success-header .check-icon{
  width:56px;height:56px;border-radius:50%;
  background:rgba(16,185,129,.2);border:2px solid var(--emerald-2);
  display:flex;align-items:center;justify-content:center;
  margin-bottom:16px;position:relative;z-index:1;
  animation:flash .5s ease both;
}
.success-header h2{font-size:24px;font-weight:700;margin:0;letter-spacing:-.02em;position:relative;z-index:1}
.success-header p{margin:6px 0 0;font-size:14px;color:rgba(255,255,255,.7);position:relative;z-index:1}

.qr-wrap{
  margin:-32px 20px 0;padding:20px;background:var(--bg-2);
  border-radius:20px;box-shadow:var(--shadow-lg);position:relative;z-index:2;
  animation:fadeUp .45s .1s ease both;
}
.qr-code{
  width:100%;aspect-ratio:1;max-width:280px;margin:0 auto;
  background:#fff;border-radius:12px;padding:16px;
  display:flex;align-items:center;justify-content:center;
  animation:qrReveal .6s .15s cubic-bezier(.2,.9,.3,1.2) both;
}
.qr-code svg{width:100%;height:100%}
.qr-meta{margin-top:12px;text-align:center;font-size:11px;color:var(--ink-3);letter-spacing:.1em;text-transform:uppercase}

.detail-grid{
  margin:20px;padding:0;display:grid;grid-template-columns:1fr 1fr;gap:1px;
  background:var(--line);border-radius:14px;overflow:hidden;border:1px solid var(--line);
}
.detail-grid > div{padding:14px;background:var(--bg-2)}
.detail-grid .k{font-size:10px;color:var(--ink-3);letter-spacing:.1em;text-transform:uppercase;margin-bottom:4px}
.detail-grid .v{font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.detail-grid .v.mono{font-family:'JetBrains Mono',monospace;font-size:11px}

.action-row{padding:0 20px 40px;display:flex;flex-direction:column;gap:10px}

/* Wide screen centering */
@media(min-width:600px){
  .topbar{padding:16px 24px 12px}
  .session-pill{margin-left:24px;margin-right:24px}
  .form-body{padding:20px 24px 40px}
  .qr-wrap{margin-left:24px;margin-right:24px}
  .detail-grid{margin-left:24px;margin-right:24px}
  .action-row{padding-left:24px;padding-right:24px}
}
</style>
@endpush

@section('content')

{{-- ================================================================
     REGISTRATION FORM (shown when no student registered yet)
     ================================================================ --}}
<div id="form-view" class="register-page">

  <div class="topbar">
    <a href="/" class="back">
      <svg class="i" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    </a>
    <h1>Exam Registration</h1>
  </div>

  <div class="progress-dots"><i class="on"></i><i></i></div>

  <div class="form-body">
    <div style="margin-bottom:20px">
      <h2 style="font-size:22px;font-weight:700;letter-spacing:-.02em;margin:0">Let's verify your payment</h2>
      <p style="font-size:14px;color:var(--ink-3);margin:6px 0 0;line-height:1.5">
        Enter your matriculation number and the Remita RRR from your fee payment to generate your exam QR.
      </p>
    </div>

    @if($session)
    <div class="session-pill" style="margin-left:0;margin-right:0">
      <div class="left">
        <span>Active Session</span>
        <b>{{ $session->semester }} · {{ $session->academic_year }}</b>
      </div>
      <div class="fee">₦{{ number_format($session->fee_amount, 0) }}</div>
    </div>
    @endif

    <form id="reg-form" style="margin-top:22px">
      @csrf

      <div class="field mono" id="field-matric">
        <label for="matric_no">Matriculation Number</label>
        <input class="input" id="matric_no" type="text" placeholder="CSC/2021/001"
          autocomplete="off" required>
        <div class="hint">
          <svg class="i i-sm" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 9h6v6H9z"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M1 9h3M1 15h3M20 9h3M20 15h3"/></svg>
          Format: Department / Year / Number
        </div>
      </div>

      <div class="field mono" id="field-rrr">
        <label for="rrr_number">Remita RRR Number</label>
        <input class="input" id="rrr_number" type="text" placeholder="280007021192"
          maxlength="20" autocomplete="off" required>
        <div class="hint">12-digit Retrieval Reference from your Remita payment receipt</div>
      </div>

      <div class="error-box fade-up" id="error-box" style="display:none">
        <svg class="i i-sm" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
        <div><b>Registration failed.</b><br><span id="error-msg"></span></div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="submit-btn" style="margin-top:22px">
        <span id="submit-label">
          <svg class="i" viewBox="0 0 24 24"><path d="M12 2l8 3v7c0 5-3.5 9-8 10-4.5-1-8-5-8-10V5l8-3z"/><path d="M9 12l2 2 4-4"/></svg>
          Generate my Exam QR
        </span>
        <span id="submit-loading" style="display:none;align-items:center;gap:8px">
          <span class="spin" style="width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;display:inline-block"></span>
          Verifying payment<span class="dots"><span></span><span></span><span></span></span>
        </span>
      </button>
    </form>

    <div style="display:flex;gap:10px;align-items:center;margin-top:20px;padding:12px;background:var(--bg);border-radius:12px;border:1px dashed var(--line-2)">
      <svg class="i i-sm" viewBox="0 0 24 24" style="color:var(--ink-3);flex-shrink:0"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
      <div style="font-size:11px;color:var(--ink-3);line-height:1.45">
        Your QR token is encrypted with <b>AES-256-GCM</b> and signed with a per-session HMAC secret. It can only be redeemed once.
      </div>
    </div>

    <div style="text-align:center;font-size:11px;color:var(--ink-4);margin-top:20px">
      Need help? Visit the Bursary office · Block B
    </div>
  </div>
</div>

{{-- ================================================================
     SUCCESS / QR VIEW (hidden, shown after registration)
     ================================================================ --}}
<div id="success-view" class="success-page" style="display:none">

  <div class="success-header">
    <div class="check-icon">
      <svg class="i i-lg" viewBox="0 0 24 24" style="stroke:#fff;stroke-width:3"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <h2>You're registered.</h2>
    <p>Show this QR at the exam hall entrance. Do not share it.</p>
  </div>

  <div class="qr-wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
      <span class="chip emerald">
        <svg class="i i-sm" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
        VALID
      </span>
      <span style="font-size:10px;color:var(--ink-3);letter-spacing:.12em;text-transform:uppercase">One-time use</span>
    </div>
    <div class="qr-code" id="qr-container"></div>
    <div class="qr-meta" id="qr-meta">SESSION · FIRST SEMESTER</div>
  </div>

  <div class="detail-grid">
    <div><div class="k">Student</div><div class="v" id="res-name">—</div></div>
    <div><div class="k">Matric No.</div><div class="v mono" id="res-matric">—</div></div>
    <div><div class="k">Department</div><div class="v" id="res-dept" style="font-size:12px">—</div></div>
    <div><div class="k">Token ID</div><div class="v mono" id="res-token" style="font-size:11px">—</div></div>
  </div>

  <div class="action-row">
    <button class="btn btn-ghost" onclick="resetForm()">
      Register another student
    </button>
    <a href="/" class="btn btn-ghost" style="justify-content:center">Back to Home</a>
  </div>

</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('reg-form').addEventListener('submit', async (e) => {
  e.preventDefault();

  const matric = document.getElementById('matric_no').value.trim();
  const rrr    = document.getElementById('rrr_number').value.trim();
  const errBox = document.getElementById('error-box');
  const errMsg = document.getElementById('error-msg');

  // Reset error
  errBox.style.display = 'none';
  document.getElementById('field-matric').classList.remove('err');
  document.getElementById('field-rrr').classList.remove('err');

  // Client-side validation
  if (!matric) {
    document.getElementById('field-matric').classList.add('err');
    errMsg.textContent = 'Matriculation number is required.';
    errBox.style.display = 'flex';
    return;
  }
  if (!rrr || rrr.length < 10) {
    document.getElementById('field-rrr').classList.add('err');
    errMsg.textContent = 'Remita RRR must be at least 10 digits.';
    errBox.style.display = 'flex';
    return;
  }

  // Loading state
  setLoading(true);

  try {
    const resp = await fetch('/student/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ matric_no: matric, rrr_number: rrr }),
    });

    const data = await resp.json();

    if (!resp.ok || !data.success) {
      throw new Error(data.message || 'Registration failed. Please try again.');
    }

    // Populate success view
    const d = data.data;
    document.getElementById('res-name').textContent   = d.full_name;
    document.getElementById('res-matric').textContent = d.matric_no;
    document.getElementById('res-token').textContent  = d.token_id.slice(0,8) + '…' + d.token_id.slice(-4);
    document.getElementById('qr-container').innerHTML = d.qr_svg;

    // Show success
    document.getElementById('form-view').style.display    = 'none';
    document.getElementById('success-view').style.display = 'flex';

  } catch (err) {
    errMsg.textContent       = err.message;
    errBox.style.display     = 'flex';
  } finally {
    setLoading(false);
  }
});

function setLoading(on) {
  const btn     = document.getElementById('submit-btn');
  const label   = document.getElementById('submit-label');
  const loading = document.getElementById('submit-loading');
  btn.disabled         = on;
  label.style.display  = on ? 'none' : 'flex';
  loading.style.display = on ? 'flex' : 'none';
}

function resetForm() {
  document.getElementById('matric_no').value = '';
  document.getElementById('rrr_number').value = '';
  document.getElementById('error-box').style.display = 'none';
  document.getElementById('success-view').style.display = 'none';
  document.getElementById('form-view').style.display    = 'flex';
}
</script>
@endpush
