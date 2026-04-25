@extends('layouts.portal')

@section('title', 'Exam Registration')

@section('content')
<style>
    .register-wrap { min-height: 100vh; background: var(--bg); display: flex; flex-direction: column; animation: fadeUp .4s ease both; }

    /* Progress dots */
    .progress-dots { display: flex; gap: 6px; padding: 8px 20px 0; }
    .progress-dots i { height: 3px; flex: 1; background: var(--line); border-radius: 2px; transition: background .3s, transform .2s; }
    .progress-dots i.on { background: var(--accent); }

    /* Session pill */
    .session-pill {
        margin: 16px 20px 0; padding: 14px 16px;
        background: linear-gradient(135deg, rgba(15,32,80,.04), rgba(45,108,255,.06));
        border: 1px solid var(--line); border-radius: 14px;
        display: flex; justify-content: space-between; align-items: center; gap: 12px;
        transition: box-shadow .2s, border-color .2s;
    }
    .session-pill:hover { box-shadow: var(--shadow-sm); border-color: var(--line-2); }
    .session-pill .left b  { display: block; font-size: 13px; font-weight: 600; }
    .session-pill .left span { font-size: 11px; color: var(--ink-3); letter-spacing: .06em; text-transform: uppercase; }
    .session-pill .fee { font-size: 20px; font-weight: 700; font-family: 'JetBrains Mono', monospace; white-space: nowrap; color: var(--navy); }

    /* Security note */
    .sec-note {
        display: flex; gap: 10px; align-items: flex-start;
        padding: 12px 14px; background: var(--bg); border: 1px dashed var(--line-2);
        border-radius: 12px; font-size: 11px; color: var(--ink-3); line-height: 1.5; margin-top: 8px;
    }

    /* QR success */
    .success-header {
        padding: 40px 24px 60px;
        background: linear-gradient(180deg, var(--navy) 0%, var(--navy-3) 100%);
        color: #fff; position: relative; overflow: hidden;
    }
    .success-header::before {
        content: ""; position: absolute; inset: 0;
        background: radial-gradient(circle at 10% 20%, rgba(91,141,255,.2), transparent 40%),
                    radial-gradient(circle at 90% 80%, rgba(16,185,129,.15), transparent 40%);
    }
    .success-header > * { position: relative; z-index: 1; }
    .success-header .check {
        width: 56px; height: 56px; border-radius: 50%;
        background: rgba(16,185,129,.2); border: 2px solid var(--emerald-2);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px; animation: flash .5s ease both;
    }
    .success-header h2 { font-size: 24px; font-weight: 700; margin: 0; letter-spacing: -.02em; }
    .success-header p  { margin: 6px 0 0; font-size: 14px; color: rgba(255,255,255,.7); }

    .qr-wrap {
        margin: -40px 20px 0; padding: 20px;
        background: var(--bg-2); border-radius: 20px; box-shadow: var(--shadow-lg);
        position: relative; z-index: 2; animation: fadeUp .5s .15s cubic-bezier(.16,1,.3,1) both;
        border: 1px solid var(--line);
    }
    .qr-wrap-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
    .qr-code {
        width: 100%; aspect-ratio: 1; background: #fff; border-radius: 12px; padding: 12px;
        display: flex; align-items: center; justify-content: center;
        animation: qrReveal .6s .15s cubic-bezier(.2,.9,.3,1.2) both;
    }
    .qr-code svg { width: 100%; height: 100%; }
    .qr-meta { margin-top: 12px; text-align: center; font-size: 11px; color: var(--ink-3); letter-spacing: .1em; text-transform: uppercase; }

    .detail-grid {
        margin: 20px; display: grid; grid-template-columns: 1fr 1fr;
        gap: 1px; background: var(--line); border-radius: 14px; overflow: hidden; border: 1px solid var(--line);
        animation: fadeUp .4s .25s ease both;
    }
    .detail-grid > div { padding: 14px; background: var(--bg-2); transition: background .15s; }
    .detail-grid > div:hover { background: var(--bg); }
    .detail-grid .k { font-size: 10px; color: var(--ink-3); letter-spacing: .1em; text-transform: uppercase; margin-bottom: 4px; }
    .detail-grid .v { font-size: 14px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .detail-grid .v.mono { font-family: 'JetBrains Mono', monospace; font-size: 11px; }

    .action-row { padding: 0 20px 36px; display: flex; flex-direction: column; gap: 10px; }
</style>

<!-- FORM STATE -->
<div id="form-state" class="register-wrap">
    <!-- Topbar -->
    <div class="topbar">
        <a href="/" class="back" aria-label="Back">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <h1>Exam Registration</h1>
    </div>

    <div class="progress-dots"><i class="on"></i><i></i></div>

    <div style="padding: 20px; flex: 1; max-width: 600px; margin: 0 auto; width: 100%;">
        <div style="margin-bottom: 22px;">
            <h2 style="font-size:22px;font-weight:700;letter-spacing:-.02em;margin:0">Let's verify your payment</h2>
            <p style="font-size:14px;color:var(--ink-3);margin:8px 0 0;line-height:1.5">
                Enter your matriculation number and the Remita RRR from your fee payment to generate your exam QR.
            </p>
        </div>

        <!-- Session pill -->
        <div class="session-pill">
            <div class="left">
                <span>Active Session</span>
                <b>{{ ($session->semester ?? 'Active Semester') }} &middot; {{ $session->academic_year ?? '' }}</b>
            </div>
            <div class="fee">₦{{ number_format($session->fee_amount ?? 0, 0) }}</div>
        </div>

        <!-- Form -->
        <form id="reg-form" style="margin-top: 24px;">
            <div class="field mono">
                <label for="matric_no">Matriculation Number</label>
                <input id="matric_no" type="text" class="input" placeholder="CSC/2021/001" autocomplete="off" required>
                <div class="hint">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="13" rx="2"/><path d="M7 2h10"/></svg>
                    Format: Department / Year / Number
                </div>
            </div>

            <div class="field mono">
                <label for="rrr_number">Remita RRR Number</label>
                <input id="rrr_number" type="text" class="input" placeholder="280007021192" maxlength="12" autocomplete="off" required>
                <div class="hint">12-digit Retrieval Reference from your Remita payment receipt</div>
            </div>

            <div id="error-box" class="error-box" style="display:none;margin-bottom:16px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <div><b>Registration failed.</b><br><span id="error-text"></span></div>
            </div>

            <button type="submit" id="submit-btn" class="btn btn-primary btn-block" style="margin-top:4px">
                <svg id="btn-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span id="btn-label">Generate my Exam QR</span>
                <span id="btn-dots" class="dots" style="display:none"><span></span><span></span><span></span></span>
            </button>
        </form>

        <div class="sec-note" style="margin-top:20px">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Your QR token is encrypted with <strong>AES-256-GCM</strong> and signed with a per-session HMAC key. It is valid for one-time use only.
        </div>
    </div>
</div>

<!-- SUCCESS STATE -->
<div id="success-state" style="display:none; min-height:100vh; background:var(--bg); display:none; flex-direction:column;">
    <div class="success-header">
        <div class="check">
            <svg width="26" height="26" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
        </div>
        <h2>You're registered.</h2>
        <p>Show this QR at the exam hall entrance. Do not share it.</p>
    </div>

    <div class="qr-wrap">
        <div class="qr-wrap-top">
            <span class="chip emerald">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                VALID
            </span>
            <span style="font-size:10px;color:var(--ink-3);letter-spacing:.12em;text-transform:uppercase">One-time use</span>
        </div>
        <div class="qr-code" id="qr-container"></div>
        <div class="qr-meta" id="qr-meta">Session · One-time QR</div>
    </div>

    <div class="detail-grid">
        <div><div class="k">Student</div><div class="v" id="res-name"></div></div>
        <div><div class="k">Matric No.</div><div class="v mono" id="res-matric"></div></div>
        <div><div class="k">Session</div><div class="v" style="font-size:12px">{{ ($session->semester ?? '') . ' ' . ($session->academic_year ?? '') }}</div></div>
        <div><div class="k">Token ID</div><div class="v mono" id="res-token" style="font-size:10px"></div></div>
    </div>

    <div class="action-row">
        <button class="btn btn-ghost" onclick="resetForm()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12a9 9 0 100-4.5"/><path d="M3 3v5h5"/></svg>
            Register another student
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('reg-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn    = document.getElementById('submit-btn');
    const label  = document.getElementById('btn-label');
    const icon   = document.getElementById('btn-icon');
    const dots   = document.getElementById('btn-dots');
    const errBox = document.getElementById('error-box');

    label.textContent = 'Verifying payment';
    icon.style.display  = 'none';
    dots.style.display  = 'inline-flex';
    btn.disabled = true;
    errBox.style.display = 'none';

    try {
        const resp = await fetch('/student/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                matric_no:  document.getElementById('matric_no').value.trim(),
                rrr_number: document.getElementById('rrr_number').value.trim(),
            }),
        });
        const data = await resp.json();
        if (!resp.ok || !data.success) throw new Error(data.message || 'Registration failed.');

        document.getElementById('res-name').textContent   = data.data.full_name;
        document.getElementById('res-matric').textContent = data.data.matric_no;
        document.getElementById('res-token').textContent  = data.data.token_id.slice(0,8) + '…' + data.data.token_id.slice(-4);
        document.getElementById('qr-container').innerHTML = data.data.qr_svg;
        document.getElementById('qr-meta').textContent    = 'SESSION #' + (data.data.session_id ?? '') + ' · ONE-TIME QR';

        document.getElementById('form-state').style.display    = 'none';
        const s = document.getElementById('success-state');
        s.style.display = 'flex';
        s.style.flexDirection = 'column';

    } catch (err) {
        document.getElementById('error-text').textContent = err.message;
        errBox.style.display = 'flex';
    } finally {
        label.textContent   = 'Generate my Exam QR';
        icon.style.display  = '';
        dots.style.display  = 'none';
        btn.disabled = false;
    }
});

function resetForm() {
    document.getElementById('matric_no').value  = '';
    document.getElementById('rrr_number').value = '';
    document.getElementById('success-state').style.display = 'none';
    document.getElementById('form-state').style.display    = 'flex';
    document.getElementById('form-state').style.flexDirection = 'column';
}
</script>
@endpush
