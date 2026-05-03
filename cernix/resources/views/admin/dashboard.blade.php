@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard')
@section('breadcrumb', 'Admin / Dashboard')

@php
    use Carbon\Carbon;
    $sessionName = fn ($session) => $session->name ?: $session->semester;
    $sessionStatus = function ($session) {
        if ($session->is_active) return ['Active', 'green'];
        if ($session->scheduled_start && Carbon::parse($session->scheduled_start)->isFuture()) return ['Pending', 'yellow'];
        return ['Closed', 'gray'];
    };
    $activityClass = fn ($type) => str_replace('.', '_', $type);
    $decisionClass = fn ($decision) => match ($decision) {
        'APPROVED' => 'green',
        'DUPLICATE' => 'amber',
        default => 'red',
    };
    $decisionLabel = fn ($decision) => match ($decision) {
        'APPROVED' => 'Approved',
        'DUPLICATE' => 'Duplicate',
        default => 'Rejected',
    };
    $approvedDeg = $totalScans ? round(($approvedScans / $totalScans) * 360, 1) : 0;
    $rejectedDeg = $totalScans ? round(($rejectedScans / $totalScans) * 360, 1) : 0;
@endphp

@section('content')
<section class="admin-hero">
    <div class="institution-card">
        <img src="/aaua-logo.png" alt="Adekunle Ajasin University">
        <div>
            <b>Adekunle Ajasin University</b>
            <span>Faculty of Computing · CERNIX Admin Control</span>
        </div>
    </div>
    <div class="active-session-card">
        <div class="eyebrow">Active Session</div>
        @if ($activeSession)
            <strong>{{ $sessionName($activeSession) }}</strong>
            <div class="chip-row">
                <span class="soft-chip">{{ number_format($activeSession->student_count) }} students</span>
                <span class="soft-chip">{{ $activeSession->examiner_name ?? 'Unassigned examiner' }}</span>
            </div>
        @else
            <strong>No active session</strong>
            <span class="muted">Create or activate a session before exam verification starts.</span>
        @endif
    </div>
</section>

<section class="stats-grid">
    <article class="card stat-card navy">
        <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="stat-label">Total Scans</div>
        <div class="stat-value">{{ number_format($totalScans) }}</div>
        <div class="stat-help">All verification attempts</div>
    </article>
    <article class="card stat-card success">
        <div class="stat-label">Approved</div>
        <div class="stat-value">{{ number_format($approvedScans) }}</div>
        <div class="stat-help">Valid authenticated passes</div>
    </article>
    <article class="card stat-card danger">
        <div class="stat-label">Rejected</div>
        <div class="stat-value">{{ number_format($rejectedScans) }}</div>
        <div class="stat-help">Invalid or failed attempts</div>
    </article>
    <article class="card stat-card warning">
        <div class="stat-label">Duplicate</div>
        <div class="stat-value">{{ number_format($duplicateScans) }}</div>
        <div class="stat-help">Already-used valid tokens</div>
    </article>
</section>

<section class="stats-grid">
    <article class="card stat-card">
        <div class="stat-label">Students</div>
        <div class="stat-value">{{ number_format($totalStudents) }}</div>
        <div class="stat-help">Total registered</div>
    </article>
    <article class="card stat-card success">
        <div class="stat-label">Active Examiners</div>
        <div class="stat-value">{{ number_format($totalExaminers) }}</div>
        <div class="stat-help">Enabled scanner accounts</div>
    </article>
    <article class="card stat-card info">
        <div class="stat-label">Sessions</div>
        <div class="stat-value">{{ number_format($activeSessions) }}</div>
        <div class="stat-help">Currently active</div>
    </article>
    <article class="card stat-card warning">
        <div class="stat-label">Scans Today</div>
        <div class="stat-value">{{ number_format($scansToday) }}</div>
        <div class="stat-help">Verification attempts</div>
    </article>
</section>

<section class="stats-grid">
    <article class="card stat-card">
        <div class="stat-label">QR Tokens</div>
        <div class="stat-value">{{ number_format($totalTokens) }}</div>
        <div class="stat-help">Issued access passes</div>
    </article>
    <article class="card stat-card success">
        <div class="stat-label">Verified Payments</div>
        <div class="stat-value">{{ number_format($verifiedPayments) }}</div>
        <div class="stat-help">Remita records captured</div>
    </article>
    <article class="card stat-card warning">
        <div class="stat-label">Pending Payments</div>
        <div class="stat-value">{{ number_format($pendingPayments) }}</div>
        <div class="stat-help">Registered without payment record</div>
    </article>
    <article class="card stat-card info">
        <div class="stat-label">Today's Exams</div>
        <div class="stat-value">{{ number_format($todaysExams->count()) }}</div>
        <div class="stat-help">Published timetable entries</div>
    </article>
</section>

