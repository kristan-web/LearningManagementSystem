<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks non-admin users from admin-scoped routes before route-model
 * binding resolves (so they get 403, not 404).
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        abort_unless($request->user()?->role === ucfirst($role), 403);

        return $next($request);
    }
}