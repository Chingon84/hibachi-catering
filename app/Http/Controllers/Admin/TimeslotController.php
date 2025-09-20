<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Timeslot;
use Carbon\Carbon; // 👈 añade esta línea

class TimeslotController extends Controller
{
    public function index(Request $req)
    {
        $d = $req->query('d', now()->toDateString());
        $list = Timeslot::where('date',$d)->orderBy('time')->get();
        // Count bookings (reservations) per time on this date
        try {
            $counts = \App\Models\Reservation::whereDate('date', $d)
                ->selectRaw('time, COUNT(*) as c')
                ->groupBy('time')
                ->pluck('c', 'time');
            $guestSums = \App\Models\Reservation::whereDate('date', $d)
                ->where(function($q){ $q->whereNull('status')->orWhere('status','!=','canceled'); })
                ->selectRaw('time, COALESCE(SUM(guests),0) as g')
                ->groupBy('time')
                ->pluck('g','time');
        } catch (\Throwable $e) {
            $counts = collect();
            $guestSums = collect();
        }
        return view('admin.timeslots', ['d'=>$d, 'list'=>$list, 'counts'=>$counts, 'guestSums'=>$guestSums]);
    }

    public function store(Request $req)
{
    $allDay = $req->boolean('all_day');
    $closeDay = $req->boolean('close_day');

    if ($allDay || $closeDay) {
        $data = $req->validate([
            'date'     => 'required|date',
            'capacity' => 'nullable|integer|min:0',
        ]);

        $date = \Carbon\Carbon::parse($data['date'])->toDateString(); // YYYY-MM-DD
        $isOpen = $closeDay ? false : $req->boolean('is_open');
        $capacity = $data['capacity'] ?? 100;
        $maxBookings = null;

        // Genera intervalos de 1 hora entre 07:00 y 22:00 (7 AM – 10 PM)
        $count = 0;
        // If closing entire day, first mark any existing slots (including :30) as closed
        if ($closeDay) {
            try { Timeslot::where('date', $date)->update(['is_open' => false]); } catch (\Throwable $e) {}
        }
        for ($h = 7; $h <= 22; $h++) {
            $time = sprintf('%02d:00:00', $h);
                \App\Models\Timeslot::updateOrCreate(
                    ['date' => $date, 'time' => $time],
                    [ 'capacity' => $capacity, 'is_open' => $isOpen ]
                );
            $count++;
        }

        $msg = $closeDay ? 'Day marked as fully booked (all hours closed).' : "Saved $count slots";
        return redirect()->route('admin.timeslots', ['d'=>$date])->with('ok', $msg);
    }

    // Single slot flow
    $data = $req->validate([
        'date'     => 'required|date',
        'time'     => 'required',
        'capacity' => 'nullable|integer|min:0',
    ]);

    $date = \Carbon\Carbon::parse($data['date'])->toDateString();   // YYYY-MM-DD
    $time = \Carbon\Carbon::parse($data['time'])->format('H:i:s');  // HH:MM:SS
    $isOpen = $req->boolean('is_open');

    \App\Models\Timeslot::updateOrCreate(
        ['date' => $date, 'time' => $time],
        [ 'capacity' => $data['capacity'] ?? 100, 'is_open'  => $isOpen ]
    );

    return redirect()->route('admin.timeslots', ['d'=>$date])->with('ok','Saved');
}


    public function delete(Request $req, int $id)
    {
        $ts = Timeslot::find($id);
        $dateStr = null;
        if ($ts) {
            $dateStr = is_string($ts->date) ? $ts->date : optional($ts->date)->format('Y-m-d');
            try { $ts->delete(); } catch (\Throwable $e) { /* no-op */ }
        } else {
            // if already gone, fall through
        }
        $d = (string) $req->input('d', $dateStr ?: now()->toDateString());
        return redirect()->route('admin.timeslots', ['d' => $d]);
    }

    public function updateStatus(Request $req, int $id)
    {
        $ts = Timeslot::findOrFail($id);
        $status = (string) $req->input('status', $ts->is_open ? 'open' : 'closed');
        $isOpen = in_array(strtolower($status), ['open','1','true','yes'], true);
        $ts->is_open = $isOpen;
        $ts->save();

        $date = $req->input('d', optional($ts->date)->format('Y-m-d'));
        return redirect()->route('admin.timeslots', ['d'=>$date])->with('ok','Status updated');
    }

