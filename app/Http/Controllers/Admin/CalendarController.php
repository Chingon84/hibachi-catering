<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $req)
    {
        $view = (string) $req->query('view', 'month'); // month|week|day
        $today = Carbon::today();
        if ($view === 'week') {
            $d = (string) $req->query('d', $today->toDateString());
            try { $day = Carbon::parse($d); } catch (\Throwable $e) { $day = $today; }
            $start = $day->copy()->startOfWeek();
            $end   = $day->copy()->endOfWeek();
            $month = $day->copy()->startOfMonth();
        } elseif ($view === 'day') {
            $d = (string) $req->query('d', $today->toDateString());
            try { $day = Carbon::parse($d); } catch (\Throwable $e) { $day = $today; }
            $start = $day->copy();
            $end   = $day->copy();
            $month = $day->copy()->startOfMonth();
        } else { // month
            $m = (string) $req->query('m', $today->format('Y-m'));
            try { $month = Carbon::createFromFormat('Y-m', $m)->startOfMonth(); }
            catch (\Throwable $e) { $month = $today->copy()->startOfMonth(); }
            $start = $month->copy()->startOfWeek();
            $end   = $month->copy()->endOfMonth()->endOfWeek();
            $view = 'month';
        }

        // Fetch reservations in the visible calendar range
        $rows = Reservation::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        // Bucket by date string
        $byDate = [];
        foreach ($rows as $r) {
            $key = optional($r->date)->toDateString();
            if (!$key) continue;
            $byDate[$key][] = $r;
        }

        return view('admin.calendar', [
            'month'   => $month,
            'start'   => $start,
            'end'     => $end,
            'byDate'  => $byDate,
            'view'    => $view,
        ]);
    }

    // Return event details as JSON for popover
    public function eventJson(int $id)
    {
        $r = Reservation::with(['items','payments'])->findOrFail($id);
        $out = [
            'id' => $r->id,
            'title' => $r->customer_name ?? '—',
            'status' => $r->status,
            'invoice_status' => $r->invoice_status,
            'date' => optional($r->date)->format('Y-m-d'),
            'time' => \Carbon\Carbon::parse($r->time)->format('g:i A'),
            'guests' => (int) ($r->guests ?? 0),
            'color' => $r->color ?? null,
            'address' => $r->address,
            'city' => $r->city,
            'zip_code' => $r->zip_code,
            'email' => $r->email,
            'phone' => $r->phone,
            'setup_color' => $r->setup_color,
            'event_type' => $r->event_type,
            'stairs' => (bool) ($r->stairs ?? false),
            'notes' => $r->notes,
            'booked_by' => $r->booked_by,
            'totals' => [
                'subtotal' => (float) ($r->subtotal ?? 0),
                'travel_fee' => (float) ($r->travel_fee ?? 0),
                'gratuity' => (float) ($r->gratuity ?? 0),
                'tax' => (float) ($r->tax ?? 0),
                'total' => (float) ($r->total ?? 0),
                'deposit_paid' => (float) ($r->deposit_paid ?? 0),
                'balance' => (float) ($r->balance ?? 0),
            ],
            'items' => $r->items->map(fn($it)=>[
                'id' => $it->id,
                'name' => $it->name_snapshot,
                'qty' => (int) $it->qty,
                'unit_price' => (float) ($it->unit_price_snapshot ?? 0),
                'line_total' => (float) ($it->line_total ?? 0),
                'description' => $it->description,
            ])->values(),
            'adjustments' => collect((array)($r->invoice_adjustments ?? []))->map(function($a){
                return [
                    'label' => (string) ($a['label'] ?? 'Adjustment'),
                    'amount'=> (float) ($a['amount'] ?? 0),
                ];
            })->values(),
            'payments' => $r->payments->map(fn($p)=>[
                'id' => $p->id,
                'amount' => (float) ($p->amount ?? 0),
                'method' => $p->method ?? null,
                'status' => $p->status ?? null,
                'created_at' => optional($p->created_at)->format('Y-m-d H:i:s'),
            ])->values(),
            'links' => [
                'invoice' => route('admin.reservations.invoice', ['id'=>$r->id]),
                'edit'    => route('admin.reservations.show', ['id'=>$r->id]),
            ],
        ];
        return response()->json($out);
    }
}
