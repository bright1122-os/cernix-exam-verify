@extends('layouts.portal')

@section('title', 'Examiner Scanner')

@section('content')
<style>
    .scanner-wrap { position: relative; width: 100%; height: 100vh; background: #000; color: #fff; display: flex; flex-direction: column; }

    .scanner-viewport {
        position: relative; flex: 1; overflow: hidden; background: #0a0e1c;
    }
    .camera-feed {
        position: absolute; inset: 0;
        background: radial-gradient(circle at 50% 40%, rgba(45,108,255,.12), transparent 60%),
                    linear-gradient(135deg, #1a1f35 0%, #050810 100%);
    }
    .camera-feed::before {
        content: ""; position: absolute; inset: 0;
        background-image: repeating-linear-gradient(0deg,rgba(255,255,255,.015) 0,rgba(255,255,255,.015) 1px,transparent 1px,transparent 3px);
    }
    .fake-hall {
        position: absolute; inset: 10% 15%; opacity: .25;
        background: linear-gradient(180deg, rgba(255,255,255,.1), transparent 30%),
                    repeating-linear-gradient(45deg,rgba(255,255,255,.03) 0 10px,transparent 10px 20px);
        border-radius: 8px;
    }
    .reticle {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        width: 240px; height: 240px; pointer-events: none;
    }
    .reticle .corners span {
        position: absolute; width: 28px; height: 28px;
        border: 3px solid #fff; border-radius: 8px;
    }
    .reticle .corners span:nth-child(1) { top: 0; left: 0; border-right: none; border-bottom: none; }
    .reticle .corners span:nth-child(2) { top: 0; right: 0; border-left: none; border-bottom: none; }
    .reticle .corners span:nth-child(3) { bottom: 0; left: 0; border-right: none; border-top: none; }
    .reticle .corners span:nth-child(4) { bottom: 0; right: 0; border-left: none; border-top: none; }
    .reticle .scan-line {
        position: absolute; left: 10%; right: 10%; height: 2px;
        background: linear-gradient(90deg,transparent,var(--blue-2),transparent);
        box-shadow: 0 0 12px var(--blue-2);
        animation: scanline 1.8s ease-in-out infinite alternate;
    }
    .reticle .dim-overlay {
        position: absolute; inset: -200vh; box-shadow: 0 0 0 200vh rgba(0,0,0,.55); border-radius: 12px;
    }
    @keyframes scanline {
        from { top: 20%; } to { top: 80%; }
    }

    .scanner-top {
        position: absolute; top: 0; left: 0; right: 0;
        padding: 56px 20px 16px; display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(180deg,rgba(0,0,0,.85),transparent); z-index: 20;
    }
    .ex-info { display: flex; gap: 10px; align-items: center; }
    .ex-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: var(--navy-2); display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 14px;
    }
    .ex-info b { display: block; font-size: 13px; font-weight: 600; }
    .ex-info span { font-size: 11px; color: rgba(255,255,255,.6); }
    .iconbtn {
        width: 40px; height: 40px; border-radius: 12px; background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.14); display: flex; align-items: center; justify-content: center;
        color: #fff; cursor: pointer; transition: background .15s;
    }
    .iconbtn:hover { background: rgba(255,255,255,.14); }

    .scanner-prompt {
        position: absolute; left: 0; right: 0; top: 140px; text-align: center; z-index: 10;
        font-size: 13px; color: rgba(255,255,255,.75); letter-spacing: .06em;
    }
    .scanner-prompt b { color: #fff; font-weight: 600; }

    .scanner-bottom {
        position: absolute; bottom: 0; left: 0; right: 0; z-index: 15;
        background: linear-gradient(180deg,transparent,rgba(0,0,0,.92) 40%);
        padding: 60px 16px 44px;
    }
    .scanner-stats {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 18px;
    }
    .stat-tile {
        padding: 10px 8px; background: rgba(255,255,255,.06); border-radius: 12px;
        border: 1px solid rgba(255,255,255,.08); text-align: center;
    }
    .stat-tile b { display: block; font-size: 18px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
    .stat-tile span { font-size: 9px; letter-spacing: .1em; text-transform: uppercase; color: rgba(255,255,255,.55); }
    .stat-tile.approved b { color: var(--emerald-2); }
    .stat-tile.rejected b { color: var(--red-2); }
    .stat-tile.duplicate b { color: var(--amber-2); }

    .last-scan {
        padding: 12px 14px; border-radius: 14px; background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.1); display: flex; align-items: center; gap: 12px;
    }
    .last-scan .dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,.3); }
    .last-scan.approved { background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.3); }
    .last-scan.approved .dot { background: var(--emerald-2); box-shadow: 0 0 8px var(--emerald-2); }
    .last-scan.rejected { background: rgba(239,68,68,.12); border-color: rgba(239,68,68,.3); }
    .last-scan.rejected .dot { background: var(--red-2); }
    .last-scan.duplicate { background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.3); }
    .last-scan.duplicate .dot { background: var(--amber-2); }
    .last-scan .info { flex: 1; min-width: 0; }
    .last-scan .info b { display: block; font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .last-scan .info span { font-size: 11px; color: rgba(255,255,255,.55); }
    .last-scan .time { font-size: 11px; color: rgba(255,255,255,.5); font-family: 'JetBrains Mono', monospace; }

    .scan-actions {
        display: flex; gap: 10px; margin-top: 14px;
    }
    .scan-actions button {
        flex: 1; padding: 10px; border-radius: 12px; background: rgba(255,255,255,.08);
        color: #fff; font-size: 13px; font-weight: 500; border: 1px solid rgba(255,255,255,.1);
        display: flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer;
    }
    .scan-actions button:hover { background: rgba(255,255,255,.14); }
    .scan-actions button.primary { background: var(--blue); border-color: var(--blue); }
    .scan-actions button.primary:hover { background: var(--blue-2); }

    /* Takeovers */
    .takeover {
        position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: space-between;
        color: #fff; z-index: 100; animation: flash .35s cubic-bezier(.2,.9,.3,1.15) both;
        overflow: hidden; display: none;
    }
    .takeover.approved { background: linear-gradient(180deg,#047857 0%, #065f46 100%); }
    .takeover.rejected { background: linear-gradient(180deg,#b91c1c 0%, #7f1d1d 100%); }
    .takeover.duplicate { background: linear-gradient(180deg,#b45309 0%, #78350f 100%); }
    .takeover.show { display: flex; }
    .takeover::before {
        content: ""; position: absolute; inset: 0;
        background: radial-gradient(circle at 50% 0%, rgba(255,255,255,.15), transparent 60%);
    }
    .takeover .top { padding: 56px 20px 12px; display: flex; justify-content: space-between; align-items: flex-start; position: relative; z-index: 1; }
    .takeover .top .status-label { font-size: 11px; font-weight: 700; letter-spacing: .3em; opacity: .75; }
    .takeover .top .time { font-family: 'JetBrains Mono', monospace; font-size: 12px; opacity: .75; }
    .takeover .center { text-align: center; padding: 0 24px; position: relative; z-index: 1; }
    .takeover .big-icon { width: 140px; height: 140px; border-radius: 50%; background: rgba(255,255,255,.14);
        border: 3px solid rgba(255,255,255,.4); display: flex; align-items: center; justify-content: center;
        margin: 0 auto 28px;
    }
    .takeover .big-icon svg { width: 72px; height: 72px; stroke: #fff; stroke-width: 3; }
    .takeover h1 { font-size: 56px; font-weight: 800; letter-spacing: .04em; margin: 0; line-height: .95; }
    .takeover p { font-size: 17px; margin: 12px 0 0; opacity: .85; }

    .student-card {
        margin: 28px 20px 0; padding: 18px; background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.2); border-radius: 18px;
        display: flex; gap: 14px; align-items: center; backdrop-filter: blur(8px); position: relative; z-index: 1;
    }
    .student-card .avatar {
        width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,.2);
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 22px; flex-shrink: 0;
    }
    .student-card .nm { font-size: 16px; font-weight: 600; margin: 0; }
    .student-card .mt { font-size: 12px; opacity: .7; margin: 2px 0 0; font-family: 'JetBrains Mono', monospace; }
    .student-card .dept { font-size: 11px; opacity: .6; margin: 4px 0 0; text-transform: uppercase; letter-spacing: .1em; }

    .meta-row { margin: 16px 20px 0; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; position: relative; z-index: 1; }
    .meta-cell { padding: 10px 12px; background: rgba(255,255,255,.08); border-radius: 12px; }
    .meta-cell .k { font-size: 10px; opacity: .6; letter-spacing: .1em; text-transform: uppercase; }
    .meta-cell .v { font-size: 13px; font-weight: 600; margin-top: 2px; font-family: 'JetBrains Mono', monospace; }

    .takeover .bottom { padding: 20px; display: flex; gap: 10px; position: relative; z-index: 1; }
    .takeover .bottom button {
        flex: 1; padding: 16px; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.25);
        color: #fff; font-size: 15px; font-weight: 600; border-radius: 14px; backdrop-filter: blur(8px);
        cursor: pointer;
    }
    .takeover .bottom button.primary { background: #fff; color: #065f46; border-color: #fff; }
    .takeover.rejected .bottom button.primary { color: #7f1d1d; }
    .takeover.duplicate .bottom button.primary { color: #78350f; }
    .takeover .bottom button:hover { background: rgba(255,255,255,.28); }
    .takeover .bottom button.primary:hover { filter: brightness(.96); }

    @keyframes flash { from { opacity: 0; transform: scale(.95); } to { opacity: 1; transform: none; } }
</style>

<div class="scanner-wrap">
    <!-- Viewport with camera/reticle -->
    <div class="scanner-viewport">
        <div class="camera-feed">
            <div class="fake-hall"></div>
        </div>
        <div class="reticle">
            <div class="dim-overlay"></div>
            <div class="corners"><span></span><span></span><span></span><span></span></div>
            <div class="scan-line"></div>
        </div>
    </div>

    <!-- Top bar: examiner info + logout -->
    <div class="scanner-top">
        <div class="ex-info">
            <div class="ex-avatar">{{ strtoupper(substr($examiner['full_name'] ?? 'EX', 0, 2)) }}</div>
            <div><b>{{ $examiner['full_name'] ?? 'Examiner' }}</b><span>{{ '@' . ($examiner['username'] ?? 'examiner') }} · {{ ucfirst($examiner['role'] ?? 'examiner') }}</span></div>
        </div>
        <a href="/examiner/logout" class="iconbtn" aria-label="Logout">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h6a2 2 0 012 2v2"/>
            </svg>
        </a>
    </div>

    <!-- Scan prompt -->
    <div class="scanner-prompt" id="scan-prompt">Point the camera at the student's <b>CERNIX QR</b></div>

    <!-- Bottom panel: stats + last scan + actions -->
    <div class="scanner-bottom">
        <div class="scanner-stats">
            <div class="stat-tile"><b id="total-scans">0</b><span>Scans</span></div>
            <div class="stat-tile approved"><b id="approved-count">0</b><span>Approved</span></div>
            <div class="stat-tile rejected"><b id="rejected-count">0</b><span>Rejected</span></div>
            <div class="stat-tile duplicate"><b id="duplicate-count">0</b><span>Duplicate</span></div>
        </div>

        <div class="last-scan" id="last-scan">
            <span class="dot"></span>
            <div class="info"><b>No scans yet</b><span>Awaiting first QR</span></div>
            <span class="time">--:--</span>
        </div>

        <div class="scan-actions">
            <button onclick="simulateScan('APPROVED')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                Demo Approve
            </button>
            <button onclick="simulateScan('REJECTED')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                Reject
            </button>
            <button onclick="simulateScan('DUPLICATE')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Duplicate
            </button>
        </div>
    </div>

    <!-- APPROVED Takeover -->
    <div class="takeover approved" id="takeover-approved">
        <div class="top">
            <span class="status-label">DECISION · APPROVED</span>
            <span class="time" id="approved-time">09:14:44</span>
        </div>
        <div class="center">
            <div class="big-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></div>
            <h1>VERIFIED</h1>
            <p>Access granted. Allow entry.</p>
        </div>
        <div>
            <div class="student-card">
                <div class="avatar" id="approved-avatar">AE</div>
                <div style="flex:1">
                    <p class="nm" id="approved-name">Adaeze Ekwueme</p>
                    <p class="mt" id="approved-matric">CSC/2021/002</p>
                    <p class="dept" id="approved-dept">Computer Science</p>
                </div>
            </div>
            <div class="meta-row">
                <div class="meta-cell"><div class="k">Token</div><div class="v" id="approved-token">a7f2…6b</div></div>
                <div class="meta-cell"><div class="k">Session</div><div class="v">#1</div></div>
            </div>
        </div>
        <div class="bottom">
            <button onclick="resetScan()">Next Scan</button>
            <button class="primary" onclick="resetScan()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                Admit Student
            </button>
        </div>
    </div>

    <!-- REJECTED Takeover -->
    <div class="takeover rejected" id="takeover-rejected">
        <div class="top">
            <span class="status-label">DECISION · REJECTED</span>
            <span class="time" id="rejected-time">09:40:18</span>
        </div>
        <div class="center">
            <div class="big-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg></div>
            <h1>REJECTED</h1>
            <p>Do not admit. Escalate to supervisor.</p>
        </div>
        <div>
            <div style="margin:20px;position:relative;z-index:1">
                <div class="student-card" style="margin:0;flex-direction:column">
                    <p style="font-size:12px;opacity:.8;margin:0 0 8px">Rejection Reason</p>
                    <div style="font-size:13px;line-height:1.5"><b>HMAC signature mismatch</b><br><span style="opacity:.75;font-size:12px">Token was tampered with or forged</span></div>
                </div>
            </div>
            <div class="meta-row">
                <div class="meta-cell"><div class="k">Scan #</div><div class="v" id="rejected-scan">1</div></div>
                <div class="meta-cell"><div class="k">Logged</div><div class="v">YES</div></div>
            </div>
        </div>
        <div class="bottom">
            <button onclick="resetScan()">Dismiss</button>
            <button class="primary" onclick="resetScan()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 5l3 3m3-3l-3 3m3-3H4"/></svg>
                Alert Supervisor
            </button>
        </div>
    </div>

    <!-- DUPLICATE Takeover -->
    <div class="takeover duplicate" id="takeover-duplicate">
        <div class="top">
            <span class="status-label">DECISION · ALREADY USED</span>
            <span class="time" id="duplicate-time">09:31:12</span>
        </div>
        <div class="center">
            <div class="big-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
            <h1 style="margin-bottom:0">ALREADY<br>USED</h1>
            <p>Token was redeemed earlier. Entry denied.</p>
        </div>
        <div>
            <div class="student-card">
                <div class="avatar" id="dup-avatar">AO</div>
                <div style="flex:1">
                    <p class="nm" id="dup-name">Adebayo Oluwaseun</p>
                    <p class="mt" id="dup-matric">CSC/2021/001</p>
                    <p class="dept" style="color:rgba(255,255,255,.9);margin-top:6px">First redeemed: <b>08:54:21</b></p>
                </div>
            </div>
            <div class="meta-row">
                <div class="meta-cell"><div class="k">Original Hall</div><div class="v">Hall B</div></div>
                <div class="meta-cell"><div class="k">Original Examiner</div><div class="v">examiner3</div></div>
            </div>
        </div>
        <div class="bottom">
            <button onclick="resetScan()">Dismiss</button>
            <button class="primary" onclick="resetScan()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                View Audit Trail
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let stats = { total: 0, approved: 0, rejected: 0, duplicate: 0 };
const students = [
    { name: 'Adebayo Oluwaseun Emmanuel', matric: 'CSC/2021/001', dept: 'Computer Science', initials: 'AO' },
    { name: 'Adaeze Ekwueme', matric: 'CSC/2021/002', dept: 'Computer Science', initials: 'AE' },
    { name: 'Tunde Balogun', matric: 'CSC/2021/003', dept: 'Computer Science', initials: 'TB' },
];

function updateStats() {
    document.getElementById('total-scans').textContent = stats.total;
    document.getElementById('approved-count').textContent = stats.approved;
    document.getElementById('rejected-count').textContent = stats.rejected;
    document.getElementById('duplicate-count').textContent = stats.duplicate;
}

function showTakeover(type) {
    ['approved', 'rejected', 'duplicate'].forEach(t => {
        document.getElementById('takeover-' + t).classList.remove('show');
    });
    if (type) document.getElementById('takeover-' + type).classList.add('show');
}

function resetScan() {
    showTakeover(null);
    document.getElementById('scan-prompt').textContent = 'Point the camera at the student\'s CERNIX QR';
}

function simulateScan(decision) {
    stats.total++;
    const now = new Date().toLocaleTimeString();
    const student = students[Math.floor(Math.random() * students.length)];

    if (decision === 'APPROVED') {
        stats.approved++;
        document.getElementById('approved-name').textContent = student.name;
        document.getElementById('approved-matric').textContent = student.matric;
        document.getElementById('approved-dept').textContent = student.dept;
        document.getElementById('approved-avatar').textContent = student.initials;
        document.getElementById('approved-time').textContent = now;
        document.getElementById('last-scan').className = 'last-scan approved';
        document.getElementById('last-scan').innerHTML =
            '<span class="dot"></span>' +
            '<div class="info"><b>' + student.name.split(' ').slice(0, 2).join(' ') + '</b><span>' + student.matric + ' · APPROVED</span></div>' +
            '<span class="time">' + now.split(' ')[0] + '</span>';
        showTakeover('approved');
    } else if (decision === 'REJECTED') {
        stats.rejected++;
        document.getElementById('rejected-time').textContent = now;
        document.getElementById('rejected-scan').textContent = stats.total;
        document.getElementById('last-scan').className = 'last-scan rejected';
        document.getElementById('last-scan').innerHTML =
            '<span class="dot"></span>' +
            '<div class="info"><b>QR Tampered</b><span>Token ID · REJECTED</span></div>' +
            '<span class="time">' + now.split(' ')[0] + '</span>';
        showTakeover('rejected');
    } else {
        stats.duplicate++;
        document.getElementById('dup-name').textContent = student.name;
        document.getElementById('dup-matric').textContent = student.matric;
        document.getElementById('dup-avatar').textContent = student.initials;
        document.getElementById('duplicate-time').textContent = now;
        document.getElementById('last-scan').className = 'last-scan duplicate';
        document.getElementById('last-scan').innerHTML =
            '<span class="dot"></span>' +
            '<div class="info"><b>' + student.name.split(' ')[0] + '</b><span>' + student.matric + ' · DUPLICATE</span></div>' +
            '<span class="time">' + now.split(' ')[0] + '</span>';
        showTakeover('duplicate');
    }
    updateStats();
}
</script>
@endpush
