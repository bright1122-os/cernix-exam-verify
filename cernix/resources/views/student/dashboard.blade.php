@extends('layouts.portal')

@section('title', 'Student Dashboard')

@php
    use Carbon\Carbon;

    $photoUrl = $student->photo_path ? url('/photo-thumb/' . basename($student->photo_path)) : null;
    $initials = strtoupper(substr(trim($student->full_name ?: $student->matric_no), 0, 1));
    $tokenStatus = $token->status ?? 'NOT GENERATED';
    $qrStatusLabel = match ($tokenStatus) {
        'USED' => 'Verified',
        'REVOKED' => 'Revoked',
        'UNUSED' => 'Pending Verification',
        default => 'Not Registered',
    };
    $examStatusClass = fn ($status) => match ($status) {
        'today' => 'green',
        'missed', 'cancelled' => 'red',
        default => 'yellow',
    };
    $examStatusLabel = fn ($status) => match ($status) {
        'today' => 'Today',
        'missed' => 'Missed',
        'cancelled' => 'Cancelled',
        default => 'Upcoming',
    };
@endphp

@section('content')
<style>
    .student-shell { min-height: 100vh; padding: 24px; background: var(--bg); }
    .student-page { width: 100%; max-width: 980px; margin: 0 auto; display: grid; gap: 18px; }
    .student-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: var(--shadow); overflow: hidden; }
    .student-head { padding: 22px; display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; border-bottom: 1px solid var(--line); }
    .student-identity { display: flex; gap: 16px; align-items: center; min-width: 0; }
    .student-photo { width: 72px; height: 88px; border-radius: 14px; border: 1px solid var(--line); background: var(--bg); overflow: hidden; display: grid; place-items: center; color: var(--ink-3); font-weight: 800; flex: 0 0 auto; }
    .student-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .student-head h1 { margin: 0 0 4px; font-size: 24px; line-height: 1.15; font-weight: 800; color: var(--ink); }
    .student-head p { margin: 0; font-size: 13px; color: var(--ink-3); }
    .student-body { padding: 22px; }
    .student-grid { display: grid; grid-template-columns: minmax(260px, .8fr) minmax(0, 1.2fr); gap: 18px; align-items: start; }
    .qr-box { width: 260px; max-width: 100%; margin: 0 auto; padding: 12px; background: #fff; border: 1px solid var(--line); border-radius: 14px; }
    .qr-box svg { width: 100%; height: auto; display: block; }
    .meta-list { display: grid; gap: 10px; }
    .meta-row { display: flex; justify-content: space-between; gap: 12px; padding: 11px 0; border-top: 1px solid var(--line); font-size: 14px; }
    .meta-row:first-child { border-top: 0; padding-top: 0; }
    .meta-row b { color: var(--ink); }
    .meta-row span { color: var(--ink-3); text-align: right; }
    .badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 6px 10px; font-size: 12px; font-weight: 800; }
    .badge.green { background: rgba(4,120,87,.11); color: var(--emerald); }
    .badge.yellow { background: #fef3c7; color: #92400e; }
    .badge.red { background: rgba(185,28,28,.10); color: var(--red); }
    .badge.gray { background: #e5e7eb; color: #374151; }
    .section-title { margin: 0 0 6px; font-size: 17px; font-weight: 800; color: var(--ink); }
    .section-copy { margin: 0 0 16px; color: var(--ink-3); font-size: 13px; }
    .exam-card { padding: 16px; border: 1px solid var(--line); border-radius: 14px; background: rgba(244,244,239,.55); display: grid; gap: 8px; }
    .exam-card strong { font-size: 18px; color: var(--ink); }
    .exam-card span, .empty { color: var(--ink-3); font-size: 13px; }
    .timetable-list { display: grid; gap: 10px; }
    .time-row { display: grid; grid-template-columns: 1fr auto; gap: 12px; padding: 14px 0; border-top: 1px solid var(--line); }
    .time-row:first-child { border-top: 0; }
    .time-row b { display: block; color: var(--ink); }
    .time-row small { color: var(--ink-3); }
    .logout-form { margin: 0; }
    .logout-form button, .refresh-link { color: var(--navy); font-weight: 800; font-size: 13px; background: transparent; border: 0; text-decoration: none; }
    @media (max-width: 760px) {
        .student-shell { padding: 18px; }
        .student-head { flex-direction: column; }
        .student-grid { grid-template-columns: 1fr; }
        .time-row { grid-template-columns: 1fr; }
    }
</style>
<main class="student-shell">
    <div class="student-page">
        <section class="student-card">
            <header class="student-head">
                <div class="student-identity">
                    <span class="student-photo">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="">
                        @else
                            {{ $initials }}
                        @endif
                    </span>
                    <div>
                        <h1>{{ $student->full_name }}</h1>
                        <p class="mono">{{ $student->matric_no }}</p>
                        <p>{{ $department->dept_name ?? 'Department unavailable' }} · {{ $student->level ?? 'Level unavailable' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('student.logout') }}" class="logout-form">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </header>
            <div class="student-body student-grid">
                <div>
                    @if ($qrSvg)
                        <div class="qr-box">{!! $qrSvg !!}</div>
                    @else
                        <div class="empty">No QR pass is available for this registration.</div>
                    @endif
                </div>
                <div class="meta-list">
                    <div class="meta-row"><b>Exam Session</b><span>{{ $session->name ?? $session->semester ?? 'Not assigned' }}</span></div>
                    <div class="meta-row"><b>Payment</b><span><span class="badge green">Verified</span></span></div>
                    <div class="meta-row"><b>Access Status</b><span><span class="badge {{ $tokenStatus === 'USED' ? 'green' : ($tokenStatus === 'REVOKED' ? 'red' : 'yellow') }}">{{ $qrStatusLabel }}</span></span></div>
                    <div class="meta-row"><b>QR Token</b><span class="mono">{{ $tokenStatus }}</span></div>
                    <div class="meta-row"><b>Registered</b><span>{{ $student->created_at ? Carbon::parse($student->created_at)->format('d M Y, H:i') : 'Unavailable' }}</span></div>
                    <div class="meta-row"><b>Refresh</b><span><a class="refresh-link" href="{{ route('student.dashboard') }}">Reload page</a></span></div>
                </div>
            </div>
        </section>

        <section class="student-card">
            <div class="student-body">
                <h2 class="section-title">Next Exam</h2>
                <p class="section-copy">Matched from your department, level, and active exam session.</p>
                @if($nextExam)
                    <div class="exam-card">
                        <span><span class="badge {{ $examStatusClass($nextExam->portal_status) }}">{{ $examStatusLabel($nextExam->portal_status) }}</span></span>
                        <strong>{{ $nextExam->course_code }}{{ $nextExam->course_title ? ' · ' . $nextExam->course_title : '' }}</strong>
                        <span>{{ Carbon::parse($nextExam->exam_date)->format('d M Y') }} · {{ substr($nextExam->start_time, 0, 5) }}{{ $nextExam->end_time ? ' - ' . substr($nextExam->end_time, 0, 5) : '' }}</span>
                        <span>Venue: {{ $nextExam->venue }}</span>
                    </div>
                @else
                    <div class="empty">No timetable entry has been published for your department and level yet.</div>
                @endif
            </div>
        </section>

        <section class="student-card">
            <div class="student-body">
                <h2 class="section-title">Full Timetable</h2>
                @if($timetable->count())
                    <div class="timetable-list">
                        @foreach($timetable as $entry)
                            <div class="time-row">
                                <div>
                                    <b>{{ $entry->course_code }}{{ $entry->course_title ? ' · ' . $entry->course_title : '' }}</b>
                                    <small>{{ Carbon::parse($entry->exam_date)->format('d M Y') }} · {{ substr($entry->start_time, 0, 5) }}{{ $entry->end_time ? ' - ' . substr($entry->end_time, 0, 5) : '' }} · {{ $entry->venue }}</small>
                                </div>
                                <span><span class="badge {{ $examStatusClass($entry->portal_status) }}">{{ $examStatusLabel($entry->portal_status) }}</span></span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty">No timetable entries found.</div>
                @endif
            </div>
        </section>
    </div>
</main>
@endsection
