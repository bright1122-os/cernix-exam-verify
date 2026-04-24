<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>CERNIX — @yield('title', 'Exam Hall Verification')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --navy:#0f2050;
  --navy-2:#1a3370;
  --navy-3:#091638;
  --blue:#2d6cff;
  --blue-2:#5b8dff;
  --emerald:#059669;
  --emerald-2:#10b981;
  --red:#dc2626;
  --red-2:#ef4444;
  --amber:#b45309;
  --amber-2:#f59e0b;
  --bg:#f4f4ef;
  --bg-2:#ffffff;
  --line:#e6e4dc;
  --line-2:#d7d4c8;
  --ink:#0a0f1f;
  --ink-2:#3b3f4c;
  --ink-3:#6b7085;
  --ink-4:#9ca1b3;
  --accent:var(--navy);
  --radius:16px;
  --radius-sm:10px;
  --shadow-sm:0 1px 2px rgba(14,18,38,.05),0 1px 1px rgba(14,18,38,.03);
  --shadow:0 10px 30px -12px rgba(14,18,38,.12),0 4px 8px -4px rgba(14,18,38,.06);
  --shadow-lg:0 30px 60px -20px rgba(14,18,38,.2);
}
*{box-sizing:border-box}
html,body{margin:0;padding:0}
body{
  font-family:'Inter',system-ui,sans-serif;
  background:var(--bg);
  color:var(--ink);
  -webkit-font-smoothing:antialiased;
  font-feature-settings:"ss01","cv11";
  min-height:100vh;
}
.mono{font-family:'JetBrains Mono',ui-monospace,monospace;font-feature-settings:"zero","ss01"}
button{font-family:inherit;border:0;cursor:pointer;background:none;color:inherit}
input,textarea,select{font-family:inherit;color:inherit}
a{color:inherit;text-decoration:none}

/* === Buttons === */
.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  padding:14px 20px;border-radius:12px;font-size:15px;font-weight:600;
  transition:all .15s ease;min-height:48px;position:relative;overflow:hidden;
}
.btn-primary{background:var(--navy);color:#fff;box-shadow:0 1px 0 rgba(255,255,255,.12) inset,0 1px 2px rgba(15,32,80,.3)}
.btn-primary:hover{background:var(--navy-2);transform:translateY(-1px);box-shadow:0 6px 14px -4px rgba(15,32,80,.4)}
.btn-primary:active{transform:translateY(0);filter:brightness(.95)}
.btn-primary:focus-visible{outline:3px solid var(--blue-2);outline-offset:2px}
.btn-primary:disabled{opacity:.7;cursor:wait}
.btn-ghost{background:var(--bg-2);color:var(--ink);border:1px solid var(--line)}
.btn-ghost:hover{border-color:var(--ink-4);background:var(--bg)}
.btn-ghost:active{transform:scale(.98)}
.btn-block{display:flex;width:100%}

/* === Card === */
.card{background:var(--bg-2);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow-sm)}
.card-pad{padding:20px}

/* === Field === */
.field{display:block;margin-bottom:16px}
.field label{display:block;font-size:12px;font-weight:600;color:var(--ink-2);margin-bottom:8px;letter-spacing:.02em}
.field .input{
  width:100%;height:50px;padding:0 14px;
  background:var(--bg-2);border:1.5px solid var(--line);
  border-radius:12px;font-size:16px;color:var(--ink);transition:all .15s;
}
.field .input:hover{border-color:var(--ink-4)}
.field .input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 4px rgba(45,108,255,.15)}
.field .input::placeholder{color:var(--ink-4)}
.field.err .input{border-color:var(--red-2);box-shadow:0 0 0 4px rgba(220,38,38,.1)}
.field .hint{font-size:12px;color:var(--ink-3);margin-top:6px;display:flex;gap:6px;align-items:center}
.field .err-msg{font-size:12px;color:var(--red);margin-top:6px;font-weight:500}
.field.mono .input{font-family:'JetBrains Mono',monospace;letter-spacing:.02em;font-size:15px}

