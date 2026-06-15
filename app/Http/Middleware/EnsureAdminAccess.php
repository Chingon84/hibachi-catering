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

        if (!$isActive) {
            abort(403, 'Your account is inactive.');
        }

        $canAccessByFlag = (int) ($user->can_access_admin ?? 0) === 1;
        $role = strtolower((string) ($user->role ?? ''));
        $canAccessByRole = in_array($role, ['owner', 'admin'], true);

        // Explicit admin access (flag or role) — let through immediately.
        if ($canAccessByFlag || $canAccessByRole) {
            return $next($request);
        }

        // Staff / employee portal users should use /staff, not /admin.
        if (method_exists($user, 'isStaffPortalUser') && $user->isStaffPortalUser()) {
            return redirect()->route('staff.dashboard');
        }

        abort(403, 'You do not have access to the admin area.');
    }
}
