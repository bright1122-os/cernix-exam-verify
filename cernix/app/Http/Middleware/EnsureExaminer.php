<?php

namespace App\Http\Middleware;

use App\Support\Roles;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureExaminer
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('examiner');

        if (! $guard->check()) {
            return redirect()->route('examiner.login');
        }

        $user = $guard->user();

        if (Roles::normalize($user->role) !== Roles::EXAMINER) {
            $guard->logout();
            return redirect()->route('examiner.login')->withErrors(['username' => 'Unauthorized.']);
        }

        if (! $user->is_active) {
            $guard->logout();
            return redirect()->route('examiner.login')->withErrors(['username' => 'Account deactivated.']);
        }

        $request->session()->put('examiner_id', (int) $user->examiner_id);
        $request->session()->put('examiner_role', Roles::EXAMINER);
        $request->session()->put('examiner_name', $user->full_name);
        $request->session()->put('examiner_username', $user->username);

        return $next($request);
    }
}