    // Update capacity for a specific timeslot (and reopen if remaining > 0)
    public function updateCapacity(Request $req, int $id)
    {
        $data = $req->validate([
            'capacity' => 'required|integer|min:0',
            'd' => 'nullable|date',
        ]);
        $ts = Timeslot::findOrFail($id);
        $ts->capacity = (int) $data['capacity'];
        $ts->save();

        // If capacity increase leaves remaining > 0, reopen the slot automatically
        try {
            $dateStr = is_string($ts->date) ? $ts->date : optional($ts->date)->format('Y-m-d');
            $booked = \App\Models\Reservation::whereDate('date', $dateStr)
                ->where('time', $ts->time)
                ->where(function($q){ $q->whereNull('status')->orWhere('status','!=','canceled'); })
                ->sum('guests');
            $remaining = max(0, (int)$ts->capacity - (int)$booked);
            if ($remaining > 0 && !$ts->is_open) {
                $ts->is_open = true;
                $ts->save();
            }
        } catch (\Throwable $e) {}

        $d = (string) ($data['d'] ?? (is_string($ts->date) ? $ts->date : optional($ts->date)->format('Y-m-d')));
        return redirect()->route('admin.timeslots', ['d'=>$d])->with('ok','Capacity updated');
    }

    // Bulk update capacities for all slots listed for a date
    public function bulkUpdate(Request $req)
    {
        $data = $req->validate([
            'd' => 'required|date',
            'cap' => 'required|array',
            'cap.*' => 'nullable|integer|min:0',
        ]);
        $d = $data['d'];
        $caps = $data['cap'];
        foreach ($caps as $id => $cap) {
            try {
                $ts = Timeslot::find((int)$id);
                if (!$ts) continue;
                $ts->capacity = (int) $cap;
                $ts->save();
                // Auto-open if remaining > 0; close if remaining <= 0
                $dateStr = is_string($ts->date) ? $ts->date : optional($ts->date)->format('Y-m-d');
                $booked = \App\Models\Reservation::whereDate('date', $dateStr)
                    ->where('time', $ts->time)
                    ->where(function($q){ $q->whereNull('status')->orWhere('status','!=','canceled'); })
                    ->sum('guests');
                $remaining = max(0, (int)$ts->capacity - (int)$booked);
                $newOpen = $remaining > 0;
                if ($ts->is_open !== $newOpen) {
                    $ts->is_open = $newOpen;
                    $ts->save();
                }
            } catch (\Throwable $e) { /* skip */ }
        }
        return redirect()->route('admin.timeslots', ['d'=>$d])->with('ok','Saved changes');
    }

    // Returns slots for a given date as JSON (used by calendar UI)
    public function json(Request $req)
    {
        $d = (string) $req->query('d', now()->toDateString());
        $list = Timeslot::where('date', $d)->orderBy('time')->get(['id','date','time','capacity','is_open']);
        // booking counts
        try {
            $counts = \App\Models\Reservation::whereDate('date', $d)
                ->selectRaw('time, COUNT(*) as c')
                ->groupBy('time')
                ->pluck('c', 'time');
            $guestSums = \App\Models\Reservation::whereDate('date', $d)
                ->where(function($q){ $q->whereNull('status')->orWhere('status','!=','canceled'); })
                ->selectRaw('time, COALESCE(SUM(guests),0) as g')
                ->groupBy('time')
                ->pluck('g','time');
        } catch (\Throwable $e) {
            $counts = collect();
            $guestSums = collect();
        }
        return response()->json([
            'date' => $d,
            'slots' => $list->map(function($r){
                return [
                    'id' => $r->id,
                    'time' => substr((string)$r->time, 0, 5),
                    'time_label' => \Carbon\Carbon::parse($r->time)->format('g:i A'),
                    'capacity' => (int) $r->capacity,
                    'is_open' => (bool) $r->is_open,
                ];
            }),
            'bookings' => $counts,
            'guest_sums' => $guestSums,
        ]);
    }

