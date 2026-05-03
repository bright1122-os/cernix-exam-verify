<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CryptoService;
use App\Services\QrTokenService;
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

        $qrSvg = null;
        if ($token) {
            $qrSvg = (new QrTokenService(new CryptoService()))->buildQrCode([
                'token_id' => $token->token_id,
                'encrypted_payload' => $token->encrypted_payload,
                'hmac_signature' => $token->hmac_signature,
                'session_id' => (int) $token->session_id,
            ]);
        }

        return view('student.dashboard', compact('student', 'token', 'session', 'qrSvg'));
    }
}
