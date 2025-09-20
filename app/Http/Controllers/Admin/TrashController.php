<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;

class TrashController extends Controller
{
    public function index(Request $req)
    {
        $rows = Reservation::onlyTrashed()->orderByDesc('deleted_at')->limit(200)->get();
        return view('admin.trash', ['rows' => $rows]);
    }

    public function restore(int $id)
    {
        $r = Reservation::onlyTrashed()->findOrFail($id);
        $r->restore();
        try { $r->items()->withTrashed()->restore(); } catch (\Throwable $e) {}
        try { $r->payments()->withTrashed()->restore(); } catch (\Throwable $e) {}
        return redirect()->route('admin.trash')->with('ok','Reservation restored');
    }

    public function forceDelete(int $id)
    {
        $r = Reservation::onlyTrashed()->findOrFail($id);
        try {
            try { $r->items()->withTrashed()->forceDelete(); } catch (\Throwable $e) {}
            try { $r->payments()->withTrashed()->forceDelete(); } catch (\Throwable $e) {}
            $r->forceDelete();
        } catch (\Throwable $e) {
            return redirect()->route('admin.trash')->withErrors(['trash'=>'Could not delete permanently']);
        }
        return redirect()->route('admin.trash')->with('ok','Reservation deleted permanently');
    }
}

