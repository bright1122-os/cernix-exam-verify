@extends('layouts.portal')

@section('title', 'Admin Dashboard')

@section('content')
<style>
    .admin-wrap { display: flex; height: 100vh; background: var(--bg); }

    /* Sidebar */
    .admin-sidebar {
        width: 240px; background: var(--bg-2); border-right: 1px solid var(--line);
        display: flex; flex-direction: column; padding: 20px 12px;
        overflow-y: auto; position: relative; z-index: 100;
    }
    .admin-sidebar .logo-mini {
        font-size: 12px; font-weight: 700; letter-spacing: .1em;
        text-transform: uppercase; color: var(--navy); margin-bottom: 24px;
    }
    .admin-sidebar .nav-section { margin-bottom: 16px; }
    .admin-sidebar .nav-section-title {
        font-size: 10px; color: var(--ink-4); letter-spacing: .08em;
        text-transform: uppercase; padding: 8px 12px; font-weight: 600; margin-bottom: 4px;
    }
    .nav-item {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 10px; font-size: 13px; color: var(--ink-2);
        font-weight: 500; cursor: pointer; transition: background .15s; text-decoration: none;
    }
    .nav-item:hover { background: var(--bg); }
    .nav-item.on { background: var(--navy); color: #fff; }
    .nav-item svg { flex-shrink: 0; }
    .nav-spacer { flex: 1; }

    /* Main content */
    .admin-main {
        flex: 1; display: flex; flex-direction: column; overflow-y: auto;
    }
    .admin-header {
        padding: 24px 40px; border-bottom: 1px solid var(--line);
        display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;
    }
    .admin-header h1 { margin: 0; font-size: 20px; font-weight: 700; }
    .admin-header-right { display: flex; gap: 12px; align-items: center; }

    .admin-content { padding: 40px; flex: 1; overflow-y: auto; }

    /* Session hero */
    .session-hero {
        padding: 24px; border-radius: 18px; margin-bottom: 32px;
        background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
        color: #fff; position: relative; overflow: hidden;
    }
    .session-hero::before {
        content: ""; position: absolute; inset: 0;
        background: radial-gradient(circle at 10% 50%, rgba(91,141,255,.2), transparent 50%);
        pointer-events: none;
    }
    .session-hero > * { position: relative; z-index: 1; }
    .session-hero h2 { margin: 0; font-size: 20px; font-weight: 700; }
    .session-hero p { margin: 8px 0 0; font-size: 13px; color: rgba(255,255,255,.7); }
    .session-meta { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 20px; }
    .session-meta > div { display: flex; flex-direction: column; gap: 4px; }
    .session-meta .k { font-size: 10px; color: rgba(255,255,255,.6); letter-spacing: .06em; text-transform: uppercase; }
    .session-meta .v { font-size: 16px; font-weight: 700; }

    /* Stat grid */
    .stat-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px;
    }
    .stat-card {
        background: var(--bg-2); border: 1px solid var(--line); border-radius: 16px;
        padding: 20px; display: flex; flex-direction: column; gap: 4px;
    }
    .stat-card .k { font-size: 12px; color: var(--ink-3); letter-spacing: .06em; text-transform: uppercase; }
    .stat-card .v { font-size: 28px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
    .stat-card .trend { font-size: 11px; color: var(--emerald); }

    /* Tabs */
    .admin-tabs {
        display: flex; gap: 0; border-bottom: 1px solid var(--line); margin-bottom: 24px;
    }
    .admin-tabs button {
        padding: 14px 20px; font-size: 13px; font-weight: 600; color: var(--ink-3);
        border: none; background: none; cursor: pointer; border-bottom: 2px solid transparent;
        transition: all .15s; position: relative;
    }
    .admin-tabs button:hover { color: var(--ink-2); }
    .admin-tabs button.active {
        color: var(--navy); border-bottom-color: var(--navy);
    }

    /* Panel */
    .panel { background: var(--bg-2); border: 1px solid var(--line); border-radius: 16px; overflow: hidden; margin-bottom: 20px; }
    .panel-head { padding: 16px 20px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; }
    .panel-head h3 { margin: 0; font-size: 15px; font-weight: 600; }
    .panel-head .count { font-size: 11px; color: var(--ink-3); letter-spacing: .08em; }

    /* Log rows */
    .log-row {
        display: grid; grid-template-columns: 36px 1fr auto; gap: 12px; align-items: center;
        padding: 14px 20px; border-top: 1px solid var(--line); transition: background .15s;
    }
    .log-row:first-child { border-top: none; }
    .log-row:hover { background: var(--bg); }
    .log-row .n { font-size: 11px; color: var(--ink-4); font-family: 'JetBrains Mono', monospace; }
    .log-row .body b { display: block; font-size: 13px; font-weight: 500; }
    .log-row .body .sub { font-size: 11px; color: var(--ink-3); font-family: 'JetBrains Mono', monospace; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 260px; }
    .log-row .right { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
    .log-row .right .t { font-size: 11px; color: var(--ink-3); font-family: 'JetBrains Mono', monospace; white-space: nowrap; }
    .log-row .right .s { font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 600; }
    .log-row .right .s.approved { background: rgba(16,185,129,.12); color: var(--emerald); }
    .log-row .right .s.rejected { background: rgba(220,38,38,.12); color: var(--red); }
    .log-row .right .s.duplicate { background: rgba(180,83,9,.12); color: var(--amber); }
</style>

<div class="admin-wrap">
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="logo-mini">CERNIX</div>

        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="#" class="nav-item on">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12h12M9 6h12M9 18h12"/><path d="M3 6h2v12H3z"/>
                </svg>
                <span>Verification Logs</span>
            </a>
            <a href="#" class="nav-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/>
                </svg>
                <span>Audit Trail</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Setup</div>
            <a href="#" class="nav-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 6v6m0 0v6M18 12h6m-6 0H6"/>
                </svg>
                <span>Sessions</span>
            </a>
            <a href="#" class="nav-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 3l9 4.5L12 12 3 7.5 12 3z"/><path d="M3 11v4.5c0 .5 3 2.5 9 2.5s9-2 9-2.5V11"/>
                </svg>
                <span>Examiners</span>
            </a>
            <a href="#" class="nav-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 3a4 4 0 100 8 4 4 0 000-8z"/>
                </svg>
                <span>Students</span>
            </a>
        </div>

        <div class="nav-spacer"></div>

        <div class="nav-section">
            <a href="#" class="nav-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 3l9 4.5L12 12 3 7.5 12 3z"/><path d="M3 11v4.5c0 .5 3 2.5 9 2.5s9-2 9-2.5V11"/>
                </svg>
                <span>Settings</span>
            </a>
            <a href="#" class="nav-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main content -->
    <div class="admin-main">
        <!-- Header -->
        <div class="admin-header">
            <h1>Dashboard</h1>
            <div class="admin-header-right">
                <div class="chip emerald">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    LIVE
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="admin-content">
            <!-- Session hero -->
            <div class="session-hero">
                <h2>Active Exam Session</h2>
                <p>All verifications are live and cryptographically logged</p>
                <div class="session-meta">
                    <div><span class="k">Semester</span><span class="v">Spring 2024</span></div>
                    <div><span class="k">Started</span><span class="v">14:30</span></div>
                    <div><span class="k">Duration</span><span class="v">4.5h</span></div>
                    <div><span class="k">Halls</span><span class="v">12</span></div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stat-grid">
                <div class="stat-card">
                    <span class="k">Total Admitted</span>
                    <span class="v">842</span>
                    <span class="trend">↑ 12% from last session</span>
                </div>
                <div class="stat-card">
                    <span class="k">Verified Tokens</span>
                    <span class="v">841</span>
                    <span class="trend">99.88% success rate</span>
                </div>
                <div class="stat-card">
                    <span class="k">Rejected</span>
                    <span class="v">1</span>
                    <span class="trend">0.12% invalid/duplicate</span>
                </div>
                <div class="stat-card">
                    <span class="k">Active Examiners</span>
                    <span class="v">12</span>
                    <span class="trend">All halls staffed</span>
                </div>
            </div>

            <!-- Verification logs -->
            <div style="margin-bottom: 40px;">
                <div class="admin-tabs">
                    <button class="active">Verification Logs</button>
                    <button>Audit Trail</button>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h3>Recent Admissions</h3>
                        <span class="count">842 total today</span>
                    </div>
                    <!-- Log rows -->
                    <div class="log-row">
                        <span class="n">#842</span>
                        <div class="body">
                            <b>Adeyemi, Kunle O.</b>
                            <span class="sub">CSC/2021/048 — Hall B3</span>
                        </div>
                        <div class="right">
                            <span class="t">14:58:12</span>
                            <span class="s approved">APPROVED</span>
                        </div>
                    </div>
                    <div class="log-row">
                        <span class="n">#841</span>
                        <div class="body">
                            <b>Okonkwo, Eze P.</b>
                            <span class="sub">CSC/2021/052 — Hall A2</span>
                        </div>
                        <div class="right">
                            <span class="t">14:57:48</span>
                            <span class="s approved">APPROVED</span>
                        </div>
                    </div>
                    <div class="log-row">
                        <span class="n">#840</span>
                        <div class="body">
                            <b>Nwankwo, Chineze M.</b>
                            <span class="sub">CSC/2021/019 — Hall C1</span>
                        </div>
                        <div class="right">
                            <span class="t">14:56:32</span>
                            <span class="s approved">APPROVED</span>
                        </div>
                    </div>
                    <div class="log-row">
                        <span class="n">#839</span>
                        <div class="body">
                            <b>Obiora, Ifeanyi L.</b>
                            <span class="sub">CSC/2021/011 — Hall A1</span>
                        </div>
                        <div class="right">
                            <span class="t">14:55:14</span>
                            <span class="s duplicate">DUPLICATE</span>
                        </div>
                    </div>
                    <div class="log-row">
                        <span class="n">#838</span>
                        <div class="body">
                            <b>Ogbonna, Chisom A.</b>
                            <span class="sub">CSC/2021/033 — Hall B2</span>
                        </div>
                        <div class="right">
                            <span class="t">14:54:01</span>
                            <span class="s approved">APPROVED</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit trail -->
            <div>
                <div class="panel">
                    <div class="panel-head">
                        <h3>System Audit Log</h3>
                        <span class="count">12,847 events today</span>
                    </div>
                    <!-- Log rows -->
                    <div class="log-row">
                        <span class="n">⚙️</span>
                        <div class="body">
                            <b>Session initialized</b>
                            <span class="sub">exam_session_id: 2024_spring_001</span>
                        </div>
                        <div class="right">
                            <span class="t">10:00:00</span>
                        </div>
                    </div>
                    <div class="log-row">
                        <span class="n">👤</span>
                        <div class="body">
                            <b>12 examiners registered</b>
                            <span class="sub">status: all_authenticated</span>
                        </div>
                        <div class="right">
                            <span class="t">10:15:23</span>
                        </div>
                    </div>
                    <div class="log-row">
                        <span class="n">✓</span>
                        <div class="body">
                            <b>Verification started</b>
                            <span class="sub">hall_count: 12 | examiner_count: 12</span>
                        </div>
                        <div class="right">
                            <span class="t">11:00:45</span>
                        </div>
                    </div>
                    <div class="log-row">
                        <span class="n">🔐</span>
                        <div class="body">
                            <b>Cryptographic keys rotated</b>
                            <span class="sub">algorithm: AES-256-GCM | signature: HMAC-SHA256</span>
                        </div>
                        <div class="right">
                            <span class="t">13:30:12</span>
                        </div>
                    </div>
                    <div class="log-row">
                        <span class="n">📊</span>
                        <div class="body">
                            <b>Hourly stats checkpoint</b>
                            <span class="sub">verified: 421 | rejected: 0 | pending: 0</span>
                        </div>
                        <div class="right">
                            <span class="t">14:00:00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
