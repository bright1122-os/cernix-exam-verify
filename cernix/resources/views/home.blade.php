@extends('layouts.cernix')

@section('title', 'Cryptographic Exam Access')

@push('styles')
<style>
/* === Landing page === */
.landing{min-height:100vh;display:flex;flex-direction:column;background:var(--bg)}

.landing .hero{
  padding:60px 24px 40px;position:relative;overflow:hidden;
}
.landing .hero .grid-bg{
  position:absolute;inset:-1px;
  background-image:
    linear-gradient(var(--line) 1px,transparent 1px),
    linear-gradient(90deg,var(--line) 1px,transparent 1px);
  background-size:24px 24px;
  mask:radial-gradient(circle at 50% 0%,#000 0%,transparent 70%);
  -webkit-mask:radial-gradient(circle at 50% 0%,#000 0%,transparent 70%);
  opacity:.6;
}
.landing .hero .glow{
  position:absolute;top:-100px;left:50%;transform:translateX(-50%);
  width:600px;height:500px;border-radius:50%;
  background:radial-gradient(circle,rgba(45,108,255,.15),transparent 60%);
  pointer-events:none;
}
.landing h1.brand{
  font-size:clamp(36px,6vw,56px);font-weight:800;letter-spacing:-.03em;line-height:1;margin:24px 0 0;
}
.landing h1.brand .n{
  background:linear-gradient(135deg,var(--navy),var(--blue));
  -webkit-background-clip:text;background-clip:text;color:transparent;
}
.landing .tag{font-size:15px;color:var(--ink-3);margin:14px 0 0;line-height:1.6;max-width:480px}
.landing .stat-strip{
  margin:32px 0 0;display:grid;grid-template-columns:repeat(3,1fr);
  border:1px solid var(--line);border-radius:14px;background:var(--bg-2);
  overflow:hidden;max-width:400px;
}
.landing .stat-strip > div{padding:14px;text-align:center;border-right:1px solid var(--line)}
.landing .stat-strip > div:last-child{border-right:none}
.landing .stat-strip b{display:block;font-size:18px;font-weight:700;letter-spacing:-.02em}
.landing .stat-strip span{font-size:10px;color:var(--ink-3);letter-spacing:.08em;text-transform:uppercase}

/* Portals */
.landing .portals{padding:0 24px;display:flex;flex-direction:column;gap:12px;max-width:560px}
.portal{
  background:var(--bg-2);border:1px solid var(--line);border-radius:18px;padding:20px;
  display:flex;align-items:center;gap:14px;transition:all .2s;
  position:relative;overflow:hidden;text-align:left;width:100%;
}
.portal:hover{transform:translateY(-2px);box-shadow:var(--shadow);border-color:var(--ink-4)}
.portal:active{transform:translateY(0);filter:brightness(.97)}
.portal .ico{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.portal .ico.student{background:rgba(45,108,255,.12);color:var(--blue)}
.portal .ico.examiner{background:rgba(5,150,105,.12);color:var(--emerald)}
.portal .ico.admin{background:rgba(15,32,80,.08);color:var(--navy)}
.portal .txt{flex:1;min-width:0}
.portal .txt h3{margin:0;font-size:16px;font-weight:600}
.portal .txt p{margin:3px 0 0;font-size:12px;color:var(--ink-3);line-height:1.4}
.portal .arrow{color:var(--ink-4);transition:transform .2s;flex-shrink:0}
.portal:hover .arrow{transform:translateX(4px);color:var(--accent)}
.portal .accent-line{
  position:absolute;left:0;top:0;bottom:0;width:3px;
  background:var(--accent);transform:scaleY(0);transform-origin:top;transition:transform .25s;
}
.portal:hover .accent-line{transform:scaleY(1)}

/* System status */
.landing .system{
  margin:20px 24px 0;max-width:560px;
  display:flex;align-items:center;gap:10px;padding:12px 16px;
  background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2);border-radius:12px;
}
.landing .footer-meta{
  padding:24px 24px 40px;font-size:11px;color:var(--ink-4);
  letter-spacing:.04em;max-width:560px;
}

/* Centered wrapper for larger screens */
@media(min-width:768px){
  .landing-inner{
    max-width:640px;margin:0 auto;padding:0 24px;
    display:flex;flex-direction:column;
  }
  .landing .hero{padding:80px 0 40px}
  .landing .portals{padding:0}
  .landing .system{margin:20px 0 0}
  .landing .footer-meta{padding:24px 0 40px}
}
</style>
@endpush

@section('content')
<div class="landing">
  <div class="landing-inner">

    {{-- Hero --}}
    <div class="hero">
      <div class="grid-bg"></div>
      <div class="glow"></div>

      <div class="logo-mark" style="position:relative">
        <span class="logo-glyph"></span>
        <span>CERNIX</span>
      </div>

      <h1 class="brand" style="position:relative">
        <span class="n">Cryptographic</span><br>Exam Access.
      </h1>

      <p class="tag" style="position:relative">
        End-to-end secure exam hall access control. AES-256-GCM encrypted QR tokens,
        HMAC-verified identities, one-time admission.
      </p>

      <div class="stat-strip" style="position:relative">
        <div><b>AES-256</b><span>Encryption</span></div>
        <div><b>HMAC</b><span>Signed</span></div>
        <div><b>One-time</b><span>Tokens</span></div>
      </div>
    </div>

    {{-- Portal cards --}}
    <div class="portals">
      <a href="/student/register" class="portal">
        <span class="accent-line"></span>
        <div class="ico student">
          <svg class="i" viewBox="0 0 24 24"><path d="M12 3l9 4.5L12 12 3 7.5 12 3z"/><path d="M3 11v4.5c0 .5 3 2.5 9 2.5s9-2 9-2.5V11"/></svg>
        </div>
        <div class="txt">
          <h3>Student Portal</h3>
          <p>Register for your exam and get your one-time QR token</p>
        </div>
        <div class="arrow">
          <svg class="i" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </div>
      </a>

      <a href="/examiner/dashboard" class="portal">
        <span class="accent-line"></span>
        <div class="ico examiner">
          <svg class="i" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 12h10"/></svg>
        </div>
        <div class="txt">
          <h3>Examiner Login</h3>
          <p>Scan student QR codes at the exam hall entrance</p>
        </div>
        <div class="arrow">
          <svg class="i" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </div>
      </a>

      <a href="/admin/dashboard" class="portal">
        <span class="accent-line"></span>
        <div class="ico admin">
          <svg class="i" viewBox="0 0 24 24"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg>
        </div>
        <div class="txt">
          <h3>Admin Dashboard</h3>
          <p>Verification logs, audit trail, and session management</p>
        </div>
        <div class="arrow">
          <svg class="i" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </div>
      </a>
    </div>

    {{-- System status --}}
    <div class="system" id="system-status">
      <span class="pulse-dot"></span>
      <div style="flex:1">
        <b style="font-size:12px;font-weight:600;color:var(--emerald)" id="status-text">Checking system…</b>
        <div style="font-size:11px;color:var(--ink-3)" id="status-sub">Please wait</div>
      </div>
      <span class="chip emerald" id="status-chip">LIVE</span>
    </div>

    <div class="footer-meta">
      CERNIX v1.0 · Secured by cryptographic primitives
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
fetch('/health').then(r => r.json()).then(d => {
  const ok = d.status === 'ok' && d.session_active;
  document.getElementById('status-text').textContent = ok ? 'System operational' : 'System up — no active session';
  document.getElementById('status-sub').textContent  = ok
    ? 'Active exam session running'
    : 'No active exam session configured';
  if (!ok) {
    document.getElementById('status-chip').textContent = 'STANDBY';
    document.getElementById('status-chip').className   = 'chip amber';
    document.getElementById('system-status').style.background = 'rgba(245,158,11,.06)';
    document.getElementById('system-status').style.borderColor = 'rgba(245,158,11,.2)';
    document.querySelector('.pulse-dot').style.background = 'var(--amber-2)';
  }
}).catch(() => {
  document.getElementById('status-text').textContent = 'Status unavailable';
  document.getElementById('status-chip').className   = 'chip red';
});
</script>
@endpush
