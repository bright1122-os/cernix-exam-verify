<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\CryptoService;
use App\Services\MockSISService;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use App\Services\RemitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentWebController extends Controller
{
    public function index()
    {
        $session = DB::table('exam_sessions')->where('is_active', true)->first();

        return view('student.register', compact('session'));
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'matric_no'  => 'required|string|max:50',
            'rrr_number' => 'required|string|max:50',
        ]);

        $session = DB::table('exam_sessions')->where('is_active', true)->first();

        if (! $session) {
            return response()->json(['success' => false, 'message' => 'No active exam session found.'], 422);
        }

        try {
            $regService = new RegistrationService(
                new MockSISService(),
                app(RemitaService::class),
                new CryptoService()
            );

            $result = $regService->registerStudent([
                'matric_no'       => $data['matric_no'],
                'full_name'       => '',
                'rrr_number'      => $data['rrr_number'],
                'expected_amount' => (float) $session->fee_amount,
                'session_id'      => (int) $session->session_id,
            ]);

            // Build the QR SVG — fetch hmac_signature from DB
            $tokenRow = DB::table('qr_tokens')
                ->where('token_id', $result['data']['token_id'])
                ->first();

            $qrService = new QrTokenService(new CryptoService());
            $qrSvg = $qrService->buildQrCode([
                'token_id'          => $result['data']['token_id'],
                'encrypted_payload' => $tokenRow->encrypted_payload,
                'hmac_signature'    => $tokenRow->hmac_signature,
                'session_id'        => (int) $session->session_id,
            ]);

            // Audit the registration
            app(AuditService::class)->logAction(
                $data['matric_no'],
                'student',
                'student.registered',
                ['token_id' => $result['data']['token_id'], 'session_id' => $session->session_id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data'    => array_merge($result['data'], ['qr_svg' => $qrSvg]),
            ]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
