@extends('layouts.admin')

@section('title', $pageTitle)
@section('page_title', $pageTitle)
@section('breadcrumb', implode(' / ', $breadcrumbs))

@php
    use Carbon\Carbon;
@endphp

@section('content')
<section class="card">
    <div class="card-head">
        <div>
            <h2>Session Info</h2>
            <p class="section-copy">Examiner assignment, scheduled time, and registered candidates.</p>
        </div>
        <a class="btn" href="{{ route('admin.sessions.index') }}">Back</a>
    </div>
    <div class="card-body form-grid three">
        <div><div class="eyebrow">Examiner</div><strong>{{ $session->examiner_name ?? 'Unassigned' }}</strong></div>
        <div><div class="eyebrow">Start Time</div><strong>{{ Carbon::parse($session->scheduled_start ?: $session->created_at)->format('d M Y, H:i') }}</strong></div>
        <div><div class="eyebrow">Students</div><strong>{{ number_format($session->student_count) }}</strong></div>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Students</h2>
            <p class="section-copy">Registered students linked to this exam session.</p>
        </div>
    </div>
    @if($students->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Department</th>
                        <th>Level</th>
                        <th>QR Status</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td data-label="Student">
                                <strong class="mono">{{ $student->matric_no }}</strong>
                                <div class="muted">{{ $student->full_name }}</div>
                            </td>
                            <td data-label="Department">{{ $student->dept_name ?? 'Not set' }}</td>
                            <td data-label="Level">{{ $student->level ?? 'Not set' }}</td>
                            <td data-label="QR Status"><span class="badge {{ $student->token_status ? 'green' : 'yellow' }}">{{ $student->token_status ? ucfirst(strtolower($student->token_status)) : 'Pending' }}</span></td>
                            <td data-label="Registered">{{ Carbon::parse($student->created_at)->format('d M Y, H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pager">{{ $students->links() }}</div>
    @else
        <div class="empty">No students registered for this session</div>
    @endif
</section>
@endsection
