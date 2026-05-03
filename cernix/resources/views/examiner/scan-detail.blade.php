@extends('layouts.portal')

@section('title', 'Scan Detail')

@php
    use Carbon\Carbon;
    $photoUrl = $scan->photo_path ? url('/photo-thumb/' . basename($scan->photo_path)) : null;
    $initials = strtoupper(substr(trim($scan->full_name ?: $scan->student_id ?: '?'), 0, 1));
    $badge = fn ($decision) => $decision === 'APPROVED' ? 'green' : ($decision === 'DUPLICATE' ? 'yellow' : 'red');
@endphp

@section('content')
<style>
    .scan-page { min-height: 100vh; padding: 24px; background: var(--bg); color: var(--ink); overflow-x: hidden; }
    .scan-wrap { max-width: 980px; margin: 0 auto; display: grid; gap: 18px; }
    .scan-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: var(--shadow); overflow: hidden; }
    .scan-head { padding: 20px 22px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
    .scan-head h1, .scan-card h2 { margin: 0; color: var(--ink); }
    .scan-head p { margin: 4px 0 0; color: var(--ink-3); font-size: 13px; }
    .scan-body { padding: 22px; }
    .identity { display: flex; gap: 16px; align-items: flex-start; }
    .photo { width: 82px; height: 100px; border-radius: 14px; border: 1px solid var(--line); background: var(--bg); display: grid; place-items: center; overflow: hidden; font-weight: 800; color: var(--ink-3); flex: 0 0 auto; }
    .photo img { width: 100%; height: 100%; object-fit: cover; }
    .badge { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-weight: 800; font-size: 12px; }
    .badge.green { background: rgba(4,120,87,.11); color: var(--emerald); }
    .badge.yellow { background: #fef3c7; color: #92400e; }
    .badge.red { background: rgba(185,28,28,.10); color: var(--red); }
    .grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .metric { border: 1px solid var(--line); border-radius: 14px; padding: 14px; background: rgba(255,255,255,.72); }
    .metric span { display: block; color: var(--ink-3); font-size: 12px; font-weight: 700; }
    .metric b { display: block; margin-top: 8px; font-size: 22px; }
    .meta { display: grid; gap: 10px; margin-top: 16px; }
    .row { display: flex; justify-content: space-between; gap: 12px; padding-top: 10px; border-top: 1px solid var(--line); font-size: 14px; }
    .row:first-child { border-top: 0; padding-top: 0; }
    .row span { color: var(--ink-3); text-align: right; overflow-wrap: anywhere; }
    .table-scroll { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 11px 12px; border-top: 1px solid var(--line); text-align: left; font-size: 13px; }
    th { color: var(--ink-3); text-transform: uppercase; letter-spacing: .05em; font-size: 11px; }
    .actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 0 14px; border-radius: 12px; border: 1px solid var(--line); background: #fff; color: var(--ink); text-decoration: none; font-weight: 800; font-size: 13px; }
    .empty { color: var(--ink-3); font-size: 13px; padding: 18px 0; }
    @media (max-width: 760px) {
        .scan-page { padding: 16px; }
        .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .scan-head { flex-direction: column; padding: 18px; }
        .scan-body { padding: 18px; }
        .identity { align-items: flex-start; }
        .identity h2 { overflow-wrap: anywhere; }
        .row { display: grid; grid-template-columns: 1fr; gap: 4px; }
        .row span { text-align: left; }
        table { min-width: 560px; }
        .actions, .btn { width: 100%; }
    }
    @media (max-width: 380px) {
        .scan-page { padding: 12px; }
        .grid { grid-template-columns: 1fr; }
        .photo { width: 72px; height: 88px; }
    }
</style>

<main class="scan-page">
    <div class="scan-wrap">
        <section class="scan-card">
            <header class="scan-head">
                <div>
                    <h1>Scan Detail</h1>
                    <p>{{ $examiner['full_name'] }} · {{ Carbon::parse($scan->timestamp)->format('d M Y, H:i') }}</p>
                </div>
                <div class="actions">
                    <a class="btn" href="{{ route('examiner.dashboard') }}">Back to Dashboard</a>
                </div>
            </header>
            <div class="scan-body">
                <div class="identity">
                    <span class="photo">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="">
                        @else
                            {{ $initials }}
                        @endif
                    </span>
                    <div>
                        <h2>{{ $scan->full_name ?? 'Student unavailable' }}</h2>
                        <p class="mono">{{ $scan->student_id ?? 'Unavailable' }}</p>
                        <p>{{ $scan->dept_name ?? 'Department unavailable' }} · {{ $scan->level ?? 'Level unavailable' }}</p>
                        <p><span class="badge {{ $badge($scan->decision) }}">{{ ucfirst(strtolower($scan->decision)) }}</span></p>
                    </div>
                </div>
                <div class="meta">
                    <div class="row"><b>QR Token</b><span class="mono">{{ $scan->token_id }}</span></div>
                    <div class="row"><b>Token Status</b><span>{{ $scan->token_status ?? 'Unavailable' }}</span></div>
                    <div class="row"><b>Session</b><span>{{ trim(($scan->session_name ?: $scan->semester).' '.$scan->academic_year) ?: 'Unavailable' }}</span></div>
                    <div class="row"><b>Device / IP</b><span class="mono">{{ $scan->device_fp }} · {{ $scan->ip_address }}</span></div>
                </div>
            </div>
        </section>

        <section class="grid">
            <article class="metric"><span>Total Scans</span><b>{{ number_format((int) $scanCounts->sum()) }}</b></article>
            <article class="metric"><span>Approved</span><b>{{ number_format((int) ($scanCounts['APPROVED'] ?? 0)) }}</b></article>
            <article class="metric"><span>Rejected</span><b>{{ number_format((int) ($scanCounts['REJECTED'] ?? 0)) }}</b></article>
            <article class="metric"><span>Duplicate</span><b>{{ number_format((int) ($scanCounts['DUPLICATE'] ?? 0)) }}</b></article>
        </section>

        <section class="scan-card">
            <div class="scan-head"><h2>Today’s Exam</h2></div>
            <div class="scan-body">
                @if($todayExam)
                    <div class="meta">
                        <div class="row"><b>Course</b><span>{{ $todayExam->course_code }}{{ $todayExam->course_title ? ' · '.$todayExam->course_title : '' }}</span></div>
                        <div class="row"><b>Time</b><span>{{ substr($todayExam->start_time, 0, 5) }}{{ $todayExam->end_time ? ' - '.substr($todayExam->end_time, 0, 5) : '' }}</span></div>
                        <div class="row"><b>Venue</b><span>{{ $todayExam->venue }}</span></div>
                    </div>
                @else
                    <div class="empty">No exam is scheduled today for this student’s department, level, and session.</div>
                @endif
            </div>
        </section>

        <section class="scan-card">
            <div class="scan-head"><h2>Previous Scan History</h2></div>
            @if($studentHistory->count())
                <div class="table-scroll">
                    <table>
                        <thead><tr><th>Decision</th><th>Examiner</th><th>Timestamp</th><th>Device</th></tr></thead>
                        <tbody>
                            @foreach($studentHistory as $history)
                                <tr>
                                    <td><span class="badge {{ $badge($history->decision) }}">{{ ucfirst(strtolower($history->decision)) }}</span></td>
                                    <td>{{ $history->examiner_name ?? 'Unknown' }}</td>
                                    <td>{{ Carbon::parse($history->timestamp)->format('d M Y, H:i') }}</td>
                                    <td class="mono">{{ $history->device_fp }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="scan-body"><div class="empty">No previous scan history found.</div></div>
            @endif
        </section>
    </div>
</main>
@endsection
