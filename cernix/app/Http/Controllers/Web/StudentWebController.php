<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\CryptoService;
use App\Services\MockSISService;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use App\Services\RemitaService;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            'photo'      => 'nullable|image|max:5120',
        ]);

        $session = DB::table('exam_sessions')->where('is_active', true)->first();

        if (! $session) {
            return response()->json(['success' => false, 'message' => 'No active exam session found.'], 422);
        }

        try {
            $cryptoService = new CryptoService();
            $existingStudent = DB::table('students')
                ->where('matric_no', $data['matric_no'])
                ->where('session_id', (int) $session->session_id)
                ->first();

            if ($existingStudent) {
                $existingPayment = DB::table('payment_records')
                    ->where('student_id', $data['matric_no'])
                    ->where('rrr_number', $data['rrr_number'])
                    ->first();

                if (! $existingPayment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Student already registered for this session. Use the original Remita RRR to access the dashboard.',
                    ], 422);
                }

                DB::table('students')
                    ->where('matric_no', $data['matric_no'])
                    ->where('session_id', (int) $session->session_id)
                    ->whereNull('password')
                    ->update([
                        'password' => Hash::make($data['rrr_number']),
                        'is_active' => true,
                    ]);

                $this->loginStudent($request, $data['matric_no']);

                return response()->json([
                    'success' => true,
                    'message' => 'Registration already exists. Opening your student dashboard.',
                    'redirect_url' => route('student.dashboard'),
                    'data' => [
                        'matric_no' => $data['matric_no'],
                        'session_id' => (int) $session->session_id,
                    ],
                ]);
            }

            $regService = new RegistrationService(
                new MockSISService(),
                new class((float) $session->fee_amount) extends RemitaService {
                    public function __construct(private float $fee) { parent::__construct(new \GuzzleHttp\Client()); }
                    public function verifyPayment(string $rrrNumber, float $expectedAmount): array {
                        if ($this->rrrAlreadyUsed($rrrNumber)) {
                            throw new \RuntimeException('RRR has already been used for a payment record.');
                        }
                        return ['status' => 'Payment Successful', 'amount' => (string) $this->fee];
                    }
                },
                $cryptoService
            );

            $result = $regService->registerStudent([
                'matric_no'       => $data['matric_no'],
                'full_name'       => '',
                'rrr_number'      => $data['rrr_number'],
                'expected_amount' => (float) $session->fee_amount,
                'session_id'      => (int) $session->session_id,
            ]);

            // Handle optional photo upload — overwrites SIS photo for this student
            $photoPath = $result['data']['photo_path'] ?? 'photos/placeholder.jpg';

            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $sanitized = preg_replace('/[^a-z0-9]/i', '_', strtolower($data['matric_no']));
                $filename  = 'upload_' . $sanitized . '_' . time() . '.jpg';
                $request->file('photo')->move(public_path('photos'), $filename);
                $photoPath = 'photos/' . $filename;

                DB::table('students')
                    ->where('matric_no', $data['matric_no'])
                    ->where('session_id', (int) $session->session_id)
                    ->update(['photo_path' => $photoPath]);

                DB::table('mock_sis')
                    ->where('matric_no', $data['matric_no'])
                    ->update(['photo_path' => $photoPath]);
            }

            DB::table('students')
                ->where('matric_no', $data['matric_no'])
                ->where('session_id', (int) $session->session_id)
                ->whereNull('password')
                ->update([
                    'password' => Hash::make($data['rrr_number']),
                    'is_active' => true,
                ]);

            // Build the QR SVG
            $tokenRow = DB::table('qr_tokens')
                ->where('token_id', $result['data']['token_id'])
                ->first();

            $qrService = new QrTokenService($cryptoService);
            $qrSvg = $qrService->buildQrCode([
                'token_id'          => $result['data']['token_id'],
                'encrypted_payload' => $tokenRow->encrypted_payload,
                'hmac_signature'    => $tokenRow->hmac_signature,
                'session_id'        => (int) $session->session_id,
            ]);

            $studentRow = DB::table('students')
                ->join('departments', 'students.department_id', '=', 'departments.dept_id')
                ->where('students.matric_no', $data['matric_no'])
                ->where('students.session_id', (int) $session->session_id)
                ->select('departments.dept_name', 'students.photo_path')
                ->first();

            $photoPath = $studentRow->photo_path ?? $photoPath;

            app(AuditService::class)->logAction(
                $data['matric_no'],
                'student',
                'student.registered',
                ['token_id' => $result['data']['token_id'], 'session_id' => $session->session_id]
            );

            $this->loginStudent($request, $data['matric_no']);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'redirect_url' => route('student.dashboard'),
                'data'    => array_merge($result['data'], [
                    'qr_svg'     => $qrSvg,
                    'department' => $studentRow->dept_name ?? '',
                    'session_id' => (int) $session->session_id,
                    'photo_path' => $photoPath,
                    'photo_url'  => '/' . $photoPath,
                ]),
            ]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function loginStudent(Request $request, string $matricNo): void
    {
        $student = Student::query()->where('matric_no', $matricNo)->first();

        if ($student) {
            Auth::guard('student')->login($student);
            $request->session()->regenerate();
        }
    }
}
