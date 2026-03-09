<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return redirect()->guest(route('login'));
        }

        $isActive = (int) ($user->is_active ?? 1) === 1;
        $canAccessByFlag = (int) ($user->can_access_admin ?? 0) === 1;
        $role = strtolower((string) ($user->role ?? ''));
        $canAccessByRole = in_array($role, ['owner', 'admin'], true);

        if (!$isActive) {
            abort(403, 'Your account is inactive.');
        }

        if (!$canAccessByFlag && !$canAccessByRole) {
            abort(403, 'You do not have access to the admin area.');
        }

        return $next($request);
    }
}
