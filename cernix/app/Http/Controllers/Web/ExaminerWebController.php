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
            'today' => (clone $base)->whereDate('timestamp', today())->count(),
            'last_scan_at' => (clone $base)->max('timestamp'),
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
                'students.level',
                'departments.dept_name',
                'exam_sessions.semester',
                'exam_sessions.academic_year'
            )
            ->orderByDesc('verification_logs.timestamp')
            ->limit(25)
            ->get();

        $activeSession = DB::table('exam_sessions')->where('is_active', true)->orderByDesc('created_at')->first();
        $todaysExams = DB::table('timetables')
            ->join('departments', 'timetables.department_id', '=', 'departments.dept_id')
            ->leftJoin('exam_sessions', 'timetables.exam_session_id', '=', 'exam_sessions.session_id')
            ->whereDate('timetables.exam_date', today())
            ->select('timetables.*', 'departments.dept_name', 'exam_sessions.name as session_name', 'exam_sessions.semester')
            ->orderBy('timetables.start_time')
            ->limit(12)
            ->get();

        return view('examiner.dashboard', compact('examiner', 'examinerStats', 'scanHistory', 'activeSession', 'todaysExams'));
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
            $result = $this->enrichStudentContext($result);
            $result['today_exam'] = $this->todayExamContext($result, $data['qr_data']);

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

    public function showScan(Request $request, int $log)
    {
        $actor = Auth::guard('examiner')->user();
        abort_unless($actor && Roles::normalize($actor->role) === Roles::EXAMINER, 403);

        $scan = DB::table('verification_logs')
            ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
            ->leftJoin('students', 'qr_tokens.student_id', '=', 'students.matric_no')
            ->leftJoin('departments', 'students.department_id', '=', 'departments.dept_id')
            ->leftJoin('exam_sessions', 'qr_tokens.session_id', '=', 'exam_sessions.session_id')
            ->where('verification_logs.log_id', $log)
            ->where('verification_logs.examiner_id', (int) $actor->examiner_id)
            ->select(
                'verification_logs.*',
                'qr_tokens.student_id',
                'qr_tokens.status as token_status',
                'qr_tokens.session_id',
                'qr_tokens.issued_at',
                'qr_tokens.used_at',
                'students.full_name',
                'students.photo_path',
                'students.department_id',
                'students.level',
                'departments.dept_name',
                'exam_sessions.name as session_name',
                'exam_sessions.semester',
                'exam_sessions.academic_year'
            )
            ->first();
        abort_unless($scan, 404);

        $scanCounts = collect();
        $studentHistory = collect();
        $todayExam = null;

        if ($scan->student_id) {
            $scanCounts = DB::table('verification_logs')
                ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
                ->where('qr_tokens.student_id', $scan->student_id)
                ->select('verification_logs.decision', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('verification_logs.decision')
                ->pluck('aggregate', 'decision');

            $studentHistory = DB::table('verification_logs')
                ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
                ->leftJoin('examiners', 'verification_logs.examiner_id', '=', 'examiners.examiner_id')
                ->where('qr_tokens.student_id', $scan->student_id)
                ->select('verification_logs.*', 'examiners.full_name as examiner_name')
                ->orderByDesc('verification_logs.timestamp')
                ->limit(12)
                ->get();

            $todayExam = DB::table('timetables')
                ->where('exam_session_id', (int) $scan->session_id)
                ->where('department_id', (int) $scan->department_id)
                ->where('level', (string) $scan->level)
                ->whereDate('exam_date', today())
                ->orderBy('start_time')
                ->first();
        }

        return view('examiner.scan-detail', [
            'examiner' => [
                'full_name' => $actor->full_name,
                'username' => $actor->username,
                'role' => Roles::normalize($actor->role),
            ],
            'scan' => $scan,
            'scanCounts' => $scanCounts,
            'studentHistory' => $studentHistory,
            'todayExam' => $todayExam,
        ]);
    }

    private function todayExamContext(array $result, array $qrData): ?array
    {
        $student = $result['student'] ?? null;
        if (! is_array($student) || empty($student['matric_no'])) {
            return null;
        }

        $studentRow = DB::table('students')
            ->where('matric_no', (string) $student['matric_no'])
            ->first(['department_id', 'level']);

        if (! $studentRow || empty($studentRow->department_id) || empty($studentRow->level)) {
            return null;
        }

        $entry = DB::table('timetables')
            ->where('exam_session_id', (int) ($qrData['session_id'] ?? 0))
            ->where('department_id', (int) $studentRow->department_id)
            ->where('level', (string) $studentRow->level)
            ->whereDate('exam_date', today())
            ->whereIn('status', ['scheduled', 'active', 'completed'])
            ->orderBy('start_time')
            ->first();

        if (! $entry) {
            return ['status' => 'none', 'label' => 'No exam scheduled today'];
        }

        $label = 'Today';
        if ($entry->end_time && now()->gt(\Carbon\Carbon::parse($entry->exam_date . ' ' . $entry->end_time))) {
            $label = 'Missed / Ended';
        }

        return [
            'status' => strtolower(str_replace([' ', '/'], ['_', ''], $label)),
            'label' => $label,
            'course_code' => $entry->course_code,
            'course_title' => $entry->course_title,
            'start_time' => substr((string) $entry->start_time, 0, 5),
            'end_time' => $entry->end_time ? substr((string) $entry->end_time, 0, 5) : null,
            'venue' => $entry->venue,
        ];
    }

    private function enrichStudentContext(array $result): array
    {
        $student = $result['student'] ?? null;
        if (! is_array($student) || empty($student['matric_no'])) {
            return $result;
        }

        $studentRow = DB::table('students')
            ->leftJoin('departments', 'students.department_id', '=', 'departments.dept_id')
            ->where('students.matric_no', (string) $student['matric_no'])
            ->select('students.level', 'students.photo_path', 'students.department_id', 'departments.dept_name')
            ->first();

        if ($studentRow) {
            $result['student']['level'] = $studentRow->level;
            $result['student']['department'] = $studentRow->dept_name ?: ($result['student']['department'] ?? null);
            $result['student']['photo_path'] = $studentRow->photo_path ?: ($result['student']['photo_path'] ?? null);
        }

        $summary = DB::table('verification_logs')
            ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
            ->where('qr_tokens.student_id', (string) $student['matric_no'])
            ->select('verification_logs.decision', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('verification_logs.decision')
            ->pluck('aggregate', 'decision');

        $result['scan_summary'] = [
            'total' => (int) $summary->sum(),
            'approved' => (int) ($summary['APPROVED'] ?? 0),
            'rejected' => (int) ($summary['REJECTED'] ?? 0),
            'duplicate' => (int) ($summary['DUPLICATE'] ?? 0),
        ];

        return $result;
    }
}
