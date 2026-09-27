<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = $request->user('api');

        if (! $user->is_active) {
            return response()->json(['message' => __('jobs.account_inactive')], 403);
        }

        if (! in_array($user->role, ['candidate', 'admin', 'super_admin'], true)) {
            return response()->json(['message' => __('jobs.access_denied')], 403);
        }

        return $next($request);
    }
}
