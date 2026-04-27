@extends('layouts.portal')

@section('title', 'Scanner Dashboard')

@section('content')
<style>
    /* ── Layout ────────────────────────────────────────────────────── */
    .ex-page {
        height: 100dvh;
        height: 100vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: #111;
        color: #f5f5f5;
    }

    /* ── Topbar ────────────────────────────────────────────────────── */
    .ex-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 13px 20px;
        background: #1a1a1a;
        border-bottom: 1px solid rgba(255,255,255,.06);
        flex-shrink: 0;
    }
    .ex-brand {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .ex-brand-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(255,255,255,.08);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ex-brand b {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .ex-user {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .ex-user-info {
        text-align: right;
        display: none;
    }
    .ex-user-info b { display: block; font-size: 12px; font-weight: 600; }
    .ex-user-info span { font-size: 10px; color: rgba(255,255,255,.45); }
    .ex-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255,255,255,.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
    }
    .ex-logout {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.07);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.6);
        transition: all .15s;
        text-decoration: none;
    }
    .ex-logout:hover { background: rgba(255,255,255,.1); color: rgba(255,255,255,.8); }

    /* ── Stats bar ──────────────────────────────────────────────────── */
    .ex-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: rgba(255,255,255,.03);
        border-bottom: 1px solid rgba(255,255,255,.04);
        flex-shrink: 0;
    }
    .stat-cell {
        padding: 12px 16px;
        background: rgba(255,255,255,.02);
        text-align: center;
        transition: background .2s;
    }
    .stat-cell b {
        display: block;
        font-size: 18px;
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
        line-height: 1;
    }
    .stat-cell span {
        font-size: 9px;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: rgba(255,255,255,.35);
        display: block;
        margin-top: 4px;
    }
    .stat-cell.approved b { color: #16a34a; }
    .stat-cell.rejected b { color: #dc2626; }
    .stat-cell.duplicate b { color: #d97706; }

    /* ── Workspace ──────────────────────────────────────────────────── */
    .ex-workspace {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Camera panel */
    .ex-camera-panel {
        flex: 1;
        min-height: 0;
        position: relative;
        background: #0a0a0a;
        overflow: hidden;
    }
    .camera-feed {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 40%, rgba(255,255,255,.04), transparent 60%), #0a0a0a;
    }
    .camera-feed::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: repeating-linear-gradient(0deg, rgba(255,255,255,.008) 0, rgba(255,255,255,.008) 1px, transparent 1px, transparent 3px);
    }
    .fake-hall {
        position: absolute;
        inset: 10% 15%;
        opacity: .1;
        background: repeating-linear-gradient(45deg, rgba(255,255,255,.02) 0 10px, transparent 10px 20px);
        border-radius: 8px;
    }

    .reticle {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 220px;
        height: 220px;
        pointer-events: none;
    }
    .reticle .corners span {
        position: absolute;
        width: 24px;
        height: 24px;
        border: 2px solid rgba(255,255,255,.7);
        border-radius: 5px;
    }
    .reticle .corners span:nth-child(1) { top: 0; left: 0; border-right: none; border-bottom: none; }
    .reticle .corners span:nth-child(2) { top: 0; right: 0; border-left: none; border-bottom: none; }
    .reticle .corners span:nth-child(3) { bottom: 0; left: 0; border-right: none; border-top: none; }
    .reticle .corners span:nth-child(4) { bottom: 0; right: 0; border-left: none; border-top: none; }

    .reticle .scan-line {
        position: absolute;
        left: 15%;
        right: 15%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.6), transparent);
        animation: scanline 1.8s ease-in-out infinite alternate;
    }
    @keyframes scanline { from { top: 20%; } to { top: 80%; } }

    .reticle .dim-overlay {
        position: absolute;
        inset: -200vh;
        box-shadow: 0 0 0 200vh rgba(0,0,0,.5);
        border-radius: 12px;
    }

    .scan-prompt {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 22px;
        text-align: center;
        z-index: 10;
        font-size: 12px;
        color: rgba(255,255,255,.65);
        letter-spacing: .05em;
    }
    .scan-prompt b { color: rgba(255,255,255,.95); font-weight: 600; }

    /* Verifying overlay */
    .verifying-overlay {
        position: absolute;
        inset: 0;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(10, 10, 10, 0.95);
        backdrop-filter: blur(6px);
        z-index: 80;
        gap: 12px;
    }
    .verifying-overlay.show { display: flex; }
    .verifying-spinner {
        width: 48px;
        height: 48px;
        border: 2px solid rgba(255,255,255,.1);
        border-top-color: rgba(255,255,255,.7);
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .verifying-label { font-size: 13px; color: rgba(255,255,255,.75); }

    /* Mobile bottom */
    .ex-mobile-bottom {
        flex-shrink: 0;
        background: #1a1a1a;
        padding: 14px 16px;
        border-top: 1px solid rgba(255,255,255,.05);
    }
    .last-scan {
        padding: 10px 12px;
        border-radius: 10px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.07);
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 10px;
        font-size: 12px;
    }
    .last-scan .dot { width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,.25); }
    .last-scan.approved { background: rgba(22,163,74,.1); border-color: rgba(22,163,74,.25); }
    .last-scan.approved .dot { background: #16a34a; }
    .last-scan.rejected { background: rgba(220,38,38,.1); border-color: rgba(220,38,38,.25); }
    .last-scan.rejected .dot { background: #dc2626; }
    .last-scan.duplicate { background: rgba(217,119,6,.1); border-color: rgba(217,119,6,.25); }
    .last-scan.duplicate .dot { background: #d97706; }
    .last-scan .info { flex: 1; min-width: 0; }
    .last-scan .info b { font-weight: 600; }
    .last-scan .time { font-size: 10px; color: rgba(255,255,255,.3); font-family: 'JetBrains Mono', monospace; }

    .scan-actions {
        display: flex;
        gap: 6px;
    }
    .scan-actions button {
        flex: 1;
        padding: 8px 6px;
        border-radius: 9px;
        background: rgba(255,255,255,.07);
        color: rgba(255,255,255,.8);
        font-size: 11px;
        font-weight: 500;
        border: 1px solid rgba(255,255,255,.08);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        transition: all .15s;
    }
    .scan-actions button:hover { background: rgba(255,255,255,.12); }
    .scan-actions svg { width: 12px; height: 12px; }

    /* Takeovers */
    .takeover {
        position: absolute;
        inset: 0;
        display: none;
        flex-direction: column;
        justify-content: space-between;
        color: #fff;
        z-index: 100;
        overflow: hidden;
    }
    .takeover.approved { background: linear-gradient(180deg, #047857 0%, #065f46 100%); }
    .takeover.rejected { background: linear-gradient(180deg, #b91c1c 0%, #7f1d1d 100%); }
    .takeover.duplicate { background: linear-gradient(180deg, #b45309 0%, #78350f 100%); }
    .takeover.show { display: flex; animation: flash .35s ease-out; }
    @keyframes flash { from { opacity: 0; transform: scale(.96); } }

    .takeover::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 0%, rgba(255,255,255,.08), transparent 50%);
    }
    .to-top {
        padding: 52px 20px 10px;
        display: flex;
        justify-content: space-between;
        position: relative;
        z-index: 1;
    }
    .to-top .status { font-size: 9px; font-weight: 700; opacity: .65; }
    .to-top .time { font-size: 10px; opacity: .65; font-family: monospace; }
    .to-center {
        text-align: center;
        padding: 0 20px;
        position: relative;
        z-index: 1;
    }
    .big-icon {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255,255,255,.1);
        border: 3px solid rgba(255,255,255,.25);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }
    .big-icon svg { width: 56px; height: 56px; stroke: #fff; stroke-width: 2.5; fill: none; }
    .to-center h1 { font-size: 44px; font-weight: 800; margin: 0; line-height: .9; }
    .to-center p { font-size: 14px; margin: 8px 0 0; opacity: .75; }
    .student-card {
        margin: 18px 20px 0;
        padding: 14px;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 14px;
        display: flex;
        gap: 11px;
        position: relative;
        z-index: 1;
    }
    .sc-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(255,255,255,.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
        flex-shrink: 0;
    }
    .student-card .nm { font-size: 14px; font-weight: 600; margin: 0; }
    .student-card .mt { font-size: 11px; opacity: .6; margin: 2px 0 0; font-family: monospace; }
    .meta-row {
        margin: 10px 20px 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
        position: relative;
        z-index: 1;
    }
    .meta-cell { padding: 8px 10px; background: rgba(255,255,255,.08); border-radius: 8px; font-size: 10px; }
    .meta-cell .k { opacity: .5; }
    .meta-cell .v { font-weight: 600; margin-top: 2px; font-family: monospace; font-size: 11px; }

    .to-bottom {
        padding: 14px 20px;
        display: flex;
        gap: 8px;
        position: relative;
        z-index: 1;
    }
    .to-bottom button {
        flex: 1;
        padding: 12px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.18);
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        border-radius: 10px;
        cursor: pointer;
    }
    .to-bottom button.primary {
        background: #fff;
        color: #065f46;
    }
    .takeover.rejected .to-bottom button.primary { color: #7f1d1d; }
    .takeover.duplicate .to-bottom button.primary { color: #78350f; }

    /* Desktop layout */
    @media (min-width: 768px) {
        .ex-workspace { flex-direction: row; }
        .ex-mobile-bottom { display: none; }
        .ex-user-info { display: block; }

        .ex-result-panel {
            width: 380px;
            flex-shrink: 0;
            background: #161616;
            border-left: 1px solid rgba(255,255,255,.05);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .res-idle {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 24px;
            color: rgba(255,255,255,.4);
        }
        .idle-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }
        .res-idle b { font-size: 14px; font-weight: 600; color: rgba(255,255,255,.6); margin-bottom: 6px; }
        .res-idle p { font-size: 11px; margin: 0; line-height: 1.5; }

        .res-scanning {
            flex: 1;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 40px 24px;
        }
        .res-scanning.show { display: flex; }
        .res-spinner {
            width: 50px;
            height: 50px;
            border: 2px solid rgba(255,255,255,.1);
            border-top-color: rgba(255,255,255,.6);
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        .res-scanning b { font-size: 13px; color: rgba(255,255,255,.7); }

        .res-result {
            flex: 1;
            display: none;
            flex-direction: column;
            overflow-y: auto;
        }
        .res-result.show { display: flex; }

        .res-status-bar {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,.05);
            flex-shrink: 0;
        }
        .res-status { font-size: 9px; opacity: .5; letter-spacing: .08em; margin-bottom: 8px; }
        .res-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
        }
        .res-badge.approved { background: rgba(22,163,74,.15); color: #16a34a; }
        .res-badge.rejected { background: rgba(220,38,38,.15); color: #dc2626; }
        .res-badge.duplicate { background: rgba(217,119,6,.15); color: #d97706; }
        .res-time { font-size: 10px; opacity: .35; margin-top: 7px; font-family: monospace; }

        .res-student {
            padding: 18px 20px;
            flex: 1;
        }
        .res-card {
            display: flex;
            gap: 12px;
            padding: 14px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 12px;
            margin-bottom: 14px;
        }
        .res-av {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
        }
        .res-card .nm { font-size: 14px; font-weight: 600; margin: 0; }
        .res-card .mt { font-size: 11px; opacity: .45; margin: 2px 0 0; font-family: monospace; }

        .res-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }
        .res-mc { padding: 9px 11px; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.05); border-radius: 8px; }
        .res-mc .k { font-size: 8px; opacity: .35; }
        .res-mc .v { font-size: 12px; font-weight: 600; margin-top: 3px; font-family: monospace; }

        .res-actions {
            display: flex;
            gap: 6px;
            padding: 0 20px 18px;
        }
        .res-actions button {
            flex: 1;
            padding: 10px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all .15s;
        }
        .res-actions .btn-ghost { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); color: rgba(255,255,255,.75); }
        .res-actions .btn-ghost:hover { background: rgba(255,255,255,.1); }
        .res-actions .btn-approve { background: #16a34a; border: 1px solid #16a34a; color: #fff; }
        .res-actions .btn-reject { background: #dc2626; border: 1px solid #dc2626; color: #fff; }
        .res-actions button:hover { transform: translateY(-1px); }

        .ex-panel-actions {
            display: flex;
            gap: 6px;
            padding: 14px 20px;
            border-top: 1px solid rgba(255,255,255,.05);
            margin-top: auto;
            flex-shrink: 0;
        }
        .ex-panel-actions button {
            flex: 1;
            padding: 8px 6px;
            border-radius: 8px;
            background: rgba(255,255,255,.06);
            color: rgba(255,255,255,.75);
            font-size: 11px;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,.07);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: all .15s;
        }
        .ex-panel-actions button:hover { background: rgba(255,255,255,.11); }
    }

    @media (max-width: 767px) {
        .ex-result-panel { display: none; }
    }
</style>

<div class="ex-page">
    <div class="ex-topbar">
        <div class="ex-brand">
            <div class="ex-brand-icon">
                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <b>Scanner</b>
        </div>
        <div class="ex-user">
            <div class="ex-user-info">
                <b>{{ $examiner['full_name'] ?? 'Examiner' }}</b>
                <span>{{ strtolower($examiner['role'] ?? 'examiner') }}</span>
            </div>
            <div class="ex-avatar">{{ strtoupper(substr($examiner['full_name'] ?? 'E', 0, 1)) }}</div>
            <a href="/examiner/logout" class="ex-logout">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h6a2 2 0 012 2v2"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="ex-stats">
        <div class="stat-cell"><b id="total-scans">0</b><span>Scans</span></div>
        <div class="stat-cell approved"><b id="approved-count">0</b><span>Approved</span></div>
        <div class="stat-cell rejected"><b id="rejected-count">0</b><span>Rejected</span></div>
        <div class="stat-cell duplicate"><b id="duplicate-count">0</b><span>Duplicates</span></div>
    </div>

    <div class="ex-workspace">
        <div class="ex-camera-panel">
            <div class="camera-feed">
                <video id="camera-video" autoplay playsinline muted style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:none;"></video>
                <div class="fake-hall" id="fake-hall"></div>
            </div>
            <canvas id="scan-canvas" style="display:none;position:absolute;"></canvas>
            <div class="reticle">
                <div class="dim-overlay"></div>
                <div class="corners"><span></span><span></span><span></span><span></span></div>
                <div class="scan-line"></div>
            </div>
            <div class="scan-prompt" id="scan-prompt">Point at <b>QR code</b></div>

            <div class="verifying-overlay" id="verifying-overlay">
                <div class="verifying-spinner"></div>
                <span class="verifying-label">Verifying…</span>
            </div>

            <div class="takeover approved" id="takeover-approved">
                <div class="to-top"><span class="status">APPROVED</span><span class="time" id="approved-time">--:--</span></div>
                <div class="to-center"><div class="big-icon"><svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></div><h1>VERIFIED</h1><p>Access granted</p></div>
                <div><div class="student-card"><div class="sc-avatar" id="approved-avatar">A</div><div style="flex:1"><p class="nm" id="approved-name">Student</p><p class="mt" id="approved-matric">—</p></div></div><div class="meta-row"><div class="meta-cell"><div class="k">Token</div><div class="v" id="approved-token">…</div></div><div class="meta-cell"><div class="k">ID</div><div class="v" id="approved-dept">—</div></div></div></div>
                <div class="to-bottom"><button onclick="resetScan()">Next</button><button class="primary" onclick="resetScan()">Admit</button></div>
            </div>

            <div class="takeover rejected" id="takeover-rejected">
                <div class="to-top"><span class="status">REJECTED</span><span class="time" id="rejected-time">--:--</span></div>
                <div class="to-center"><div class="big-icon"><svg viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg></div><h1>INVALID</h1><p>Access denied</p></div>
                <div><div style="padding:14px"><div style="font-size:12px;color:rgba(255,255,255,.8)"><b>Bad token</b><span style="opacity:.6;font-size:11px;display:block;margin-top:3px">Check signature and try again</span></div></div><div class="meta-row"><div class="meta-cell"><div class="k">Count</div><div class="v" id="rejected-scan">1</div></div><div class="meta-cell"><div class="k">Action</div><div class="v">Logged</div></div></div></div>
                <div class="to-bottom"><button onclick="resetScan()">Dismiss</button><button class="primary" onclick="resetScan()">Alert</button></div>
            </div>

            <div class="takeover duplicate" id="takeover-duplicate">
                <div class="to-top"><span class="status">DUPLICATE</span><span class="time" id="duplicate-time">--:--</span></div>
                <div class="to-center"><div class="big-icon"><svg viewBox="0 0 24 24"><path d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><h1>USED</h1><p>Already redeemed</p></div>
                <div><div class="student-card"><div class="sc-avatar" id="dup-avatar">D</div><div style="flex:1"><p class="nm" id="dup-name">Student</p><p class="mt" id="dup-matric">—</p><p style="font-size:10px;opacity:.5;margin-top:4px">Previously scanned</p></div></div><div class="meta-row"><div class="meta-cell"><div class="k">Time</div><div class="v" id="duplicate-time">--:--</div></div><div class="meta-cell"><div class="k">Hall</div><div class="v">—</div></div></div></div>
                <div class="to-bottom"><button onclick="resetScan()">Dismiss</button><button class="primary" onclick="resetScan()">Review</button></div>
            </div>
        </div>

        <div class="ex-mobile-bottom">
            <div class="last-scan" id="last-scan">
                <span class="dot"></span>
                <div class="info"><b>Waiting</b><span>Scan a QR code</span></div>
                <span class="time">—</span>
            </div>
            <div class="scan-actions">
                <button onclick="simulateScan('APPROVED')"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>Test</button>
                <button onclick="simulateScan('REJECTED')"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>Reject</button>
                <button onclick="simulateScan('DUPLICATE')"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 2"/></svg>Dup</button>
            </div>
        </div>

        <div class="ex-result-panel">
            <div class="res-idle" id="res-idle">
                <div class="idle-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h7M14 17h7M14 20h7"/>
                    </svg>
                </div>
                <b>Ready to scan</b>
                <p>Point camera at student QR code</p>
            </div>

            <div class="res-scanning" id="res-scanning">
                <div class="res-spinner"></div>
                <b>Verifying…</b>
            </div>

            <div class="res-result" id="res-result">
                <div class="res-status-bar">
                    <div class="res-status">RESULT</div>
                    <div class="res-badge approved" id="res-badge"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg><span id="res-text">Verified</span></div>
                    <div class="res-time" id="res-time">—</div>
                </div>
                <div class="res-student" id="res-student">
                    <div class="res-card">
                        <div class="res-av" id="res-av">—</div>
                        <div style="flex:1">
                            <p class="nm" id="res-name">Student</p>
                            <p class="mt" id="res-matric">—</p>
                        </div>
                    </div>
                    <div class="res-meta">
                        <div class="res-mc"><div class="k">Token</div><div class="v" id="res-token">…</div></div>
                        <div class="res-mc"><div class="k">Hall</div><div class="v" id="res-dept">—</div></div>
                        <div class="res-mc"><div class="k">Session</div><div class="v">—</div></div>
                        <div class="res-mc"><div class="k">Logged</div><div class="v">Yes</div></div>
                    </div>
                </div>
                <div class="res-actions">
                    <button class="btn-ghost" onclick="resetScan()">Next</button>
                    <button class="btn-approve" id="res-action" onclick="resetScan()">Admit</button>
                </div>
            </div>

            <div class="ex-panel-actions">
                <button onclick="simulateScan('APPROVED')">Test</button>
                <button onclick="simulateScan('REJECTED')">Reject</button>
                <button onclick="simulateScan('DUPLICATE')">Dup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
let stats={total:0,approved:0,rejected:0,duplicate:0},scanning=false,busy=false;
const csrfToken=document.querySelector('meta[name="csrf-token"]').content;

function updateStats(){
    document.getElementById('total-scans').textContent=stats.total;
    document.getElementById('approved-count').textContent=stats.approved;
    document.getElementById('rejected-count').textContent=stats.rejected;
    document.getElementById('duplicate-count').textContent=stats.duplicate;
}

function showVerifying(){
    const overlay=document.getElementById('verifying-overlay');
    if(overlay)overlay.classList.add('show');
    const idle=document.getElementById('res-idle');
    const result=document.getElementById('res-result');
    const scanning=document.getElementById('res-scanning');
    if(idle)idle.style.display='none';
    if(result)result.classList.remove('show');
    if(scanning)scanning.classList.add('show');
}

function hideVerifying(){
    const overlay=document.getElementById('verifying-overlay');
    if(overlay)overlay.classList.remove('show');
    const scanning=document.getElementById('res-scanning');
    if(scanning)scanning.classList.remove('show');
}

function showResult(type){
    const idle=document.getElementById('res-idle');
    const result=document.getElementById('res-result');
    if(!idle||!result)return;
    if(!type){
        idle.style.display='';
        result.classList.remove('show');
        return;
    }
    idle.style.display='none';
    result.classList.add('show');
    const badge=document.getElementById('res-badge');
    const text=document.getElementById('res-text');
    badge.className='res-badge '+type;
    const labels={approved:'Verified',rejected:'Invalid',duplicate:'Used'};
    text.textContent=labels[type]||type;
    document.getElementById('res-action').className=type==='approved'?'btn-approve':'btn-reject';
    document.getElementById('res-action').textContent=type==='approved'?'Admit':type==='rejected'?'Alert':'Review';
}

function showTakeover(type){
    ['approved','rejected','duplicate'].forEach(t=>document.getElementById('takeover-'+t).classList.remove('show'));
    if(type)document.getElementById('takeover-'+type).classList.add('show');
    showResult(type);
}

function updateLastScan(cls,title,sub,time){
    const el=document.getElementById('last-scan');
    if(!el)return;
    el.className='last-scan '+cls;
    el.innerHTML='<span class="dot"></span><div class="info"><b>'+title+'</b><span>'+sub+'</span></div><span class="time">'+time+'</span>';
}

function resetScan(){
    showTakeover(null);
    busy=false;
    scanning=true;
    document.getElementById('scan-prompt').textContent='Point at QR code';
}

function handleResult(result,now){
    stats.total++;
    document.getElementById('res-time').textContent=now;
    if(result.status==='APPROVED'){
        stats.approved++;
        const s=result.student||{};
        const name=s.full_name||'Unknown';
        const matric=s.matric_no||'—';
        const initials=name.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
        ['approved','res'].forEach(p=>{
            document.getElementById(p+'-name').textContent=name;
            document.getElementById(p+'-matric').textContent=matric;
            document.getElementById(p+'-av').textContent=initials;
            document.getElementById(p+'-token').textContent=(result.token_id||'').slice(0,8)+'…';
            if(p==='res')document.getElementById('res-dept').textContent=s.department||'—';
        });
        document.getElementById('approved-time').textContent=now;
        updateLastScan('approved',name,matric,now);
        showTakeover('approved');
    }else if(result.status==='DUPLICATE'){
        stats.duplicate++;
        document.getElementById('duplicate-time').textContent=now;
        document.getElementById('dup-name').textContent=(result.student||{}).full_name||'Unknown';
        updateLastScan('duplicate','Reused','Token already scanned',now);
        showTakeover('duplicate');
    }else{
        stats.rejected++;
        document.getElementById('rejected-time').textContent=now;
        document.getElementById('rejected-scan').textContent=stats.total;
        updateLastScan('rejected','Invalid','Bad or tampered token',now);
        showTakeover('rejected');
    }
    updateStats();
}

async function handleQRCode(rawData){
    if(busy)return;
    busy=true;
    scanning=false;
    let qrData;
    try{qrData=JSON.parse(rawData);}catch{busy=false;scanning=true;return;}
    const now=new Date().toLocaleTimeString();
    document.getElementById('scan-prompt').textContent='Checking…';
    showVerifying();
    try{
        const resp=await fetch('/examiner/verify',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest'},credentials:'same-origin',body:JSON.stringify({qr_data:qrData})});
        hideVerifying();
        if(resp.status===401){window.location.href='/examiner/login';return;}
        const result=await resp.json();
        handleResult(result,now);
    }catch(err){
        hideVerifying();
        stats.total++;
        stats.rejected++;
        document.getElementById('rejected-time').textContent=now;
        document.getElementById('rejected-scan').textContent=stats.total;
        updateLastScan('rejected','Error','Network issue',now);
        showTakeover('rejected');
        updateStats();
    }
}

async function startCamera(){
    const video=document.getElementById('camera-video'),fakeHall=document.getElementById('fake-hall');
    if(!navigator.mediaDevices||!navigator.mediaDevices.getUserMedia)return;
    try{
        const stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'},width:{ideal:1280},height:{ideal:720}}});
        video.srcObject=stream;
        await video.play();
        video.style.display='';
        fakeHall.style.display='none';
        scanning=true;
        requestAnimationFrame(scanFrame);
    }catch(e){}
}

function scanFrame(){
    if(!scanning){requestAnimationFrame(scanFrame);return;}
    const video=document.getElementById('camera-video'),canvas=document.getElementById('scan-canvas');
    if(video.readyState===video.HAVE_ENOUGH_DATA&&typeof jsQR!=='undefined'){
        canvas.width=video.videoWidth;
        canvas.height=video.videoHeight;
        const ctx=canvas.getContext('2d');
        ctx.drawImage(video,0,0);
        const imageData=ctx.getImageData(0,0,canvas.width,canvas.height);
        const code=jsQR(imageData.data,imageData.width,imageData.height,{inversionAttempts:'dontInvert'});
        if(code&&code.data){handleQRCode(code.data);requestAnimationFrame(scanFrame);return;}
    }
    requestAnimationFrame(scanFrame);
}

const demoStudents=[{name:'Student A',matric:'CSC/2021/001'},{name:'Student B',matric:'CSC/2021/002'},{name:'Student C',matric:'CSC/2021/003'}];

function simulateScan(decision){
    if(busy)return;
    busy=true;
    scanning=false;
    const now=new Date().toLocaleTimeString();
    const student=demoStudents[Math.floor(Math.random()*demoStudents.length)];
    document.getElementById('scan-prompt').textContent='Checking…';
    showVerifying();
    setTimeout(()=>{
        hideVerifying();
        handleResult({status:decision,student:{full_name:student.name,matric_no:student.matric,department:'Department'},token_id:'token'+Date.now()},now);
    },800);
}

startCamera();
</script>
@endpush
