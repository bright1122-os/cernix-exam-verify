@extends('layouts.admin')

@section('title', $examiner->full_name)
@section('page_title', $examiner->full_name)
@section('breadcrumb', 'Admin / Examiners / ' . $examiner->full_name)

@php use Carbon\Carbon; @endphp

@section('content')
<div class="detail-grid balanced">
    <section class="card identity-card">
        <div class="card-head">
            <div>
                <h2>{{ $examiner->full_name }}</h2>
                <p class="section-copy mono">{{ $examiner->username }}</p>
            </div>
        </div>
        <div class="card-body">
            <div class="badge-row">
                <span class="badge navy">{{ ucfirst(strtolower($examiner->role)) }}</span>
                <span class="badge {{ $examiner->is_active ? 'green' : 'gray' }}">{{ $examiner->is_active ? 'Active' : 'Inactive' }}</span>
            </div>

            <dl class="meta-list">
                <div>
                    <dt>Date Created</dt>
                    <dd>{{ $examiner->created_at ? Carbon::parse($examiner->created_at)->format('d M Y, H:i') : 'Not recorded' }}</dd>
                </div>
                <div>
                    <dt>Last Active</dt>
                    <dd>{{ $examiner->last_active_at ? Carbon::parse($examiner->last_active_at)->diffForHumans() : 'Not recorded' }}</dd>
                </div>
            </dl>

            <a href="{{ route('admin.examiners.index') }}" class="btn ghost">Back to Examiners</a>
        </div>
    </section>

    <section class="stats-grid compact">
        <article class="card stat-card">
            <div class="stat-label">Sessions</div>
            <div class="stat-value">{{ number_format($totalSessions) }}</div>
            <div class="stat-help">Assigned sessions</div>
        </article>
        <article class="card stat-card success">
            <div class="stat-label">Students</div>
            <div class="stat-value">{{ number_format($totalStudents) }}</div>
            <div class="stat-help">Across sessions</div>
        </article>
        <article class="card stat-card info">
            <div class="stat-label">Scans</div>
            <div class="stat-value">{{ number_format($totalScans) }}</div>
            <div class="stat-help">Recorded attempts</div>
        </article>
        <article class="card stat-card success">
            <div class="stat-label">Approved</div>
            <div class="stat-value">{{ number_format((int) ($decisionCounts['APPROVED'] ?? 0)) }}</div>
            <div class="stat-help">Valid passes</div>
        </article>
        <article class="card stat-card danger">
            <div class="stat-label">Rejected</div>
            <div class="stat-value">{{ number_format((int) ($decisionCounts['REJECTED'] ?? 0)) }}</div>
            <div class="stat-help">Failed attempts</div>
        </article>
        <article class="card stat-card warning">
            <div class="stat-label">Duplicate</div>
            <div class="stat-value">{{ number_format((int) ($decisionCounts['DUPLICATE'] ?? 0)) }}</div>
            <div class="stat-help">Already used</div>
        </article>
    </section>
</div>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Assigned Sessions</h2>
            <p class="section-copy">Sessions and student loads linked to this examiner.</p>
        </div>
    </div>
    @if($sessions->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Session</th>
                        <th>Students</th>
                        <th>Scans</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                        <tr>
                            <td data-label="Session"><a class="text-link" href="{{ route('admin.sessions.show', $session->session_id) }}">{{ $session->name ?: $session->semester }}</a></td>
                            <td data-label="Students">{{ number_format($session->student_count) }}</td>
                            <td data-label="Scans">{{ number_format($session->scan_count) }}</td>
                            <td data-label="Status"><span class="badge {{ $session->status_class }}">{{ $session->status_text }}</span></td>
                            <td data-label="Date">{{ $session->created_at ? Carbon::parse($session->created_at)->format('d M Y, H:i') : 'Not recorded' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">No sessions assigned yet</div>
    @endif
</section>

<section class="two-column">
    <article class="card">
        <div class="card-head"><h2>Recent Scan Activity</h2></div>
        @if($recentScans->count())
            <div class="table-wrap">
                <table class="responsive-table">
                    <thead><tr><th>Student</th><th>Decision</th><th>Time</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach($recentScans as $scan)
                            <tr>
                                <td data-label="Student"><strong class="mono">{{ $scan->student_id ?? 'Unavailable' }}</strong><div class="muted">{{ $scan->student_name ?? 'Student unavailable' }}</div></td>
                                <td data-label="Decision"><span class="badge {{ $scan->decision === 'APPROVED' ? 'green' : ($scan->decision === 'DUPLICATE' ? 'amber' : 'red') }}">{{ ucfirst(strtolower($scan->decision)) }}</span></td>
                                <td data-label="Time">{{ Carbon::parse($scan->timestamp)->format('d M Y, H:i') }}</td>
                                <td data-label="Action"><a class="text-link" href="{{ route('admin.scan-logs.show', $scan->log_id) }}">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">No scans recorded by this examiner.</div>
        @endif
    </article>
    <article class="card">
        <div class="card-head"><h2>Activity Trail</h2></div>
        <div class="card-body">
            @if($activity->count())
                <div class="activity-list">
                    @foreach($activity as $event)
                        <div class="activity-item">
                            <span class="dot {{ str_replace('.', '_', $event->event_type) }}"></span>
                            <div>
                                <div>{{ $event->description }}</div>
                                <div class="muted">{{ $event->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">No activity events found.</div>
            @endif
        </div>
    </article>
</section>
@endsection
