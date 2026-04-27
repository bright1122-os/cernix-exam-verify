@extends('layouts.portal')

@section('title', 'Scanner Dashboard')

@section('content')
<style>
    /* ── Root overrides for scanner page ───────────────────────── */
    /* No body/html overflow lock — .ex-page handles its own containment */

    /* ── Layout ─────────────────────────────────────────────────── */
    .ex-page {
        min-height: 100dvh;
        min-height: 100vh;
        height: 100dvh;
        height: 100vh;
        max-height: 100dvh;
        max-height: 100vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: var(--bg);
        color: var(--ink);
    }
    @media (max-width: 767px) {
        .ex-page {
            height: auto;
            min-height: 100svh;
            min-height: 100vh;
            max-height: none;
            overflow: visible;
        }
    }

    /* ── Topbar ──────────────────────────────────────────────────── */
    .ex-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        background: var(--bg-2);
        border-bottom: 1px solid var(--line);
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }
    .ex-brand {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .ex-brand-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: var(--navy);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ex-brand b {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: .01em;
        color: var(--ink);
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
    .ex-user-info b { display: block; font-size: 13px; font-weight: 600; color: var(--ink); }
    .ex-user-info span { font-size: 11px; color: var(--ink-3); }
    .ex-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--navy);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
        flex-shrink: 0;
    }
    .ex-logout {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: var(--bg);
        border: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--ink-3);
        transition: all .15s;
        text-decoration: none;
    }
    .ex-logout:hover { background: var(--line); color: var(--ink-2); }

    /* ── Stats bar ───────────────────────────────────────────────── */
    .ex-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0;
        background: var(--bg-2);
        border-bottom: 1px solid var(--line);
        flex-shrink: 0;
    }
    .stat-cell {
        padding: 10px 14px;
        text-align: center;
        border-right: 1px solid var(--line);
        position: relative;
    }
    .stat-cell:last-child { border-right: none; }
    .stat-cell b {
        display: block;
        font-size: 20px;
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
        line-height: 1;
        color: var(--ink);
    }
    .stat-cell span {
        font-size: 9px;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--ink-4);
        display: block;
        margin-top: 3px;
        font-weight: 500;
    }
    .stat-cell.approved b { color: var(--emerald); }
    .stat-cell.rejected b { color: var(--red); }
    .stat-cell.duplicate b { color: var(--amber); }

    /* ── Workspace ───────────────────────────────────────────────── */
    .ex-workspace {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    @media (max-width: 767px) {
        .ex-workspace {
            overflow: visible;
            flex: none;
        }
    }

    /* Camera panel */
    .ex-camera-panel {
        flex: 1;
        min-height: 0;
        position: relative;
        background: #1a1c22;
        overflow: hidden;
    }
    @media (max-width: 767px) {
        .ex-camera-panel {
            flex: none;
            height: 60vmax;
            min-height: 300px;
            max-height: 70vh;
        }
    }
    .camera-feed {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 40%, rgba(255,255,255,.04), transparent 60%), #1a1c22;
    }
    .camera-feed::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: repeating-linear-gradient(0deg, rgba(255,255,255,.006) 0, rgba(255,255,255,.006) 1px, transparent 1px, transparent 3px);
    }
    .fake-hall {
        position: absolute;
        inset: 10% 15%;
        opacity: .07;
        background: repeating-linear-gradient(45deg, rgba(255,255,255,.03) 0 10px, transparent 10px 20px);
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
        width: 26px;
        height: 26px;
        border: 2.5px solid rgba(255,255,255,.85);
        border-radius: 5px;
    }
    .reticle .corners span:nth-child(1) { top: 0; left: 0; border-right: none; border-bottom: none; }
    .reticle .corners span:nth-child(2) { top: 0; right: 0; border-left: none; border-bottom: none; }
    .reticle .corners span:nth-child(3) { bottom: 0; left: 0; border-right: none; border-top: none; }
    .reticle .corners span:nth-child(4) { bottom: 0; right: 0; border-left: none; border-top: none; }

    .reticle .scan-line {
        position: absolute;
        left: 12%;
        right: 12%;
        height: 1.5px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.7), transparent);
        animation: scanline 1.8s ease-in-out infinite alternate;
    }
    @keyframes scanline { from { top: 18%; } to { top: 82%; } }

    .reticle .dim-overlay {
        position: absolute;
        inset: -200vh;
        box-shadow: 0 0 0 200vh rgba(0,0,0,.55);
        border-radius: 14px;
    }

    .scan-prompt {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 24px;
        text-align: center;
        z-index: 10;
        font-size: 12px;
        color: rgba(255,255,255,.6);
        letter-spacing: .04em;
    }
    .scan-prompt b { color: rgba(255,255,255,.92); font-weight: 600; }

    /* Verifying overlay */
    .verifying-overlay {
        position: absolute;
        inset: 0;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(26, 28, 34, 0.93);
        backdrop-filter: blur(8px);
        z-index: 80;
        gap: 14px;
    }
    .verifying-overlay.show { display: flex; }
    .verifying-spinner {
        width: 44px;
        height: 44px;
        border: 2px solid rgba(255,255,255,.12);
        border-top-color: rgba(255,255,255,.75);
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .verifying-label { font-size: 13px; color: rgba(255,255,255,.7); font-weight: 500; }

    /* ── Mobile bottom ───────────────────────────────────────────── */
    .ex-mobile-bottom {
        flex-shrink: 0;
        background: var(--bg-2);
        padding: 14px 16px;
        border-top: 1px solid var(--line);
    }
    .last-scan {
        padding: 11px 13px;
        border-radius: 11px;
        background: var(--bg);
        border: 1px solid var(--line);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 12px;
        transition: background .2s, border-color .2s;
    }
    .last-scan .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--line-2); flex-shrink: 0; }
    .last-scan.approved { background: rgba(5,150,105,.06); border-color: rgba(5,150,105,.2); }
    .last-scan.approved .dot { background: var(--emerald); }
    .last-scan.rejected { background: rgba(220,38,38,.06); border-color: rgba(220,38,38,.2); }
    .last-scan.rejected .dot { background: var(--red); }
    .last-scan.duplicate { background: rgba(180,83,9,.06); border-color: rgba(180,83,9,.2); }
    .last-scan.duplicate .dot { background: var(--amber); }
    .last-scan .info { flex: 1; min-width: 0; }
    .last-scan .info b { font-weight: 600; color: var(--ink); font-size: 12px; }
    .last-scan .info span { font-size: 11px; color: var(--ink-3); display: block; margin-top: 1px; }
    .last-scan .time { font-size: 10px; color: var(--ink-4); font-family: 'JetBrains Mono', monospace; white-space: nowrap; }

    .scan-actions {
        display: flex;
        gap: 7px;
    }
    .scan-actions button {
        flex: 1;
        padding: 9px 6px;
        border-radius: 10px;
        background: var(--bg);
        color: var(--ink-2);
        font-size: 11px;
        font-weight: 500;
        border: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
        transition: all .15s;
    }
    .scan-actions button:hover { background: var(--line); color: var(--ink); }
    .scan-actions svg { width: 12px; height: 12px; }

    /* ── Takeovers (mobile fullscreen result) ────────────────────── */
    .takeover {
        position: absolute;
        inset: 0;
        display: none;
        flex-direction: column;
        justify-content: space-between;
        z-index: 100;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }
    .takeover.approved {
        background: linear-gradient(160deg, #d1fae5 0%, #a7f3d0 60%, #6ee7b7 100%);
        color: #064e3b;
    }
    .takeover.rejected {
        background: linear-gradient(160deg, #fee2e2 0%, #fecaca 60%, #fca5a5 100%);
        color: #7f1d1d;
    }
    .takeover.duplicate {
        background: linear-gradient(160deg, #fef3c7 0%, #fde68a 60%, #fcd34d 100%);
        color: #78350f;
    }
    .takeover.show { display: flex; animation: takeover-in .3s cubic-bezier(.2,.8,.3,1); }
    @keyframes takeover-in { from { opacity: 0; transform: scale(.97) translateY(6px); } }

    .to-top {
        padding: 48px 20px 10px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .to-top .status {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .14em;
        opacity: .6;
    }
    .to-top .t-time { font-size: 10px; opacity: .55; font-family: 'JetBrains Mono', monospace; }

    .to-center {
        text-align: center;
        padding: 0 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .big-icon {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        border: 2px solid currentColor;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        opacity: .85;
    }
    .big-icon svg { width: 48px; height: 48px; stroke: currentColor; stroke-width: 2.5; fill: none; }
    .to-center h1 { font-size: 38px; font-weight: 800; margin: 0 0 6px; line-height: 1; letter-spacing: -.01em; }
    .to-center p { font-size: 14px; margin: 0; opacity: .65; }

    /* Student card in takeover */
    .student-card {
        margin: 16px 20px 0;
        padding: 14px;
        background: rgba(255,255,255,.45);
        border: 1px solid rgba(255,255,255,.6);
        border-radius: 14px;
        display: flex;
        gap: 12px;
        align-items: center;
        backdrop-filter: blur(6px);
    }
    .sc-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(255,255,255,.5);
        border: 1.5px solid rgba(255,255,255,.7);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
        color: inherit;
    }
    .student-card .s-info { flex: 1; min-width: 0; }
    .student-card .nm { font-size: 14px; font-weight: 600; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .student-card .mt { font-size: 11px; opacity: .6; margin: 2px 0 0; font-family: 'JetBrains Mono', monospace; }

    .meta-row {
        margin: 8px 20px 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
    }
    .meta-cell {
        padding: 9px 11px;
        background: rgba(255,255,255,.4);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 10px;
        backdrop-filter: blur(4px);
        font-size: 10px;
    }
    .meta-cell .k { opacity: .55; font-weight: 500; letter-spacing: .03em; }
    .meta-cell .v { font-weight: 700; margin-top: 2px; font-family: 'JetBrains Mono', monospace; font-size: 11px; }

    .to-bottom {
        padding: 14px 20px 20px;
        display: flex;
        gap: 8px;
    }
    .to-bottom button {
        flex: 1;
        padding: 13px;
        background: rgba(255,255,255,.35);
        border: 1px solid rgba(255,255,255,.55);
        color: inherit;
        font-size: 13px;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        transition: background .15s;
        font-family: 'Inter', sans-serif;
    }
    .to-bottom button:hover { background: rgba(255,255,255,.55); }
    .to-bottom button.primary {
        background: rgba(255,255,255,.7);
        border-color: rgba(255,255,255,.85);
    }
    .to-bottom button.primary:hover { background: rgba(255,255,255,.9); }

    /* ── Desktop layout ──────────────────────────────────────────── */
    @media (min-width: 768px) {
        .ex-workspace { flex-direction: row; }
        .ex-mobile-bottom { display: none; }
        .ex-user-info { display: block; }

        .ex-result-panel {
            width: 360px;
            flex-shrink: 0;
            background: var(--bg-2);
            border-left: 1px solid var(--line);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        /* Idle state */
        .res-idle {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 28px;
            color: var(--ink-4);
        }
        .idle-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--bg);
            border: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            color: var(--ink-4);
        }
        .res-idle b { font-size: 14px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; display: block; }
        .res-idle p { font-size: 12px; margin: 0; line-height: 1.55; color: var(--ink-3); }

        /* Scanning state */
        .res-scanning {
            flex: 1;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 40px 28px;
        }
        .res-scanning.show { display: flex; }
        .res-spinner {
            width: 46px;
            height: 46px;
            border: 2px solid var(--line);
            border-top-color: var(--navy);
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        .res-scanning b { font-size: 13px; color: var(--ink-2); font-weight: 500; }

        /* Result state */
        .res-result {
            flex: 1;
            display: none;
            flex-direction: column;
            overflow-y: auto;
        }
        .res-result.show { display: flex; }

        /* Status header */
        .res-status-bar {
            padding: 16px 20px;
            border-bottom: 1px solid var(--line);
            flex-shrink: 0;
        }
        .res-status { font-size: 9px; color: var(--ink-4); letter-spacing: .1em; text-transform: uppercase; margin-bottom: 10px; font-weight: 600; }
        .res-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 13px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
        }
        .res-badge .badge-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
        .res-badge.approved { background: rgba(5,150,105,.1); color: var(--emerald); }
        .res-badge.rejected { background: rgba(220,38,38,.1); color: var(--red); }
        .res-badge.duplicate { background: rgba(180,83,9,.1); color: var(--amber); }
        .res-time { font-size: 10px; color: var(--ink-4); margin-top: 8px; font-family: 'JetBrains Mono', monospace; }

        /* Student info */
        .res-student {
            padding: 18px 20px;
            flex: 1;
        }
        .res-card {
            display: flex;
            gap: 12px;
            padding: 14px;
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 12px;
            margin-bottom: 14px;
            transition: border-color .15s;
        }
        .res-av {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--navy);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }
        .res-card .nm { font-size: 14px; font-weight: 600; margin: 0; color: var(--ink); }
        .res-card .mt { font-size: 11px; color: var(--ink-3); margin: 3px 0 0; font-family: 'JetBrains Mono', monospace; }

        /* Meta grid */
        .res-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }
        .res-mc {
            padding: 10px 12px;
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 10px;
        }
        .res-mc .k { font-size: 9px; color: var(--ink-4); font-weight: 600; letter-spacing: .07em; text-transform: uppercase; }
        .res-mc .v { font-size: 12px; font-weight: 600; margin-top: 4px; font-family: 'JetBrains Mono', monospace; color: var(--ink); }

        /* Action buttons */
        .res-actions {
            display: flex;
            gap: 7px;
            padding: 0 20px 18px;
        }
        .res-actions button {
            flex: 1;
            padding: 11px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all .15s;
            font-family: 'Inter', sans-serif;
        }
        .res-actions .btn-ghost {
            background: var(--bg);
            border: 1px solid var(--line);
            color: var(--ink-2);
        }
        .res-actions .btn-ghost:hover { border-color: var(--ink-4); background: var(--line); }
        .res-actions .btn-approve {
            background: var(--emerald);
            border: 1px solid var(--emerald);
            color: #fff;
        }
        .res-actions .btn-approve:hover { opacity: .9; transform: translateY(-1px); }
        .res-actions .btn-reject {
            background: var(--red);
            border: 1px solid var(--red);
            color: #fff;
        }
        .res-actions .btn-reject:hover { opacity: .9; transform: translateY(-1px); }

        /* Panel footer */
        .ex-panel-actions {
            display: flex;
            gap: 6px;
            padding: 13px 20px;
            border-top: 1px solid var(--line);
            flex-shrink: 0;
            margin-top: auto;
        }
        .ex-panel-actions button {
            flex: 1;
            padding: 8px 6px;
            border-radius: 9px;
            background: var(--bg);
            color: var(--ink-2);
            font-size: 11px;
            font-weight: 500;
            border: 1px solid var(--line);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: all .15s;
            font-family: 'Inter', sans-serif;
        }
        .ex-panel-actions button:hover { background: var(--line); color: var(--ink); }
    }

    @media (max-width: 767px) {
        .ex-result-panel { display: none; }
    }
</style>

<div class="ex-page">

    {{-- Topbar --}}
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
            <a href="/examiner/logout" class="ex-logout" title="Logout">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h6a2 2 0 012 2v2"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Stats bar --}}
    <div class="ex-stats">
        <div class="stat-cell"><b id="total-scans">0</b><span>Scans</span></div>
        <div class="stat-cell approved"><b id="approved-count">0</b><span>Approved</span></div>
        <div class="stat-cell rejected"><b id="rejected-count">0</b><span>Rejected</span></div>
        <div class="stat-cell duplicate"><b id="duplicate-count">0</b><span>Duplicates</span></div>
    </div>

    {{-- Main workspace --}}
    <div class="ex-workspace">

        {{-- Camera panel --}}
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

            {{-- Verifying overlay --}}
            <div class="verifying-overlay" id="verifying-overlay">
                <div class="verifying-spinner"></div>
                <span class="verifying-label">Verifying…</span>
            </div>

            {{-- APPROVED takeover --}}
            <div class="takeover approved" id="takeover-approved">
                <div class="to-top">
                    <span class="status">APPROVED</span>
                    <span class="t-time" id="approved-time">--:--</span>
                </div>
                <div class="to-center">
                    <div class="big-icon">
                        <svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h1>VERIFIED</h1>
                    <p>Access granted</p>
                </div>
                <div>
                    <div class="student-card">
                        <div class="sc-avatar" id="approved-avatar">A</div>
                        <div class="s-info">
                            <p class="nm" id="approved-name">Student Name</p>
                            <p class="mt" id="approved-matric">—</p>
                        </div>
                    </div>
                    <div class="meta-row">
                        <div class="meta-cell">
                            <div class="k">Department</div>
                            <div class="v" id="approved-dept">—</div>
                        </div>
                        <div class="meta-cell">
                            <div class="k">Token</div>
                            <div class="v" id="approved-token">…</div>
                        </div>
                    </div>
                </div>
                <div class="to-bottom">
                    <button onclick="resetScan()">Next</button>
                    <button class="primary" onclick="resetScan()">Admit</button>
                </div>
            </div>

            {{-- REJECTED takeover --}}
            <div class="takeover rejected" id="takeover-rejected">
                <div class="to-top">
                    <span class="status">REJECTED</span>
                    <span class="t-time" id="rejected-time">--:--</span>
                </div>
                <div class="to-center">
                    <div class="big-icon">
                        <svg viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <h1>INVALID</h1>
                    <p>Access denied — bad or tampered token</p>
                </div>
                <div>
                    <div class="meta-row">
                        <div class="meta-cell">
                            <div class="k">Total scans</div>
                            <div class="v" id="rejected-scan">1</div>
                        </div>
                        <div class="meta-cell">
                            <div class="k">Action</div>
                            <div class="v">Logged</div>
                        </div>
                    </div>
                </div>
                <div class="to-bottom">
                    <button onclick="resetScan()">Dismiss</button>
                    <button class="primary" onclick="resetScan()">Alert</button>
                </div>
            </div>

            {{-- DUPLICATE takeover --}}
            <div class="takeover duplicate" id="takeover-duplicate">
                <div class="to-top">
                    <span class="status">DUPLICATE</span>
                    <span class="t-time" id="duplicate-time">--:--</span>
                </div>
                <div class="to-center">
                    <div class="big-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h1>USED</h1>
                    <p>Token already redeemed</p>
                </div>
                <div>
                    <div class="student-card">
                        <div class="sc-avatar" id="dup-avatar">D</div>
                        <div class="s-info">
                            <p class="nm" id="dup-name">Student Name</p>
                            <p class="mt" id="dup-matric">—</p>
                        </div>
                    </div>
                    <div class="meta-row">
                        <div class="meta-cell">
                            <div class="k">Department</div>
                            <div class="v" id="dup-dept">—</div>
                        </div>
                        <div class="meta-cell">
                            <div class="k">Scan count</div>
                            <div class="v" id="dup-count">2</div>
                        </div>
                    </div>
                </div>
                <div class="to-bottom">
                    <button onclick="resetScan()">Dismiss</button>
                    <button class="primary" onclick="resetScan()">Review</button>
                </div>
            </div>
        </div>

        {{-- Mobile bottom bar --}}
        <div class="ex-mobile-bottom">
            <div class="last-scan" id="last-scan">
                <span class="dot"></span>
                <div class="info"><b>Waiting</b><span>Scan a QR code to begin</span></div>
                <span class="time">—</span>
            </div>
            <div class="scan-actions">
                <button onclick="simulateScan('APPROVED')">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    Test
                </button>
                <button onclick="simulateScan('REJECTED')">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    Reject
                </button>
                <button onclick="simulateScan('DUPLICATE')">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 2"/></svg>
                    Dup
                </button>
            </div>
        </div>

        {{-- Desktop result panel --}}
        <div class="ex-result-panel">

            {{-- Idle --}}
            <div class="res-idle" id="res-idle">
                <div class="idle-icon">
                    <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h7M14 17h7M14 20h7"/>
                    </svg>
                </div>
                <b>Ready to scan</b>
                <p>Point the camera at a student's QR code to verify attendance</p>
            </div>

            {{-- Scanning --}}
            <div class="res-scanning" id="res-scanning">
                <div class="res-spinner"></div>
                <b>Verifying…</b>
            </div>

            {{-- Result --}}
            <div class="res-result" id="res-result">
                <div class="res-status-bar">
                    <div class="res-status">Result</div>
                    <div class="res-badge approved" id="res-badge">
                        <span class="badge-dot"></span>
                        <span id="res-text">Verified</span>
                    </div>
                    <div class="res-time" id="res-time">—</div>
                </div>
                <div class="res-student" id="res-student">
                    <div class="res-card">
                        <div class="res-av" id="res-av">—</div>
                        <div style="flex:1;min-width:0">
                            <p class="nm" id="res-name">Student</p>
                            <p class="mt" id="res-matric">—</p>
                        </div>
                    </div>
                    <div class="res-meta">
                        <div class="res-mc">
                            <div class="k">Department</div>
                            <div class="v" id="res-dept">—</div>
                        </div>
                        <div class="res-mc">
                            <div class="k">Token</div>
                            <div class="v" id="res-token">…</div>
                        </div>
                        <div class="res-mc">
                            <div class="k">Status</div>
                            <div class="v" id="res-status-val">—</div>
                        </div>
                        <div class="res-mc">
                            <div class="k">Logged</div>
                            <div class="v">Yes</div>
                        </div>
                    </div>
                </div>
                <div class="res-actions">
                    <button class="btn-ghost" onclick="resetScan()">Next scan</button>
                    <button class="btn-approve" id="res-action" onclick="resetScan()">Admit</button>
                </div>
            </div>

            <div class="ex-panel-actions">
                <button onclick="simulateScan('APPROVED')">Test OK</button>
                <button onclick="simulateScan('REJECTED')">Test Reject</button>
                <button onclick="simulateScan('DUPLICATE')">Test Dup</button>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
let stats = { total: 0, approved: 0, rejected: 0, duplicate: 0 };
let scanning = false, busy = false;
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function updateStats() {
    document.getElementById('total-scans').textContent    = stats.total;
    document.getElementById('approved-count').textContent = stats.approved;
    document.getElementById('rejected-count').textContent = stats.rejected;
    document.getElementById('duplicate-count').textContent = stats.duplicate;
}

function setPanelState(state) {
    const idle     = document.getElementById('res-idle');
    const scanning = document.getElementById('res-scanning');
    const result   = document.getElementById('res-result');
    if (!idle) return;
    idle.style.display     = state === 'idle'     ? '' : 'none';
    scanning.classList.toggle('show', state === 'scanning');
    result.classList.toggle('show',   state === 'result');
}

function showVerifying() {
    document.getElementById('verifying-overlay').classList.add('show');
    setPanelState('scanning');
}

function hideVerifying() {
    document.getElementById('verifying-overlay').classList.remove('show');
}

function showTakeover(type) {
    ['approved', 'rejected', 'duplicate'].forEach(t => {
        document.getElementById('takeover-' + t).classList.remove('show');
    });
    if (type) document.getElementById('takeover-' + type).classList.add('show');
    if (type) setPanelState('result'); else setPanelState('idle');
}

function updateDesktopResult(type, data) {
    const badge   = document.getElementById('res-badge');
    const resText = document.getElementById('res-text');
    const action  = document.getElementById('res-action');
    const labels  = { approved: 'Verified', rejected: 'Invalid', duplicate: 'Already Used' };

    badge.className = 'res-badge ' + type;
    resText.textContent = labels[type] || type;
    document.getElementById('res-time').textContent = data.time || '—';

    if (data.name) {
        document.getElementById('res-av').textContent      = data.initials || '?';
        document.getElementById('res-name').textContent    = data.name;
        document.getElementById('res-matric').textContent  = data.matric || '—';
        document.getElementById('res-dept').textContent    = data.dept || '—';
        document.getElementById('res-token').textContent   = data.token || '—';
        document.getElementById('res-status-val').textContent = labels[type] || type;
    }

    if (type === 'approved') {
        action.className = 'btn-approve';
        action.textContent = 'Admit';
    } else {
        action.className = 'btn-reject';
        action.textContent = type === 'duplicate' ? 'Review' : 'Alert';
    }
}

function updateLastScan(cls, title, sub, time) {
    const el = document.getElementById('last-scan');
    if (!el) return;
    el.className = 'last-scan ' + cls;
    el.innerHTML = '<span class="dot"></span><div class="info"><b>' + title + '</b><span>' + sub + '</span></div><span class="time">' + time + '</span>';
}

function resetScan() {
    showTakeover(null);
    busy = false;
    scanning = true;
    document.getElementById('scan-prompt').textContent = 'Point at QR code';
}

function handleResult(result, now) {
    stats.total++;
    hideVerifying();

    if (result.status === 'APPROVED') {
        stats.approved++;
        const s       = result.student || {};
        const name    = s.full_name || 'Unknown';
        const matric  = s.matric_no || '—';
        const dept    = s.department || '—';
        const initials = name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
        const tokenShort = (result.token_id || '').slice(0, 8) + '…';

        document.getElementById('approved-avatar').textContent = initials;
        document.getElementById('approved-name').textContent   = name;
        document.getElementById('approved-matric').textContent = matric;
        document.getElementById('approved-dept').textContent   = dept;
        document.getElementById('approved-token').textContent  = tokenShort;
        document.getElementById('approved-time').textContent   = now;

        updateDesktopResult('approved', { name, matric, dept, initials, token: tokenShort, time: now });
        updateLastScan('approved', name, matric, now);
        showTakeover('approved');

    } else if (result.status === 'DUPLICATE') {
        stats.duplicate++;
        const s      = result.student || {};
        const name   = s.full_name || 'Unknown';
        const matric = s.matric_no || '—';
        const dept   = s.department || '—';
        const initials = name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();

        document.getElementById('dup-avatar').textContent = initials;
        document.getElementById('dup-name').textContent   = name;
        document.getElementById('dup-matric').textContent = matric;
        document.getElementById('dup-dept').textContent   = dept;
        document.getElementById('dup-count').textContent  = stats.duplicate + 1;
        document.getElementById('duplicate-time').textContent = now;

        updateDesktopResult('duplicate', { name, matric, dept, initials, time: now });
        updateLastScan('duplicate', name, 'Token already redeemed', now);
        showTakeover('duplicate');

    } else {
        stats.rejected++;
        document.getElementById('rejected-time').textContent = now;
        document.getElementById('rejected-scan').textContent = stats.total;

        updateDesktopResult('rejected', { time: now });
        updateLastScan('rejected', 'Invalid token', 'Bad or tampered QR', now);
        showTakeover('rejected');
    }

    updateStats();
}

async function handleQRCode(rawData) {
    if (busy) return;
    busy    = true;
    scanning = false;

    let qrData;
    try { qrData = JSON.parse(rawData); } catch { busy = false; scanning = true; return; }

    const now = new Date().toLocaleTimeString();
    document.getElementById('scan-prompt').textContent = 'Checking…';
    showVerifying();

    try {
        const resp = await fetch('/examiner/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ qr_data: qrData })
        });

        if (resp.status === 401) { window.location.href = '/examiner/login'; return; }

        const result = await resp.json();
        handleResult(result, now);

    } catch (err) {
        hideVerifying();
        stats.total++;
        stats.rejected++;
        document.getElementById('rejected-time').textContent = now;
        document.getElementById('rejected-scan').textContent = stats.total;
        updateDesktopResult('rejected', { time: now });
        updateLastScan('rejected', 'Network error', 'Could not reach server', now);
        showTakeover('rejected');
        updateStats();
        busy = false;
        scanning = true;
    }
}

async function startCamera() {
    const video    = document.getElementById('camera-video');
    const fakeHall = document.getElementById('fake-hall');
    if (!navigator.mediaDevices?.getUserMedia) return;
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        video.srcObject = stream;
        await video.play();
        video.style.display = '';
        fakeHall.style.display = 'none';
        scanning = true;
        requestAnimationFrame(scanFrame);
    } catch (e) {}
}

