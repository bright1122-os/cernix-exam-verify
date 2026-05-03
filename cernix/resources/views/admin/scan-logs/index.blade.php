@extends('layouts.admin')

@section('title', 'Scan Logs')
@section('page_title', 'Scan Logs')
@section('breadcrumb', 'Admin / Scan Logs')

@php
    use Carbon\Carbon;
    $scanBadge = fn ($decision) => $decision === 'APPROVED' ? 'green' : ($decision === 'DUPLICATE' ? 'amber' : 'red');
    $scanLabel = fn ($decision) => match ($decision) {
        'APPROVED' => 'Approved',
        'DUPLICATE' => 'Duplicate',
        default => 'Rejected',
    };
    $photoUrl = fn ($path) => $path ? url('/photo-thumb/' . basename($path)) : null;
    $initials = fn ($name, $fallback = '?') => strtoupper(substr(trim((string) ($name ?: $fallback)), 0, 1));
@endphp

@section('content')
<section class="card">
    <div class="card-head">
        <div>
            <h2>Scan Filters</h2>
            <p class="section-copy">Filter verification attempts by date, session, and decision type.</p>
        </div>
        <a class="btn" href="{{ route('admin.scan-logs.export', request()->query()) }}">Export CSV</a>
    </div>
    <div class="card-body">
        <form method="GET" class="form-grid three">
            <div class="field"><label for="date_from">Date From</label><input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}"></div>
            <div class="field"><label for="date_to">Date To</label><input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}"></div>
            <div class="field">
                <label for="session_id">Session</label>
                <select id="session_id" name="session_id">
                    <option value="">All sessions</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->session_id }}" @selected(request('session_id') == $session->session_id)>{{ $session->name ?: $session->semester }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="result">Result</label>
                <select id="result" name="result">
                    <option value="">All results</option>
                    <option value="APPROVED" @selected(request('result')==='APPROVED')>Approved</option>
                    <option value="REJECTED" @selected(request('result')==='REJECTED')>Rejected</option>
                    <option value="DUPLICATE" @selected(request('result')==='DUPLICATE')>Duplicate</option>
                </select>
            </div>
            <div class="form-action"><button class="btn primary" type="submit">Apply Filters</button></div>
        </form>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Verification Attempts</h2>
            <p class="section-copy">System-wide scanner decisions with examiner and session context.</p>
        </div>
    </div>
    @if($logs->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Session</th>
                        <th>Examiner</th>
                        <th>Result</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td data-label="Student">
                                <div class="person-cell">
                                    <span class="student-avatar">
                                        @if($photoUrl($log->photo_path ?? null))
                                            <img src="{{ $photoUrl($log->photo_path) }}" alt="">
                                        @else
                                            {{ $initials($log->student_name ?? null, $log->student_id ?? '?') }}
                                        @endif
                                    </span>
                                    <span class="person-main"><strong class="mono">{{ $log->student_id ?? 'Student unavailable' }}</strong><span>{{ $log->student_name ?? 'Student unavailable' }}</span></span>
                                </div>
                            </td>
                            <td data-label="Session" class="truncate" title="{{ trim(($log->session_name ?: $log->semester).' '.$log->academic_year) }}">{{ trim(($log->session_name ?: $log->semester).' '.$log->academic_year) }}</td>
                            <td data-label="Examiner">{{ $log->examiner_name ?? 'Unknown' }}</td>
                            <td data-label="Result"><span class="badge {{ $scanBadge($log->decision) }}">{{ $scanLabel($log->decision) }}</span></td>
                            <td data-label="Timestamp">{{ Carbon::parse($log->timestamp)->format('d M Y, H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pager">{{ $logs->links() }}</div>
    @else
        <div class="empty">No scan logs found</div>
    @endif
</section>
@endsection
