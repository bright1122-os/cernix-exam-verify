<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('student.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'student_id' => 'required|string|max:50',
            'password' => 'required|string|max:200',
        ]);

        $student = Student::query()->where('matric_no', $credentials['student_id'])->first();

        if (! $student || ! $student->password || ! Hash::check($credentials['password'], $student->password)) {
            return back()->withErrors(['student_id' => 'Invalid student ID or password.'])->onlyInput('student_id');
        }

        if (! $student->is_active) {
            return back()->withErrors(['student_id' => 'Account deactivated.'])->onlyInput('student_id');
        }

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('student')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
