<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\CryptoService;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ExaminerWebController extends Controller
{
    public function loginForm()
    {
        if (session('examiner_id')) {
            return redirect('/examiner/dashboard');
        }

        return view('examiner.login');
    }

    public function loginSubmit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $examiner = DB::table('examiners')
            ->where('username', $data['username'])
            ->where('is_active', true)
            ->first();

        if (! $examiner || ! Hash::check($data['password'], $examiner->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password.',
            ], 401);
        }

        $request->session()->put('examiner_id', $examiner->examiner_id);
        $request->session()->put('examiner_username', $examiner->username);

        return response()->json(['success' => true]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->session()->forget(['examiner_id', 'examiner_username']);

        return response()->json(['success' => true]);
    }

    public function index(Request $request)
    {
        if (! $request->session()->get('examiner_id')) {
            return redirect('/examiner/login');
        }

        return view('examiner.dashboard');
    }

    public function verify(Request $request): JsonResponse
    {
        if (! $request->session()->get('examiner_id')) {
            return response()->json(['status' => 'REJECTED', 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'qr_data' => 'required|array',
        ]);

        $examinerId = (int) $request->session()->get('examiner_id');
        $deviceFp   = substr(md5($request->userAgent() ?? 'unknown'), 0, 16);
        $ip         = $request->ip() ?? '0.0.0.0';

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
                'status'    => 'REJECTED',
                'student'   => null,
                'token_id'  => null,
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }
}
