@extends('layouts.app')

@section('title', 'Student Registration')

@section('content')
<div class="max-w-xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Student Exam Registration</h1>
        <p class="text-sm text-gray-500 mt-1">
            Register for {{ $session->semester ?? 'Active Semester' }}
            {{ $session->academic_year ?? '' }} &mdash;
            Fee: ₦{{ number_format($session->fee_amount ?? 0, 2) }}
        </p>
    </div>

    <!-- Registration Form -->
    <div id="form-section" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form id="reg-form" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="matric_no">
                    Matriculation Number
                </label>
                <input id="matric_no" type="text" placeholder="e.g. CSC/2021/001"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0f2050] focus:border-transparent"
                    required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="rrr_number">
                    Remita RRR Number
                </label>
                <input id="rrr_number" type="text" placeholder="e.g. 280007021192"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0f2050] focus:border-transparent"
                    required>
                <p class="text-xs text-gray-400 mt-1">Your 12-digit Remita Retrieval Reference from payment</p>
            </div>

            <!-- Error message -->
            <div id="error-box" class="hidden bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm"></div>

            <button type="submit" id="submit-btn"
                class="w-full bg-[#0f2050] text-white rounded-lg px-4 py-2.5 text-sm font-medium hover:bg-[#1a3370] transition flex items-center justify-center gap-2">
                <span id="submit-label">Generate QR</span>
                <svg id="spinner" class="hidden animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
            </button>
        </form>
    </div>

    <!-- Result Panel (hidden until success) -->
    <div id="result-section" class="hidden mt-6">
        <div class="bg-green-50 border border-green-200 rounded-xl p-5">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <h2 class="text-base font-semibold text-green-800">Registration Successful</h2>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm mb-5">
                <div>
                    <p class="text-gray-500 text-xs">Student Name</p>
                    <p id="res-name" class="font-medium text-gray-900"></p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">Matric Number</p>
                    <p id="res-matric" class="font-medium text-gray-900"></p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">Session</p>
                    <p class="font-medium text-gray-900">{{ ($session->semester ?? '') . ' ' . ($session->academic_year ?? '') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">Token ID</p>
                    <p id="res-token" class="font-medium text-gray-900 truncate text-xs font-mono"></p>
                </div>
            </div>

            <div class="flex flex-col items-center">
                <p class="text-xs text-gray-500 mb-3 font-medium uppercase tracking-wide">Your Exam QR Code</p>
                <div id="qr-container" class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm"></div>
                <p class="text-xs text-gray-400 mt-3">Present this QR code to the examiner at the exam hall entrance</p>
            </div>

            <button onclick="resetForm()"
                class="mt-4 w-full text-center text-sm text-[#0f2050] hover:underline">
                Register another student
            </button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('reg-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn    = document.getElementById('submit-btn');
    const label  = document.getElementById('submit-label');
    const spinner = document.getElementById('spinner');
    const errBox = document.getElementById('error-box');

    label.textContent  = 'Generating QR…';
    spinner.classList.remove('hidden');
    btn.disabled       = true;
    errBox.classList.add('hidden');

    try {
        const resp = await fetch('/student/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                matric_no:  document.getElementById('matric_no').value.trim(),
                rrr_number: document.getElementById('rrr_number').value.trim(),
            }),
        });

        const data = await resp.json();

        if (!resp.ok || !data.success) {
            throw new Error(data.message || 'Registration failed. Please try again.');
        }

        document.getElementById('res-name').textContent   = data.data.full_name;
        document.getElementById('res-matric').textContent = data.data.matric_no;
        document.getElementById('res-token').textContent  = data.data.token_id;
        document.getElementById('qr-container').innerHTML = data.data.qr_svg;

        document.getElementById('form-section').classList.add('hidden');
        document.getElementById('result-section').classList.remove('hidden');

    } catch (err) {
        errBox.textContent = err.message;
        errBox.classList.remove('hidden');
    } finally {
        label.textContent  = 'Generate QR';
        spinner.classList.add('hidden');
        btn.disabled       = false;
    }
});

function resetForm() {
    document.getElementById('matric_no').value  = '';
    document.getElementById('rrr_number').value = '';
    document.getElementById('result-section').classList.add('hidden');
    document.getElementById('form-section').classList.remove('hidden');
}
</script>
@endpush
