<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>CERNIX — Exam Scanner</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <style>
        :root {
            --navy:   #0f2050;
            --navy-2: #1a3370;
            --green:  #16a34a;
            --red:    #dc2626;
            --amber:  #d97706;
            --bg:     #f4f4ef;
            --bg-2:   #ffffff;
            --ink:    rgba(0,0,0,.88);
            --ink-2:  rgba(0,0,0,.60);
            --ink-3:  rgba(0,0,0,.40);
            --ink-4:  rgba(0,0,0,.22);
            --line:   rgba(0,0,0,.10);
            --line-2: rgba(0,0,0,.06);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ── PAGE SHELL ─────────────────────────────────────── */
        .ex-page {
            display: flex;
            flex-direction: column;
            height: 100dvh;
            background: var(--bg);
            overflow: hidden;
        }

        /* ── TOPBAR ─────────────────────────────────────────── */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 52px;
            padding: 0 16px;
            background: var(--bg-2);
            border-bottom: 1px solid var(--line);
            flex-shrink: 0;
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-icon {
            width: 32px;
            height: 32px;
            background: var(--navy);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .topbar-wordmark { font-size: 15px; font-weight: 700; color: var(--navy); letter-spacing: .05em; }
        .topbar-sub      { font-size: 11px; color: var(--ink-3); font-weight: 500; margin-top: 1px; }

        .btn-ghost-sm {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 500;
            color: var(--ink-2);
            background: transparent;
            border: 1px solid var(--line);
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
            display: inline-flex;
            align-items: center;
        }
        .btn-ghost-sm:hover { background: var(--line-2); }

        /* ── STATS BAR ──────────────────────────────────────── */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            background: var(--bg-2);
            border-bottom: 1px solid var(--line);
            flex-shrink: 0;
        }

        .stat-cell {
            padding: 9px 8px 8px;
            text-align: center;
            border-right: 1px solid var(--line);
        }
        .stat-cell:last-child { border-right: none; }

        .stat-value {
            font-size: 22px;
            font-weight: 700;
            line-height: 1;
        }
        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--ink-3);
            margin-top: 2px;
        }

        .sv-total     { color: var(--navy); }
        .sv-approved  { color: var(--green); }
        .sv-rejected  { color: var(--red); }
        .sv-duplicate { color: var(--amber); }

        /* ── WORKSPACE ──────────────────────────────────────── */
        .workspace {
            display: flex;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        /* ── CAMERA PANEL ───────────────────────────────────── */
        .camera-panel {
            flex: 1;
            min-width: 0;
            min-height: 0;
            background: #0a0a0a;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Placeholder */
        .cam-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,.45);
            text-align: center;
            padding: 24px;
        }
        .cam-placeholder svg { opacity: .35; }
        .cam-placeholder p   { font-size: 13px; }

        .btn-start {
            padding: 10px 28px;
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-start:hover { background: var(--navy-2); }

        /* Active camera */
        #cam-active {
            position: absolute;
            inset: 0;
            display: none;
        }

        #qr-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #qr-canvas { display: none; }

        /* Reticle */
        .reticle {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            pointer-events: none;
        }

        .rc { position: absolute; width: 22px; height: 22px; }
        .rc.tl { top: 0; left: 0;  border-top: 2px solid rgba(255,255,255,.85); border-left: 2px solid rgba(255,255,255,.85); border-radius: 2px 0 0 0; }
        .rc.tr { top: 0; right: 0; border-top: 2px solid rgba(255,255,255,.85); border-right: 2px solid rgba(255,255,255,.85); border-radius: 0 2px 0 0; }
        .rc.bl { bottom: 0; left: 0;  border-bottom: 2px solid rgba(255,255,255,.85); border-left: 2px solid rgba(255,255,255,.85); border-radius: 0 0 0 2px; }
        .rc.br { bottom: 0; right: 0; border-bottom: 2px solid rgba(255,255,255,.85); border-right: 2px solid rgba(255,255,255,.85); border-radius: 0 0 2px 0; }

        .scan-line {
            position: absolute;
            left: 2px; right: 2px;
            height: 1px;
            background: rgba(255,255,255,.55);
            animation: scanline 2s ease-in-out infinite;
        }
        @keyframes scanline {
            0%   { top: 4px; }
            50%  { top: calc(100% - 4px); }
            100% { top: 4px; }
        }

        /* Status pill */
        .scan-pill {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,.55);
            color: rgba(255,255,255,.80);
            font-size: 11px;
            padding: 4px 14px;
            border-radius: 999px;
            pointer-events: none;
            white-space: nowrap;
            backdrop-filter: blur(4px);
        }

        /* Stop btn overlay */
        .cam-stop-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 5px 12px;
            background: rgba(0,0,0,.45);
            color: rgba(255,255,255,.75);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 8px;
            font-size: 11px;
            cursor: pointer;
            backdrop-filter: blur(4px);
            transition: background .15s;
        }
        .cam-stop-btn:hover { background: rgba(0,0,0,.65); }

        /* ── MOBILE BOTTOM BAR ──────────────────────────────── */
        .bottom-bar {
            flex-shrink: 0;
            background: var(--bg-2);
            border-top: 1px solid var(--line);
            padding: 12px 16px;
        }
        @media (min-width: 768px) { .bottom-bar { display: none; } }

        .last-scan-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .last-scan-lbl  { font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: var(--ink-3); font-weight: 600; }
        .last-scan-info { font-size: 12px; color: var(--ink-2); }

        .demo-btns { display: flex; gap: 8px; }
        .demo-btn {
            flex: 1;
            padding: 8px 4px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: transparent;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
        }
        .demo-btn:hover { background: var(--line-2); }
        .demo-btn.g { color: var(--green); }
        .demo-btn.r { color: var(--red); }
        .demo-btn.a { color: var(--amber); }

        /* ── DESKTOP RESULT PANEL ───────────────────────────── */
        .result-panel {
            width: 380px;
            flex-shrink: 0;
            background: var(--bg-2);
            border-left: 1px solid var(--line);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }
        @media (min-width: 768px) { .result-panel { display: flex; } }

        .rp-body {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .rp-heading {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--ink-3);
            margin-bottom: 18px;
        }

        /* Idle */
        .rp-idle {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: var(--ink-4);
            text-align: center;
        }
        .rp-idle p { font-size: 13px; color: var(--ink-3); }

        /* Loading */
        .rp-loading {
            flex: 1;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: var(--ink-3);
        }
        .spinner {
            width: 26px; height: 26px;
            border: 2px solid var(--line);
            border-top-color: var(--navy);
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Status badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            margin-bottom: 16px;
        }
        .badge-approved  { background: #dcfce7; color: #166534; }
        .badge-rejected  { background: #fee2e2; color: #991b1b; }
        .badge-duplicate { background: #fef3c7; color: #92400e; }

        /* Student card */
        .student-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            background: var(--bg);
            border-radius: 12px;
            margin-bottom: 12px;
        }
        .avatar {
            width: 46px; height: 46px;
            background: var(--line);
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ink-3);
        }
        .stu-name   { font-size: 14px; font-weight: 600; color: var(--ink); }
        .stu-matric { font-size: 12px; color: var(--ink-3); margin-top: 2px; }

        /* Meta grid */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 16px;
        }
        .meta-cell {
            padding: 10px 12px;
            background: var(--bg);
            border-radius: 10px;
        }
        .meta-cell.span2 { grid-column: span 2; }
        .meta-key   { font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: var(--ink-3); margin-bottom: 3px; }
        .meta-val   { font-size: 12px; color: var(--ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: monospace; }
        .meta-val.ok { color: var(--green); font-family: inherit; font-weight: 600; }

        /* Alert panels */
        .alert-panel { padding: 18px; border-radius: 12px; margin-bottom: 16px; }
        .alert-panel.red    { background: #fff5f5; border: 1px solid #fecaca; }
        .alert-panel.amber  { background: #fffbeb; border: 1px solid #fde68a; }
        .alert-title { font-size: 14px; font-weight: 700; margin-bottom: 5px; }
        .alert-panel.red   .alert-title { color: var(--red); }
        .alert-panel.amber .alert-title { color: var(--amber); }
        .alert-desc  { font-size: 12px; }
        .alert-panel.red   .alert-desc { color: #991b1b; }
        .alert-panel.amber .alert-desc { color: #92400e; }

        /* Action buttons */
        .action-btns { margin-top: auto; padding-top: 8px; }
        .btn-reset {
            width: 100%;
            padding: 10px;
            background: transparent;
            border: 1px solid var(--line);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            color: var(--ink-2);
            cursor: pointer;
            transition: background .15s;
        }
        .btn-reset:hover { background: var(--line-2); }

        /* Manual input footer */
        .manual-section {
            flex-shrink: 0;
            padding: 14px 20px 16px;
            border-top: 1px solid var(--line);
        }
        .manual-lbl { font-size: 10px; color: var(--ink-3); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 7px; }
        .manual-ta {
            width: 100%;
            height: 66px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 11px;
            font-family: monospace;
            resize: none;
            outline: none;
            color: var(--ink);
            background: var(--bg);
            transition: border-color .15s;
        }
        .manual-ta:focus { border-color: var(--navy); }
        .btn-manual {
            width: 100%;
            margin-top: 7px;
            padding: 8px;
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            color: var(--ink-2);
            cursor: pointer;
            transition: background .15s;
        }
        .btn-manual:hover { background: var(--line-2); }

        /* ── MOBILE TAKEOVERS ───────────────────────────────── */
        .takeover {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 200;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
            text-align: center;
            animation: tko-in .18s ease;
        }
        @keyframes tko-in { from { opacity: 0; transform: scale(.97); } to { opacity: 1; transform: scale(1); } }
        .takeover.show { display: flex; }

        .tko-approved  { background: linear-gradient(160deg, #14532d, #16a34a); color: #fff; }
        .tko-rejected  { background: linear-gradient(160deg, #7f1d1d, #dc2626); color: #fff; }
        .tko-duplicate { background: linear-gradient(160deg, #78350f, #d97706); color: #fff; }

        .tko-icon   { font-size: 60px; line-height: 1; margin-bottom: 14px; }
        .tko-title  { font-size: 30px; font-weight: 800; letter-spacing: .06em; margin-bottom: 6px; }
        .tko-name   { font-size: 18px; font-weight: 600; margin-bottom: 3px; }
        .tko-matric { font-size: 13px; opacity: .70; margin-bottom: 6px; }
        .tko-sub    { font-size: 13px; opacity: .75; margin-bottom: 36px; }

        .btn-tko-dismiss {
            padding: 12px 36px;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.28);
            border-radius: 12px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-tko-dismiss:hover { background: rgba(255,255,255,.28); }
    </style>
</head>
<body>
<div class="ex-page">

    {{-- TOPBAR --}}
    <header class="topbar">
        <div class="topbar-brand">
            <div class="topbar-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div>
                <div class="topbar-wordmark">CERNIX</div>
                <div class="topbar-sub">Exam Scanner</div>
            </div>
        </div>
        <a href="/" class="btn-ghost-sm">← Home</a>
    </header>

    {{-- STATS BAR --}}
    <div class="stats-bar">
        <div class="stat-cell">
            <div class="stat-value sv-total" id="stat-total">0</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-cell">
            <div class="stat-value sv-approved" id="stat-approved">0</div>
            <div class="stat-label">Approved</div>
        </div>
        <div class="stat-cell">
            <div class="stat-value sv-rejected" id="stat-rejected">0</div>
            <div class="stat-label">Rejected</div>
        </div>
        <div class="stat-cell">
            <div class="stat-value sv-duplicate" id="stat-duplicate">0</div>
            <div class="stat-label">Duplicate</div>
        </div>
    </div>

    {{-- WORKSPACE --}}
    <div class="workspace">

        {{-- CAMERA PANEL --}}
        <div class="camera-panel">
            <canvas id="qr-canvas"></canvas>

            {{-- Placeholder --}}
            <div id="cam-placeholder" class="cam-placeholder">
                <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.89L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                </svg>
                <p>Camera inactive</p>
                <button class="btn-start" onclick="startCamera()">Start Scanning</button>
            </div>

            {{-- Active camera --}}
            <div id="cam-active">
                <video id="qr-video" autoplay playsinline muted></video>
                <div class="reticle">
                    <div class="rc tl"></div>
                    <div class="rc tr"></div>
                    <div class="rc bl"></div>
                    <div class="rc br"></div>
                    <div class="scan-line"></div>
                </div>
                <div class="scan-pill" id="scan-status">Scanning…</div>
                <button class="cam-stop-btn" onclick="stopCamera()">Stop</button>
            </div>
        </div>

        {{-- MOBILE BOTTOM BAR --}}
        <div class="bottom-bar">
            <div class="last-scan-row">
                <span class="last-scan-lbl">Last Scan</span>
                <span class="last-scan-info" id="last-scan-info">—</span>
            </div>
            <div class="demo-btns">
                <button class="demo-btn g" onclick="simulateScan('approved')">✓ Approved</button>
                <button class="demo-btn r" onclick="simulateScan('rejected')">✗ Rejected</button>
                <button class="demo-btn a" onclick="simulateScan('duplicate')">! Duplicate</button>
            </div>
        </div>

        {{-- DESKTOP RESULT PANEL --}}
        <aside class="result-panel">
            <div class="rp-body">
                <div class="rp-heading">Verification Result</div>

                {{-- Idle --}}
                <div id="panel-idle" class="rp-idle">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>Awaiting QR scan</p>
                </div>

                {{-- Loading --}}
                <div id="panel-loading" class="rp-loading">
                    <div class="spinner"></div>
                    <p style="font-size:13px;color:var(--ink-3)">Verifying…</p>
                </div>

                {{-- Approved --}}
                <div id="panel-approved" style="display:none;flex-direction:column;flex:1">
                    <span class="status-badge badge-approved">✓ APPROVED</span>
                    <div class="student-card">
                        <div class="avatar">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="stu-name" id="res-student-name"></div>
                            <div class="stu-matric" id="res-student-matric"></div>
                        </div>
                    </div>
                    <div class="meta-grid">
                        <div class="meta-cell span2">
                            <div class="meta-key">Token ID</div>
                            <div class="meta-val" id="res-token-id"></div>
                        </div>
                        <div class="meta-cell">
                            <div class="meta-key">Scanned At</div>
                            <div class="meta-val" id="res-timestamp" style="font-family:inherit"></div>
                        </div>
                        <div class="meta-cell">
                            <div class="meta-key">Decision</div>
                            <div class="meta-val ok">Access Granted</div>
                        </div>
                    </div>
                    <div class="action-btns">
                        <button class="btn-reset" onclick="resetResult()">Reset / Next Scan</button>
                    </div>
                </div>

                {{-- Rejected --}}
                <div id="panel-rejected" style="display:none;flex-direction:column;flex:1">
                    <span class="status-badge badge-rejected">✗ REJECTED</span>
                    <div class="alert-panel red">
                        <div class="alert-title">Access Denied</div>
                        <div class="alert-desc">QR code is invalid, tampered, expired, or belongs to an inactive session.</div>
                    </div>
                    <div class="action-btns">
                        <button class="btn-reset" onclick="resetResult()">Reset / Next Scan</button>
                    </div>
                </div>

                {{-- Duplicate --}}
                <div id="panel-duplicate" style="display:none;flex-direction:column;flex:1">
                    <span class="status-badge badge-duplicate">! DUPLICATE</span>
                    <div class="alert-panel amber">
                        <div class="alert-title">Already Scanned</div>
                        <div class="alert-desc">This QR code was already used. Possible duplicate entry attempt.</div>
                    </div>
                    <div class="action-btns">
                        <button class="btn-reset" onclick="resetResult()">Reset / Next Scan</button>
                    </div>
                </div>
            </div>

            {{-- Manual fallback --}}
            <div class="manual-section">
                <div class="manual-lbl">Manual QR JSON (fallback)</div>
                <textarea id="manual-qr" class="manual-ta"
                    placeholder='{"token_id":"...","encrypted_payload":"...","hmac_signature":"...","session_id":1}'></textarea>
                <button class="btn-manual" onclick="verifyManual()">Verify Manually</button>
            </div>
        </aside>

    </div>{{-- /workspace --}}

</div>{{-- /ex-page --}}

{{-- MOBILE TAKEOVERS --}}
<div id="takeover-approved" class="takeover tko-approved">
    <div class="tko-icon">✓</div>
    <div class="tko-title">APPROVED</div>
    <div class="tko-name" id="tko-name"></div>
    <div class="tko-matric" id="tko-matric"></div>
    <div class="tko-sub">Identity verified — access granted</div>
    <button class="btn-tko-dismiss" onclick="dismissTakeover()">Scan Next</button>
</div>

<div id="takeover-rejected" class="takeover tko-rejected">
    <div class="tko-icon">✗</div>
    <div class="tko-title">REJECTED</div>
    <div class="tko-sub">QR code invalid, tampered, or expired</div>
    <button class="btn-tko-dismiss" onclick="dismissTakeover()">Scan Next</button>
</div>

<div id="takeover-duplicate" class="takeover tko-duplicate">
    <div class="tko-icon">!</div>
    <div class="tko-title">DUPLICATE</div>
    <div class="tko-sub">This QR code was already used</div>
    <button class="btn-tko-dismiss" onclick="dismissTakeover()">Scan Next</button>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let videoStream = null;
let scanLoop    = null;
let lastScanned = null;
let isScanning  = false;

const counts = { total: 0, approved: 0, rejected: 0, duplicate: 0 };

function isMobile() { return window.innerWidth < 768; }

/* ── CAMERA ─────────────────────────────────────────────── */
async function startCamera() {
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
        });
        document.getElementById('qr-video').srcObject = videoStream;
        document.getElementById('cam-placeholder').style.display = 'none';
        document.getElementById('cam-active').style.display = 'block';
        isScanning = true;
        scanLoop = requestAnimationFrame(scanFrame);
    } catch (err) {
        alert('Camera access denied or unavailable: ' + err.message);
    }
}

function stopCamera() {
    if (videoStream) { videoStream.getTracks().forEach(t => t.stop()); videoStream = null; }
    if (scanLoop)    { cancelAnimationFrame(scanLoop); scanLoop = null; }
    isScanning = false;
    document.getElementById('cam-active').style.display = 'none';
    document.getElementById('cam-placeholder').style.display = 'flex';
}

function scanFrame() {
    const video  = document.getElementById('qr-video');
    const canvas = document.getElementById('qr-canvas');

    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const img  = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });

        if (code && code.data !== lastScanned) {
            lastScanned = code.data;
            setScanStatus('QR detected — verifying…');
            verifyQrData(code.data);
            return; // pause until reset
        }
    }
    if (isScanning) scanLoop = requestAnimationFrame(scanFrame);
}

/* ── VERIFY ─────────────────────────────────────────────── */
function verifyManual() {
    const raw = document.getElementById('manual-qr').value.trim();
    if (raw) verifyQrData(raw);
}

async function verifyQrData(rawJson) {
    let qrData;
    try { qrData = JSON.parse(rawJson); }
    catch { handleResult('rejected', null, null); return; }

    showPanel('loading');

    try {
        const resp = await fetch('/examiner/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ qr_data: qrData }),
        });

        const data = await resp.json();

        if (data.status === 'APPROVED' && data.student) {
            const name   = data.student.full_name ?? '';
            const matric = data.student.matric_no ?? '';
            document.getElementById('res-student-name').textContent   = name;
            document.getElementById('res-student-matric').textContent = matric;
            document.getElementById('res-token-id').textContent       = data.token_id ?? '';
            document.getElementById('res-timestamp').textContent      = formatTs(data.timestamp);
            document.getElementById('tko-name').textContent           = name;
            document.getElementById('tko-matric').textContent         = matric;
            handleResult('approved', name, matric);
        } else if (data.status === 'DUPLICATE') {
            handleResult('duplicate', null, null);
        } else {
            handleResult('rejected', null, null);
        }
    } catch {
        handleResult('rejected', null, null);
    }
}

/* ── RESULT HANDLING ────────────────────────────────────── */
function handleResult(type, name, matric) {
    counts.total++;
    counts[type]++;
    updateStats();

    const label = name ? `${type.toUpperCase()} — ${name}` : type.toUpperCase();
    document.getElementById('last-scan-info').textContent = label;

    showPanel(type);
    if (isMobile()) showTakeover(type);
}

function showPanel(type) {
    ['idle', 'loading', 'approved', 'rejected', 'duplicate'].forEach(t => {
        const el = document.getElementById('panel-' + t);
        if (!el) return;
        if (t === 'idle')    { el.style.display = (type === 'idle')    ? 'flex'  : 'none'; }
        else if (t === 'loading') { el.style.display = (type === 'loading') ? 'flex'  : 'none'; }
        else                 { el.style.display = (t === type)         ? 'flex'  : 'none'; }
    });
}

function showTakeover(type) {
    ['approved', 'rejected', 'duplicate'].forEach(t =>
        document.getElementById('takeover-' + t).classList.remove('show')
    );
    document.getElementById('takeover-' + type).classList.add('show');
    setTimeout(dismissTakeover, 3500);
}

function dismissTakeover() {
    ['approved', 'rejected', 'duplicate'].forEach(t =>
        document.getElementById('takeover-' + t).classList.remove('show')
    );
}

function resetResult() {
    lastScanned = null;
    isScanning  = !!videoStream;
    showPanel('idle');
    document.getElementById('manual-qr').value = '';
    setScanStatus('Scanning…');
    if (videoStream && isScanning) scanLoop = requestAnimationFrame(scanFrame);
}

function updateStats() {
    document.getElementById('stat-total').textContent     = counts.total;
    document.getElementById('stat-approved').textContent  = counts.approved;
    document.getElementById('stat-rejected').textContent  = counts.rejected;
    document.getElementById('stat-duplicate').textContent = counts.duplicate;
}

function setScanStatus(msg) {
    const el = document.getElementById('scan-status');
    if (el) el.textContent = msg;
}

function formatTs(ts) {
    if (!ts) return '';
    try { return new Date(ts).toLocaleTimeString(); } catch { return ts; }
}

/* ── DEMO SIMULATION ────────────────────────────────────── */
function simulateScan(type) {
    const demo = { full_name: 'Adewale Okonkwo', matric_no: 'CSC/2021/001' };
    counts.total++;
    counts[type]++;
    updateStats();

    const label = type === 'approved'
        ? `APPROVED — ${demo.full_name}`
        : type.toUpperCase();
    document.getElementById('last-scan-info').textContent = label;

    if (type === 'approved') {
        document.getElementById('res-student-name').textContent   = demo.full_name;
        document.getElementById('res-student-matric').textContent = demo.matric_no;
        document.getElementById('res-token-id').textContent       = 'demo-' + Date.now().toString(16);
        document.getElementById('res-timestamp').textContent      = new Date().toLocaleTimeString();
        document.getElementById('tko-name').textContent           = demo.full_name;
        document.getElementById('tko-matric').textContent         = demo.matric_no;
    }

    showPanel(type);
    if (isMobile()) showTakeover(type);
}
</script>
</body>
</html>
