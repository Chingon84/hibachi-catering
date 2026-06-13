<?php

namespace App\Http\Middleware;

use App\Services\OnlineUserPresence;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackOnlinePresence
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if ($user && (int) ($user->is_active ?? 1) === 1) {
            app(OnlineUserPresence::class)->mark((int) $user->id);
        }

        return $next($request);
    }
}
