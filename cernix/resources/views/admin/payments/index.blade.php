@extends('layouts.admin')

@section('title', 'Payments')
@section('page_title', 'Payments')
@section('breadcrumb', 'Admin / Payments')

@php use Carbon\Carbon; @endphp

@section('content')
<section class="card">
    <div class="card-head">
        <div>
            <h2>Payment Records</h2>
            <p class="section-copy">Verified Remita records linked to student registrations. Secrets and raw credentials are never displayed.</p>
        </div>
        <form method="GET" class="inline-search">
            <input name="search" value="{{ request('search') }}" placeholder="Matric, RRR, or name">
            <button class="btn" type="submit">Search</button>
        </form>
    </div>
    @if($payments->count())
        <div class="table-wrap">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>RRR</th>
                        <th>Declared</th>
                        <th>Confirmed</th>
                        <th>Status</th>
                        <th>Verified</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td data-label="Student">
                                <strong>{{ $payment->full_name ?? 'Student unavailable' }}</strong>
                                <div class="muted mono">{{ $payment->student_id }}</div>
                                <div class="muted">{{ $payment->dept_name ?? 'Department unavailable' }}{{ $payment->level ? ' · '.$payment->level : '' }}</div>
                            </td>
                            <td data-label="RRR" class="mono">{{ $payment->rrr_number }}</td>
                            <td data-label="Declared">₦{{ number_format((float) $payment->amount_declared, 2) }}</td>
                            <td data-label="Confirmed">₦{{ number_format((float) $payment->amount_confirmed, 2) }}</td>
                            <td data-label="Status"><span class="badge green">Verified</span></td>
                            <td data-label="Verified">{{ $payment->verified_at ? Carbon::parse($payment->verified_at)->format('d M Y, H:i') : 'Unavailable' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pager">{{ $payments->links() }}</div>
    @else
        <div class="empty">No payment records found.</div>
    @endif
</section>
@endsection
