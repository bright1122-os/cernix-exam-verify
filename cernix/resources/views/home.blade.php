@extends('layouts.app')

@section('title', 'CERNIX — Secure Exam Verification')

@section('content')
<div class="max-w-3xl mx-auto text-center py-12">

    <!-- Logo / Title -->
    <div class="mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#0f2050] mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-gray-900 tracking-tight">CERNIX</h1>
        <p class="text-lg text-gray-500 mt-2">Cryptographic Exam Registration &amp; Verification System</p>
    </div>

    <!-- Tagline -->
    <p class="text-gray-600 max-w-xl mx-auto mb-10 leading-relaxed">
        End-to-end secure exam hall access control — AES-256-GCM encrypted QR tokens,
        HMAC-verified identities, and atomic one-time admission for higher institutions.
    </p>

    <!-- Portal cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-12">
        <a href="/student/register"
            class="group bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md hover:border-[#0f2050] transition text-left">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center mb-3 group-hover:bg-[#0f2050] transition">
                <svg class="w-5 h-5 text-[#0f2050] group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2 class="font-semibold text-gray-900 mb-1">Student Portal</h2>
            <p class="text-xs text-gray-500">Register for your exam and generate your one-time QR access token</p>
        </a>

        <a href="/examiner/dashboard"
            class="group bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md hover:border-[#0f2050] transition text-left">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center mb-3 group-hover:bg-[#0f2050] transition">
                <svg class="w-5 h-5 text-green-700 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <h2 class="font-semibold text-gray-900 mb-1">Examiner Portal</h2>
            <p class="text-xs text-gray-500">Scan student QR codes at the exam hall entrance to approve or deny access</p>
        </a>

        <a href="/admin/dashboard"
            class="group bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md hover:border-[#0f2050] transition text-left">
            <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center mb-3 group-hover:bg-[#0f2050] transition">
                <svg class="w-5 h-5 text-purple-700 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="font-semibold text-gray-900 mb-1">Admin Portal</h2>
            <p class="text-xs text-gray-500">View verification logs, audit trail, and real-time activity statistics</p>
        </a>
    </div>

    <!-- System status badge -->
    <div id="health-badge" class="inline-flex items-center gap-2 text-xs text-gray-400 bg-gray-100 rounded-full px-4 py-1.5">
        <span class="w-2 h-2 rounded-full bg-gray-300"></span>
        Checking system status…
    </div>

</div>
@endsection

@push('scripts')
<script>
fetch('/health').then(r => r.json()).then(data => {
    const badge = document.getElementById('health-badge');
    const ok    = data.status === 'ok' && data.session_active;
    badge.innerHTML = ok
        ? '<span class="w-2 h-2 rounded-full bg-green-500"></span> System operational &mdash; active session running'
        : '<span class="w-2 h-2 rounded-full bg-yellow-500"></span> System up &mdash; no active exam session';
    badge.className = 'inline-flex items-center gap-2 text-xs rounded-full px-4 py-1.5 ' +
        (ok ? 'text-green-700 bg-green-50' : 'text-yellow-700 bg-yellow-50');
}).catch(() => {
    document.getElementById('health-badge').textContent = 'System status unavailable';
});
</script>
@endpush
