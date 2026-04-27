@extends('layouts.portal')

@section('title', 'Examiner Scanner')

@section('content')
<style>
    /* ── Layout ────────────────────────────────────────────────────── */
    .ex-page {
        min-height: 100vh;
        background: #0d0f1c;
        color: #fff;
        display: flex;
        flex-direction: column;
    }

    /* ── Topbar ────────────────────────────────────────────────────── */
    .ex-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        background: rgba(255,255,255,.03);
        border-bottom: 1px solid rgba(255,255,255,.06);
        z-index: 10;
    }
    .ex-brand {
        display: flex;
        align-items: center;
        gap: 11px;
    }
    .ex-brand-icon {
        width: 33px;
        height: 33px;
        border-radius: 10px;
        background: var(--navy);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ex-brand b {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: -.01em;
    }
    .ex-brand span {
        font-size: 10px;
        color: rgba(255,255,255,.4);
        letter-spacing: .06em;
        display: block;
        margin-top: 1px;
    }
    .ex-user {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .ex-user-info {
        text-align: right;
    }
    .ex-user-info b { display: block; font-size: 13px; font-weight: 600; }
    .ex-user-info span { font-size: 11px; color: rgba(255,255,255,.4); }
    .ex-avatar {
        width: 33px;
        height: 33px;
        border-radius: 50%;
        background: var(--navy-2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
        flex-shrink: 0;
    }
    .ex-logout {
        width: 35px;
        height: 35px;
        border-radius: 9px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.08);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.6);
        transition: background .15s;
        text-decoration: none;
    }
    .ex-logout:hover { background: rgba(255,255,255,.1); color: rgba(255,255,255,.8); }

    /* ── Stats bar ──────────────────────────────────────────────────── */
    .ex-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: rgba(255,255,255,.04);
        border-bottom: 1px solid rgba(255,255,255,.04);
    }
    .stat-cell {
        padding: 12px 16px;
        background: rgba(255,255,255,.02);
        text-align: center;
        transition: background .25s;
    }
    .stat-cell:hover { background: rgba(255,255,255,.04); }
    .stat-cell b {
        display: block;
        font-size: 19px;
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
        line-height: 1;
    }
    .stat-cell span {
        font-size: 10px;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: rgba(255,255,255,.35);
        display: block;
        margin-top: 5px;
    }
    .stat-cell.approved b { color: var(--emerald-2); }
    .stat-cell.rejected b { color: var(--red-2); }
    .stat-cell.duplicate b { color: var(--amber-2); }

    /* ── Workspace ──────────────────────────────────────────────────── */
    .ex-workspace {
        flex: 1;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    /* Camera panel */
    .ex-camera-panel {
        position: relative;
        flex: 1;
        background: #0a0c1a;
        overflow: hidden;
    }
    .camera-feed {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 50% 40%, rgba(45,108,255,.08), transparent 60%),
            linear-gradient(135deg, #151b35 0%, #0a0d1a 100%);
    }
    .camera-feed::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: repeating-linear-gradient(0deg, rgba(255,255,255,.01) 0, rgba(255,255,255,.01) 1px, transparent 1px, transparent 3px);
    }
    .fake-hall {
        position: absolute;
        inset: 10% 15%;
        opacity: .15;
        background:
            linear-gradient(180deg, rgba(255,255,255,.08), transparent 30%),
            repeating-linear-gradient(45deg, rgba(255,255,255,.02) 0 10px, transparent 10px 20px);
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
        border: 2.5px solid rgba(255,255,255,.8);
        border-radius: 6px;
    }
    .reticle .corners span:nth-child(1) { top: 0; left: 0; border-right: none; border-bottom: none; }
    .reticle .corners span:nth-child(2) { top: 0; right: 0; border-left: none; border-bottom: none; }
    .reticle .corners span:nth-child(3) { bottom: 0; left: 0; border-right: none; border-top: none; }
    .reticle .corners span:nth-child(4) { bottom: 0; right: 0; border-left: none; border-top: none; }
    .reticle .scan-line {
        position: absolute;
        left: 10%;
        right: 10%;
        height: 1.5px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.6), transparent);
        box-shadow: 0 0 10px rgba(255,255,255,.3);
        animation: scanline 1.8s ease-in-out infinite alternate;
    }
    .reticle .dim-overlay {
        position: absolute;
        inset: -200vh;
        box-shadow: 0 0 0 200vh rgba(0,0,0,.5);
        border-radius: 12px;
    }
    @keyframes scanline {
        from { top: 20%; } to { top: 80%; }
    }

    .scan-prompt {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 20px;
        text-align: center;
        z-index: 10;
        font-size: 13px;
        color: rgba(255,255,255,.7);
        letter-spacing: .05em;
    }
    .scan-prompt b { color: #fff; font-weight: 600; }

    /* Verifying overlay */
    .verifying-overlay {
        position: absolute;
        inset: 0;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(10, 12, 22, 0.92);
        backdrop-filter: blur(8px);
        z-index: 80;
        color: #fff;
        text-align: center;
        gap: 16px;
    }
    .verifying-overlay.show {
        display: flex;
        animation: fadeIn .2s ease;
    }
    .verifying-spinner {
        width: 52px;
        height: 52px;
        border: 2.5px solid rgba(255,255,255,.12);
        border-top-color: rgba(255,255,255,.8);
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .verifying-label {
        font-size: 14px;
        font-weight: 600;
        color: rgba(255,255,255,.8);
        letter-spacing: .04em;
    }

    /* Mobile bottom */
    .ex-mobile-bottom {
        padding: 16px;
        background: rgba(0,0,0,.7);
    }
    .last-scan {
        padding: 11px 14px;
        border-radius: 12px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.08);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .last-scan .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: rgba(255,255,255,.25);
    }
    .last-scan.approved { background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.25); }
    .last-scan.approved .dot { background: var(--emerald-2); box-shadow: 0 0 6px var(--emerald-2); }
    .last-scan.rejected { background: rgba(239,68,68,.1); border-color: rgba(239,68,68,.25); }
    .last-scan.rejected .dot { background: var(--red-2); }
    .last-scan.duplicate { background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.25); }
    .last-scan.duplicate .dot { background: var(--amber-2); }
    .last-scan .info { flex: 1; min-width: 0; }
    .last-scan .info b { display: block; font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .last-scan .info span { font-size: 11px; color: rgba(255,255,255,.45); }
    .last-scan .time { font-size: 11px; color: rgba(255,255,255,.35); font-family: 'JetBrains Mono', monospace; }

    .scan-actions {
        display: flex;
        gap: 8px;
    }
    .scan-actions button {
        flex: 1;
        padding: 9px 6px;
        border-radius: 10px;
        background: rgba(255,255,255,.07);
        color: #fff;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid rgba(255,255,255,.1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        cursor: pointer;
        transition: background .15s;
    }
    .scan-actions button:hover { background: rgba(255,255,255,.13); }

    /* Takeovers */
    .takeover {
        position: absolute;
        inset: 0;
        display: none;
        flex-direction: column;
        justify-content: space-between;
        color: #fff;
        z-index: 100;
        animation: flash .35s cubic-bezier(.16,1,.3,1) both;
        overflow: hidden;
    }
    .takeover.approved { background: linear-gradient(180deg, #047857 0%, #065f46 100%); }
    .takeover.rejected { background: linear-gradient(180deg, #b91c1c 0%, #7f1d1d 100%); }
    .takeover.duplicate { background: linear-gradient(180deg, #b45309 0%, #78350f 100%); }
    .takeover.show { display: flex; }
    .takeover::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 0%, rgba(255,255,255,.1), transparent 60%);
    }
    .to-top {
        padding: 56px 20px 12px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        position: relative;
        z-index: 1;
    }
    .to-top .status-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .3em;
        opacity: .65;
    }
    .to-top .time {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        opacity: .65;
    }
    .to-center {
        text-align: center;
        padding: 0 24px;
        position: relative;
        z-index: 1;
    }
    .big-icon {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
        border: 3px solid rgba(255,255,255,.28);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .big-icon svg { width: 60px; height: 60px; stroke: #fff; stroke-width: 3; fill: none; }
    .to-center h1 { font-size: 48px; font-weight: 800; letter-spacing: .04em; margin: 0; line-height: .95; }
    .to-center p { font-size: 15px; margin: 10px 0 0; opacity: .75; }
    .student-card {
        margin: 20px 20px 0;
        padding: 16px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 16px;
        display: flex;
        gap: 12px;
        align-items: center;
        backdrop-filter: blur(8px);
        position: relative;
        z-index: 1;
    }
    .sc-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: rgba(255,255,255,.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 20px;
        flex-shrink: 0;
    }
    .student-card .nm { font-size: 15px; font-weight: 600; margin: 0; }
    .student-card .mt { font-size: 12px; opacity: .6; margin: 2px 0 0; font-family: 'JetBrains Mono', monospace; }
    .student-card .dept { font-size: 11px; opacity: .5; margin: 4px 0 0; text-transform: uppercase; letter-spacing: .08em; }
    .meta-row {
        margin: 12px 20px 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        position: relative;
        z-index: 1;
    }
    .meta-cell { padding: 9px 11px; background: rgba(255,255,255,.07); border-radius: 10px; }
    .meta-cell .k { font-size: 9px; opacity: .5; letter-spacing: .1em; text-transform: uppercase; }
    .meta-cell .v { font-size: 12px; font-weight: 600; margin-top: 2px; font-family: 'JetBrains Mono', monospace; }
    .to-bottom {
        padding: 16px 20px;
        display: flex;
        gap: 8px;
        position: relative;
        z-index: 1;
    }
    .to-bottom button {
        flex: 1;
        padding: 14px;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.2);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        transition: background .15s;
    }
    .to-bottom button.primary {
        background: #fff;
        color: #065f46;
        border-color: #fff;
    }
    .takeover.rejected .to-bottom button.primary { color: #7f1d1d; }
    .takeover.duplicate .to-bottom button.primary { color: #78350f; }
    .to-bottom button:hover { filter: brightness(.92); }

    @keyframes flash { from { opacity: 0; transform: scale(.96); } to { opacity: 1; transform: none; } }

    /* ── Desktop layout ──────────────────────────────────────────────── */
    @media (min-width: 768px) {
        .ex-page {
            height: 100vh;
            overflow: hidden;
        }
        .ex-workspace {
            flex-direction: row;
            overflow: hidden;
        }
        .ex-camera-panel {
            flex: 1;
            min-height: 0;
        }
        .ex-mobile-bottom {
            display: none;
        }
        .scan-prompt {
            bottom: 24px;
        }
        .takeover {
            display: none !important;
        }

        .ex-result-panel {
            display: flex;
            flex-direction: column;
            width: 410px;
            flex-shrink: 0;
            background: #0f1225;
            border-left: 1px solid rgba(255,255,255,.06);
            overflow-y: auto;
        }

        .ex-panel-actions {
            display: flex;
            gap: 8px;
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,.06);
            margin-top: auto;
        }
        .ex-panel-actions button {
            flex: 1;
            padding: 9px 6px;
            border-radius: 10px;
            background: rgba(255,255,255,.07);
            color: #fff;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,.1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            transition: background .15s;
        }
        .ex-panel-actions button:hover { background: rgba(255,255,255,.13); }
    }
    @media (max-width: 767px) {
        .ex-result-panel { display: none; }
        .ex-mobile-bottom { display: block; }
    }

    /* Desktop result panel states */
    .res-idle {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 40px 24px;
        color: rgba(255,255,255,.5);
    }
    .idle-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.08);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: rgba(255,255,255,.25);
    }
    .res-idle b {
        display: block;
        font-size: 15px;
        font-weight: 600;
        color: rgba(255,255,255,.65);
        margin-bottom: 6px;
    }
    .res-idle p {
        font-size: 12px;
        color: rgba(255,255,255,.3);
        margin: 0;
        line-height: 1.5;
    }

    .res-scanning {
        flex: 1;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 24px;
        gap: 16px;
    }
    .res-scanning.show { display: flex; }
    .res-scan-spinner {
        width: 54px;
        height: 54px;
        border: 2.5px solid rgba(255,255,255,.1);
        border-top-color: rgba(255,255,255,.6);
        border-radius: 50%;
        animation: spin .8s linear infinite;
    }
    .res-scanning b {
        font-size: 14px;
        font-weight: 600;
        color: rgba(255,255,255,.7);
        letter-spacing: .03em;
    }

    .res-result {
        flex: 1;
        display: none;
        flex-direction: column;
        overflow-y: auto;
    }
    .res-result.show { display: flex; }

    .res-status-bar {
        padding: 18px 20px 16px;
        border-bottom: 1px solid rgba(255,255,255,.06);
        flex-shrink: 0;
    }
    .res-status-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .25em;
        opacity: .5;
        margin-bottom: 8px;
    }
    .res-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: .04em;
    }
    .res-status-badge.approved { background: rgba(16,185,129,.15); color: var(--emerald-2); border: 1px solid rgba(16,185,129,.25); }
    .res-status-badge.rejected { background: rgba(239,68,68,.15); color: var(--red-2); border: 1px solid rgba(239,68,68,.25); }
    .res-status-badge.duplicate { background: rgba(245,158,11,.15); color: var(--amber-2); border: 1px solid rgba(245,158,11,.25); }
    .res-timestamp {
        font-size: 11px;
        color: rgba(255,255,255,.3);
        margin-top: 8px;
        font-family: 'JetBrains Mono', monospace;
    }

    .res-student-block {
        padding: 20px;
        flex: 1;
    }
    .res-student-card {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 16px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 14px;
        margin-bottom: 16px;
    }
    .res-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--navy-2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 20px;
        flex-shrink: 0;
    }
    .res-student-card .nm { font-size: 15px; font-weight: 600; margin: 0; }
    .res-student-card .mt { font-size: 12px; color: rgba(255,255,255,.5); margin: 2px 0 0; font-family: 'JetBrains Mono', monospace; }
    .res-student-card .dept { font-size: 11px; color: rgba(255,255,255,.35); margin: 4px 0 0; text-transform: uppercase; letter-spacing: .08em; }

    .res-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .res-meta-cell {
        padding: 10px 12px;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 10px;
    }
    .res-meta-cell .k { font-size: 9px; color: rgba(255,255,255,.3); letter-spacing: .1em; text-transform: uppercase; }
    .res-meta-cell .v { font-size: 13px; font-weight: 600; margin-top: 3px; font-family: 'JetBrains Mono', monospace; }

    .res-rejection-block {
        padding: 14px;
        background: rgba(239,68,68,.08);
        border: 1px solid rgba(239,68,68,.15);
        border-radius: 12px;
        margin-bottom: 14px;
    }
    .res-rejection-block .k { font-size: 10px; color: var(--red-2); letter-spacing: .1em; text-transform: uppercase; font-weight: 600; margin-bottom: 6px; }
    .res-rejection-block .v { font-size: 13px; color: rgba(255,255,255,.8); font-weight: 600; }
    .res-rejection-block .sub { font-size: 11px; color: rgba(255,255,255,.45); margin-top: 2px; }

    .res-result-actions {
        display: flex;
        gap: 8px;
        padding: 0 20px 20px;
    }
    .res-result-actions button {
        flex: 1;
        padding: 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .res-result-actions .btn-ghost-dark {
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.75);
    }
    .res-result-actions .btn-ghost-dark:hover { background: rgba(255,255,255,.1); }
    .res-result-actions .btn-admit {
        background: var(--emerald);
        border: 1px solid var(--emerald);
        color: #fff;
    }
    .res-result-actions .btn-reject {
        background: var(--red);
        border: 1px solid var(--red);
        color: #fff;
    }
    .res-result-actions .btn-admit:hover,
    .res-result-actions .btn-reject:hover { filter: brightness(1.1); transform: translateY(-1px); }
</style>

<div class="ex-page">

    <!-- Topbar -->
    <div class="ex-topbar">
        <div class="ex-brand">
            <div class="ex-brand-icon">
                <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div>
                <b>CERNIX</b>
                <span>EXAM VERIFICATION</span>
            </div>
        </div>
        <div class="ex-user">
            <div class="ex-user-info">
                <b>{{ $examiner['full_name'] ?? 'Examiner' }}</b>
                <span>{{ '@' . ($examiner['username'] ?? 'examiner') }} · {{ ucfirst($examiner['role'] ?? 'examiner') }}</span>
            </div>
            <div class="ex-avatar">{{ strtoupper(substr($examiner['full_name'] ?? 'EX', 0, 2)) }}</div>
            <a href="/examiner/logout" class="ex-logout" aria-label="Logout">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h6a2 2 0 012 2v2"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Stats bar -->
    <div class="ex-stats">
        <div class="stat-cell"><b id="total-scans">0</b><span>Total Scans</span></div>
        <div class="stat-cell approved"><b id="approved-count">0</b><span>Approved</span></div>
        <div class="stat-cell rejected"><b id="rejected-count">0</b><span>Rejected</span></div>
        <div class="stat-cell duplicate"><b id="duplicate-count">0</b><span>Duplicates</span></div>
    </div>

    <!-- Workspace -->
    <div class="ex-workspace">

        <!-- Camera panel -->
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
            <div class="scan-prompt" id="scan-prompt">Point camera at the student's <b>CERNIX QR</b></div>

            <!-- Verifying overlay (NEW) -->
            <div class="verifying-overlay" id="verifying-overlay">
                <div class="verifying-spinner"></div>
                <span class="verifying-label">Verifying QR…</span>
            </div>

            <!-- Mobile takeovers -->
            <div class="takeover approved" id="takeover-approved">
                <div class="to-top"><span class="status-label">DECISION · APPROVED</span><span class="time" id="approved-time">09:14:44</span></div>
                <div class="to-center">
                    <div class="big-icon"><svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></div>
                    <h1>VERIFIED</h1><p>Access granted. Allow entry.</p>
                </div>
                <div>
                    <div class="student-card">
                        <div class="sc-avatar" id="approved-avatar">AE</div>
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
                <div class="to-bottom">
                    <button onclick="resetScan()">Next Scan</button>
                    <button class="primary" onclick="resetScan()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        Admit Student
                    </button>
                </div>
            </div>

            <div class="takeover rejected" id="takeover-rejected">
                <div class="to-top"><span class="status-label">DECISION · REJECTED</span><span class="time" id="rejected-time">09:40:18</span></div>
                <div class="to-center">
                    <div class="big-icon"><svg viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg></div>
                    <h1>REJECTED</h1><p>Do not admit. Escalate to supervisor.</p>
                </div>
                <div>
                    <div style="margin:20px;position:relative;z-index:1">
                        <div class="student-card" style="margin:0;flex-direction:column">
                            <p style="font-size:12px;opacity:.8;margin:0 0 8px">Rejection Reason</p>
                            <div style="font-size:13px;line-height:1.5">
                                <b>HMAC signature mismatch</b><br>
                                <span style="opacity:.7;font-size:12px">Token was tampered with or forged</span>
                            </div>
                        </div>
                    </div>
                    <div class="meta-row">
                        <div class="meta-cell"><div class="k">Scan #</div><div class="v" id="rejected-scan">1</div></div>
                        <div class="meta-cell"><div class="k">Logged</div><div class="v">YES</div></div>
                    </div>
                </div>
                <div class="to-bottom">
                    <button onclick="resetScan()">Dismiss</button>
                    <button class="primary" onclick="resetScan()">Alert Supervisor</button>
                </div>
            </div>

            <div class="takeover duplicate" id="takeover-duplicate">
                <div class="to-top"><span class="status-label">DECISION · ALREADY USED</span><span class="time" id="duplicate-time">09:31:12</span></div>
                <div class="to-center">
                    <div class="big-icon"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
                    <h1 style="margin-bottom:0">ALREADY<br>USED</h1><p>Token was redeemed earlier. Entry denied.</p>
                </div>
                <div>
                    <div class="student-card">
                        <div class="sc-avatar" id="dup-avatar">AO</div>
                        <div style="flex:1">
                            <p class="nm" id="dup-name">Adebayo Oluwaseun</p>
                            <p class="mt" id="dup-matric">CSC/2021/001</p>
                            <p class="dept" style="color:rgba(255,255,255,.7);margin-top:6px">First redeemed: <b>08:54:21</b></p>
                        </div>
                    </div>
                    <div class="meta-row">
                        <div class="meta-cell"><div class="k">Original Hall</div><div class="v">Hall B</div></div>
                        <div class="meta-cell"><div class="k">Examiner</div><div class="v">examiner3</div></div>
                    </div>
                </div>
                <div class="to-bottom">
                    <button onclick="resetScan()">Dismiss</button>
                    <button class="primary" onclick="resetScan()">View Audit Trail</button>
                </div>
            </div>
        </div>

        <!-- Mobile bottom -->
        <div class="ex-mobile-bottom">
            <div class="last-scan" id="last-scan">
                <span class="dot"></span>
                <div class="info"><b>No scans yet</b><span>Awaiting first QR</span></div>
                <span class="time">--:--</span>
            </div>
            <div class="scan-actions">
                <button onclick="simulateScan('APPROVED')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>Approve
                </button>
                <button onclick="simulateScan('REJECTED')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>Reject
                </button>
                <button onclick="simulateScan('DUPLICATE')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>Dup
                </button>
            </div>
        </div>

        <!-- Desktop result panel -->
        <div class="ex-result-panel">
            <!-- Idle state -->
            <div class="res-idle" id="res-idle">
                <div class="idle-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h.01M14 17h.01M17 14h.01M17 17h.01M20 17h.01M20 20h.01M17 20h.01M14 20h.01M20 14h.01"/>
                    </svg>
                </div>
                <b>Ready to scan</b>
                <p>Point camera at a student's CERNIX QR code.<br>Results appear here instantly.</p>
            </div>

            <!-- Scanning state (NEW) -->
            <div class="res-scanning" id="res-scanning">
                <div class="res-scan-spinner"></div>
                <b>Processing QR…</b>
            </div>

            <!-- Result state -->
            <div class="res-result" id="res-result">
                <div class="res-status-bar">
                    <div class="res-status-label">SCAN RESULT</div>
                    <div class="res-status-badge approved" id="res-status-badge">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        <span id="res-status-text">VERIFIED</span>
                    </div>
                    <div class="res-timestamp" id="res-timestamp"></div>
                </div>

                <div class="res-student-block">
                    <!-- Approved block -->
                    <div id="res-approved-block">
                        <div class="res-student-card">
                            <div class="res-avatar" id="res-avatar">AE</div>
                            <div style="flex:1">
                                <p class="nm" id="res-name-d">Adaeze Ekwueme</p>
                                <p class="mt" id="res-matric-d">CSC/2021/002</p>
                                <p class="dept" id="res-dept-d">Computer Science</p>
                            </div>
                        </div>
                        <div class="res-meta-grid">
                            <div class="res-meta-cell"><div class="k">Token</div><div class="v" id="res-token-d">a7f2…</div></div>
                            <div class="res-meta-cell"><div class="k">Session</div><div class="v" id="res-session-d">#1</div></div>
                            <div class="res-meta-cell"><div class="k">Status</div><div class="v" style="color:var(--emerald-2)">VALID</div></div>
                            <div class="res-meta-cell"><div class="k">Logged</div><div class="v">YES</div></div>
                        </div>
                    </div>

                    <!-- Rejected block -->
                    <div id="res-rejected-block" style="display:none">
                        <div class="res-rejection-block">
                            <div class="k">Rejection Reason</div>
                            <div class="v" id="res-reject-reason">HMAC signature mismatch</div>
                            <div class="sub">Token was tampered with or forged</div>
                        </div>
                        <div class="res-meta-grid">
                            <div class="res-meta-cell"><div class="k">Scan #</div><div class="v" id="res-scan-num-d">1</div></div>
                            <div class="res-meta-cell"><div class="k">Logged</div><div class="v">YES</div></div>
                        </div>
                    </div>

                    <!-- Duplicate block -->
                    <div id="res-duplicate-block" style="display:none">
                        <div class="res-student-card" style="margin-bottom:12px">
                            <div class="res-avatar" id="res-dup-avatar-d">AO</div>
                            <div style="flex:1">
                                <p class="nm" id="res-dup-name-d">Adebayo Oluwaseun</p>
                                <p class="mt" id="res-dup-matric-d">CSC/2021/001</p>
                                <p class="dept" style="color:var(--amber-2);margin-top:4px">Token already redeemed</p>
                            </div>
                        </div>
                        <div class="res-meta-grid">
                            <div class="res-meta-cell"><div class="k">First Used</div><div class="v" id="res-dup-time-d">08:54:21</div></div>
                            <div class="res-meta-cell"><div class="k">Hall</div><div class="v">Hall B</div></div>
                        </div>
                    </div>
                </div>

                <div class="res-result-actions">
                    <button class="btn-ghost-dark" onclick="resetScan()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 4v6h6"/><path d="M23 20v-6h-6"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>
                        Next Scan
                    </button>
                    <button class="btn-admit" onclick="resetScan()" id="res-action-btn">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        Admit
                    </button>
                </div>
            </div>

            <!-- Desktop demo actions -->
            <div class="ex-panel-actions">
                <button onclick="simulateScan('APPROVED')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>Demo Approve
                </button>
                <button onclick="simulateScan('REJECTED')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>Reject
                </button>
                <button onclick="simulateScan('DUPLICATE')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>Duplicate
                </button>
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

function updateDesktopResult(type){
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
    const badge=document.getElementById('res-status-badge');
    const statusText=document.getElementById('res-status-text');
    badge.className='res-status-badge '+type;
    const labels={approved:'VERIFIED',rejected:'REJECTED',duplicate:'ALREADY USED'};
    statusText.textContent=labels[type]||type.toUpperCase();
    document.getElementById('res-approved-block').style.display=type==='approved'?'':'none';
    document.getElementById('res-rejected-block').style.display=type==='rejected'?'':'none';
    document.getElementById('res-duplicate-block').style.display=type==='duplicate'?'':'none';
    const actionBtn=document.getElementById('res-action-btn');
    if(type==='approved'){
        actionBtn.className='btn-admit';
        actionBtn.innerHTML='<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>Admit';
    }else if(type==='rejected'){
        actionBtn.className='btn-reject';
        actionBtn.innerHTML='Alert Supervisor';
    }else{
        actionBtn.className='btn-reject';
        actionBtn.innerHTML='View Audit';
    }
}

function showTakeover(type){
    ['approved','rejected','duplicate'].forEach(t=>document.getElementById('takeover-'+t).classList.remove('show'));
    if(type)document.getElementById('takeover-'+type).classList.add('show');
    updateDesktopResult(type);
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
    document.getElementById('scan-prompt').textContent='Point camera at the student\'s CERNIX QR';
}

function handleResult(result,now){
    stats.total++;
    document.getElementById('res-timestamp').textContent=now;
    if(result.status==='APPROVED'){
        stats.approved++;
        const s=result.student||{};
        const name=s.full_name||'Unknown';
        const matric=s.matric_no||'—';
        const initials=name.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
        document.getElementById('approved-name').textContent=name;
        document.getElementById('approved-matric').textContent=matric;
        document.getElementById('approved-dept').textContent=s.department||('Dept '+(s.department_id||'—'));
        document.getElementById('approved-avatar').textContent=initials;
        document.getElementById('approved-time').textContent=now;
        document.getElementById('approved-token').textContent=(result.token_id||'').slice(0,8)+'…';
        document.getElementById('res-name-d').textContent=name;
        document.getElementById('res-matric-d').textContent=matric;
        document.getElementById('res-dept-d').textContent=s.department||'';
        document.getElementById('res-avatar').textContent=initials;
        document.getElementById('res-token-d').textContent=(result.token_id||'').slice(0,8)+'…';
        updateLastScan('approved',name,matric+' · APPROVED',now);
        showTakeover('approved');
    }else if(result.status==='DUPLICATE'){
        stats.duplicate++;
        document.getElementById('duplicate-time').textContent=now;
        document.getElementById('res-dup-time-d').textContent=now;
        updateLastScan('duplicate','Already Used','Token redeemed · DUPLICATE',now);
        showTakeover('duplicate');
    }else{
        stats.rejected++;
        document.getElementById('rejected-time').textContent=now;
        document.getElementById('rejected-scan').textContent=stats.total;
        document.getElementById('res-scan-num-d').textContent=stats.total;
        updateLastScan('rejected','Invalid QR','REJECTED',now);
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
    document.getElementById('scan-prompt').textContent='Processing…';
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
        document.getElementById('res-scan-num-d').textContent=stats.total;
        updateLastScan('rejected','Network Error','Could not reach server',now);
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

const demoStudents=[{name:'Adebayo Oluwaseun Emmanuel',matric:'CSC/2021/001',dept:'Computer Science'},{name:'Adaeze Ekwueme',matric:'CSC/2021/002',dept:'Computer Science'},{name:'Tunde Balogun',matric:'CSC/2021/003',dept:'Computer Science'}];

function simulateScan(decision){
    if(busy)return;
    busy=true;
    scanning=false;
    const now=new Date().toLocaleTimeString();
    const student=demoStudents[Math.floor(Math.random()*demoStudents.length)];
    document.getElementById('scan-prompt').textContent='Processing…';
    showVerifying();
    setTimeout(()=>{
        hideVerifying();
        handleResult({status:decision,student:{full_name:student.name,matric_no:student.matric,department:student.dept},token_id:'demo'+Date.now()},now);
    },800);
}

startCamera();
</script>
@endpush
