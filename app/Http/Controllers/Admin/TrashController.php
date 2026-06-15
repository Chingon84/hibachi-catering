<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;

class TrashController extends Controller
{
    public function index(Request $req)
    {
        $perPage = $this->adminPerPage($req);
        $rows = Reservation::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.trash', ['rows' => $rows, 'perPage' => $perPage]);
    }

    private function adminPerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);

        return in_array($perPage, [10, 15, 25], true) ? $perPage : 25;
    }

    public function restore(int $id)
    {
        $r = Reservation::onlyTrashed()->findOrFail($id);
        $r->restore();
        $r->items()->withTrashed()->restore();
        $r->payments()->withTrashed()->restore();

        return redirect()->route('admin.trash')->with('ok', 'Reservation restored');
    }

    public function forceDelete(int $id)
    {
        $r = Reservation::onlyTrashed()->findOrFail($id);

        try {
            $r->items()->withTrashed()->forceDelete();
            $r->payments()->withTrashed()->forceDelete();
            $r->forceDelete();
        } catch (\Throwable $e) {
            return redirect()->route('admin.trash')->withErrors(['trash' => 'Could not delete permanently']);
        }

        return redirect()->route('admin.trash')->with('ok', 'Reservation deleted permanently');
    }
}
