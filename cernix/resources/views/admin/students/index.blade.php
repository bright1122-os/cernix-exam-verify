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
        <form method="GET" class="inline-search">
            <input name="search" value="{{ request('search') }}" placeholder="Student ID or name">
            <button class="btn" type="submit">Search</button>
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
