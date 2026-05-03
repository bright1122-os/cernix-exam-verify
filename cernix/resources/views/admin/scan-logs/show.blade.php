@extends('layouts.admin')

@section('title', 'Scan Detail')
@section('page_title', 'Scan Detail')
@section('breadcrumb', implode(' / ', $breadcrumbs))

@php
    use Carbon\Carbon;
    $scanBadge = fn ($decision) => $decision === 'APPROVED' ? 'green' : ($decision === 'DUPLICATE' ? 'amber' : 'red');
    $photoUrl = $log->photo_path ? url('/photo-thumb/' . basename($log->photo_path)) : null;
    $initials = strtoupper(substr(trim($log->student_name ?: $log->student_id ?: '?'), 0, 1));
@endphp

@section('content')
<section class="detail-grid balanced">
    <article class="card identity-card">
        <div class="card-head">
            <div>
                <h2>Student Identity</h2>
                <p class="section-copy">Photo is shown here because this is a detailed verification context.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="person-cell" style="margin-bottom:20px">
                <span class="student-avatar large">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="">
                    @else
                        {{ $initials }}
                    @endif
                </span>
                <span class="person-main">
                    <strong>{{ $log->student_name ?? 'Student unavailable' }}</strong>
                    <span class="mono">{{ $log->student_id ?? 'Unavailable' }}</span>
                </span>
            </div>
            <dl class="meta-list">
                <div><dt>Department</dt><dd>{{ $log->dept_name ?? 'Unavailable' }}</dd></div>
                <div><dt>Level</dt><dd>{{ $log->level ?? 'Unavailable' }}</dd></div>
                <div><dt>Session</dt><dd>{{ trim(($log->session_name ?: $log->semester).' '.$log->academic_year) ?: 'Unavailable' }}</dd></div>
            </dl>
        </div>
    </article>
    <article class="card">
        <div class="card-head">
            <div>
                <h2>Verification Event</h2>
                <p class="section-copy">Trace information captured when the scanner contacted the server.</p>
            </div>
            <a class="btn" href="{{ route('admin.scan-logs.index') }}">Back</a>
        </div>
        <div class="card-body">
            <dl class="meta-list">
                <div><dt>Decision</dt><dd><span class="badge {{ $scanBadge($log->decision) }}">{{ ucfirst(strtolower($log->decision)) }}</span></dd></div>
                <div><dt>Token</dt><dd class="mono">{{ $log->token_id }}</dd></div>
                <div><dt>Examiner</dt><dd>{{ $log->examiner_name ?? 'Unknown' }}</dd></div>
                <div><dt>Timestamp</dt><dd>{{ Carbon::parse($log->timestamp)->format('d M Y, H:i') }}</dd></div>
                <div><dt>Device</dt><dd class="mono">{{ $log->device_fp }}</dd></div>
                <div><dt>IP Address</dt><dd class="mono">{{ $log->ip_address }}</dd></div>
            </dl>
        </div>
    </article>
</section>
@endsection