<section class="two-column">
    <article class="card">
        <div class="card-head">
            <div>
                <h2>Verification Mix</h2>
                <p class="section-copy">Approved, rejected, and duplicate scan decisions across the system.</p>
            </div>
        </div>
        <div class="card-body decision-layout">
            <div class="donut" data-total="{{ number_format($totalScans) }}" style="--approved: {{ $approvedDeg }}deg; --rejected: {{ $rejectedDeg }}deg"></div>
            <div class="metric-list">
                <div class="metric-row"><span><span class="badge green">Approved</span></span><b>{{ number_format($approvedScans) }}</b></div>
                <div class="metric-row"><span><span class="badge red">Rejected</span></span><b>{{ number_format($rejectedScans) }}</b></div>
                <div class="metric-row"><span><span class="badge amber">Duplicate</span></span><b>{{ number_format($duplicateScans) }}</b></div>
            </div>
        </div>
    </article>

    <article class="card">
        <div class="card-head">
            <div>
                <h2>System Status</h2>
                <p class="section-copy">Runtime checks for the live verification environment.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="health-row"><span>Database</span><span class="status-inline {{ $dbOk ? '' : 'bad' }}">{{ $dbOk ? 'OK' : 'Error' }}</span></div>
            <div class="health-row"><span>Storage</span><span class="status-inline {{ $storageOk ? '' : 'bad' }}">{{ $storageOk ? 'OK' : 'Error' }}</span></div>
            <div class="health-row"><span>App Environment</span><span class="status-inline">{{ $environment }}</span></div>
        </div>
    </article>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Today's Timetable</h2>
            <p class="section-copy">Exam entries scheduled for today across active departments and levels.</p>
        </div>
        <a class="btn" href="{{ route('admin.timetables.index', ['date' => today()->toDateString()]) }}">Open timetable</a>
    </div>
    @if($todaysExams->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead><tr><th>Course</th><th>Department</th><th>Level</th><th>Time</th><th>Venue</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($todaysExams as $exam)
                        <tr>
                            <td data-label="Course"><strong>{{ $exam->course_code }}</strong><div class="muted">{{ $exam->course_title }}</div></td>
                            <td data-label="Department">{{ $exam->dept_name }}</td>
                            <td data-label="Level">{{ $exam->level }}</td>
                            <td data-label="Time">{{ substr($exam->start_time, 0, 5) }}{{ $exam->end_time ? ' - ' . substr($exam->end_time, 0, 5) : '' }}</td>
                            <td data-label="Venue">{{ $exam->venue }}</td>
                            <td data-label="Status"><span class="badge {{ $exam->status === 'cancelled' ? 'red' : ($exam->status === 'completed' ? 'gray' : 'green') }}">{{ ucfirst($exam->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">No exams are scheduled for today.</div>
    @endif
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Recent Verification Logs</h2>
            <p class="section-copy">Latest scan decisions recorded by examiners.</p>
        </div>
        <a class="btn" href="{{ route('admin.scan-logs.index') }}">View all</a>
    </div>
    @if ($recentVerificationLogs->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Session</th>
                        <th>Examiner</th>
                        <th>Result</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentVerificationLogs as $log)
                        <tr>
                            <td data-label="Student">
                                <strong class="mono">{{ $log->student_id ?? 'Student unavailable' }}</strong>
                                <div class="muted">{{ $log->student_name ?? 'Student unavailable' }}</div>
                            </td>
                            <td data-label="Session" class="truncate" title="{{ trim(($log->session_name ?: $log->semester) . ' ' . $log->academic_year) }}">{{ trim(($log->session_name ?: $log->semester) . ' ' . $log->academic_year) }}</td>
                            <td data-label="Examiner">{{ $log->examiner_name ?? 'Unknown' }}</td>
                            <td data-label="Result"><span class="badge {{ $decisionClass($log->decision) }}">{{ $decisionLabel($log->decision) }}</span></td>
                            <td data-label="Time">{{ Carbon::parse($log->timestamp)->format('d M Y, H:i') }}<div><a class="text-link" href="{{ route('admin.scan-logs.show', $log->log_id) }}">View</a></div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">No verification logs yet</div>
    @endif
</section>

<section class="two-column">
    <article class="card">
        <div class="card-head">
            <div>
                <h2>Examiner Onboarding</h2>
                <p class="section-copy">Create examiner accounts for scanner access. This restores the admin creation flow as an obvious operational action.</p>
            </div>
            <a class="btn primary" href="{{ route('admin.examiners.index') }}#create-examiner">Add Examiner</a>
        </div>
        <div class="card-body">
            <div class="portal-panel">
                <div class="portal-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <b>Registration route active</b>
                    <span>The existing form posts to <span class="mono">admin.examiners.store</span> and remains protected inside the admin middleware group.</span>
                </div>
            </div>
        </div>
    </article>

    <article class="card">
        <div class="card-head"><h2>Recent Examiners</h2></div>
        <div class="card-body">
            @if($recentExaminers->count())
                <div class="activity-list">
                    @foreach($recentExaminers as $examiner)
                        <div class="activity-item">
                            <span class="dot {{ $examiner->is_active ? 'examiner_created' : '' }}"></span>
                            <div>
                                <div><a class="text-link" href="{{ route('admin.examiners.show', $examiner->examiner_id) }}">{{ $examiner->full_name }}</a></div>
                                <div class="muted mono">{{ $examiner->username }} · {{ $examiner->is_active ? 'Active' : 'Inactive' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">No examiner accounts found</div>
            @endif
        </div>
    </article>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Exam Sessions</h2>
            <p class="section-copy">Manage active exam windows, assigned examiners, and registered students.</p>
        </div>
        <a class="btn primary" href="{{ route('admin.sessions.index') }}">New Session</a>
    </div>
    @if ($sessions->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Session</th>
                        <th>Examiner</th>
                        <th>Students</th>
                        <th>Start Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sessions as $session)
                        @php([$label, $class] = $sessionStatus($session))
                        <tr>
                            <td data-label="Session"><a class="text-link" href="{{ route('admin.sessions.show', $session->session_id) }}">{{ $sessionName($session) }}</a></td>
                            <td data-label="Examiner" class="truncate" title="{{ $session->examiner_name ?? 'Unassigned' }}">{{ $session->examiner_name ?? 'Unassigned' }}</td>
                            <td data-label="Students">{{ number_format($session->student_count) }}</td>
                            <td data-label="Start">{{ Carbon::parse($session->scheduled_start ?: $session->created_at)->format('d M Y, H:i') }}</td>
                            <td data-label="Status"><span class="badge {{ $class }}">{{ $label }}</span></td>
                            <td data-label="Actions">
                                <div class="link-actions">
                                    <a class="text-link" href="{{ route('admin.sessions.show', $session->session_id) }}">View</a>
                                    @if ($session->is_active)
                                        <form method="POST" action="{{ route('admin.sessions.close', $session->session_id) }}">@csrf<button class="text-link warning" type="submit">Close</button></form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.sessions.delete', $session->session_id) }}" data-confirm-inline class="confirm-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="text-link danger ask-confirm">Delete</button>
                                        <span class="confirm-question">Are you sure?</span>
                                        <button type="submit" class="text-link danger confirm-btn">Confirm</button>
                                        <button type="button" class="text-link cancel-btn">Cancel</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pager">{{ $sessions->links() }}</div>
    @else
        <div class="empty">No exam sessions yet. Create one to get started.</div>
    @endif
</section>

<section class="bottom-grid">
    <article class="card">
        <div class="card-head">
            <div>
                <h2>Recent Activity</h2>
                <p class="section-copy">Operational events from registration, sessions, and account management.</p>
            </div>
            <a class="btn" href="{{ route('admin.activity.index') }}">Open activity</a>
        </div>
        <div class="card-body">
            @if ($recentActivity->count())
                <div class="activity-list">
                    @foreach ($recentActivity as $activity)
                        <div class="activity-item">
                            <span class="dot {{ $activityClass($activity->event_type) }}"></span>
                            <div>
                                <div>{{ $activity->description }}</div>
                                <div class="muted">{{ $activity->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">No records found</div>
            @endif
        </div>
    </article>

    <div class="stack">
        <article class="card">
            <div class="card-head"><h2>Audit Trail</h2></div>
            <div class="card-body">
                @if ($recentAuditLogs->count())
                    <div class="activity-list">
                        @foreach ($recentAuditLogs as $audit)
                            <div class="activity-item">
                                <span class="dot"></span>
                                <div>
                                    <div>{{ $audit->action }}</div>
                                    <div class="muted mono">{{ $audit->actor_type }} #{{ $audit->actor_id }} · {{ Carbon::parse($audit->timestamp)->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty">No audit events recorded yet</div>
                @endif
            </div>
        </article>

        <article class="card">
            <div class="card-head"><h2>Quick Actions</h2></div>
            <div class="card-body quick-actions">
                <a class="btn" href="{{ route('admin.examiners.index') }}#create-examiner">Add Examiner</a>
                <a class="btn" href="{{ route('admin.sessions.index') }}">Create Session</a>
                <a class="btn" href="{{ route('admin.timetables.index') }}">Manage Timetable</a>
                <a class="btn" href="{{ route('admin.students.index') }}">View All Students</a>
                <a class="btn" href="{{ route('admin.payments.index') }}">View Payments</a>
                <a class="btn" href="{{ route('admin.scan-logs.export') }}">Export Scan Logs</a>
            </div>
        </article>
    </div>
</section>
@endsection
