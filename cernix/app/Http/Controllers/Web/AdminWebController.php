<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWebController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('verification_logs')->orderByDesc('timestamp');

        if ($request->filled('examiner_id')) {
            $query->where('examiner_id', (int) $request->input('examiner_id'));
        }

        if ($request->filled('decision')) {
            $allowedDecisions = ['APPROVED', 'REJECTED', 'DUPLICATE'];
            $decision = strtoupper($request->input('decision'));
            if (in_array($decision, $allowedDecisions, true)) {
                $query->where('decision', $decision);
            }
        }

        $verificationLogs = $query->limit(100)->get();

        $auditLogs = DB::table('audit_log')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $stats = [
            'total'     => DB::table('verification_logs')->count(),
            'approved'  => DB::table('verification_logs')->where('decision', 'APPROVED')->count(),
            'rejected'  => DB::table('verification_logs')->where('decision', 'REJECTED')->count(),
            'duplicate' => DB::table('verification_logs')->where('decision', 'DUPLICATE')->count(),
        ];

        return view('admin.dashboard', compact('verificationLogs', 'auditLogs', 'stats'));
    }
}
