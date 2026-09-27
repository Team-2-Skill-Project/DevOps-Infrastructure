<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAdmin
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = $request->user('api');

        if (! $user->is_active) {
            return response()->json(['message' => 'Your account is inactive.'], 403);
        }

        if (! in_array($user->role, ['admin', 'super_admin'], true)) {
            return response()->json(['message' => 'Only administrators can manage skills.'], 403);
        }

        return $next($request);
    }
}
