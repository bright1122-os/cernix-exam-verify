<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\AuditService;
use App\Services\CryptoService;
use App\Support\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminWebController extends Controller
{
    public function index(Request $request)
    {
        $adminActor = $this->adminActor($request);

        return view('admin.dashboard', array_merge($this->dashboardData($request), [
            'adminActor' => $adminActor,
            'pageTitle' => 'Dashboard',
            'breadcrumbs' => ['Admin', 'Dashboard'],
        ]));
    }

    public function sessions(Request $request)
    {
        $adminActor = $this->adminActor($request);

        return view('admin.sessions.index', [
            'adminActor' => $adminActor,
            'sessions' => $this->sessionsQuery($request)->paginate(15, ['*'], 'sessions_page')->withQueryString(),
            'allExaminers' => $this->activeExaminers(),
            'pageTitle' => 'Exam Sessions',
            'breadcrumbs' => ['Admin', 'Exam Sessions'],
        ]);
    }

    public function showSession(Request $request, int $session)
    {
        $adminActor = $this->adminActor($request);
        $sessionRow = $this->sessionsQuery($request)->where('exam_sessions.session_id', $session)->first();
        abort_unless($sessionRow, 404);

        $students = DB::table('students')
            ->leftJoin('departments', 'students.department_id', '=', 'departments.dept_id')
            ->leftJoin('qr_tokens', function ($join) {
                $join->on('students.matric_no', '=', 'qr_tokens.student_id')
                    ->on('students.session_id', '=', 'qr_tokens.session_id');
            })
            ->where('students.session_id', $session)
            ->select('students.*', 'departments.dept_name', 'qr_tokens.status as token_status')
            ->orderBy('students.full_name')
            ->paginate(20, ['*'], 'students_page')
            ->withQueryString();

        return view('admin.sessions.show', [
            'adminActor' => $adminActor,
            'session' => $sessionRow,
            'students' => $students,
            'pageTitle' => $this->sessionName($sessionRow),
            'breadcrumbs' => ['Admin', 'Exam Sessions', $this->sessionName($sessionRow)],
        ]);
    }

    public function examiners(Request $request)
    {
        $adminActor = $this->adminActor($request);

        return view('admin.examiners.index', [
            'adminActor' => $adminActor,
            'examiners' => $this->examinersQuery()->paginate(15, ['*'], 'examiners_page')->withQueryString(),
            'pageTitle' => 'Examiners',
            'breadcrumbs' => ['Admin', 'Examiners'],
        ]);
    }

    public function showExaminer(Request $request, int $examiner)
    {
        $adminActor = $this->adminActor($request);
        $examinerRow = DB::table('examiners')
            ->where('examiner_id', $examiner)
            ->whereIn('role', [Roles::EXAMINER, 'examiner'])
            ->first();
        abort_unless($examinerRow, 404);

        $sessions = DB::table('exam_sessions')
            ->where('examiner_id', $examiner)
            ->select(
                'exam_sessions.*',
                DB::raw('(SELECT COUNT(*) FROM students WHERE students.session_id = exam_sessions.session_id) as student_count'),
                DB::raw('(SELECT COUNT(*) FROM qr_tokens INNER JOIN verification_logs ON verification_logs.token_id = qr_tokens.token_id WHERE qr_tokens.session_id = exam_sessions.session_id) as scan_count')
            )
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($session) {
                [$statusText, $statusClass] = $this->sessionStatus($session);
                $session->status_text = $statusText;
                $session->status_class = $statusClass;

                return $session;
            });
        $decisionCounts = DB::table('verification_logs')
            ->where('examiner_id', $examiner)
            ->select('decision', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('decision')
            ->pluck('aggregate', 'decision');
        $recentScans = $this->scanLogsQuery(new Request())
            ->where('verification_logs.examiner_id', $examiner)
            ->limit(10)
            ->get();
        $activity = ActivityLog::query()
            ->where('user_id', $examiner)
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.examiners.show', [
            'adminActor' => $adminActor,
            'examiner' => $examinerRow,
            'sessions' => $sessions,
            'totalSessions' => $sessions->count(),
            'totalStudents' => $sessions->sum('student_count'),
            'totalScans' => $sessions->sum('scan_count'),
            'decisionCounts' => $decisionCounts,
            'recentScans' => $recentScans,
            'activity' => $activity,
            'pageTitle' => $examinerRow->full_name,
            'breadcrumbs' => ['Admin', 'Examiners', $examinerRow->full_name],
        ]);
    }

    public function students(Request $request)
    {
        $adminActor = $this->adminActor($request);

        return view('admin.students.index', [
            'adminActor' => $adminActor,
            'students' => $this->studentsQuery($request)->paginate(20, ['*'], 'students_page')->withQueryString(),
            'departments' => DB::table('departments')->orderBy('dept_name')->get(),
            'sessions' => DB::table('exam_sessions')->orderByDesc('created_at')->get(),
            'pageTitle' => 'Students',
            'breadcrumbs' => ['Admin', 'Students'],
        ]);
    }

    public function showStudent(Request $request, string $student)
    {
        $adminActor = $this->adminActor($request);
        $studentRow = DB::table('students')
            ->leftJoin('departments', 'students.department_id', '=', 'departments.dept_id')
            ->leftJoin('exam_sessions', 'students.session_id', '=', 'exam_sessions.session_id')
            ->where('students.matric_no', $student)
            ->select('students.*', 'departments.dept_name', 'exam_sessions.name as session_name', 'exam_sessions.semester', 'exam_sessions.academic_year')
            ->first();
        abort_unless($studentRow, 404);

        $scanHistory = DB::table('verification_logs')
            ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
            ->leftJoin('examiners', 'verification_logs.examiner_id', '=', 'examiners.examiner_id')
            ->where('qr_tokens.student_id', $student)
            ->select('verification_logs.*', 'examiners.full_name as examiner_name')
            ->orderByDesc('verification_logs.timestamp')
            ->paginate(20, ['*'], 'scan_page');

        $token = DB::table('qr_tokens')->where('student_id', $student)->orderByDesc('issued_at')->first();
        $payment = DB::table('payment_records')->where('student_id', $student)->orderByDesc('verified_at')->first();
        $scanCounts = DB::table('verification_logs')
            ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
            ->where('qr_tokens.student_id', $student)
            ->select('verification_logs.decision', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('verification_logs.decision')
            ->pluck('aggregate', 'decision');
        $lastScan = DB::table('verification_logs')
            ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
            ->leftJoin('examiners', 'verification_logs.examiner_id', '=', 'examiners.examiner_id')
            ->where('qr_tokens.student_id', $student)
            ->select('verification_logs.*', 'examiners.full_name as examiner_name')
            ->orderByDesc('verification_logs.timestamp')
            ->first();
        $timetable = DB::table('timetables')
            ->join('departments', 'timetables.department_id', '=', 'departments.dept_id')
            ->where('timetables.exam_session_id', (int) $studentRow->session_id)
            ->where('timetables.department_id', (int) $studentRow->department_id)
            ->where('timetables.level', (string) $studentRow->level)
            ->select('timetables.*', 'departments.dept_name')
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        return view('admin.students.show', [
            'adminActor' => $adminActor,
            'student' => $studentRow,
            'token' => $token,
            'payment' => $payment,
            'scanCounts' => $scanCounts,
            'lastScan' => $lastScan,
            'timetable' => $timetable,
            'scanHistory' => $scanHistory,
            'pageTitle' => $studentRow->full_name,
            'breadcrumbs' => ['Admin', 'Students', $studentRow->matric_no],
        ]);
    }

    public function scanLogs(Request $request)
    {
        $adminActor = $this->adminActor($request);

        return view('admin.scan-logs.index', [
            'adminActor' => $adminActor,
            'logs' => $this->scanLogsQuery($request)->paginate(25)->withQueryString(),
            'sessions' => DB::table('exam_sessions')->orderByDesc('created_at')->get(),
            'pageTitle' => 'Scan Logs',
            'breadcrumbs' => ['Admin', 'Scan Logs'],
        ]);
    }

    public function showScanLog(Request $request, int $log)
    {
        $adminActor = $this->adminActor($request);
        $scan = $this->scanLogsQuery(new Request())->where('verification_logs.log_id', $log)->first();
        abort_unless($scan, 404);

        return view('admin.scan-logs.show', [
            'adminActor' => $adminActor,
            'log' => $scan,
            'pageTitle' => 'Scan Detail',
            'breadcrumbs' => ['Admin', 'Scan Logs', (string) $log],
        ]);
    }

    public function payments(Request $request)
    {
        $adminActor = $this->adminActor($request);
        $query = DB::table('payment_records')
            ->leftJoin('students', 'payment_records.student_id', '=', 'students.matric_no')
            ->leftJoin('departments', 'students.department_id', '=', 'departments.dept_id')
            ->select(
                'payment_records.*',
                'students.full_name',
                'students.level',
                'departments.dept_name',
                DB::raw('(SELECT status FROM qr_tokens WHERE qr_tokens.student_id = payment_records.student_id ORDER BY issued_at DESC LIMIT 1) as token_status')
            );

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($inner) use ($search) {
                $inner->where('payment_records.student_id', 'like', $search)
                    ->orWhere('payment_records.rrr_number', 'like', $search)
                    ->orWhere('students.full_name', 'like', $search);
            });
        }

        return view('admin.payments.index', [
            'adminActor' => $adminActor,
            'payments' => $query->orderByDesc('payment_records.verified_at')->paginate(25)->withQueryString(),
            'pageTitle' => 'Payments',
            'breadcrumbs' => ['Admin', 'Payments'],
        ]);
    }

    public function showPayment(Request $request, int $payment)
    {
        $adminActor = $this->adminActor($request);
        $paymentRow = DB::table('payment_records')
            ->leftJoin('students', 'payment_records.student_id', '=', 'students.matric_no')
            ->leftJoin('departments', 'students.department_id', '=', 'departments.dept_id')
            ->leftJoin('exam_sessions', 'students.session_id', '=', 'exam_sessions.session_id')
            ->where('payment_records.payment_id', $payment)
            ->select(
                'payment_records.*',
                'students.full_name',
                'students.matric_no',
                'students.department_id',
                'students.level',
                'students.photo_path',
                'students.session_id',
                'students.created_at as registered_at',
                'departments.dept_name',
                'exam_sessions.name as session_name',
                'exam_sessions.semester',
                'exam_sessions.academic_year'
            )
            ->first();
        abort_unless($paymentRow, 404);

        $token = DB::table('qr_tokens')
            ->where('student_id', $paymentRow->student_id)
            ->orderByDesc('issued_at')
            ->first();

        $timetable = DB::table('timetables')
            ->where('exam_session_id', (int) $paymentRow->session_id)
            ->where('department_id', (int) $paymentRow->department_id)
            ->where('level', (string) $paymentRow->level)
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        $scanCounts = DB::table('verification_logs')
            ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
            ->where('qr_tokens.student_id', $paymentRow->student_id)
            ->select('verification_logs.decision', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('verification_logs.decision')
            ->pluck('aggregate', 'decision');

        $recentScans = $this->scanLogsQuery(new Request())
            ->where('qr_tokens.student_id', $paymentRow->student_id)
            ->limit(10)
            ->get();

        return view('admin.payments.show', [
            'adminActor' => $adminActor,
            'payment' => $paymentRow,
            'token' => $token,
            'timetable' => $timetable,
            'scanCounts' => $scanCounts,
            'recentScans' => $recentScans,
            'remitaResponse' => json_decode($paymentRow->remita_response ?: '{}', true) ?: [],
            'pageTitle' => 'Payment Detail',
            'breadcrumbs' => ['Admin', 'Payments', $paymentRow->rrr_number],
        ]);
    }

    public function timetables(Request $request)
    {
        $adminActor = $this->adminActor($request);

        return view('admin.timetables.index', [
            'adminActor' => $adminActor,
            'entries' => $this->timetablesQuery($request)->paginate(20)->withQueryString(),
            'sessions' => DB::table('exam_sessions')->orderByDesc('created_at')->get(),
            'departments' => DB::table('departments')->orderBy('dept_name')->get(),
            'pageTitle' => 'Timetable',
            'breadcrumbs' => ['Admin', 'Timetable'],
        ]);
    }

    public function storeTimetable(Request $request)
    {
        $adminActor = $this->adminActor($request);
        $data = $this->validateTimetable($request);
        $duplicate = DB::table('timetables')
            ->where('exam_session_id', $data['exam_session_id'])
            ->where('department_id', $data['department_id'])
            ->where('level', $data['level'])
            ->where('course_code', strtoupper($data['course_code']))
            ->where('exam_date', $data['exam_date'])
            ->where('start_time', $data['start_time'])
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors(['course_code' => 'This timetable entry already exists for the selected session, department, level, date, and time.']);
        }

        DB::table('timetables')->insert([
            ...$this->timetablePayload($data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->recordActivity($adminActor, 'session_opened', "Timetable entry {$data['course_code']} created.");
        $this->logAudit($adminActor, 'timetable.created', ['course_code' => $data['course_code']]);

        return back()->with('status', 'Timetable entry created.');
    }

    public function editTimetable(Request $request, int $timetable)
    {
        $adminActor = $this->adminActor($request);
        $entry = $this->timetablesQuery(new Request())->where('timetables.id', $timetable)->first();
        abort_unless($entry, 404);

        return view('admin.timetables.edit', [
            'adminActor' => $adminActor,
            'entry' => $entry,
            'sessions' => DB::table('exam_sessions')->orderByDesc('created_at')->get(),
            'departments' => DB::table('departments')->orderBy('dept_name')->get(),
            'pageTitle' => 'Edit Timetable',
            'breadcrumbs' => ['Admin', 'Timetable', $entry->course_code],
        ]);
    }

    public function updateTimetable(Request $request, int $timetable)
    {
        $adminActor = $this->adminActor($request);
        $entry = DB::table('timetables')->where('id', $timetable)->first();
        abort_unless($entry, 404);

        $data = $this->validateTimetable($request);
        $duplicate = DB::table('timetables')
            ->where('id', '!=', $timetable)
            ->where('exam_session_id', $data['exam_session_id'])
            ->where('department_id', $data['department_id'])
            ->where('level', $data['level'])
            ->where('course_code', strtoupper($data['course_code']))
            ->where('exam_date', $data['exam_date'])
            ->where('start_time', $data['start_time'])
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors(['course_code' => 'This timetable entry already exists for the selected session, department, level, date, and time.']);
        }

        DB::table('timetables')->where('id', $timetable)->update([
            ...$this->timetablePayload($data),
            'updated_at' => now(),
        ]);

        $this->recordActivity($adminActor, 'session_opened', "Timetable entry {$data['course_code']} updated.");
        $this->logAudit($adminActor, 'timetable.updated', ['course_code' => $data['course_code']], 'timetable', (string) $timetable);

        return redirect()->route('admin.timetables.index')->with('status', 'Timetable entry updated.');
    }

    public function deleteTimetable(Request $request, int $timetable)
    {
        $adminActor = $this->adminActor($request);
        $entry = DB::table('timetables')->where('id', $timetable)->first();
        abort_unless($entry, 404);

        DB::table('timetables')->where('id', $timetable)->delete();

        $this->recordActivity($adminActor, 'session_closed', "Timetable entry {$entry->course_code} deleted.");
        $this->logAudit($adminActor, 'timetable.deleted', ['course_code' => $entry->course_code], 'timetable', (string) $timetable);

        return back()->with('status', 'Timetable entry deleted.');
    }

    public function exportScanLogs(Request $request): StreamedResponse
    {
        $this->adminActor($request);
        $logs = $this->scanLogsQuery($request)->get();

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student ID', 'Name', 'Session', 'Examiner', 'Result', 'Timestamp']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->student_id,
                    $log->student_name,
                    trim(($log->session_name ?: $log->semester) . ' ' . $log->academic_year),
                    $log->examiner_name,
                    $log->decision,
                    $log->timestamp,
                ]);
            }

            fclose($handle);
        }, 'scan-logs.csv', ['Content-Type' => 'text/csv']);
    }

    public function activity(Request $request)
    {
        $adminActor = $this->adminActor($request);

        return view('admin.activity.index', [
            'adminActor' => $adminActor,
            'activities' => ActivityLog::query()->latest('created_at')->paginate(30),
            'pageTitle' => 'Activity',
            'breadcrumbs' => ['Admin', 'Activity'],
        ]);
    }

    public function settings(Request $request)
    {
        $adminActor = $this->adminActor($request);

        return view('admin.settings.index', [
            'adminActor' => $adminActor,
            'settings' => $this->settingsPayload(),
            'timezones' => timezone_identifiers_list(),
            'pageTitle' => 'Settings',
            'breadcrumbs' => ['Admin', 'Settings'],
        ]);
    }

    public function updateSettings(Request $request)
    {
        $adminActor = $this->adminActor($request);
        $data = $request->validate([
            'app_name' => 'required|string|max:100',
            'app_timezone' => 'required|string|max:100',
            'default_session_duration' => 'required|integer|min:15|max:1440',
            'allow_re_registration' => 'nullable|boolean',
            'qr_token_expiry' => 'required|integer|min:5|max:1440',
            'require_https' => 'nullable|boolean',
            'session_lifetime' => 'required|integer|min:10|max:1440',
        ]);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }
        Setting::setValue('allow_re_registration', $request->boolean('allow_re_registration'));
        Setting::setValue('require_https', $request->boolean('require_https'));
        Artisan::call('config:clear');

        $this->recordActivity($adminActor, 'settings_updated', 'System settings updated.');

        return back()->with('status', 'Settings saved.');
    }

    public function clearScanLogs(Request $request)
    {
        $adminActor = $this->adminActor($request);
        DB::table('verification_logs')->delete();
        $this->recordActivity($adminActor, 'scan_fail', 'All scan logs were cleared.');

        return back()->with('status', 'Scan logs cleared.');
    }

    public function resetSystem(Request $request)
    {
        $adminActor = $this->adminActor($request);
        $request->validate(['reset_confirmation' => 'required|in:RESET']);

        DB::transaction(function () {
            DB::table('verification_logs')->delete();
            DB::table('payment_records')->delete();
            DB::table('qr_tokens')->delete();
            DB::table('students')->delete();
        });

        $this->recordActivity($adminActor, 'session_closed', 'System registration and scan data reset.');

        return back()->with('status', 'System reset completed.');
    }

    public function storeSession(Request $request)
    {
        $adminActor = $this->adminActor($request);
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'examiner_id' => 'required|integer|exists:examiners,examiner_id',
            'scheduled_start' => 'nullable|date',
            'fee_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $crypto = new CryptoService();

        DB::transaction(function () use ($data, $crypto) {
            if ($data['is_active'] ?? false) {
                DB::table('exam_sessions')->update(['is_active' => false, 'updated_at' => now()]);
            }

            DB::table('exam_sessions')->insert([
                'name' => $data['name'],
                'semester' => $data['name'],
                'academic_year' => now()->format('Y') . '/' . now()->addYear()->format('Y'),
                'fee_amount' => $data['fee_amount'] ?? 100000,
                'aes_key' => $crypto->generateRandomKey(),
                'hmac_secret' => $crypto->generateRandomKey(),
                'examiner_id' => $data['examiner_id'],
                'scheduled_start' => $data['scheduled_start'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? false),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $event = ($data['is_active'] ?? false) ? 'session_opened' : 'session_closed';
        $this->recordActivity($adminActor, $event, "Session {$data['name']} created.");
        $this->logAudit($adminActor, 'session.created', ['name' => $data['name']]);

        return back()->with('status', 'Session created.');
    }

    public function closeSession(Request $request, int $session)
    {
        $adminActor = $this->adminActor($request);
        $row = DB::table('exam_sessions')->where('session_id', $session)->first();
        abort_unless($row, 404);

        DB::table('exam_sessions')->where('session_id', $session)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        $this->recordActivity($adminActor, 'session_closed', 'Session ' . $this->sessionName($row) . ' closed.');
        $this->logAudit($adminActor, 'session.closed', ['session_id' => $session], 'exam_session', (string) $session);

        return back()->with('status', 'Session closed.');
    }

    public function deleteSession(Request $request, int $session)
    {
        $adminActor = $this->adminActor($request);
        $row = DB::table('exam_sessions')->where('session_id', $session)->first();
        abort_unless($row, 404);

        DB::transaction(function () use ($session) {
            $students = DB::table('students')->where('session_id', $session)->pluck('matric_no');
            $tokens = DB::table('qr_tokens')->where('session_id', $session)->pluck('token_id');

            DB::table('verification_logs')->whereIn('token_id', $tokens)->delete();
            DB::table('payment_records')->whereIn('student_id', $students)->delete();
            DB::table('qr_tokens')->where('session_id', $session)->delete();
            DB::table('students')->where('session_id', $session)->delete();
            DB::table('exam_sessions')->where('session_id', $session)->delete();
        });

        $this->recordActivity($adminActor, 'session_closed', 'Session ' . $this->sessionName($row) . ' deleted.');
        $this->logAudit($adminActor, 'session.deleted', ['session_id' => $session], 'exam_session', (string) $session);

        return back()->with('status', 'Session deleted.');
    }

    public function storeExaminer(Request $request)
    {
        $adminActor = $this->adminActor($request);
        $data = $request->validate([
            'full_name' => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:examiners,username',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $id = DB::table('examiners')->insertGetId([
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'password_hash' => Hash::make($data['password']),
            'role' => 'examiner',
            'admin_user_id' => null,
            'is_active' => true,
            'last_active_at' => null,
            'created_at' => now(),
        ]);

        $this->recordActivity($adminActor, 'examiner_created', "Examiner {$data['username']} created.");
        $this->logAudit($adminActor, 'examiner.created', ['username' => $data['username']], 'examiner', (string) $id);

        return back()->with('status', 'Examiner created.');
    }

    public function toggleExaminer(Request $request, int $examiner)
    {
        $adminActor = $this->adminActor($request);
        $target = DB::table('examiners')->where('examiner_id', $examiner)->whereIn('role', [Roles::EXAMINER, 'examiner'])->first();
        abort_unless($target, 404);

        $newState = ! (bool) $target->is_active;
        DB::table('examiners')->where('examiner_id', $examiner)->update(['is_active' => $newState]);

        $this->recordActivity($adminActor, $newState ? 'examiner_created' : 'session_closed', "Examiner {$target->username} " . ($newState ? 'activated.' : 'deactivated.'));
        $this->logAudit(
            $adminActor,
            $newState ? 'examiner.activated' : 'examiner.deactivated',
            ['username' => $target->username],
            'examiner',
            (string) $examiner,
            ['is_active' => (bool) $target->is_active],
            ['is_active' => $newState]
        );

        return back()->with('status', $newState ? 'Examiner activated.' : 'Examiner deactivated.');
    }

    public function deleteExaminer(Request $request, int $examiner)
    {
        $adminActor = $this->adminActor($request);
        $target = DB::table('examiners')->where('examiner_id', $examiner)->whereIn('role', [Roles::EXAMINER, 'examiner'])->first();
        abort_unless($target, 404);

        if (DB::table('verification_logs')->where('examiner_id', $examiner)->exists()) {
            return back()->withErrors(['examiner' => 'Deactivate examiners with scan history instead of deleting them.']);
        }

        DB::transaction(function () use ($examiner) {
            DB::table('exam_sessions')->where('examiner_id', $examiner)->update(['examiner_id' => null, 'updated_at' => now()]);
            DB::table('examiners')->where('examiner_id', $examiner)->delete();
        });

        $this->recordActivity($adminActor, 'session_closed', "Examiner {$target->username} deleted.");
        $this->logAudit($adminActor, 'examiner.deleted', ['username' => $target->username], 'examiner', (string) $examiner);

        return back()->with('status', 'Examiner deleted.');
    }

    public function deleteStudent(Request $request, string $student)
    {
        $adminActor = $this->adminActor($request);
        $studentRow = DB::table('students')->where('matric_no', $student)->first();
        abort_unless($studentRow, 404);

        DB::transaction(function () use ($student) {
            $tokens = DB::table('qr_tokens')->where('student_id', $student)->pluck('token_id');
            DB::table('verification_logs')->whereIn('token_id', $tokens)->delete();
            DB::table('payment_records')->where('student_id', $student)->delete();
            DB::table('qr_tokens')->where('student_id', $student)->delete();
            DB::table('students')->where('matric_no', $student)->delete();
        });

        $this->recordActivity($adminActor, 'student_registered', "Student {$student} deleted.");
        $this->logAudit($adminActor, 'student.deleted', ['matric_no' => $student], 'student', $student);

        return redirect()->route('admin.students.index')->with('status', 'Student deleted.');
    }

    private function dashboardData(Request $request): array
    {
        $scanDecisionCounts = DB::table('verification_logs')
            ->select('decision', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('decision')
            ->pluck('aggregate', 'decision');

        $activeSession = $this->sessionsQuery($request)
            ->where('exam_sessions.is_active', true)
            ->first();

        return [
            'totalStudents' => DB::table('students')->count(),
            'totalExaminers' => DB::table('examiners')->whereIn('role', [Roles::EXAMINER, 'examiner'])->where('is_active', true)->count(),
            'activeSessions' => DB::table('exam_sessions')->where('is_active', true)->count(),
            'totalTokens' => DB::table('qr_tokens')->count(),
            'verifiedPayments' => DB::table('payment_records')->count(),
            'pendingPayments' => max(0, DB::table('students')->count() - DB::table('payment_records')->distinct('student_id')->count('student_id')),
            'scansToday' => DB::table('verification_logs')->whereDate('timestamp', today())->count(),
            'totalScans' => (int) $scanDecisionCounts->sum(),
            'approvedScans' => (int) ($scanDecisionCounts['APPROVED'] ?? 0),
            'rejectedScans' => (int) ($scanDecisionCounts['REJECTED'] ?? 0),
            'duplicateScans' => (int) ($scanDecisionCounts['DUPLICATE'] ?? 0),
            'activeSession' => $activeSession,
            'todaysExams' => $this->todaysExams(),
            'sessions' => $this->sessionsQuery($request)->paginate(10, ['*'], 'sessions_page')->withQueryString(),
            'recentExaminers' => $this->examinersQuery()->limit(5)->get(),
            'recentVerificationLogs' => $this->scanLogsQuery(new Request())->limit(8)->get(),
            'recentActivity' => ActivityLog::query()->latest('created_at')->limit(15)->get(),
            'recentAuditLogs' => $this->recentAuditLogs(),
            'dbOk' => $this->dbOk(),
            'storageOk' => is_writable(storage_path()),
            'environment' => app()->environment(),
        ];
    }

    private function recentAuditLogs()
    {
        if (! Schema::hasTable('audit_log')) {
            return collect();
        }

        return DB::table('audit_log')
            ->orderByDesc('timestamp')
            ->limit(8)
            ->get();
    }

    private function sessionsQuery(Request $request)
    {
        $query = DB::table('exam_sessions')
            ->leftJoin('examiners', 'exam_sessions.examiner_id', '=', 'examiners.examiner_id')
            ->select(
                'exam_sessions.*',
                'examiners.full_name as examiner_name',
                DB::raw('(SELECT COUNT(*) FROM students WHERE students.session_id = exam_sessions.session_id) as student_count')
            );

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($inner) use ($search) {
                $inner->where('exam_sessions.name', 'like', $search)
                    ->orWhere('exam_sessions.semester', 'like', $search)
                    ->orWhere('examiners.full_name', 'like', $search);
            });
        }

        return $query->orderByDesc('exam_sessions.created_at');
    }

    private function examinersQuery()
    {
        return DB::table('examiners')
            ->leftJoin('exam_sessions', 'examiners.examiner_id', '=', 'exam_sessions.examiner_id')
            ->select(
                'examiners.*',
                DB::raw('COUNT(exam_sessions.session_id) as sessions_count'),
                DB::raw('(SELECT COUNT(*) FROM verification_logs WHERE verification_logs.examiner_id = examiners.examiner_id) as scan_count'),
                DB::raw('(SELECT MAX(timestamp) FROM verification_logs WHERE verification_logs.examiner_id = examiners.examiner_id) as last_scan_at')
            )
            ->whereIn('examiners.role', [Roles::EXAMINER, 'examiner'])
            ->groupBy('examiners.examiner_id', 'examiners.full_name', 'examiners.username', 'examiners.password_hash', 'examiners.role', 'examiners.admin_user_id', 'examiners.is_active', 'examiners.last_active_at', 'examiners.created_at')
            ->orderBy('examiners.full_name');
    }

    private function studentsQuery(Request $request)
    {
        $query = DB::table('students')
            ->leftJoin('departments', 'students.department_id', '=', 'departments.dept_id')
            ->select(
                'students.*',
                'departments.dept_name',
                DB::raw('(SELECT status FROM qr_tokens WHERE qr_tokens.student_id = students.matric_no ORDER BY issued_at DESC LIMIT 1) as token_status'),
                DB::raw('(SELECT COUNT(*) FROM payment_records WHERE payment_records.student_id = students.matric_no) as payment_count')
            );

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($inner) use ($search) {
                $inner->where('students.matric_no', 'like', $search)
                    ->orWhere('students.full_name', 'like', $search);
            });
        }
        if ($request->filled('department_id')) {
            $query->where('students.department_id', (int) $request->input('department_id'));
        }
        if ($request->filled('level')) {
            $query->where('students.level', $request->input('level'));
        }
        if ($request->filled('session_id')) {
            $query->where('students.session_id', (int) $request->input('session_id'));
        }
        if ($request->filled('qr_status')) {
            $query->whereRaw('(SELECT status FROM qr_tokens WHERE qr_tokens.student_id = students.matric_no ORDER BY issued_at DESC LIMIT 1) = ?', [$request->input('qr_status')]);
        }
        if ($request->filled('payment_status')) {
            if ($request->input('payment_status') === 'verified') {
                $query->whereExists(function ($inner) {
                    $inner->selectRaw('1')->from('payment_records')->whereColumn('payment_records.student_id', 'students.matric_no');
                });
            } elseif ($request->input('payment_status') === 'pending') {
                $query->whereNotExists(function ($inner) {
                    $inner->selectRaw('1')->from('payment_records')->whereColumn('payment_records.student_id', 'students.matric_no');
                });
            }
        }

        return $query->orderByDesc('students.created_at');
    }

    private function scanLogsQuery(Request $request)
    {
        $query = DB::table('verification_logs')
            ->join('qr_tokens', 'verification_logs.token_id', '=', 'qr_tokens.token_id')
            ->leftJoin('students', 'qr_tokens.student_id', '=', 'students.matric_no')
            ->leftJoin('exam_sessions', 'qr_tokens.session_id', '=', 'exam_sessions.session_id')
            ->leftJoin('examiners', 'verification_logs.examiner_id', '=', 'examiners.examiner_id')
            ->select(
                'verification_logs.*',
                'qr_tokens.student_id',
                'students.full_name as student_name',
                'students.level',
                'students.department_id',
                'students.photo_path',
                'departments.dept_name',
                'exam_sessions.name as session_name',
                'exam_sessions.semester',
                'exam_sessions.academic_year',
                'examiners.full_name as examiner_name'
            )
            ->leftJoin('departments', 'students.department_id', '=', 'departments.dept_id');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($inner) use ($search) {
                $inner->where('qr_tokens.student_id', 'like', $search)
                    ->orWhere('verification_logs.token_id', 'like', $search)
                    ->orWhere('students.full_name', 'like', $search)
                    ->orWhere('examiners.full_name', 'like', $search)
                    ->orWhere('examiners.username', 'like', $search);
            });
        }
        if ($request->filled('session_id')) {
            $query->where('qr_tokens.session_id', (int) $request->input('session_id'));
        }
        if ($request->filled('result')) {
            $query->where('verification_logs.decision', strtoupper($request->input('result')));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('verification_logs.timestamp', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('verification_logs.timestamp', '<=', $request->input('date_to'));
        }

        return $query->orderByDesc('verification_logs.timestamp');
    }

    private function timetablesQuery(Request $request)
    {
        $query = DB::table('timetables')
            ->join('exam_sessions', 'timetables.exam_session_id', '=', 'exam_sessions.session_id')
            ->join('departments', 'timetables.department_id', '=', 'departments.dept_id')
            ->select(
                'timetables.*',
                'exam_sessions.name as session_name',
                'exam_sessions.semester',
                'exam_sessions.academic_year',
                'departments.dept_name'
            );

        if ($request->filled('session_id')) {
            $query->where('timetables.exam_session_id', (int) $request->input('session_id'));
        }
        if ($request->filled('department_id')) {
            $query->where('timetables.department_id', (int) $request->input('department_id'));
        }
        if ($request->filled('level')) {
            $query->where('timetables.level', $request->input('level'));
        }
        if ($request->filled('date')) {
            $query->whereDate('timetables.exam_date', $request->input('date'));
        }

        return $query->orderBy('timetables.exam_date')->orderBy('timetables.start_time');
    }

    private function validateTimetable(Request $request): array
    {
        return $request->validate([
            'exam_session_id' => 'required|integer|exists:exam_sessions,session_id',
            'department_id' => 'required|integer|exists:departments,dept_id',
            'level' => 'required|string|max:30',
            'course_code' => 'required|string|max:30',
            'course_title' => 'nullable|string|max:255',
            'exam_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'venue' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'required|in:scheduled,active,completed,cancelled',
        ]);
    }

    private function timetablePayload(array $data): array
    {
        return [
            'exam_session_id' => (int) $data['exam_session_id'],
            'department_id' => (int) $data['department_id'],
            'level' => trim($data['level']),
            'course_code' => strtoupper(trim($data['course_code'])),
            'course_title' => $data['course_title'] ? trim($data['course_title']) : null,
            'exam_date' => $data['exam_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'] ?? null,
            'venue' => trim($data['venue']),
            'capacity' => $data['capacity'] ?? null,
            'status' => $data['status'],
        ];
    }

    private function activeExaminers()
    {
        return DB::table('examiners')
            ->whereIn('role', [Roles::EXAMINER, 'examiner'])
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['examiner_id', 'full_name', 'username']);
    }

    private function todaysExams()
    {
        return DB::table('timetables')
            ->join('exam_sessions', 'timetables.exam_session_id', '=', 'exam_sessions.session_id')
            ->join('departments', 'timetables.department_id', '=', 'departments.dept_id')
            ->whereDate('timetables.exam_date', today())
            ->select('timetables.*', 'exam_sessions.name as session_name', 'exam_sessions.semester', 'departments.dept_name')
            ->orderBy('timetables.start_time')
            ->limit(8)
            ->get();
    }

    private function settingsPayload(): array
    {
        return [
            'app_name' => Setting::getValue('app_name', config('app.name')),
            'app_timezone' => Setting::getValue('app_timezone', config('app.timezone')),
            'default_session_duration' => Setting::getValue('default_session_duration', '120'),
            'allow_re_registration' => Setting::getValue('allow_re_registration', '0'),
            'qr_token_expiry' => Setting::getValue('qr_token_expiry', '240'),
            'require_https' => Setting::getValue('require_https', '0'),
            'session_lifetime' => Setting::getValue('session_lifetime', (string) config('session.lifetime')),
        ];
    }

    private function adminActor(Request $request): object
    {
        $actor = DB::table('examiners')
            ->where('examiner_id', (int) $request->session()->get('examiner_id'))
            ->where('is_active', true)
            ->first();

        abort_unless($actor && Roles::isAdminLike($actor->role), 403);

        return $actor;
    }

    private function recordActivity(object $adminActor, string $eventType, string $description): void
    {
        ActivityLog::record($eventType, $description, (int) $adminActor->examiner_id);
    }

    private function logAudit(
        object $adminActor,
        string $action,
        array $metadata = [],
        ?string $targetType = null,
        ?string $targetId = null,
        ?array $before = null,
        ?array $after = null
    ): void {
        app(AuditService::class)->logAction(
            (string) $adminActor->examiner_id,
            strtolower(Roles::normalize($adminActor->role)),
            $action,
            $metadata,
            $targetType,
            $targetId,
            $before,
            $after
        );
    }

    private function dbOk(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function sessionName(object $session): string
    {
        return $session->name ?: $session->semester;
    }

    public function sessionStatus(object $session): array
    {
        if ((bool) $session->is_active) {
            return ['Active', 'green'];
        }

        if (! empty($session->scheduled_start) && now()->lt($session->scheduled_start)) {
            return ['Pending', 'yellow'];
        }

        return ['Closed', 'gray'];
    }
}
