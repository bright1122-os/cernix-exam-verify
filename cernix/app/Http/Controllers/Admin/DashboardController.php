<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\CryptoService;
use App\Services\QrTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function __construct(
        private readonly QrTokenService $qrTokenService,
        private readonly CryptoService $cryptoService,
        private readonly AuditService $auditService,
    ) {}

    // ── Guard ──────────────────────────────────────────────────────────────────

    private function forbidden(): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
    }

    private function isAdmin(): bool
    {
        return Auth::guard('api')->user()?->role === 'admin';
    }

    // ── Sessions ───────────────────────────────────────────────────────────────

    public function sessions(): JsonResponse
    {
        if (! $this->isAdmin()) return $this->forbidden();

        return response()->json([
            'status' => 'success',
            'data'   => DB::table('exam_sessions')->orderByDesc('session_id')->get(),
        ]);
    }

    public function createSession(Request $request): JsonResponse
    {
        if (! $this->isAdmin()) return $this->forbidden();

        $data = $request->validate([
            'semester'      => 'required|string|max:100',
            'academic_year' => 'required|string|max:20',
            'fee_amount'    => 'required|numeric|min:0',
        ]);

        $id = DB::table('exam_sessions')->insertGetId([
            'semester'      => $data['semester'],
            'academic_year' => $data['academic_year'],
            'fee_amount'    => $data['fee_amount'],
            'aes_key'       => $this->cryptoService->generateRandomKey(),
            'hmac_secret'   => $this->cryptoService->generateRandomKey(),
            'is_active'     => false,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Session created',
            'data'    => DB::table('exam_sessions')->where('session_id', $id)->first(),
        ], 201);
    }

    public function activateSession(Request $request, int $id): JsonResponse
    {
        if (! $this->isAdmin()) return $this->forbidden();

        if (! DB::table('exam_sessions')->where('session_id', $id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Session not found'], 404);
        }

        DB::transaction(function () use ($id) {
            DB::table('exam_sessions')->update(['is_active' => false, 'updated_at' => now()]);
            DB::table('exam_sessions')
                ->where('session_id', $id)
                ->update(['is_active' => true, 'updated_at' => now()]);
        });

        $this->auditService->logAction(
            (string) Auth::guard('api')->user()->id,
            'admin',
            'session.activated',
            ['session_id' => $id]
        );

        return response()->json(['status' => 'success', 'message' => 'Session activated']);
    }

    // ── Examiners ──────────────────────────────────────────────────────────────

    public function examiners(): JsonResponse
    {
        if (! $this->isAdmin()) return $this->forbidden();

        return response()->json([
            'status' => 'success',
            'data'   => DB::table('examiners')
                ->select('examiner_id', 'full_name', 'username', 'role', 'is_active', 'created_at')
                ->orderByDesc('examiner_id')
                ->get(),
        ]);
    }

    public function createExaminer(Request $request): JsonResponse
    {
        if (! $this->isAdmin()) return $this->forbidden();

        $data = $request->validate([
            'full_name' => 'required|string|max:100',
            'username'  => 'required|string|max:100|unique:examiners,username',
            'password'  => 'required|string|min:8',
            'role'      => 'in:examiner,admin',
        ]);

        $id = DB::table('examiners')->insertGetId([
            'full_name'     => $data['full_name'],
            'username'      => $data['username'],
            'password_hash' => Hash::make($data['password']),
            'role'          => $data['role'] ?? 'examiner',
            'is_active'     => false,
            'created_at'    => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Examiner created',
            'data'    => DB::table('examiners')
                ->select('examiner_id', 'full_name', 'username', 'role', 'is_active', 'created_at')
                ->where('examiner_id', $id)
                ->first(),
        ], 201);
    }

    public function toggleExaminer(Request $request, int $id): JsonResponse
    {
        if (! $this->isAdmin()) return $this->forbidden();

        $examiner = DB::table('examiners')->where('examiner_id', $id)->first();

        if (! $examiner) {
            return response()->json(['status' => 'error', 'message' => 'Examiner not found'], 404);
        }

        $newState = ! $examiner->is_active;
        DB::table('examiners')->where('examiner_id', $id)->update(['is_active' => $newState]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Examiner status updated',
            'data'    => ['is_active' => $newState],
        ]);
    }

    // ── Tokens ─────────────────────────────────────────────────────────────────

    public function revokeToken(Request $request, string $id): JsonResponse
    {
        if (! $this->isAdmin()) return $this->forbidden();

        try {
            $this->qrTokenService->revoke($id);

            $this->auditService->logAction(
                (string) Auth::guard('api')->user()->id,
                'admin',
                'token.revoked',
                ['token_id' => $id]
            );

            return response()->json(['status' => 'success', 'message' => 'Token revoked']);

        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    // ── Logs & Stats ───────────────────────────────────────────────────────────

    public function logs(Request $request): JsonResponse
    {
        if (! $this->isAdmin()) return $this->forbidden();

        $query = DB::table('verification_logs')->orderByDesc('timestamp');

        if ($request->filled('examiner_id')) {
            $query->where('examiner_id', (int) $request->input('examiner_id'));
        }

        if ($request->filled('decision')) {
            $decision = strtoupper($request->input('decision'));
            if (in_array($decision, ['APPROVED', 'REJECTED', 'DUPLICATE'], true)) {
                $query->where('decision', $decision);
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->limit(100)->get(),
        ]);
    }

    public function stats(): JsonResponse
    {
        if (! $this->isAdmin()) return $this->forbidden();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total'     => DB::table('verification_logs')->count(),
                'approved'  => DB::table('verification_logs')->where('decision', 'APPROVED')->count(),
                'rejected'  => DB::table('verification_logs')->where('decision', 'REJECTED')->count(),
                'duplicate' => DB::table('verification_logs')->where('decision', 'DUPLICATE')->count(),
            ],
        ]);
    }
}