/* === Chip === */
.chip{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600;letter-spacing:.04em}
.chip.emerald{background:rgba(16,185,129,.12);color:var(--emerald)}
.chip.amber{background:rgba(245,158,11,.15);color:var(--amber)}
.chip.red{background:rgba(239,68,68,.12);color:var(--red)}
.chip.navy{background:rgba(15,32,80,.1);color:var(--navy)}
.chip.blue{background:rgba(45,108,255,.12);color:var(--blue)}

/* === SVG icons === */
.i{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
.i-sm{width:16px;height:16px}
.i-lg{width:24px;height:24px}

/* === Logo === */
.logo-mark{display:inline-flex;align-items:center;gap:8px;font-weight:800;letter-spacing:.18em;font-size:13px}
.logo-glyph{
  width:26px;height:26px;background:var(--navy);border-radius:7px;
  display:flex;align-items:center;justify-content:center;color:#fff;
  box-shadow:inset 0 0 0 1px rgba(255,255,255,.1);position:relative;
}
.logo-glyph::before{
  content:"";position:absolute;inset:6px;border:1.5px solid #fff;border-radius:3px;
  border-right-color:transparent;border-bottom-color:transparent;transform:rotate(45deg);
}
.logo-glyph::after{
  content:"";position:absolute;width:6px;height:6px;background:var(--blue-2);
  border-radius:50%;bottom:4px;right:4px;
}

/* === Divider === */
.div-line{height:1px;background:var(--line);margin:20px 0}

/* === Error box === */
.error-box{
  display:flex;gap:10px;padding:12px 14px;background:rgba(239,68,68,.08);
  border:1px solid rgba(239,68,68,.25);border-radius:12px;
  font-size:13px;color:var(--red);line-height:1.45;
}

/* === Loading dots === */
.dots{display:inline-flex;gap:3px}
.dots span{width:4px;height:4px;border-radius:50%;background:currentColor;animation:pulse 1.2s infinite}
.dots span:nth-child(2){animation-delay:.15s}
.dots span:nth-child(3){animation-delay:.3s}

/* === Pulse dot === */
.pulse-dot{width:8px;height:8px;border-radius:50%;background:var(--emerald-2);flex-shrink:0;
  box-shadow:0 0 0 0 rgba(16,185,129,.5);animation:dotPulse 1.8s infinite}

/* === Panel === */
.panel{background:var(--bg-2);border:1px solid var(--line);border-radius:16px;overflow:hidden;margin-bottom:20px}
.panel-head{padding:16px 20px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center}
.panel-head h3{margin:0;font-size:15px;font-weight:600}
.panel-head .count{font-size:11px;color:var(--ink-3);letter-spacing:.08em}

/* === Animations === */
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
@keyframes scanline{0%{top:10%}100%{top:90%}}
@keyframes qrReveal{from{opacity:0;transform:scale(.85) rotate(-2deg);filter:blur(4px)}to{opacity:1;transform:scale(1) rotate(0);filter:blur(0)}}
@keyframes flash{0%{opacity:0;transform:scale(.94)}60%{opacity:1;transform:scale(1.02)}100%{opacity:1;transform:scale(1)}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes dotPulse{0%{box-shadow:0 0 0 0 rgba(16,185,129,.5)}70%{box-shadow:0 0 0 8px rgba(16,185,129,0)}100%{box-shadow:0 0 0 0 rgba(16,185,129,0)}}
@keyframes counter{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.fade-up{animation:fadeUp .4s ease both}
.fade-in{animation:fadeIn .3s ease both}
.spin{animation:spin 1s linear infinite}

@stack('styles')
</style>
@stack('head')
</head>
<body>
@yield('content')
@stack('scripts')
</body>
</html>
