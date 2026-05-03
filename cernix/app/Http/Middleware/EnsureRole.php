<?php

namespace App\Http\Middleware;

use App\Support\Roles;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth('api')->user();
        $role = $user ? Roles::normalize($user->role) : null;

        if (! $role && $request->hasSession() && $request->session()->has('examiner_id')) {
            $actor = DB::table('examiners')
                ->where('examiner_id', (int) $request->session()->get('examiner_id'))
                ->where('is_active', true)
                ->first();

            if ($actor) {
                $role = Roles::normalize($actor->role);
                $request->session()->put('examiner_role', $role);
                $request->session()->put('examiner_name', $actor->full_name);
                $request->session()->put('examiner_username', $actor->username);
            } else {
                $request->session()->flush();
            }
        }

        if (! $role) {
            if ($request->expectsJson()) {
                abort(401);
            }

            return redirect('/examiner/login');
        }

        $allowed = array_map(fn (string $role) => Roles::normalize($role), $roles);

        if (! in_array($role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
