@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Read-only view of all verification activity and audit log</p>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Scans</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Approved</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Rejected</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['rejected'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Duplicates</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['duplicate'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6 shadow-sm">
        <form method="GET" action="/admin/dashboard" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Examiner ID</label>
                <input name="examiner_id" type="text" value="{{ request('examiner_id') }}"
                    placeholder="Any"
                    class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0f2050] w-32">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="decision" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0f2050]">
                    <option value="">All</option>
                    <option value="APPROVED"  {{ request('decision') === 'APPROVED'  ? 'selected' : '' }}>APPROVED</option>
                    <option value="REJECTED"  {{ request('decision') === 'REJECTED'  ? 'selected' : '' }}>REJECTED</option>
                    <option value="DUPLICATE" {{ request('decision') === 'DUPLICATE' ? 'selected' : '' }}>DUPLICATE</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="bg-[#0f2050] text-white rounded-lg px-4 py-1.5 text-sm hover:bg-[#1a3370] transition">
                    Filter
                </button>
                <a href="/admin/dashboard"
                    class="border border-gray-300 text-gray-600 rounded-lg px-4 py-1.5 text-sm hover:bg-gray-50 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Verification Logs -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Verification Logs</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ $verificationLogs->count() }} records shown (newest first)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Token ID</th>
                        <th class="px-4 py-3 font-medium">Examiner</th>
                        <th class="px-4 py-3 font-medium">Decision</th>
                        <th class="px-4 py-3 font-medium">Timestamp</th>
                        <th class="px-4 py-3 font-medium">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($verificationLogs as $log)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600 max-w-[160px] truncate">{{ $log->token_id }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $log->examiner_id }}</td>
                        <td class="px-4 py-3">
                            @if($log->decision === 'APPROVED')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">APPROVED</span>
                            @elseif($log->decision === 'REJECTED')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">REJECTED</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">DUPLICATE</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $log->timestamp }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $log->ip_address }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">No verification records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Audit Log -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Audit Log</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ $auditLogs->count() }} entries (newest first, last 50)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Actor</th>
                        <th class="px-4 py-3 font-medium">Type</th>
                        <th class="px-4 py-3 font-medium">Action</th>
                        <th class="px-4 py-3 font-medium">Metadata</th>
                        <th class="px-4 py-3 font-medium">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($auditLogs as $entry)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 text-gray-700 font-medium">{{ $entry->actor_id }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $entry->actor_type === 'examiner' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $entry->actor_type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $entry->action }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500 max-w-[200px] truncate font-mono">{{ $entry->metadata }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $entry->created_at }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">No audit entries found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
