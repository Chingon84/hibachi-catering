<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
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
            $this->logDenied($request, $permission, $user, 'inactive');
            abort(403, 'Your account is inactive.');
        }

        if (!$canAccessByFlag && !$canAccessByRole) {
            $this->logDenied($request, $permission, $user, 'no_admin_access');
            abort(403, 'You do not have access to the admin area.');
        }

        if (method_exists($user, 'hasPermission') && $user->hasPermission($permission)) {
            return $next($request);
        }

        $this->logDenied($request, $permission, $user, 'missing_permission');
        abort(403, 'Insufficient permissions');
    }

    private function logDenied(Request $request, string $permission, object $user, string $reason): void
    {
        if (!app()->isLocal()) {
            return;
        }

        Log::warning('Permission denied', [
            'reason' => $reason,
            'permission' => $permission,
            'path' => $request->path(),
            'user_id' => $user->id ?? null,
            'role' => $user->role ?? null,
            'can_access_admin' => (int) ($user->can_access_admin ?? 0),
            'is_active' => (int) ($user->is_active ?? 0),
        ]);
    }
}
