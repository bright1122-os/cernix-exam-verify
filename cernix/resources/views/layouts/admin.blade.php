<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CERNIX — @yield('title', 'Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --navy: #374151;
            --navy-2: #1f2937;
            --navy-3: #111827;
            --blue: #64748b;
            --blue-2: #94a3b8;
            --emerald: #047857;
            --emerald-2: #34d399;
            --red: #b91c1c;
            --red-2: #f87171;
            --amber: #92400e;
            --amber-2: #fbbf24;
            --teal: #0f766e;
            --purple: #7c3aed;
            --orange: #ea580c;
            --bg: #f4f4ef;
            --bg-2: #ffffff;
            --card: #ffffff;
            --line: #e6e4dc;
            --line-2: #d7d4c8;
            --ink: #0a0f1f;
            --ink-2: #3b3f4c;
            --ink-3: #6b7085;
            --ink-4: #9ca1b3;
            --shadow-sm: 0 10px 28px rgba(17, 24, 39, .055);
            --shadow: 0 22px 55px -18px rgba(17,24,39,.16), 0 6px 18px -12px rgba(17,24,39,.12);
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body { font-family: Inter, system-ui, sans-serif; background: var(--bg); color: var(--ink); line-height: 1.5; }
        a { color: inherit; }
        button, input, select, textarea { font: inherit; }
        button, .clickable { cursor: pointer; }
        .admin-app { min-height: 100vh; display: grid; grid-template-columns: 252px minmax(0, 1fr); }
        .admin-sidebar {
            position: fixed; inset: 0 auto 0 0; width: 252px; z-index: 30;
            background: rgba(255,255,255,.76); color: var(--ink); display: flex; flex-direction: column;
            padding: 22px 16px; overflow-y: auto;
            border-right: 1px solid var(--line);
            backdrop-filter: blur(14px);
        }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; letter-spacing: .08em; margin-bottom: 28px; }
        .brand-logo { width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; background: #fff; border: 1px solid var(--line); overflow: hidden; }
        .brand-logo img { width: 36px; height: 36px; object-fit: contain; display: block; }
        .admin-nav { display: grid; gap: 5px; }
        .admin-nav a {
            display: flex; align-items: center; gap: 10px; min-height: 42px; padding: 0 12px;
            border-radius: 999px; color: var(--ink-3); text-decoration: none;
            font-size: 14px; font-weight: 600; transition: background .15s, color .15s, transform .15s;
        }
        .admin-nav a:hover { background: rgba(17,24,39,.045); color: var(--ink); transform: translateX(1px); }
        .admin-nav a.active { background: #fff; color: var(--ink); box-shadow: var(--shadow-sm); border: 1px solid var(--line); }
        .admin-nav svg { flex: 0 0 auto; }
        .sidebar-footer { margin-top: auto; border-top: 1px solid var(--line); padding-top: 16px; }
        .sidebar-user { display: grid; grid-template-columns: 36px 1fr; gap: 10px; align-items: center; }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%; display: grid; place-items: center;
            background: var(--ink); color: #fff; font-weight: 800;
        }
        .sidebar-user b { display: block; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user span { display: block; font-size: 12px; color: var(--ink-3); }
        .logout-form { margin: 0; }
        .logout-link { display: inline-block; margin-top: 14px; color: var(--ink-3); font-size: 13px; text-decoration: none; background: transparent; border: 0; padding: 0; cursor: pointer; }
        .logout-link:hover { color: var(--ink); }
        .admin-main { grid-column: 2; min-width: 0; }
        .admin-header {
            position: sticky; top: 0; z-index: 20; min-height: 72px; background: rgba(255,255,255,.92);
            backdrop-filter: blur(10px); border-bottom: 1px solid var(--line);
            display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 0 32px;
        }
        .header-title h1 { margin: 0; font-size: 22px; line-height: 1.2; font-weight: 700; color: var(--ink); }
        .breadcrumb { color: var(--ink-3); font-size: 13px; margin-top: 4px; }
        .header-user { display: flex; align-items: center; gap: 10px; color: var(--ink-2); font-size: 13px; font-weight: 600; }
        .header-user .avatar { background: var(--navy); }
        .hamburger { display: none; width: 40px; height: 40px; border: 1px solid var(--line); border-radius: 10px; background: #fff; }
        .admin-content { padding: 32px; }
        .content-inner { width: 100%; max-width: 1200px; margin: 0 auto; display: grid; gap: 32px; }
        .card { background: rgba(255,255,255,.88); border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid var(--line); overflow: hidden; }
        .card-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 20px 22px; border-bottom: 1px solid var(--line); }
        .card-head h2 { margin: 0; font-size: 17px; line-height: 1.2; font-weight: 700; color: var(--ink); }
        .card-body { padding: 22px; }
        .muted { color: var(--ink-3); font-size: 13px; }
        .mono { font-family: 'JetBrains Mono', ui-monospace, monospace; }
        .section-copy { margin: 6px 0 0; color: var(--ink-3); font-size: 13px; line-height: 1.55; max-width: 660px; }
        .admin-hero { display: grid; grid-template-columns: minmax(0, 1fr) minmax(280px, 380px); gap: 20px; align-items: stretch; }
        .institution-card {
            display: flex; align-items: center; gap: 14px; padding: 18px 20px;
            background: rgba(255,255,255,.72); border: 1px solid var(--line); border-radius: 16px;
        }
        .institution-card img { width: 46px; height: 46px; object-fit: contain; flex-shrink: 0; }
        .institution-card b { display: block; font-size: 18px; line-height: 1.15; color: var(--navy); letter-spacing: -.02em; }
        .institution-card span { display: block; margin-top: 4px; font-size: 13px; color: var(--ink-4); }
        .active-session-card {
            padding: 20px; border-radius: 16px; border: 1px solid var(--line-2);
            background: rgba(17,17,17,.035); display: grid; gap: 12px;
        }
        .eyebrow { font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--ink-3); }
        .active-session-card strong { font-size: 20px; color: var(--ink); line-height: 1.2; }
        .chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .soft-chip {
            display: inline-flex; align-items: center; gap: 7px; min-height: 32px; padding: 0 12px;
            border-radius: 999px; background: #fff; border: 1px solid var(--line); color: var(--ink-3);
            font-size: 12px; font-weight: 600;
        }
        .soft-chip::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--emerald); box-shadow: 0 0 0 4px rgba(5,150,105,.12); }
        .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
        .stat-card { position: relative; min-height: 142px; padding: 22px; border-top: 4px solid var(--blue); }
        .stat-card.success { border-top-color: var(--emerald); }
        .stat-card.warning { border-top-color: var(--amber); }
        .stat-card.info { border-top-color: var(--teal); }
        .stat-card.danger { border-top-color: var(--red); }
        .stat-card.navy { border-top-color: var(--navy); }
        .stat-card svg { position: absolute; top: 18px; right: 18px; color: var(--ink-4); }
        .stat-label { font-size: 13px; color: var(--ink-3); font-weight: 700; }
        .stat-value { margin-top: 18px; font-size: 32px; line-height: 1.1; font-weight: 800; color: var(--ink); }
        .stat-help { margin-top: 5px; font-size: 13px; color: var(--ink-3); }
        .decision-layout { display: grid; grid-template-columns: 220px minmax(0, 1fr); gap: 24px; align-items: center; }
        .donut {
            --approved: 0deg; --rejected: 0deg;
            width: 180px; aspect-ratio: 1; border-radius: 50%; margin: 0 auto;
            background: conic-gradient(var(--emerald) 0 var(--approved), var(--red) var(--approved) calc(var(--approved) + var(--rejected)), var(--amber) calc(var(--approved) + var(--rejected)) 360deg);
            display: grid; place-items: center; box-shadow: inset 0 0 0 1px var(--line);
        }
        .donut::after { content: attr(data-total); width: 112px; aspect-ratio: 1; border-radius: 50%; background: #fff; display: grid; place-items: center; color: var(--ink); font-size: 28px; font-weight: 800; box-shadow: 0 0 0 1px var(--line); }
        .metric-list { display: grid; gap: 10px; }
        .metric-row { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-top: 1px solid var(--line); color: var(--ink-2); font-size: 14px; }
        .metric-row:first-child { border-top: 0; }
        .metric-row b { color: var(--ink); }
        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 780px; }
        th { padding: 12px 16px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: var(--ink-3); font-weight: 700; background: #f9fafb; }
        td { padding: 12px 16px; border-top: 1px solid var(--line); color: var(--ink-2); font-size: 14px; vertical-align: middle; }
        tr:hover td { background: #fbfcfe; }
        .truncate { max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; }
        .badge.green { background: rgba(4,120,87,.11); color: var(--emerald); }
        .badge.red { background: rgba(185,28,28,.10); color: var(--red); }
        .badge.yellow { background: #fef3c7; color: #92400e; }
        .badge.gray { background: #e5e7eb; color: #374151; }
        .badge.blue { background: rgba(100,116,139,.12); color: #475569; }
        .badge.navy { background: rgba(17,24,39,.09); color: var(--ink); }
        .badge.amber { background: rgba(146,64,14,.12); color: var(--amber); }
        .btn { display: inline-flex; align-items: center; gap: 8px; min-height: 38px; padding: 0 13px; border-radius: 10px; border: 1px solid var(--line-2); background: #fff; color: var(--ink-2); text-decoration: none; font-size: 13px; font-weight: 700; transition: background .15s, border-color .15s, color .15s; }
        .btn:hover { background: #f9fafb; border-color: var(--ink-4); }
        .btn.primary { color: #fff; background: var(--ink); border-color: var(--ink); }
        .btn.primary:hover { background: var(--ink-2); }
        .btn.ghost { background: var(--bg-2); border-color: var(--line); }
        .btn.danger { color: var(--red); border-color: rgba(220,38,38,.25); }
        .btn.danger:hover { background: rgba(220,38,38,.06); }
        .link-actions { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
        .text-link { border: 0; background: transparent; color: var(--blue); padding: 0; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .text-link.danger { color: var(--red); }
        .text-link.warning { color: var(--amber); }
        .confirm-inline { display: inline-flex; gap: 8px; align-items: center; }
        .confirm-inline .confirm-question { display: none; color: var(--ink-3); font-size: 12px; }
        .confirm-inline.is-confirming .confirm-question { display: inline; }
        .confirm-inline.is-confirming .ask-confirm { display: none; }
        .confirm-btn, .cancel-btn { display: none; }
        .confirm-inline.is-confirming .confirm-btn, .confirm-inline.is-confirming .cancel-btn { display: inline-flex; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .form-grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .field { display: grid; gap: 7px; }
        .field label { color: var(--ink-2); font-size: 13px; font-weight: 700; }
        .field .hint { color: var(--ink-3); font-size: 12px; }
        input, select, textarea { width: 100%; min-height: 42px; border: 1.5px solid var(--line-2); border-radius: 12px; padding: 9px 11px; background: #fff; color: var(--ink); font-size: 14px; }
        input:focus, select:focus, textarea:focus { outline: 3px solid rgba(45,108,255,.14); border-color: var(--blue); }
        .notice { padding: 13px 15px; border-radius: 12px; background: rgba(5,150,105,.09); color: var(--emerald); font-size: 14px; font-weight: 700; }
        .notice.error { background: rgba(220,38,38,.08); color: var(--red); }
        .empty { padding: 44px 22px; text-align: center; color: var(--ink-3); }
        .bottom-grid { display: grid; grid-template-columns: minmax(0, 3fr) minmax(320px, 2fr); gap: 24px; }
        .activity-list { display: grid; gap: 16px; }
        .activity-item { display: grid; grid-template-columns: 12px minmax(0, 1fr); gap: 12px; align-items: start; }
        .dot { margin-top: 6px; width: 10px; height: 10px; border-radius: 50%; background: var(--ink-4); }
        .dot.student_registered { background: var(--blue); }
        .dot.qr_generated { background: var(--purple); }
        .dot.scan_pass { background: var(--emerald); }
        .dot.scan_fail { background: var(--red); }
        .dot.session_opened { background: var(--teal); }
        .dot.session_closed { background: #6b7280; }
        .dot.examiner_created { background: var(--orange); }
        .health-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-top: 1px solid var(--line); }
        .health-row:first-child { border-top: 0; }
        .status-inline { display: inline-flex; align-items: center; gap: 7px; font-size: 13px; font-weight: 800; }
        .status-inline::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--emerald); }
        .status-inline.bad::before { background: var(--red); }
        .quick-actions { display: grid; gap: 10px; }
        .pager { padding: 14px 18px; border-top: 1px solid var(--line); }
        .stack { display: grid; gap: 24px; }
        .stack.tight { gap: 14px; }
        .inline-search { display: flex; gap: 10px; align-items: center; }
        .inline-search input { min-width: 220px; }
        .check-control { display: flex; gap: 8px; align-items: center; min-height: 42px; color: var(--ink-2); font-size: 14px; }
        .check-control input { width: auto; min-height: 0; }
        .form-action { align-self: end; }
        .detail-grid { display: grid; grid-template-columns: minmax(280px, 1fr) minmax(0, 2fr); gap: 24px; align-items: start; }
        .detail-grid.balanced { grid-template-columns: minmax(280px, .95fr) minmax(0, 1.35fr); }
        .identity-card .card-head { align-items: flex-start; }
        .badge-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
        .meta-list { display: grid; gap: 14px; margin: 0 0 22px; }
        .meta-list div { display: grid; gap: 4px; padding-top: 14px; border-top: 1px solid var(--line); }
        .meta-list div:first-child { padding-top: 0; border-top: 0; }
        .meta-list dt { color: var(--ink-3); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
        .meta-list dd { margin: 0; color: var(--ink-2); font-size: 14px; }
        .stats-grid.compact { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .two-column { display: grid; grid-template-columns: minmax(0, 1fr) minmax(320px, .65fr); gap: 24px; align-items: start; }
        .portal-panel {
            border: 1px solid var(--line); border-radius: 16px; background: rgba(255,255,255,.62);
            padding: 18px; display: flex; gap: 14px; align-items: flex-start;
        }
        .portal-icon {
            width: 42px; height: 42px; border-radius: 12px; background: var(--ink);
            color: #fff; display: grid; place-items: center; flex-shrink: 0;
        }
        .portal-panel b { display: block; color: var(--ink); font-size: 15px; }
        .portal-panel span { display: block; margin-top: 3px; color: var(--ink-3); font-size: 13px; line-height: 1.45; }
        .person-cell { display: flex; align-items: center; gap: 11px; min-width: 0; }
        .student-avatar {
            width: 40px; height: 44px; border-radius: 10px; overflow: hidden; flex: 0 0 auto;
            background: var(--bg); border: 1px solid var(--line); display: grid; place-items: center;
            color: var(--ink-3); font-size: 12px; font-weight: 800; letter-spacing: .04em;
        }
        .student-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .student-avatar.large { width: 72px; height: 88px; border-radius: 12px; }
        .person-main { min-width: 0; }
        .person-main strong, .person-main span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .person-main span { margin-top: 2px; color: var(--ink-3); font-size: 12px; }
        .overlay { display: none; position: fixed; inset: 0; background: rgba(16,24,40,.45); z-index: 25; }
        @media (max-width: 1023px) {
            .admin-app { display: block; }
            .admin-sidebar { transform: translateX(-100%); transition: transform .2s ease; }
            .admin-sidebar.open { transform: translateX(0); }
            .overlay.show { display: block; }
            .admin-main { grid-column: auto; }
            .hamburger { display: grid; place-items: center; }
            .admin-header { padding: 0 20px; }
            .admin-content { padding: 24px 20px; }
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .stats-grid.compact { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .bottom-grid { grid-template-columns: 1fr; }
            .detail-grid { grid-template-columns: 1fr; }
            .two-column, .admin-hero, .decision-layout { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .header-user span { display: none; }
            .stats-grid, .stats-grid.compact, .form-grid, .form-grid.three { grid-template-columns: 1fr; }
            .admin-content { padding: 18px 14px; }
            .card-head { align-items: flex-start; flex-direction: column; }
            .stat-card { min-height: 120px; }
            .inline-search { width: 100%; flex-direction: column; align-items: stretch; }
            .inline-search input { min-width: 0; }
            .table-wrap { overflow: visible; }
            table.responsive-table { min-width: 0; border-collapse: separate; border-spacing: 0 12px; }
            table.responsive-table thead { display: none; }
            table.responsive-table, table.responsive-table tbody, table.responsive-table tr, table.responsive-table td { display: block; width: 100%; }
            table.responsive-table tr { border: 1px solid var(--line); border-radius: 14px; background: #fff; overflow: hidden; box-shadow: var(--shadow-sm); }
            table.responsive-table td { display: grid; grid-template-columns: 112px minmax(0, 1fr); gap: 10px; border-top: 1px solid var(--line); padding: 11px 14px; }
            table.responsive-table td:first-child { border-top: 0; }
            table.responsive-table td::before { content: attr(data-label); color: var(--ink-3); font-size: 11px; text-transform: uppercase; letter-spacing: .06em; font-weight: 800; }
            .donut { width: 150px; }
            .donut::after { width: 96px; font-size: 23px; }
        }
    </style>
    @stack('head')
</head>
<body>
@php
    $adminActor = $adminActor ?? null;
    $adminName = $adminActor->full_name ?? session('examiner_name', 'Admin');
    $initial = strtoupper(substr($adminName, 0, 1));
    $nav = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'],
        ['label' => 'Exam Sessions', 'route' => 'admin.sessions.index', 'icon' => 'M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z'],
        ['label' => 'Examiners', 'route' => 'admin.examiners.index', 'icon' => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75'],
        ['label' => 'Students', 'route' => 'admin.students.index', 'icon' => 'M4 19.5A2.5 2.5 0 016.5 17H20M4 4.5A2.5 2.5 0 016.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15z'],
        ['label' => 'Timetable', 'route' => 'admin.timetables.index', 'icon' => 'M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'],
        ['label' => 'Payments', 'route' => 'admin.payments.index', 'icon' => 'M21 8V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2v-1M3 10h18M7 15h.01M11 15h2'],
        ['label' => 'Scan Logs', 'route' => 'admin.scan-logs.index', 'icon' => 'M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Activity', 'route' => 'admin.activity.index', 'icon' => 'M3 3v18h18M7 15l4-4 3 3 5-7'],
        ['label' => 'Settings', 'route' => 'admin.settings.index', 'icon' => 'M12 15.5A3.5 3.5 0 1012 8a3.5 3.5 0 000 7.5zM19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06A1.65 1.65 0 0015 19.4a1.65 1.65 0 00-1 .6 1.65 1.65 0 01-2 0 1.65 1.65 0 00-1-.6 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-.6-1 1.65 1.65 0 010-2 1.65 1.65 0 00.6-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.6a1.65 1.65 0 001-.6 1.65 1.65 0 012 0 1.65 1.65 0 001 .6 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9c0 .36.12.7.33 1a1.65 1.65 0 010 2 1.65 1.65 0 00-.33 1z'],
    ];
@endphp
<div class="overlay" id="admin-overlay"></div>
<div class="admin-app">
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="brand"><span class="brand-logo"><img src="/aaua-logo.png" alt="Adekunle Ajasin University"></span><span>CERNIX</span></div>
        <nav class="admin-nav">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="{{ $item['icon'] }}"/></svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="avatar">{{ $initial }}</div>
                <div><b title="{{ $adminName }}">{{ $adminName }}</b><span>Administrator</span></div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-link">Logout</button>
            </form>
        </div>
    </aside>
    <main class="admin-main">
        <header class="admin-header">
            <button class="hamburger" id="admin-menu" type="button" aria-label="Open navigation">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="header-title">
                <h1>@yield('page_title', 'Dashboard')</h1>
                <div class="breadcrumb">@yield('breadcrumb', 'Admin / Dashboard')</div>
            </div>
            <div class="header-user">
                <span>{{ $adminName }}</span>
                <div class="avatar">{{ $initial }}</div>
            </div>
        </header>
        <section class="admin-content">
            <div class="content-inner">
                @if (session('status'))
                    <div class="notice">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="notice error">{{ $errors->first() }}</div>
                @endif
                @yield('content')
            </div>
        </section>
    </main>
</div>
<script>
const sidebar = document.getElementById('admin-sidebar');
const overlay = document.getElementById('admin-overlay');
document.getElementById('admin-menu')?.addEventListener('click', () => {
    sidebar.classList.add('open');
    overlay.classList.add('show');
});
overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
});
document.querySelectorAll('[data-confirm-inline]').forEach((wrap) => {
    wrap.querySelector('.ask-confirm')?.addEventListener('click', () => wrap.classList.add('is-confirming'));
    wrap.querySelector('.cancel-btn')?.addEventListener('click', () => wrap.classList.remove('is-confirming'));
});
</script>
@stack('scripts')
</body>
</html>
