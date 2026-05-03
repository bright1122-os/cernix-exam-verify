<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CERNIX Exam Pass</title>
    <style>
        :root { --bg:#f4f4ef; --card:#fff; --line:#e6e4dc; --ink:#0a0f1f; --muted:#6b7085; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: var(--ink); background: var(--bg); }
        .page { max-width: 900px; margin: 0 auto; padding: 28px; }
        .pass { background: var(--card); border: 1px solid var(--line); border-radius: 20px; overflow: hidden; }
        .head { display: flex; align-items: center; gap: 16px; padding: 22px; border-bottom: 1px solid var(--line); }
        .head img.logo { width: 56px; height: 56px; object-fit: contain; }
        .head h1 { margin: 0; font-size: 22px; line-height: 1.15; }
        .head p { margin: 4px 0 0; color: var(--muted); }
        .body { padding: 22px; display: grid; grid-template-columns: 1fr 260px; gap: 22px; align-items: start; }
        .identity { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 18px; }
        .photo { width: 82px; height: 100px; border-radius: 14px; border: 1px solid var(--line); background: var(--bg); display: grid; place-items: center; overflow: hidden; font-weight: 800; color: var(--muted); }
        .photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        h2 { margin: 0 0 6px; font-size: 24px; }
        .muted { color: var(--muted); }
        .meta { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 16px; }
        .box { border: 1px solid var(--line); border-radius: 14px; padding: 12px; }
        .box span { display: block; color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; }
        .box b { display: block; margin-top: 4px; font-size: 14px; }
        .qr { border: 1px solid var(--line); border-radius: 16px; padding: 14px; text-align: center; }
        .qr svg { width: 100%; height: auto; display: block; }
        .section { padding: 0 22px 22px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-top: 1px solid var(--line); padding: 10px; text-align: left; font-size: 13px; }
        th { color: var(--muted); text-transform: uppercase; letter-spacing: .05em; font-size: 11px; }
        .print-actions { display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 14px; }
        .btn { border: 1px solid var(--line); background: #fff; border-radius: 12px; padding: 10px 14px; font-weight: 800; cursor: pointer; }
        @media print {
            body { background: #fff; }
            .page { padding: 0; }
            .print-actions { display: none; }
            .pass { border-radius: 0; }
        }
        @media (max-width: 720px) {
            .body { grid-template-columns: 1fr; }
            .meta { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
@php
    use Carbon\Carbon;
    $photoUrl = $student->photo_path ? url('/photo-thumb/' . basename($student->photo_path)) : null;
    $initials = strtoupper(substr(trim($student->full_name ?: $student->matric_no), 0, 1));
@endphp
<main class="page">
    <div class="print-actions">
        <button class="btn" type="button" onclick="window.print()">Print / Save PDF</button>
    </div>
    <article class="pass">
        <header class="head">
            <img class="logo" src="/aaua-logo.png" alt="Adekunle Ajasin University">
            <div>
                <h1>Adekunle Ajasin University</h1>
                <p>CERNIX Exam Access Pass · {{ $session->name ?? $session->semester ?? 'Exam Session' }}</p>
            </div>
        </header>
        <div class="body">
            <section>
                <div class="identity">
                    <span class="photo">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="">
                        @else
                            {{ $initials }}
                        @endif
                    </span>
                    <div>
                        <h2>{{ $student->full_name }}</h2>
                        <div class="muted">{{ $student->matric_no }}</div>
                        <div class="muted">{{ $department->dept_name ?? 'Department unavailable' }} · {{ $student->level ?? 'Level unavailable' }}</div>
                    </div>
                </div>
                <div class="meta">
                    <div class="box"><span>Payment</span><b>{{ $payment ? 'Verified' : 'Unavailable' }}</b></div>
                    <div class="box"><span>RRR</span><b>{{ $payment->rrr_number ?? 'Unavailable' }}</b></div>
                    <div class="box"><span>QR Status</span><b>{{ $token->status ?? 'Not generated' }}</b></div>
                    <div class="box"><span>Token</span><b>{{ $token->token_id ?? 'Unavailable' }}</b></div>
                    <div class="box"><span>Generated</span><b>{{ now()->format('d M Y, H:i') }}</b></div>
                </div>
            </section>
            <aside class="qr">
                @if($qrSvg)
                    {!! $qrSvg !!}
                @else
                    <p class="muted">QR unavailable</p>
                @endif
            </aside>
        </div>
        <section class="section">
            <h3>Exam Timetable</h3>
            @if($timetable->count())
                <table>
                    <thead>
                        <tr><th>Course</th><th>Date</th><th>Time</th><th>Venue</th></tr>
                    </thead>
                    <tbody>
                        @foreach($timetable as $entry)
                            <tr>
                                <td><b>{{ $entry->course_code }}</b><br>{{ $entry->course_title }}</td>
                                <td>{{ Carbon::parse($entry->exam_date)->format('d M Y') }}</td>
                                <td>{{ substr($entry->start_time, 0, 5) }}{{ $entry->end_time ? ' - ' . substr($entry->end_time, 0, 5) : '' }}</td>
                                <td>{{ $entry->venue }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="muted">No timetable entries have been published for this pass.</p>
            @endif
        </section>
    </article>
</main>
</body>
</html>
