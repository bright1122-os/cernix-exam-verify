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
            <div class="field"><label for="search">Search</label><input id="search" name="search" value="{{ request('search') }}" placeholder="Student, examiner, token"></div>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td data-label="Student">
                                <strong class="mono">{{ $log->student_id ?? 'Student unavailable' }}</strong>
                                <div class="muted">{{ $log->student_name ?? 'Student unavailable' }}</div>
                            </td>
                            <td data-label="Session" class="truncate" title="{{ trim(($log->session_name ?: $log->semester).' '.$log->academic_year) }}">{{ trim(($log->session_name ?: $log->semester).' '.$log->academic_year) }}</td>
                            <td data-label="Examiner">{{ $log->examiner_name ?? 'Unknown' }}</td>
                            <td data-label="Result"><span class="badge {{ $scanBadge($log->decision) }}">{{ $scanLabel($log->decision) }}</span></td>
                            <td data-label="Timestamp">{{ Carbon::parse($log->timestamp)->format('d M Y, H:i') }}</td>
                            <td data-label="Actions"><a class="text-link" href="{{ route('admin.scan-logs.show', $log->log_id) }}">View</a></td>
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
