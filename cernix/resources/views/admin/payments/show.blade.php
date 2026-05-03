@extends('layouts.admin')

@section('title', 'Payment Detail')
@section('page_title', 'Payment Detail')
@section('breadcrumb', implode(' / ', $breadcrumbs))

@php
    use Carbon\Carbon;
    $photoUrl = $payment->photo_path ? url('/photo-thumb/' . basename($payment->photo_path)) : null;
    $initials = strtoupper(substr(trim($payment->full_name ?: $payment->student_id), 0, 1));
    $scanBadge = fn ($decision) => $decision === 'APPROVED' ? 'green' : ($decision === 'DUPLICATE' ? 'amber' : 'red');
@endphp

@section('content')
<section class="detail-grid balanced">
    <article class="card identity-card">
        <div class="card-head">
            <div>
                <h2>{{ $payment->full_name ?? 'Student unavailable' }}</h2>
                <p class="section-copy mono">{{ $payment->student_id }}</p>
            </div>
            <a class="btn" href="{{ route('admin.payments.index') }}">Back</a>
        </div>
        <div class="card-body">
            <div class="person-cell" style="margin-bottom:20px">
                <span class="student-avatar large">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="">
                    @else
                        {{ $initials }}
                    @endif
                </span>
                <span class="person-main">
                    <strong>{{ $payment->full_name ?? 'Student unavailable' }}</strong>
                    <span>{{ $payment->dept_name ?? 'Department unavailable' }} · {{ $payment->level ?? 'Level unavailable' }}</span>
                </span>
            </div>
            <dl class="meta-list">
                <div><dt>Session</dt><dd>{{ trim(($payment->session_name ?: $payment->semester).' '.$payment->academic_year) ?: 'Unavailable' }}</dd></div>
                <div><dt>Registered</dt><dd>{{ $payment->registered_at ? Carbon::parse($payment->registered_at)->format('d M Y, H:i') : 'Unavailable' }}</dd></div>
                <div><dt>Student Detail</dt><dd><a class="text-link" href="{{ route('admin.students.show', $payment->student_id) }}">Open student profile</a></dd></div>
            </dl>
        </div>
    </article>

    <article class="card">
        <div class="card-head"><h2>Payment and QR</h2></div>
        <div class="card-body">
            <dl class="meta-list">
                <div><dt>RRR</dt><dd class="mono">{{ $payment->rrr_number }}</dd></div>
                <div><dt>Amount Expected</dt><dd>₦{{ number_format((float) $payment->amount_declared, 2) }}</dd></div>
                <div><dt>Amount Confirmed</dt><dd>₦{{ number_format((float) $payment->amount_confirmed, 2) }}</dd></div>
                <div><dt>Verified At</dt><dd>{{ $payment->verified_at ? Carbon::parse($payment->verified_at)->format('d M Y, H:i') : 'Unavailable' }}</dd></div>
                <div><dt>QR Token</dt><dd class="mono">{{ $token->token_id ?? 'Unavailable' }}</dd></div>
                <div><dt>QR Status</dt><dd><span class="badge {{ $token ? ($token->status === 'REVOKED' ? 'red' : ($token->status === 'USED' ? 'green' : 'yellow')) : 'gray' }}">{{ $token->status ?? 'Not issued' }}</span></dd></div>
                <div><dt>Issued</dt><dd>{{ $token && $token->issued_at ? Carbon::parse($token->issued_at)->format('d M Y, H:i') : 'Unavailable' }}</dd></div>
                <div><dt>Used</dt><dd>{{ $token && $token->used_at ? Carbon::parse($token->used_at)->format('d M Y, H:i') : 'Not used' }}</dd></div>
            </dl>
        </div>
    </article>
</section>

<section class="stats-grid compact">
    <article class="card stat-card"><div class="stat-label">Total Scans</div><div class="stat-value">{{ number_format((int) $scanCounts->sum()) }}</div><div class="stat-help">All attempts</div></article>
    <article class="card stat-card success"><div class="stat-label">Approved</div><div class="stat-value">{{ number_format((int) ($scanCounts['APPROVED'] ?? 0)) }}</div><div class="stat-help">Valid admissions</div></article>
    <article class="card stat-card danger"><div class="stat-label">Rejected</div><div class="stat-value">{{ number_format((int) ($scanCounts['REJECTED'] ?? 0)) }}</div><div class="stat-help">Failed attempts</div></article>
    <article class="card stat-card warning"><div class="stat-label">Duplicate</div><div class="stat-value">{{ number_format((int) ($scanCounts['DUPLICATE'] ?? 0)) }}</div><div class="stat-help">Repeat scans</div></article>
</section>

<section class="two-column">
    <article class="card">
        <div class="card-head"><h2>Timetable Match</h2></div>
        <div class="card-body">
            @if($timetable->count())
                <div class="activity-list">
                    @foreach($timetable as $entry)
                        <div class="activity-item">
                            <span class="dot session_opened"></span>
                            <div>
                                <div><strong>{{ $entry->course_code }}</strong> {{ $entry->course_title }}</div>
                                <div class="muted">{{ Carbon::parse($entry->exam_date)->format('d M Y') }} · {{ substr($entry->start_time, 0, 5) }}{{ $entry->end_time ? ' - '.substr($entry->end_time, 0, 5) : '' }} · {{ $entry->venue }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">No timetable entries match this student.</div>
            @endif
        </div>
    </article>
    <article class="card">
        <div class="card-head"><h2>Remita Response</h2></div>
        <div class="card-body">
            @if(count($remitaResponse))
                <dl class="meta-list">
                    @foreach($remitaResponse as $key => $value)
                        <div><dt>{{ str_replace('_', ' ', $key) }}</dt><dd>{{ is_scalar($value) ? $value : json_encode($value) }}</dd></div>
                    @endforeach
                </dl>
            @else
                <div class="empty">No Remita response summary is available.</div>
            @endif
        </div>
    </article>
</section>

<section class="card">
    <div class="card-head"><h2>Recent Scans</h2></div>
    @if($recentScans->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead><tr><th>Decision</th><th>Examiner</th><th>Time</th><th>Device</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach($recentScans as $scan)
                        <tr>
                            <td data-label="Decision"><span class="badge {{ $scanBadge($scan->decision) }}">{{ ucfirst(strtolower($scan->decision)) }}</span></td>
                            <td data-label="Examiner">{{ $scan->examiner_name ?? 'Unknown' }}</td>
                            <td data-label="Time">{{ Carbon::parse($scan->timestamp)->format('d M Y, H:i') }}</td>
                            <td data-label="Device" class="mono">{{ $scan->device_fp }}</td>
                            <td data-label="Action"><a class="text-link" href="{{ route('admin.scan-logs.show', $scan->log_id) }}">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">No scan history found for this payment.</div>
    @endif
</section>
@endsection