    // Return reservations list for a given date; if time HH:MM provided, filters to that hour
    public function bookingsJson(Request $req)
    {
        $d = (string) $req->query('d');
        $t = $req->query('t');
        if (!$d) return response()->json(['date'=>null, 'items'=>[]]);
        $query = \App\Models\Reservation::whereDate('date', $d)
            ->where(function($q){ $q->whereNull('status')->orWhere('status','!=','canceled'); });
        if ($t) {
            $time = strlen($t) === 5 ? ($t.':00') : (string)$t;
            $query->where('time', $time);
        }
        try {
            $rows = $query->orderBy('time')->orderBy('id','desc')
                ->get(['id','customer_name','guests','status','invoice_number','booked_by','time']);
        } catch (\Throwable $e) { $rows = collect(); }

        return response()->json([
            'date' => $d,
            'filter_time' => $t ? substr((string)$t,0,5) : null,
            'items'=> $rows->map(function($r){
                return [
                    'id' => $r->id,
                    'name' => (string) ($r->customer_name ?: ('#'.$r->id)),
                    'guests' => (int) ($r->guests ?? 0),
                    'status' => (string) ($r->status ?? 'pending'),
                    'by' => (string) ($r->booked_by ?? ''),
                    'inv' => $r->invoice_number,
                    'time' => substr((string)$r->time,0,5),
                    'time_label' => \Carbon\Carbon::parse($r->time)->format('g:i A'),
                ];
            }),
        ]);
    }

    // Month status: which dates are fully closed (all slots closed and at least one slot exists)
    public function monthStatusJson(Request $req)
    {
        $y = (int) $req->query('y', (int) date('Y'));
        $m = (int) $req->query('m', (int) date('m'));
        try {
            $start = Carbon::create($y, $m, 1)->toDateString();
            $end   = Carbon::create($y, $m, 1)->endOfMonth()->toDateString();
        } catch (\Throwable $e) {
            return response()->json(['full'=>[]]);
        }

        $rows = Timeslot::selectRaw("date, SUM(CASE WHEN is_open=1 THEN 1 ELSE 0 END) as open_cnt, COUNT(*) as total_cnt")
            ->whereBetween('date', [$start, $end])
            ->groupBy('date')
            ->get();

        $full = [];
        foreach ($rows as $r) {
            $date = is_string($r->date) ? $r->date : optional($r->date)->format('Y-m-d');
            if (!$date) continue;
            $open = (int) ($r->open_cnt ?? 0);
            $total= (int) ($r->total_cnt ?? 0);
            // Fully booked if there is at least one slot and none is open
            $full[$date] = ($total > 0 && $open === 0);
        }
        return response()->json(['full' => $full]);
    }

    // Auto-fill all days in the visible month with hourly slots (07:00–22:00)
    public function autoFillMonth(Request $req)
    {
        $data = $req->validate([
            'y' => 'required|integer|min:2000|max:2100',
            'm' => 'required|integer|min:1|max:12',
            'capacity' => 'nullable|integer|min:0',
            'is_open' => 'nullable|boolean',
        ]);

        $y = (int) $data['y'];
        $m = (int) $data['m'];
        try { $start = Carbon::create($y, $m, 1); } catch (\Throwable $e) { return back()->withErrors('Invalid month.'); }

        $capacity = $data['capacity'] ?? 100;
        $isOpen = $req->boolean('is_open', true);

        $days = cal_days_in_month(CAL_GREGORIAN, $m, $y);
        for ($d=1; $d <= $days; $d++) {
            $date = sprintf('%04d-%02d-%02d', $y, $m, $d);
            for ($h=7; $h<=22; $h++) {
                $time = sprintf('%02d:00:00', $h);
                Timeslot::updateOrCreate(
                    ['date'=>$date,'time'=>$time],
                    ['capacity'=>$capacity,'is_open'=>$isOpen]
                );
            }
        }

        return redirect()->route('admin.timeslots', ['d'=>$start->toDateString()])
            ->with('ok', 'Auto-filled month with hourly slots.');
    }

    // Clear all timeslots for a given month (visible calendar month)
    public function clearMonth(Request $req)
    {
        $data = $req->validate([
            'y' => 'required|integer|min:2000|max:2100',
            'm' => 'required|integer|min:1|max:12',
        ]);
        $y = (int) $data['y'];
        $m = (int) $data['m'];
        try { $start = Carbon::create($y, $m, 1); } catch (\Throwable $e) { return back()->withErrors('Invalid month.'); }
        $startDate = $start->toDateString();
        $endDate = $start->copy()->endOfMonth()->toDateString();
        $count = 0;
        try {
            $count = Timeslot::whereBetween('date', [$startDate, $endDate])->delete();
        } catch (\Throwable $e) { /* ignore */ }
        return redirect()->route('admin.timeslots', ['d' => $startDate])
            ->with('ok', "Deleted $count slots for ${m}/$y");
    }

    
}
