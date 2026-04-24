<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\CryptoService;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExaminerWebController extends Controller
{
    public function login()
    {
        return view('examiner.login');
    }

    public function index()
    {
        return view('examiner.dashboard');
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'qr_data' => 'required|array',
        ]);

        // Use the first active examiner for the demo; in production this would come from session auth
        $examiner = DB::table('examiners')->where('is_active', true)->first();
        $examinerId = $examiner ? (int) $examiner->examiner_id : 0;

        $deviceFp = substr(md5($request->userAgent() ?? 'unknown'), 0, 16);
        $ip       = $request->ip() ?? '0.0.0.0';

        try {
            $service = new VerificationService(new CryptoService());
            $result  = $service->verifyQr($data['qr_data'], $examinerId, $deviceFp, $ip);

            if ($result['status'] === 'APPROVED') {
                app(AuditService::class)->logAction(
                    (string) $examinerId,
                    'examiner',
                    'scan.approved',
                    ['token_id' => $result['token_id']]
                );
            }

            return response()->json($result);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'REJECTED',
                'student' => null,
                'token_id'  => null,
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }
}
