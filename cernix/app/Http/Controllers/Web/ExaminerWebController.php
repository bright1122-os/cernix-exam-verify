<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\CryptoService;
use App\Services\VerificationService;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExaminerWebController extends Controller
{
    public function index(Request $request)
    {
        $actor = Auth::guard('examiner')->user();

        $role = Roles::normalize($actor->role);

        if ($role !== Roles::EXAMINER) {
            abort(403);
        }

        $request->session()->put('examiner_role', $role);
        $request->session()->put('examiner_name', $actor->full_name);
        $request->session()->put('examiner_username', $actor->username);

        $examiner = [
            'id'        => (int) $actor->examiner_id,
            'full_name' => $actor->full_name,
            'username'  => $actor->username,
            'role'      => $role,
        ];

        $base = DB::table('verification_logs')->where('examiner_id', $examiner['id']);
        $counts = (clone $base)
            ->select('decision', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('decision')
            ->pluck('aggregate', 'decision');

        $examinerStats = [
            'total' => (int) $counts->sum(),
            'approved' => (int) ($counts['APPROVED'] ?? 0),
            'rejected' => (int) ($counts['REJECTED'] ?? 0),
            'duplicate' => (int) ($counts['DUPLICATE'] ?? 0),
            'trend' => (clone $base)
                ->select(DB::raw('DATE(timestamp) as day'), DB::raw('COUNT(*) as total'))
                ->groupBy('day')
                ->orderBy('day')
                ->limit(14)
                ->get(),
        ];

        $scanHistory = DB::table('verification_logs')
            ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
            ->join('exam_sessions', 'qr_tokens.session_id', '=', 'exam_sessions.session_id')
            ->leftJoin('students', 'qr_tokens.student_id', '=', 'students.matric_no')
            ->leftJoin('departments', 'students.department_id', '=', 'departments.dept_id')
            ->where('verification_logs.examiner_id', $examiner['id'])
            ->select(
                'verification_logs.*',
                'qr_tokens.student_id as matric_no',
                'students.full_name as student_name',
                'students.photo_path',
                'departments.dept_name',
                'exam_sessions.semester',
                'exam_sessions.academic_year'
            )
            ->orderByDesc('verification_logs.timestamp')
            ->limit(25)
            ->get();

        return view('examiner.dashboard', compact('examiner', 'examinerStats', 'scanHistory'));
    }

    public function verify(Request $request): JsonResponse
    {
        if (! $request->session()->has('examiner_id')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Not authenticated.',
            ], 401);
        }

        $data = $request->validate([
            'qr_data' => 'required|array',
        ]);

        $examinerId = (int) $request->session()->get('examiner_id');

        $deviceFp = substr(md5($request->userAgent() ?? 'unknown'), 0, 16);
        $ip       = $request->ip() ?? '0.0.0.0';

        try {
            $service = new VerificationService(new CryptoService());
            $result  = $service->verifyQr($data['qr_data'], $examinerId, $deviceFp, $ip);

            // Surface examiner identity for the verification card
            $result['examiner'] = $request->session()->get('examiner_name', 'Examiner');

            DB::table('examiners')->where('examiner_id', $examinerId)->update(['last_active_at' => now()]);

            app(AuditService::class)->logAction(
                (string) $examinerId,
                'examiner',
                'scan.' . strtolower($result['status']),
                [
                    'token_id' => $result['token_id'] ?? null,
                    'reason' => $result['reason'] ?? null,
                ],
                'qr_token',
                $result['token_id'] ?? null,
                null,
                ['decision' => $result['status']],
                isset($result['trace_id']) ? (string) $result['trace_id'] : null,
                isset($data['qr_data']['session_id']) ? (int) $data['qr_data']['session_id'] : null
            );

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
