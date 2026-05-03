<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CryptoService;
use App\Services\QrTokenService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function index(): View
    {
        $student = Auth::guard('student')->user();
        $token = DB::table('qr_tokens')
            ->where('student_id', $student->matric_no)
            ->orderByDesc('issued_at')
            ->first();

        $session = $token
            ? DB::table('exam_sessions')->where('session_id', (int) $token->session_id)->first()
            : DB::table('exam_sessions')->where('session_id', (int) $student->session_id)->first();

        $department = DB::table('departments')->where('dept_id', (int) $student->department_id)->first();
        $timetable = collect();
        $nextExam = null;

        if ($session && $department && $student->level) {
            $timetable = DB::table('timetables')
                ->where('exam_session_id', (int) $session->session_id)
                ->where('department_id', (int) $student->department_id)
                ->where('level', (string) $student->level)
                ->orderBy('exam_date')
                ->orderBy('start_time')
                ->get()
                ->map(function ($entry) {
                    $entry->portal_status = $this->portalExamStatus($entry);
                    return $entry;
                });

            $nextExam = $timetable
                ->first(fn ($entry) => in_array($entry->portal_status, ['today', 'upcoming'], true) && $entry->status !== 'cancelled')
                ?: $timetable->first();
        }

        $qrSvg = null;
        if ($token) {
            $qrSvg = (new QrTokenService(new CryptoService()))->buildQrCode([
                'token_id' => $token->token_id,
                'encrypted_payload' => $token->encrypted_payload,
                'hmac_signature' => $token->hmac_signature,
                'session_id' => (int) $token->session_id,
            ]);
        }

        return view('student.dashboard', compact('student', 'token', 'session', 'department', 'timetable', 'nextExam', 'qrSvg'));
    }

    private function portalExamStatus(object $entry): string
    {
        if ($entry->status === 'cancelled') {
            return 'cancelled';
        }

        $date = Carbon::parse($entry->exam_date);

        if ($date->isToday()) {
            if ($entry->end_time && now()->gt(Carbon::parse($entry->exam_date . ' ' . $entry->end_time))) {
                return 'missed';
            }

            return 'today';
        }

        return $date->isFuture() ? 'upcoming' : 'missed';
    }
}
