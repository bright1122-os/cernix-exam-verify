<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Examiner;
use App\Services\AuditService;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ExaminerAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('examiner.login');
    }

    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:200',
        ]);

        $examiner = Examiner::query()
            ->where('username', $credentials['username'])
            ->first();

        if (! $examiner || Roles::normalize($examiner->role) !== Roles::EXAMINER || ! Hash::check($credentials['password'], $examiner->password_hash)) {
            return $this->loginFailure($request, 'Invalid credentials.', $credentials['username']);
        }

        if (! $examiner->is_active) {
            return $this->loginFailure($request, 'Account deactivated.', $credentials['username']);
        }

        Auth::guard('examiner')->login($examiner);
        $request->session()->regenerate();

        app(AuditService::class)->logAction(
            (string) $examiner->examiner_id,
            'examiner',
            'examiner.login',
            ['username' => $examiner->username]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'examiner_id' => $examiner->examiner_id,
                    'full_name' => $examiner->full_name,
                    'username' => $examiner->username,
                    'role' => $examiner->role,
                ],
            ]);
        }

        return redirect()->route('examiner.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $examiner = Auth::guard('examiner')->user();

        if ($examiner) {
            app(AuditService::class)->logAction((string) $examiner->examiner_id, 'examiner', 'examiner.logout', []);
        }

        Auth::guard('examiner')->logout();
        $request->session()->forget(['examiner_id', 'examiner_role', 'examiner_name', 'examiner_username']);
        $request->session()->regenerateToken();

        return redirect()->route('examiner.login');
    }

    private function loginFailure(Request $request, string $message, ?string $username): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => $message], 401);
        }

        return back()->withErrors(['username' => $message])->withInput(['username' => $username]);
    }
}
