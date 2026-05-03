@extends('layouts.admin')

@section('title', 'Exam Sessions')
@section('page_title', 'Exam Sessions')
@section('breadcrumb', 'Admin / Exam Sessions')

@php
    use Carbon\Carbon;
    $sessionName = fn ($session) => $session->name ?: $session->semester;
    $sessionStatus = function ($session) {
        if ($session->is_active) return ['Active', 'green'];
        if ($session->scheduled_start && Carbon::parse($session->scheduled_start)->isFuture()) return ['Pending', 'yellow'];
        return ['Closed', 'gray'];
    };
@endphp

@section('content')
<section class="card">
    <div class="card-head">
        <div>
            <h2>Create Session</h2>
            <p class="section-copy">Open a new exam window and assign a responsible examiner.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.sessions.store') }}" class="form-grid three">
            @csrf
            <div class="field">
                <label for="name">Session Name</label>
                <input id="name" name="name" value="{{ old('name') }}" required placeholder="First Semester Main Exam">
            </div>
            <div class="field">
                <label for="examiner_id">Assign Examiner</label>
                <select id="examiner_id" name="examiner_id" required>
                    <option value="">Select examiner</option>
                    @foreach($allExaminers as $examiner)
                        <option value="{{ $examiner->examiner_id }}" @selected(old('examiner_id') == $examiner->examiner_id)>{{ $examiner->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="scheduled_start">Scheduled Start</label>
                <input id="scheduled_start" name="scheduled_start" type="datetime-local" value="{{ old('scheduled_start') }}">
            </div>
            <div class="field">
                <label for="fee_amount">Fee</label>
                <input id="fee_amount" name="fee_amount" type="number" min="0" step="0.01" value="{{ old('fee_amount', 100000) }}">
            </div>
            <div class="field">
                <label>Status</label>
                <label class="check-control"><input type="checkbox" name="is_active" value="1" @checked(old('is_active'))> Active now</label>
            </div>
            <div class="form-action"><button class="btn primary" type="submit">Create Session</button></div>
        </form>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Exam Sessions</h2>
            <p class="section-copy">Review session status, examiner assignment, and registered student count.</p>
        </div>
        <form method="GET" class="inline-search">
            <input name="search" value="{{ request('search') }}" placeholder="Search sessions">
            <button class="btn" type="submit">Search</button>
        </form>
    </div>
    @if($sessions->count())
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
                    @foreach($sessions as $session)
                        @php([$label, $class] = $sessionStatus($session))
                        <tr>
                            <td data-label="Session"><a class="text-link" href="{{ route('admin.sessions.show', $session->session_id) }}">{{ $sessionName($session) }}</a></td>
                            <td data-label="Examiner">{{ $session->examiner_name ?? 'Unassigned' }}</td>
                            <td data-label="Students">{{ number_format($session->student_count) }}</td>
                            <td data-label="Start">{{ Carbon::parse($session->scheduled_start ?: $session->created_at)->format('d M Y, H:i') }}</td>
                            <td data-label="Status"><span class="badge {{ $class }}">{{ $label }}</span></td>
                            <td data-label="Actions">
                                <div class="link-actions">
                                    <a class="text-link" href="{{ route('admin.sessions.show', $session->session_id) }}">View</a>
                                    @if($session->is_active)
                                        <form method="POST" action="{{ route('admin.sessions.close', $session->session_id) }}">@csrf<button class="text-link warning" type="submit">Close</button></form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.sessions.delete', $session->session_id) }}" data-confirm-inline class="confirm-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="text-link danger ask-confirm">Delete</button>
                                        <span class="confirm-question">Are you sure?</span>
                                        <button class="text-link danger confirm-btn" type="submit">Confirm</button>
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
@endsection
