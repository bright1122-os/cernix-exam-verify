<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Examiner;
use App\Support\Roles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:200',
        ]);

        $admin = Examiner::query()
            ->where('username', $credentials['username'])
            ->first();

        if (! $admin || ! Roles::isAdminLike($admin->role) || ! Hash::check($credentials['password'], $admin->password_hash)) {
            return back()->withErrors(['username' => 'Invalid credentials.'])->onlyInput('username');
        }

        if (! $admin->is_active) {
            return back()->withErrors(['username' => 'Account deactivated.'])->onlyInput('username');
        }

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->forget(['examiner_id', 'examiner_role', 'examiner_name', 'examiner_username']);
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
