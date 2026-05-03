@extends('layouts.admin')

@section('title', 'Timetable')
@section('page_title', 'Timetable')
@section('breadcrumb', 'Admin / Timetable')

@php
    use Carbon\Carbon;
    $statusClass = fn ($status) => match ($status) {
        'active' => 'green',
        'completed' => 'gray',
        'cancelled' => 'red',
        default => 'yellow',
    };
@endphp

@section('content')
<section class="card">
    <div class="card-head">
        <div>
            <h2>Create Timetable Entry</h2>
            <p class="section-copy">Students inherit exam schedule by department, level, and active session. Timetable data is not embedded in QR payloads.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.timetables.store') }}" class="form-grid three">
            @csrf
            <div class="field">
                <label for="exam_session_id">Session</label>
                <select id="exam_session_id" name="exam_session_id" required>
                    <option value="">Select session</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->session_id }}" @selected(old('exam_session_id') == $session->session_id)>{{ $session->name ?: $session->semester }} {{ $session->academic_year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="department_id">Department</label>
                <select id="department_id" name="department_id" required>
                    <option value="">Select department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->dept_id }}" @selected(old('department_id') == $department->dept_id)>{{ $department->dept_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label for="level">Level</label><input id="level" name="level" value="{{ old('level', '300') }}" required></div>
            <div class="field"><label for="course_code">Course Code</label><input id="course_code" name="course_code" value="{{ old('course_code') }}" placeholder="CSC 301" required></div>
            <div class="field"><label for="course_title">Course Title</label><input id="course_title" name="course_title" value="{{ old('course_title') }}" placeholder="Algorithms"></div>
            <div class="field"><label for="exam_date">Date</label><input id="exam_date" type="date" name="exam_date" value="{{ old('exam_date') }}" required></div>
            <div class="field"><label for="start_time">Start</label><input id="start_time" type="time" name="start_time" value="{{ old('start_time') }}" required></div>
            <div class="field"><label for="end_time">End</label><input id="end_time" type="time" name="end_time" value="{{ old('end_time') }}"></div>
            <div class="field"><label for="venue">Venue / Hall</label><input id="venue" name="venue" value="{{ old('venue') }}" required></div>
            <div class="field"><label for="capacity">Capacity</label><input id="capacity" type="number" min="1" name="capacity" value="{{ old('capacity') }}"></div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach(['scheduled', 'active', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', 'scheduled') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-action"><button class="btn primary" type="submit">Create Entry</button></div>
        </form>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Timetable Entries</h2>
            <p class="section-copy">Filter by session, department, level, or date.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="form-grid three">
            <div class="field">
                <label for="filter_session">Session</label>
                <select id="filter_session" name="session_id">
                    <option value="">All sessions</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->session_id }}" @selected(request('session_id') == $session->session_id)>{{ $session->name ?: $session->semester }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="filter_department">Department</label>
                <select id="filter_department" name="department_id">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->dept_id }}" @selected(request('department_id') == $department->dept_id)>{{ $department->dept_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label for="filter_level">Level</label><input id="filter_level" name="level" value="{{ request('level') }}" placeholder="300"></div>
            <div class="field"><label for="filter_date">Date</label><input id="filter_date" type="date" name="date" value="{{ request('date') }}"></div>
            <div class="form-action"><button class="btn" type="submit">Filter</button></div>
        </form>
    </div>
    @if($entries->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Department</th>
                        <th>Session</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td data-label="Course"><strong class="mono">{{ $entry->course_code }}</strong><div class="muted">{{ $entry->course_title ?: 'Untitled course' }}</div></td>
                            <td data-label="Department">{{ $entry->dept_name }} <span class="muted">· {{ $entry->level }}</span></td>
                            <td data-label="Session">{{ $entry->session_name ?: $entry->semester }}</td>
                            <td data-label="Date">{{ Carbon::parse($entry->exam_date)->format('d M Y') }} <span class="muted mono">{{ substr($entry->start_time, 0, 5) }}{{ $entry->end_time ? ' - ' . substr($entry->end_time, 0, 5) : '' }}</span></td>
                            <td data-label="Venue">{{ $entry->venue }}</td>
                            <td data-label="Status"><span class="badge {{ $statusClass($entry->status) }}">{{ ucfirst($entry->status) }}</span></td>
                            <td data-label="Actions">
                                <div class="link-actions">
                                    <a class="text-link" href="{{ route('admin.timetables.edit', $entry->id) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.timetables.delete', $entry->id) }}" data-confirm-inline class="confirm-inline">
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
        <div class="pager">{{ $entries->links() }}</div>
    @else
        <div class="empty">No timetable entries found</div>
    @endif
</section>
@endsection
