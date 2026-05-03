<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('student');

        if (! $guard->check()) {
            return redirect()->route('student.login');
        }

        if (! $guard->user()->is_active) {
            $guard->logout();
            return redirect()->route('student.login')->withErrors(['student_id' => 'Account deactivated.']);
        }

        return $next($request);
    }
}
