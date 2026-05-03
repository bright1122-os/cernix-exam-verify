@extends('layouts.portal')

@section('title', 'Student Dashboard')

@section('content')
<style>
    .student-shell { min-height: 100vh; padding: 24px; display: grid; place-items: center; background: var(--bg); }
    .student-card { width: 100%; max-width: 560px; background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: var(--shadow); overflow: hidden; }
    .student-head { padding: 24px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; }
    .student-head h1 { margin: 0 0 4px; font-size: 22px; font-weight: 800; color: var(--ink); }
    .student-head p { margin: 0; font-size: 13px; color: var(--ink-3); }
    .student-body { padding: 24px; display: grid; gap: 18px; }
    .qr-box { width: 260px; max-width: 100%; margin: 0 auto; padding: 12px; background: #fff; border: 1px solid var(--line); border-radius: 14px; }
    .qr-box svg { width: 100%; height: auto; display: block; }
    .meta-row { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-top: 1px solid var(--line); font-size: 14px; }
    .meta-row b { color: var(--ink); }
    .meta-row span { color: var(--ink-3); text-align: right; }
    .status { display: inline-flex; align-items: center; border-radius: 999px; padding: 6px 10px; font-size: 12px; font-weight: 800; color: #fff; background: var(--emerald); }
    .logout-form { margin: 0; }
    .logout-form button { color: var(--navy); font-weight: 800; font-size: 13px; }
</style>
<main class="student-shell">
    <section class="student-card">
        <header class="student-head">
            <div>
                <h1>{{ $student->full_name }}</h1>
                <p class="mono">{{ $student->matric_no }}</p>
            </div>
            <form method="POST" action="{{ route('student.logout') }}" class="logout-form">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </header>
        <div class="student-body">
            @if ($qrSvg)
                <div class="qr-box">{!! $qrSvg !!}</div>
            @endif

            <div class="meta-row">
                <b>Status</b>
                <span><span class="status">{{ $token && $token->status === 'USED' ? 'Verified' : 'Pending Verification' }}</span></span>
            </div>
            <div class="meta-row">
                <b>Session</b>
                <span>{{ $session->name ?? $session->semester ?? 'Not assigned' }}</span>
            </div>
            <div class="meta-row">
                <b>Registered</b>
                <span>{{ optional($student->created_at)->format('d M Y, H:i') ?? 'Unavailable' }}</span>
            </div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-ghost btn-block">Refresh</a>
        </div>
    </section>
</main>
@endsection
