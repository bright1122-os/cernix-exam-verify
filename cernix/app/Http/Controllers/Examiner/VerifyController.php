<?php

namespace App\Http\Controllers\Examiner;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerifyController extends Controller
{
    public function __construct(
        private readonly VerificationService $verificationService,
        private readonly AuditService $auditService,
    ) {}

    public function verify(Request $request): JsonResponse
    {
        if (Auth::guard('api')->user()?->role !== 'examiner') {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'qr_data' => 'required|array',
        ]);

        // Map JWT user to examiners table by email ↔ username; fall back to first active examiner
        $user     = Auth::guard('api')->user();
        $examiner = DB::table('examiners')
            ->where('username', $user->email)
            ->where('is_active', true)
            ->first()
            ?? DB::table('examiners')->where('is_active', true)->first();

        $examinerId = $examiner ? (int) $examiner->examiner_id : 0;
        $deviceFp   = substr(md5($request->userAgent() ?? 'unknown'), 0, 16);
        $ip         = $request->ip() ?? '0.0.0.0';

        $result = $this->verificationService->verifyQr($data['qr_data'], $examinerId, $deviceFp, $ip);

        if ($result['status'] === 'APPROVED') {
            $this->auditService->logAction(
                (string) $examinerId,
                'examiner',
                'scan.approved',
                ['token_id' => $result['token_id']]
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Verification complete',
            'data'    => $result,
        ]);
    }
}
