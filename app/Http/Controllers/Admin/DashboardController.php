<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OnlineUserPresence;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, OnlineUserPresence $presence)
    {
        $user = $request->user();

        return view('admin.dashboard', [
            'onlineUsers' => $user && $user->hasRole(['owner', 'admin'])
                ? $presence->visibleFor($user)
                : null,
        ]);
    }
}
