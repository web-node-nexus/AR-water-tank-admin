<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->with('error', 'Your account is inactive or unauthorized.');
        }

        if (! in_array($user->role, [UserRole::SuperAdmin, UserRole::Manager], true)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
