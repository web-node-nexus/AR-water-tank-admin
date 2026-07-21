<?php

namespace App\Http\Middleware;

use App\Models\ServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof ServiceProvider) {
            return response()->json(['message' => 'Unauthorized. Provider access only.'], 403);
        }

        if (! $user->is_active) {
            $user->tokens()->delete();

            return response()->json(['message' => 'Your account has been deactivated.'], 403);
        }

        return $next($request);
    }
}
