@extends('layouts.admin')

@section('title', 'Activity')
@section('page_title', 'Activity')
@section('breadcrumb', 'Admin / Activity')

@section('content')
<section class="card">
    <div class="card-head">
        <div>
            <h2>Recent Activity</h2>
            <p class="section-copy">Chronological system events from registrations, sessions, scans, and account changes.</p>
        </div>
    </div>
    <div class="card-body">
        @if($activities->count())
            <div class="activity-list">
                @foreach($activities as $activity)
                    <div class="activity-item">
                        <span class="dot {{ str_replace('.', '_', $activity->event_type) }}"></span>
                        <div>
                            <div>{{ $activity->description }}</div>
                            <div class="muted">{{ $activity->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="pager">{{ $activities->links() }}</div>
        @else
            <div class="empty">No activity records found</div>
        @endif
    </div>
</section>
@endsection
