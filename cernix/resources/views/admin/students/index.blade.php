@extends('layouts.admin')

@section('title', 'Students')
@section('page_title', 'Students')
@section('breadcrumb', 'Admin / Students')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<section class="card">
    <div class="card-head">
        <div>
            <h2>Student Registry</h2>
            <p class="section-copy">Search registered candidates and review QR issuance status.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="form-grid three">
            <div class="field"><label for="search">Search</label><input id="search" name="search" value="{{ request('search') }}" placeholder="Student ID or name"></div>
            <div class="field">
                <label for="department_id">Department</label>
                <select id="department_id" name="department_id">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->dept_id }}" @selected(request('department_id') == $department->dept_id)>{{ $department->dept_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label for="level">Level</label><input id="level" name="level" value="{{ request('level') }}" placeholder="e.g. 300"></div>
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
                <label for="qr_status">QR</label>
                <select id="qr_status" name="qr_status">
                    <option value="">Any QR status</option>
                    <option value="UNUSED" @selected(request('qr_status') === 'UNUSED')>Unused</option>
                    <option value="USED" @selected(request('qr_status') === 'USED')>Used</option>
                    <option value="REVOKED" @selected(request('qr_status') === 'REVOKED')>Revoked</option>
                </select>
            </div>
            <div class="field">
                <label for="payment_status">Payment</label>
                <select id="payment_status" name="payment_status">
                    <option value="">Any payment status</option>
                    <option value="verified" @selected(request('payment_status') === 'verified')>Verified</option>
                    <option value="pending" @selected(request('payment_status') === 'pending')>Pending</option>
                </select>
            </div>
            <div class="form-action"><button class="btn primary" type="submit">Apply Filters</button></div>
        </form>
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
                        <th>Payment</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        @php $hasToken = (bool) $student->token_status; @endphp
                        <tr>
                            <td data-label="Student">
                                <strong class="mono">{{ $student->matric_no }}</strong>
                                <div class="muted">{{ $student->full_name }}</div>
                            </td>
                            <td data-label="Department">{{ $student->dept_name ?? 'Not set' }}</td>
                            <td data-label="Level">{{ $student->level ?? 'Not set' }}</td>
                            <td data-label="QR Status"><span class="badge {{ $hasToken ? 'green' : 'yellow' }}">{{ $hasToken ? 'Generated' : 'Pending' }}</span></td>
                            <td data-label="Payment"><span class="badge {{ $student->payment_count ? 'green' : 'yellow' }}">{{ $student->payment_count ? 'Verified' : 'Pending' }}</span></td>
                            <td data-label="Registered">{{ Carbon::parse($student->created_at)->format('d M Y, H:i') }}</td>
                            <td data-label="Actions">
                                <div class="link-actions">
                                    <a class="text-link" href="{{ route('admin.students.show', $student->matric_no) }}">View</a>
                                    <form method="POST" action="{{ route('admin.students.delete', $student->matric_no) }}" data-confirm-inline class="confirm-inline">
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
        <div class="pager">{{ $students->links() }}</div>
    @else
        <div class="empty">No student records found</div>
    @endif
</section>
@endsection
