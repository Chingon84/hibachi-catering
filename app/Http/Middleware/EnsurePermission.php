<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();
        if (!$user || !$user->is_active || !$user->can_access_admin) {
            abort(403);
        }
        if (method_exists($user, 'hasPermission') && $user->hasPermission($permission)) {
            return $next($request);
        }
        abort(403, 'Insufficient permissions');
    }
}