function scanFrame() {
    if (!scanning) { requestAnimationFrame(scanFrame); return; }
    const video  = document.getElementById('camera-video');
    const canvas = document.getElementById('scan-canvas');
    if (video.readyState === video.HAVE_ENOUGH_DATA && typeof jsQR !== 'undefined') {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });
        if (code?.data) { handleQRCode(code.data); }
    }
    requestAnimationFrame(scanFrame);
}

const demoStudents = [
    { name: 'Adebayo Okafor',   matric: 'CSC/2021/001', department: 'Computer Science' },
    { name: 'Fatima Aliyu',     matric: 'EEE/2021/042', department: 'Electrical Engineering' },
    { name: 'Chukwuemeka Eze',  matric: 'MCB/2022/019', department: 'Microbiology' },
];

function simulateScan(decision) {
    if (busy) return;
    busy    = true;
    scanning = false;
    const now     = new Date().toLocaleTimeString();
    const student = demoStudents[Math.floor(Math.random() * demoStudents.length)];
    document.getElementById('scan-prompt').textContent = 'Checking…';
    showVerifying();
    setTimeout(() => {
        handleResult({
            status: decision,
            student: { full_name: student.name, matric_no: student.matric, department: student.department },
            token_id: 'tok_' + Date.now()
        }, now);
    }, 800);
}

startCamera();
</script>
@endpush
