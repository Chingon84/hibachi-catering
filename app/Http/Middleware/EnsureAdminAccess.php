<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            // Remember target and redirect to login
            session(['intended' => $request->fullUrl()]);
            return redirect()->route('login');
        }

        $user = auth()->user();
        if (!$user->is_active || !$user->can_access_admin) {
            abort(403, 'You do not have access to the admin area.');
        }

        return $next($request);
    }
}

