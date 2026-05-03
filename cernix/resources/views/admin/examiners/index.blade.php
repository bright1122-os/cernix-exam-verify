@extends('layouts.admin')

@section('title', 'Examiners')
@section('page_title', 'Examiners')
@section('breadcrumb', 'Admin / Examiners')

@php use Carbon\Carbon; @endphp

@section('content')
<section class="two-column" id="create-examiner">
    <article class="card">
        <div class="card-head">
            <div>
                <h2>Add Examiner</h2>
                <p class="section-copy">Create a scanner account for an authorized invigilator. Role assignment is fixed to examiner.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="portal-panel" style="margin-bottom:18px">
                <div class="portal-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <b>Examiner onboarding</b>
                    <span>New accounts can access the scanner after signing in with the username and password you provide.</span>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.examiners.store') }}" class="form-grid">
                @csrf
                <div class="field">
                    <label for="full_name">Full Name</label>
                    <input id="full_name" name="full_name" value="{{ old('full_name') }}" required placeholder="Examiner name">
                </div>
                <div class="field">
                    <label for="username">Email / Username</label>
                    <input id="username" name="username" value="{{ old('username') }}" required placeholder="examiner@aaua.edu.ng">
                    <span class="hint">Used for examiner sign-in.</span>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-action">
                    <button class="btn primary" type="submit">Add Examiner</button>
                </div>
            </form>
        </div>
    </article>

    <aside class="card">
        <div class="card-head"><h2>Access Rules</h2></div>
        <div class="card-body stack tight">
            <div class="portal-panel">
                <div class="portal-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><b>Scoped access</b><span>Examiner accounts can scan and review their verification activity only.</span></div>
            </div>
            <div class="portal-panel">
                <div class="portal-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </div>
                <div><b>Audit linked</b><span>Creation, activation, and deactivation are recorded for accountability.</span></div>
            </div>
        </div>
    </aside>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Examiner Accounts</h2>
            <p class="section-copy">Manage scanner access, assigned sessions, and account status.</p>
        </div>
    </div>
    @if($examiners->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Sessions</th>
                        <th>Scans</th>
                        <th>Last Scan</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($examiners as $examiner)
                        <tr>
                            <td data-label="Name"><strong>{{ $examiner->full_name }}</strong></td>
                            <td data-label="Username" class="mono">{{ $examiner->username }}</td>
                            <td data-label="Sessions">{{ number_format($examiner->sessions_count) }}</td>
                            <td data-label="Scans">{{ number_format($examiner->scan_count) }}</td>
                            <td data-label="Last Scan">{{ $examiner->last_scan_at ? Carbon::parse($examiner->last_scan_at)->diffForHumans() : 'No scans' }}</td>
                            <td data-label="Registered">{{ Carbon::parse($examiner->created_at)->format('d M Y, H:i') }}</td>
                            <td data-label="Status"><span class="badge {{ $examiner->is_active ? 'green' : 'gray' }}">{{ $examiner->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td data-label="Actions">
                                <div class="link-actions">
                                    <a href="{{ route('admin.examiners.show', $examiner->examiner_id) }}" class="text-link">View</a>
                                    <form method="POST" action="{{ route('admin.examiners.toggle', $examiner->examiner_id) }}">
                                        @csrf
                                        <button class="text-link warning" type="submit">{{ $examiner->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.examiners.delete', $examiner->examiner_id) }}" data-confirm-inline class="confirm-inline">
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
        <div class="pager">{{ $examiners->links() }}</div>
    @else
        <div class="empty">No examiner accounts found</div>
    @endif
</section>
@endsection
