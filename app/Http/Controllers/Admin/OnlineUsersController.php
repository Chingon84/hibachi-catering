<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OnlineUserPresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnlineUsersController extends Controller
{
    public function index(Request $request, OnlineUserPresence $presence): JsonResponse
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole(['owner', 'admin']), 403);

        return response()->json($presence->visibleFor($user));
    }
}
