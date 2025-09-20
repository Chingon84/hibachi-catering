<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffBookingController extends Controller
{
    public function step1(Request $req)
    {
        $data = session('staff_booking', []);
        // Prefill from query if provided (e.g., from Calendar quick-add)
        $qDate = $req->query('date', $req->query('d'));
        $qTime = $req->query('time', $req->query('t'));
        $qGuests = $req->query('guests');
        if ($qDate && empty($data['event_date'])) {
            try { $data['event_date'] = \Carbon\Carbon::parse($qDate)->toDateString(); } catch (\Throwable $e) {}
        }
        if ($qTime && empty($data['event_time'])) {
            try { $data['event_time'] = \Carbon\Carbon::parse($qTime)->format('H:i'); } catch (\Throwable $e) {}
        }
        if (is_numeric($qGuests) && empty($data['guest_count'])) {
            $data['guest_count'] = (int) $qGuests;
        }
        return view('admin.staff_bookings_step1', ['data' => $data]);
    }

    public function submitStep1(Request $req)
    {
        $validated = $req->validate([
            'event_date'   => 'required|date',
            'event_time'   => 'required',
            'guest_count'  => 'required|integer|min:1',
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'company'      => 'nullable|string|max:150',
            'phone'        => 'required|string|max:40',
            'email'        => 'required|email|max:150',
            'address'      => 'required|string|max:200',
            'city'         => 'required|string|max:120',
            'zip'          => 'required|string|max:16',
            'serving_style'=> 'required|in:table,buffet',
            'event_type'   => 'required|string|max:120',
            'setup_color'  => 'nullable|string|max:120',
            'stairs'       => 'required|in:yes,no',
            'heard_about'  => 'nullable|string|max:200',
            'handled_by'   => 'required|string|max:100',
            'agent_notes'  => 'nullable|string',
        ]);

        session(['staff_booking' => $validated]);
        return redirect()->to('/admin/staff-bookings/step2');
    }

    public function step2()
    {
        $data = session('staff_booking', []);
        $cats = $this->menuCategories();
        $constants = [
            'GRATUITY' => 0.18,
            'TAX'      => 0.1025,
        ];
        $travelFee = (float) data_get($data, 'calc.travel', 0.00);
        $guests = (int)($data['guest_count'] ?? 0);
        return view('admin.staff_bookings_step2', [
            'data' => $data,
            'menuCategories' => $cats,
            'constants' => $constants,
            'travel_fee' => $travelFee,
            'guests' => $guests,
        ]);
    }

    public function submitStep2(Request $req)
    {
        $cats = $this->menuCategories();
        $items = (array) $req->input('items', []); // code => qty
        $flat = $this->flatMenu();
        $deposit = (float) $req->input('deposit_paid', 0);
        $discount = (float) $req->input('discount', 0);
        $paymentMethod = (string) $req->input('payment_method', '');
        $paymentDate   = (string) $req->input('payment_date', '');
        // Custom fees (array of label/amount)
        $extrasIn = (array) $req->input('custom_fees', []);
        $labels = (array) ($extrasIn['label'] ?? []);
        $amounts = (array) ($extrasIn['amount'] ?? []);
        $extras = [];
        $extrasSum = 0.0;
        foreach ($labels as $i => $lbl) {
            $lbl = trim((string) $lbl);
            $amt = (float) ($amounts[$i] ?? 0);
            if ($lbl === '' && $amt <= 0) continue;
            $amt = max(0, round($amt, 2));
            $extras[] = ['label' => $lbl !== '' ? $lbl : 'Custom fee', 'amount' => $amt];
            $extrasSum += $amt;
        }

        $lines = [];
        $subtotal = 0.0;
        foreach ($items as $code => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) continue;
            if (!isset($flat[$code])) continue;
            $name = $flat[$code]['name'];
            $price = (float) $flat[$code]['price'];
            $line = round($price * $qty, 2);
            $subtotal += $line;
            $lines[] = [
                'code'  => $code,
                'name'  => $name,
                'price' => $price,
                'qty'   => $qty,
                'total' => $line,
                'cat'   => $flat[$code]['cat'] ?? 'Menu',
            ];
        }

        $GRAT = 0.18; $TAX = 0.1025; $travel = (float) $req->input('travel_fee', 0);
        $manualGrat = $req->input('gratuity', null);
        $manualTax  = $req->input('tax', null);
        $gratuity = is_null($manualGrat) ? round($subtotal * $GRAT, 2) : max(0, round((float)$manualGrat, 2));
        $tax      = is_null($manualTax)  ? round($subtotal * $TAX, 2) : max(0, round((float)$manualTax, 2));
        $total    = round(max(0, $subtotal + $travel + $extrasSum + $gratuity + $tax - max(0, $discount)), 2);
        $balance  = max(0, round($total - $deposit, 2));

        $sb = session('staff_booking', []);
        $sb['selected_items'] = $lines;
        $sb['calc'] = [
            'subtotal' => round($subtotal, 2),
            'travel'   => $travel,
            'extras'   => $extras,
            'extras_sum' => round($extrasSum, 2),
            'gratuity' => $gratuity,
            'tax'      => $tax,
            'total'    => $total,
            'paid'     => round($deposit, 2),
            'discount' => round(max(0, $discount), 2),
            'balance'  => $balance,
            'payment_method' => $paymentMethod,
            'payment_date'   => $paymentDate,
        ];
        session(['staff_booking' => $sb]);

        return redirect()->route('admin.staff_bookings.step3');
    }

    public function step3()
    {
        $data = session('staff_booking', []);
        return view('admin.staff_bookings_step3', ['data' => $data]);
    }

    public function confirm(Request $req)
    {
        $sb = session('staff_booking', []);
        if (empty($sb)) {
            return redirect()->route('admin.staff_bookings.step1')->withErrors(['sb'=>'Session expired']);
        }

        $calc = (array) ($sb['calc'] ?? []);
        $lines = (array) ($sb['selected_items'] ?? []);

        // Create reservation
        $r = new \App\Models\Reservation();
        $r->code = 'RSV-'.strtoupper(str()->random(6));
        try {
            $max = (int) (\App\Models\Reservation::max('invoice_number') ?? 0);
            $r->invoice_number = $max >= 100 ? ($max + 1) : 100;
        } catch (\Throwable $e) {
            $r->invoice_number = null;
        }

        $r->status = ($calc['paid'] ?? 0) > 0 ? 'confirmed' : 'pending_payment';
        $r->guests = (int) ($sb['guest_count'] ?? 0);
        $r->date   = !empty($sb['event_date']) ? \Carbon\Carbon::parse($sb['event_date'])->toDateString() : now()->toDateString();
        $r->time   = !empty($sb['event_time']) ? \Carbon\Carbon::parse($sb['event_time'])->format('H:i:s') : '18:00:00';
        $r->customer_name = trim((string) (($sb['first_name'] ?? '').' '.($sb['last_name'] ?? '')));
        $r->company = $sb['company'] ?? null;
        $r->phone   = $sb['phone'] ?? null;
        $r->email   = $sb['email'] ?? null;
        $r->address = $sb['address'] ?? null;
        $r->city    = $sb['city'] ?? null;
        $r->zip_code= $sb['zip'] ?? null;
        $r->event_type  = $sb['event_type'] ?? null;
        $r->setup_color = $sb['setup_color'] ?? null;
        $r->stairs      = strtolower((string)($sb['stairs'] ?? 'no')) === 'yes';
        $r->heard_about = $sb['heard_about'] ?? null;
        $r->notes       = $sb['agent_notes'] ?? null;
        $r->booked_by   = $sb['handled_by'] ?? 'Staff';

        // Totals (fold extras into travel_fee so admin totals match better)
        $travel   = (float) ($calc['travel'] ?? 0);
        $extras   = (float) ($calc['extras_sum'] ?? 0);
        $r->travel_fee = round($travel + $extras, 2);
        $r->gratuity   = (float) ($calc['gratuity'] ?? 0);
        $r->tax        = (float) ($calc['tax'] ?? 0);
        $r->discount   = (float) ($calc['discount'] ?? 0);
        $r->subtotal   = (float) ($calc['subtotal'] ?? 0);
        $r->total      = (float) ($calc['total'] ?? 0);
        $r->deposit_paid = (float) ($calc['paid'] ?? 0);
        $r->balance    = (float) ($calc['balance'] ?? max(0, $r->total - $r->deposit_paid));
        $r->save();

        // Items
        foreach ($lines as $it) {
            $name = $it['name'] ?? 'Item';
            $price= (float) ($it['price'] ?? 0);
            $qty  = (int) ($it['qty'] ?? 1);
            $r->items()->create([
                'menu_id'             => null,
                'name_snapshot'       => $name,
                'unit_price_snapshot' => $price,
                'qty'                 => $qty,
                'line_total'          => round($price * $qty, 2),
            ]);
        }

        // Optional payment record
        $paid = (float) ($calc['paid'] ?? 0);
        if ($paid > 0.0) {
            $prov = strtolower((string) ($calc['payment_method'] ?? 'manual'));
            $p = \App\Models\Payment::create([
                'reservation_id' => $r->id,
                'provider'       => $prov !== '' ? $prov : 'manual',
                'amount'         => $paid,
                'currency'       => 'USD',
                'status'         => 'succeeded',
                'transaction_id' => null,
                'payload_json'   => null,
            ]);
            if (!empty($calc['payment_date'])) {
                try {
                    $p->created_at = \Carbon\Carbon::parse($calc['payment_date'])->startOfDay();
                    $p->save();
                } catch (\Throwable $e) {}
            }
        }

        // Clear session for a fresh start
        session()->forget('staff_booking');

        // Redirect to Calendar focused on the event date
        return redirect()->route('admin.calendar', ['view'=>'day', 'd'=>$r->date])->with('ok','Reservation created');
    }

    private function flatMenu(): array
    {
        // Read directly from file to bypass config cache
        $path = base_path('config/menu.php');
        if (is_file($path)) {
            try {
                $cfg = include $path;
                if (!is_array($cfg)) { $cfg = (array) config('menu'); }
            } catch (\Throwable $e) {
                $cfg = (array) config('menu');
            }
        } else {
            $cfg = (array) config('menu');
        }
        $out = [];
        foreach ($cfg as $cat => $items) {
            foreach ((array)$items as $it) {
                if (!isset($it['key'])) continue;
                $out[$it['key']] = [
                    'name' => $it['name'] ?? $it['key'],
                    'price'=> (float)($it['price'] ?? 0),
                    'cat'  => $cat,
                ];
            }
        }
        return $out;
    }

    private function menuCategories(): array
    {
        // Read directly from file to bypass config cache
        $path = base_path('config/menu.php');
        if (is_file($path)) {
            try {
                $cfg = include $path;
                if (!is_array($cfg)) { $cfg = (array) config('menu'); }
            } catch (\Throwable $e) {
                $cfg = (array) config('menu');
            }
        } else {
            $cfg = (array) config('menu');
        }
        $cats = [];
        foreach ($cfg as $cat => $items) {
            $cats[$cat] = [];
            foreach ((array)$items as $it) {
                if (!isset($it['key'])) continue;
                $cats[$cat][$it['key']] = [
                    'name'  => $it['name'] ?? $it['key'],
                    'price' => (float)($it['price'] ?? 0),
                ];
            }
        }
        return $cats;
    }
}
