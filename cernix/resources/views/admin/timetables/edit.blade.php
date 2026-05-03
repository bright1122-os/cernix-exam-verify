@extends('layouts.admin')

@section('title', 'Edit Timetable')
@section('page_title', 'Edit Timetable')
@section('breadcrumb', implode(' / ', $breadcrumbs))

@section('content')
<section class="card">
    <div class="card-head">
        <div>
            <h2>{{ $entry->course_code }}</h2>
            <p class="section-copy">Update this exam timetable entry. QR verification continues to use only the existing secure QR payload.</p>
        </div>
        <a class="btn" href="{{ route('admin.timetables.index') }}">Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.timetables.update', $entry->id) }}" class="form-grid three">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="exam_session_id">Session</label>
                <select id="exam_session_id" name="exam_session_id" required>
                    @foreach($sessions as $session)
                        <option value="{{ $session->session_id }}" @selected(old('exam_session_id', $entry->exam_session_id) == $session->session_id)>{{ $session->name ?: $session->semester }} {{ $session->academic_year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="department_id">Department</label>
                <select id="department_id" name="department_id" required>
                    @foreach($departments as $department)
                        <option value="{{ $department->dept_id }}" @selected(old('department_id', $entry->department_id) == $department->dept_id)>{{ $department->dept_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label for="level">Level</label><input id="level" name="level" value="{{ old('level', $entry->level) }}" required></div>
            <div class="field"><label for="course_code">Course Code</label><input id="course_code" name="course_code" value="{{ old('course_code', $entry->course_code) }}" required></div>
            <div class="field"><label for="course_title">Course Title</label><input id="course_title" name="course_title" value="{{ old('course_title', $entry->course_title) }}"></div>
            <div class="field"><label for="exam_date">Date</label><input id="exam_date" type="date" name="exam_date" value="{{ old('exam_date', $entry->exam_date) }}" required></div>
            <div class="field"><label for="start_time">Start</label><input id="start_time" type="time" name="start_time" value="{{ old('start_time', substr($entry->start_time, 0, 5)) }}" required></div>
            <div class="field"><label for="end_time">End</label><input id="end_time" type="time" name="end_time" value="{{ old('end_time', $entry->end_time ? substr($entry->end_time, 0, 5) : '') }}"></div>
            <div class="field"><label for="venue">Venue / Hall</label><input id="venue" name="venue" value="{{ old('venue', $entry->venue) }}" required></div>
            <div class="field"><label for="capacity">Capacity</label><input id="capacity" type="number" min="1" name="capacity" value="{{ old('capacity', $entry->capacity) }}"></div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach(['scheduled', 'active', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $entry->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-action"><button class="btn primary" type="submit">Save Changes</button></div>
        </form>
    </div>
</section>
@endsection
