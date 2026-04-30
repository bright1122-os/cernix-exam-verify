<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminWebController extends Controller
{
    public function index(Request $request)
    {
        $role = Roles::normalize($request->session()->get('examiner_role'));

        if (! $request->session()->has('examiner_id')) {
            return redirect('/examiner/login');
        }

        if (! Roles::isAdminLike($role)) {
            abort(403);
        }

        $query = DB::table('verification_logs')
            ->join('examiners', 'verification_logs.examiner_id', '=', 'examiners.examiner_id', 'left')
            ->select('verification_logs.*', 'examiners.username as examiner_username')
            ->orderByDesc('verification_logs.timestamp');

        if ($request->filled('examiner_id')) {
            $query->where('verification_logs.examiner_id', (int) $request->input('examiner_id'));
        }

        if ($request->filled('decision')) {
            $allowedDecisions = ['APPROVED', 'REJECTED', 'DUPLICATE'];
            $decision = strtoupper($request->input('decision'));
            if (in_array($decision, $allowedDecisions, true)) {
                $query->where('verification_logs.decision', $decision);
            }
        }

        if ($request->filled('session_id')) {
            $query->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
                ->where('qr_tokens.session_id', (int) $request->input('session_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('verification_logs.timestamp', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('verification_logs.timestamp', '<=', $request->input('date_to'));
        }

        $verificationLogs = $query->paginate(50)->withQueryString();

        $auditLogs = DB::table('audit_log')
            ->orderByDesc('timestamp')
            ->paginate(50, ['*'], 'audit_page')
            ->withQueryString();

        $stats = Cache::remember('web_admin_stats:' . md5(json_encode($request->only(['session_id', 'date_from', 'date_to']))), 30, function () use ($request) {
            $base = DB::table('verification_logs')
                ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id');

            if ($request->filled('session_id')) {
                $base->where('qr_tokens.session_id', (int) $request->input('session_id'));
            }
            if ($request->filled('date_from')) {
                $base->whereDate('verification_logs.timestamp', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $base->whereDate('verification_logs.timestamp', '<=', $request->input('date_to'));
            }

            $counts = (clone $base)
                ->select('verification_logs.decision', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('verification_logs.decision')
                ->pluck('aggregate', 'decision');

            $total = (int) $counts->sum();
            $approved = (int) ($counts['APPROVED'] ?? 0);
            $rejected = (int) ($counts['REJECTED'] ?? 0);

            return [
                'total'        => $total,
                'approved'     => $approved,
                'rejected'     => $rejected,
                'duplicate'    => (int) ($counts['DUPLICATE'] ?? 0),
                'success_rate' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
                'reject_rate'  => $total > 0 ? round(($rejected / $total) * 100, 1) : 0,
                'examiners'    => DB::table('examiners')->where('is_active', true)->count(),
                'daily'        => (clone $base)
                    ->select(DB::raw('DATE(verification_logs.timestamp) as day'), DB::raw('COUNT(*) as total'))
                    ->groupBy('day')
                    ->orderBy('day')
                    ->limit(30)
                    ->get(),
            ];
        });

        $activeSession = DB::table('exam_sessions')->where('is_active', true)->first();
        $sessions = DB::table('exam_sessions')->orderByDesc('session_id')->get();
        $examinerStats = DB::table('examiners')
            ->leftJoin('verification_logs', 'examiners.examiner_id', '=', 'verification_logs.examiner_id')
            ->select(
                'examiners.*',
                DB::raw('COUNT(verification_logs.log_id) as scans_performed'),
                DB::raw("SUM(CASE WHEN verification_logs.decision = 'APPROVED' THEN 1 ELSE 0 END) as approved_scans")
            )
            ->groupBy('examiners.examiner_id', 'examiners.full_name', 'examiners.username', 'examiners.password_hash', 'examiners.role', 'examiners.admin_user_id', 'examiners.is_active', 'examiners.last_active_at', 'examiners.created_at')
            ->orderBy('examiners.full_name')
            ->paginate(25, ['*'], 'examiner_page')
            ->withQueryString();

        $studentTrace = collect();
        if ($request->filled('matric_no')) {
            $studentTrace = DB::table('verification_logs')
                ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
                ->join('exam_sessions', 'qr_tokens.session_id', '=', 'exam_sessions.session_id')
                ->leftJoin('examiners', 'verification_logs.examiner_id', '=', 'examiners.examiner_id')
                ->where('qr_tokens.student_id', $request->input('matric_no'))
                ->select('verification_logs.*', 'examiners.full_name as examiner_name', 'exam_sessions.semester', 'exam_sessions.academic_year')
                ->orderByDesc('verification_logs.timestamp')
                ->get();
        }

        return view('admin.dashboard', compact('verificationLogs', 'auditLogs', 'stats', 'activeSession', 'sessions', 'examinerStats', 'studentTrace'));
    }
}
