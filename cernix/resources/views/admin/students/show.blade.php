@extends('layouts.admin')

@section('title', $pageTitle)
@section('page_title', $pageTitle)
@section('breadcrumb', implode(' / ', $breadcrumbs))

@php
    use Carbon\Carbon;
    $scanBadge = fn ($decision) => $decision === 'APPROVED' ? 'green' : ($decision === 'DUPLICATE' ? 'amber' : 'red');
    $photoUrl = fn ($path) => $path ? url('/photo-thumb/' . basename($path)) : null;
    $initials = fn ($name, $fallback = '?') => strtoupper(substr(trim((string) ($name ?: $fallback)), 0, 1));
@endphp

@section('content')
<section class="card">
    <div class="card-head">
        <div>
            <h2>Student Detail</h2>
            <p class="section-copy">Registration, session, QR status, and verification trail.</p>
        </div>
        <a class="btn" href="{{ route('admin.students.index') }}">Back</a>
    </div>
    <div class="card-body">
        <div class="person-cell" style="margin-bottom:20px">
            <span class="student-avatar large">
                @if($photoUrl($student->photo_path ?? null))
                    <img src="{{ $photoUrl($student->photo_path) }}" alt="">
                @else
                    {{ $initials($student->full_name ?? null, $student->matric_no ?? '?') }}
                @endif
            </span>
            <span class="person-main">
                <strong>{{ $student->full_name }}</strong>
                <span class="mono">{{ $student->matric_no }}</span>
            </span>
        </div>
        <div class="form-grid three">
        <div><div class="eyebrow">Student ID</div><strong class="mono">{{ $student->matric_no }}</strong></div>
        <div><div class="eyebrow">Department</div><strong>{{ $student->dept_name ?? 'Not set' }}</strong></div>
        <div><div class="eyebrow">Level</div><strong>{{ $student->level ?? 'Not set' }}</strong></div>
        <div><div class="eyebrow">Session</div><strong>{{ trim(($student->session_name ?: $student->semester) . ' ' . $student->academic_year) }}</strong></div>
        <div><div class="eyebrow">QR Status</div><span class="badge {{ $token ? 'green' : 'yellow' }}">{{ $token ? ucfirst(strtolower($token->status)) : 'Pending' }}</span></div>
        <div><div class="eyebrow">Payment</div><span class="badge {{ $payment ? 'green' : 'yellow' }}">{{ $payment ? 'Verified' : 'Pending' }}</span></div>
        <div><div class="eyebrow">Registered</div><strong>{{ Carbon::parse($student->created_at)->format('d M Y, H:i') }}</strong></div>
        <div><div class="eyebrow">Approved</div><strong>{{ number_format((int) ($scanCounts['APPROVED'] ?? 0)) }}</strong></div>
        <div><div class="eyebrow">Rejected</div><strong>{{ number_format((int) ($scanCounts['REJECTED'] ?? 0)) }}</strong></div>
        <div><div class="eyebrow">Duplicate</div><strong>{{ number_format((int) ($scanCounts['DUPLICATE'] ?? 0)) }}</strong></div>
        <div><div class="eyebrow">Total Scans</div><strong>{{ number_format((int) $scanCounts->sum()) }}</strong></div>
        <div><div class="eyebrow">Last Scan</div><strong>{{ $lastScan ? Carbon::parse($lastScan->timestamp)->format('d M Y, H:i') : 'No scans' }}</strong></div>
        <div><div class="eyebrow">Last Examiner</div><strong>{{ $lastScan->examiner_name ?? 'Unavailable' }}</strong></div>
        </div>
    </div>
</section>

<section class="two-column">
    <article class="card">
        <div class="card-head"><h2>Payment Record</h2></div>
        <div class="card-body">
            @if($payment)
                <dl class="meta-list">
                    <div><dt>RRR</dt><dd class="mono">{{ $payment->rrr_number }}</dd></div>
                    <div><dt>Amount</dt><dd>₦{{ number_format((float) $payment->amount_confirmed, 2) }}</dd></div>
                    <div><dt>Verified</dt><dd>{{ $payment->verified_at ? Carbon::parse($payment->verified_at)->format('d M Y, H:i') : 'Unavailable' }}</dd></div>
                </dl>
            @else
                <div class="empty">No payment record is linked to this student.</div>
            @endif
        </div>
    </article>
    <article class="card">
        <div class="card-head"><h2>Matched Timetable</h2></div>
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
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Scan History</h2>
            <p class="section-copy">Verification decisions recorded for this student.</p>
        </div>
    </div>
    @if($scanHistory->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Result</th>
                        <th>Examiner</th>
                        <th>Timestamp</th>
                        <th>Device</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scanHistory as $scan)
                        <tr>
                            <td data-label="Result"><span class="badge {{ $scanBadge($scan->decision) }}">{{ ucfirst(strtolower($scan->decision)) }}</span></td>
                            <td data-label="Examiner">{{ $scan->examiner_name ?? 'Unknown' }}</td>
                            <td data-label="Timestamp">{{ Carbon::parse($scan->timestamp)->format('d M Y, H:i') }}</td>
                            <td data-label="Device" class="mono truncate" title="{{ $scan->device_fp }}">{{ $scan->device_fp }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pager">{{ $scanHistory->links() }}</div>
    @else
        <div class="empty">No scan history found</div>
    @endif
</section>
@endsection
