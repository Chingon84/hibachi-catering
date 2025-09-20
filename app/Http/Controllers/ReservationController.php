<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\Timeslot;
use App\Models\ReservationItem;
use App\Support\MenuLabel;

class ReservationController extends Controller
{
    /** ======= CONSTANTES DE COTIZACIÓN ======= */
    private const GRATUITY = 0.18;  // 18%
    private const TAX      = 0.1025; // 10.25%
    private const TRAVEL_RATE = 3.00; // $/milla (zip -> zip)

    /** ======= WIZARD: SHOW ======= */
   public function show(Request $req, int $step = 1)
{
    $step = max(1, min(5, $step));

    $reservation = null;
    if ($id = data_get(session('resv'), 'reservation_id')) {
        $reservation = \App\Models\Reservation::find($id);
    }

    // Prefill Step 1 from query (e.g., from Calendar quick-add)
    if ($step === 1) {
        $prefill = [];
        $qGuests = $req->query('guests');
        $qDate   = $req->query('date');   // YYYY-MM-DD
        $qTime   = $req->query('time');   // HH:mm
        if (is_numeric($qGuests) && (int)$qGuests > 0) {
            $prefill['guests'] = (int) $qGuests;
        }
        if ($qDate) {
            try { $prefill['date'] = \Carbon\Carbon::parse($qDate)->toDateString(); } catch (\Throwable $e) {}
        }
        if ($qTime) {
            // accept HH:mm or HH:mm:ss, normalize to HH:mm
            try { $prefill['time'] = \Carbon\Carbon::parse($qTime)->format('H:i'); } catch (\Throwable $e) {}
        }
        if (!empty($prefill)) {
            session(['resv' => array_merge(session('resv', []), $prefill)]);
        }
    }

    $data = [
        'step'        => $step,
        'state'       => session('resv', []),
        'reservation' => $reservation,
    ];

    if ($step === 3) {
        $menuPlano = $this->menu();                     // <- tu menú PLANO tal como ya lo tienes
        $data['menuCategories'] = $this->menuToCategories($menuPlano);
        $data['constants'] = ['TAX' => 0.1025, 'GRATUITY' => 0.18];
    }

    return view("reservations.step{$step}", $data);
}

/** Agrupa tu menú plano por categorías (déjala dentro de la clase) */
private function menuToCategories(array $menu): array
{
    $out = [];
    foreach ($menu as $code => $it) {
        $cat = $it['category'] ?? 'General';
        $name = MenuLabel::standardize($it['name'] ?? 'Item');
        $desc = $it['desc'] ?? null;
        if ($desc !== null) {
            $desc = MenuLabel::standardizeText($desc);
        }
        $out[$cat][$code] = [
            'name'  => $name,
            'price' => (float)($it['price'] ?? 0),
            'desc'  => $desc,
        ];
    }
    return $out;
}


    /** ======= WIZARD: SUBMIT ======= */
    public function submit(Request $req, int $step)
    {
        $step = max(1, min(5, $step));

        /* ---------- STEP 1: Date & Time ---------- */
        if ($step === 1) {
            $data = $req->validate([
                'guests' => 'required|integer|min:1',
                'date'   => 'required|date_format:Y-m-d',
                'time'   => 'required|date_format:H:i',
            ]);

            // Weekend (Fri/Sat/Sun) minimum guests = 10
            try {
                $dow = \Carbon\Carbon::parse($data['date'])->dayOfWeek; // 0=Sun .. 6=Sat
                $isWeekend = in_array($dow, [0,5,6], true); // Sun, Fri, Sat
                if ($isWeekend && (int)$data['guests'] < 10) {
                    return back()->withErrors(['guests' => 'Minimum 10 guests on Fridays, Saturdays and Sundays.'])->withInput();
                }
            } catch (\Throwable $e) { /* ignore, fallback to base rule */ }

            // Enforce 6-hour minimum lead time (Los Angeles timezone)
            $tz = config('app.timezone') ?: env('APP_TIMEZONE', 'America/Los_Angeles');
            $eventAt = Carbon::parse($data['date'].' '.$data['time'], $tz);
            $minLead = Carbon::now($tz)->seconds(0)->addHour();
            if ($eventAt->lt($minLead)) {
                return back()->withErrors(['time' => 'Please select a time at least 6 hours from now.'])->withInput();
            }

            // Capacity guard: ensure requested guests fit into remaining capacity
            try {
                $slot = Timeslot::where('date', $data['date'])
                    ->where('time', $data['time'].':00')
                    ->first();
                if (!$slot || !$slot->is_open) {
                    return back()->withErrors(['time' => 'Selected time is not available.'])->withInput();
                }
                $bookedGuests = Reservation::whereDate('date', $data['date'])
                    ->where('time', $data['time'].':00')
                    ->where(function($q){ $q->whereNull('status')->orWhere('status','!=','canceled'); })
                    ->sum('guests');
                $remaining = max(0, (int)$slot->capacity - (int)$bookedGuests);
                if ((int)$data['guests'] > $remaining) {
                    return back()->withErrors(['guests' => 'Not enough capacity available for that time.'])->withInput();
                }
                // No per-slot booking count limit; governed solely by capacity remaining
            } catch (\Throwable $e) {
                // On error, do not block the user silently
            }

            $state = session('resv', []);

            $reservation = null;
            if (!empty($state['reservation_id'])) {
                $reservation = Reservation::find($state['reservation_id']);
            }
            if (!$reservation) {
                $reservation = new Reservation();
                $reservation->code = 'RSV-'.Str::upper(Str::random(6));
                $reservation->status = 'draft';
                // Assign next invoice number starting at 100
                try {
                    $max = (int) (Reservation::max('invoice_number') ?? 0);
                    $reservation->invoice_number = $max >= 100 ? ($max + 1) : 100;
                } catch (\Throwable $e) {
                    $reservation->invoice_number = null; // fallback
                }
            }

            // Ensure invoice number exists (if this reservation predates invoices)
            if (empty($reservation->invoice_number)) {
                try {
                    $max = (int) (Reservation::max('invoice_number') ?? 0);
                    $reservation->invoice_number = $max >= 100 ? ($max + 1) : 100;
                } catch (\Throwable $e) {
                    // no-op
                }
            }

            $reservation->guests = (int) $data['guests'];
            $reservation->date   = $data['date'];
            $reservation->time   = $data['time'].':00';
            $reservation->save();

            // After saving, if capacity reaches zero, auto-close the slot
            try {
                if (isset($slot) && $slot) {
                    $bookedGuests = Reservation::whereDate('date', $reservation->date)
                        ->where('time', $reservation->time)
                        ->where(function($q){ $q->whereNull('status')->orWhere('status','!=','canceled'); })
                        ->sum('guests');
                    $remaining = max(0, (int)$slot->capacity - (int)$bookedGuests);
                    if ($remaining <= 0 && $slot->is_open) {
                        $slot->is_open = false;
                        $slot->save();
                    }
                }
            } catch (\Throwable $e) {}

            $state = array_merge($state, $data, ['reservation_id' => $reservation->id]);
            session(['resv' => $state]);

            return redirect()->route('reservations.step', ['step'=>2]);
        }

        /* ---------- STEP 2: Guest details ---------- */
        if ($step === 2) {
            $data = $req->validate([
                'first_name'     => 'required|string|max:60',
                'last_name'      => 'required|string|max:60',
                'company'        => 'nullable|string|max:100',
                'phone'          => 'nullable|string|max:40',
                'email'          => 'nullable|email|max:120',
                'address'        => 'nullable|string|max:200',
                'city'           => 'nullable|string|max:100',
                'zip_code'       => 'nullable|string|max:10',   // usamos ZIP para cálculo
                'event_type'     => 'nullable|string|max:60',
                'setup_color'    => 'nullable|string|max:60',
                'stairs'         => 'nullable|boolean',
                'heard_about'    => 'nullable|string|max:60',
                'distance_miles' => 'nullable|numeric|min:0',
                'travel_fee'     => 'nullable|numeric|min:0',
                'notes'          => 'nullable|string|max:500',
            ]);

            $id = data_get(session('resv'), 'reservation_id');
            abort_if(!$id, 400, 'Reservation not started.');
            $reservation = Reservation::findOrFail($id);

            $zip     = trim((string)($data['zip_code'] ?? ''));
            $stairs  = filter_var($data['stairs'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Preferimos distancia y fee que vienen del front (calculado por ZIP),
            // si no, estimamos con 0.
            $distance  = isset($data['distance_miles']) && $data['distance_miles'] !== ''
                ? (float)$data['distance_miles']
                : (float)data_get(session('resv'), 'distance_miles', 0.0);

            $travelFee = isset($data['travel_fee']) && $data['travel_fee'] !== ''
                ? (float)$data['travel_fee']
                : round($distance * self::TRAVEL_RATE, 2);

            $customer_name = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));

            $reservation->fill([
                'customer_name'  => $customer_name,
                'company'        => $data['company']   ?? null,
                'phone'          => $data['phone']     ?? null,
                'email'          => $data['email']     ?? null,
                'address'        => $data['address']   ?? null,
                'city'           => $data['city']      ?? null,
                'zip_code'       => $zip ?: null,
                'event_type'     => $data['event_type'] ?? null,
                'setup_color'    => $data['setup_color'] ?? null,
                'stairs'         => $stairs,
                'heard_about'    => $data['heard_about'] ?? null,
                'notes'          => $data['notes'] ?? null,
                // Persist zeros; do not coerce 0 to null
                'distance_miles' => $distance,
                'travel_fee'     => $travelFee,
            ])->save();

            session(['resv' => array_merge(session('resv', []), [
                'first_name'     => $data['first_name'],
                'last_name'      => $data['last_name'],
                'company'        => $data['company'] ?? null,
                'phone'          => $data['phone'] ?? null,
                'email'          => $data['email'] ?? null,
                'address'        => $data['address'] ?? null,
                'city'           => $data['city'] ?? null,
                'zip_code'       => $zip ?: null,
                'event_type'     => $data['event_type'] ?? null,
                'setup_color'    => $data['setup_color'] ?? null,
                'stairs'         => $stairs,
                'heard_about'    => $data['heard_about'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'distance_miles' => $distance,
                'travel_fee'     => $travelFee,
            ])]);

            return redirect()->route('reservations.step', ['step'=>3]);
        }

        /* ---------- STEP 3: Menú + estimate ---------- */
        if ($step === 3) {
            // Esperamos items[code] => qty
            $items = $req->input('items', []);
            if (!is_array($items)) $items = [];

            $menu = $this->menu(); // canonical
            $lines = [];
            $subtotal = 0.0;

            foreach ($items as $code => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) continue;
                if (!isset($menu[$code])) continue;

                $name  = MenuLabel::standardize($menu[$code]['name']);
                $price = (float) $menu[$code]['price'];
                $line  = $price * $qty;

                $subtotal += $line;
                $lines[] = [
                    'code'       => $code,
                    'name'       => $name,
                    'unit_price' => $price,
                    'qty'        => $qty,
                    'line_total' => $line,
                ];
            }

            $state = session('resv', []);
            $travelFee = (float) ($state['travel_fee'] ?? 0);

            $gratuity = round($subtotal * self::GRATUITY, 2);
            $tax      = round($subtotal * self::TAX, 2);
            $total    = round($subtotal + $travelFee + $gratuity + $tax, 2);

            // Guardamos líneas en BD
            $id = data_get($state, 'reservation_id');
            abort_if(!$id, 400, 'Reservation not started.');
            $reservation = Reservation::findOrFail($id);

            // Borra anteriores y crea nuevos (ajustado a columnas *_snapshot)
            $reservation->items()->delete();
            foreach ($lines as $L) {
                $reservation->items()->create([
                    'menu_id'              => null,
                    'name_snapshot'        => $L['name'],
                    'unit_price_snapshot'  => $L['unit_price'],
                    'qty'                  => $L['qty'],
                    'line_total'           => $L['line_total'],
                ]);
            }

            // Persistir totales en la reserva
            try {
                $reservation->subtotal  = round($subtotal, 2);
                $reservation->travel_fee= round($travelFee, 2);
                $reservation->gratuity  = $gratuity;
                $reservation->tax       = $tax;
                $reservation->total     = $total;
                $currentPaid = (float) ($reservation->deposit_paid ?? 0);
                $reservation->balance   = max(0, round($total - $currentPaid, 2));
                $reservation->save();
            } catch (\Throwable $e) {
                // noop
            }

            // Persistimos estimate en sesión (lo verás en Step 4)
            session(['resv' => array_merge($state, [
                'estimate' => [
                    'subtotal'  => round($subtotal, 2),
                    'travel'    => round($travelFee, 2),
                    'gratuity'  => $gratuity,
                    'tax'       => $tax,
                    'total'     => $total,
                ],
            ])]);

            return redirect()->route('reservations.step', ['step'=>4]);
        }

        /* ---------- STEP 4: Payment (deposit) ---------- */
        if ($step === 4) {
            $id = data_get(session('resv'), 'reservation_id');
            abort_if(!$id, 400, 'Reservation not started.');

            $data = $req->validate([
                'card_name'     => 'required|string|max:120',
                'card_number'   => 'required|string',
                'card_exp'      => 'required|string',
                'card_cvc'      => 'required|string',
                'deposit_amount'=> 'required|numeric|min:0',
            ]);

            $reservation = Reservation::findOrFail($id);

            // Nota: aquí no procesamos tarjeta real; sólo guardamos el depósito requerido.
            $reservation->deposit_due = (float)$data['deposit_amount'];
            $reservation->status = 'pending_payment';
            $reservation->save();

            // Mantener en sesión el monto de depósito para mostrar en el siguiente paso
            session(['resv' => array_merge(session('resv', []), [
                'deposit_amount' => (float)$data['deposit_amount'],
            ])]);

            return redirect()->route('reservations.step', ['step'=>5]);
        }

        // Otros steps (4 pago / 5 confirmación) aún placeholders
        return redirect()->route('reservations.step', ['step'=>min($step+1,5)]);
    }

    /** ======= API: disponibilidad por fecha ======= */
    public function availability(Request $req)
    {
        $date = $req->query('date');
        $guests = (int) $req->query('guests', 1); // guests not strictly required for listing

        if (!$date) {
            return response()->json(['error'=>'Invalid parameters: date required'], 400);
        }

        $tz = config('app.timezone') ?: env('APP_TIMEZONE', 'America/Los_Angeles');
        $now = Carbon::now($tz)->seconds(0);
        $today = $now->toDateString();
        // Pre-calculate total booked guests per hour for the date
        try {
            $bookedGuests = Reservation::whereDate('date', $date)
                ->where(function($q){ $q->whereNull('status')->orWhere('status','!=','canceled'); })
                ->selectRaw('time, COALESCE(SUM(guests),0) as g')
                ->groupBy('time')
                ->pluck('g','time');
        } catch (\Throwable $e) { $bookedGuests = collect(); }

        $slots = Timeslot::where('date', $date)
            ->orderBy('time')
            ->get()
            ->filter(function($t) {
                // Keep only open slots with capacity; lead-time handled below
                return $t->is_open && $t->capacity > 0;
            })
            ->values()
            ->map(function($t) use ($tz, $date, $today, $now, $guests, $bookedGuests){
                $timeStr = substr($t->time,0,5);
                $eventAt = Carbon::parse($date.' '.$timeStr.':00', $tz);
                $available = true;
                // Capacity check: require capacity >= requested guests
                $reqGuests = max(1, (int) $guests);
                $booked = (int) ($bookedGuests[$timeStr.':00'] ?? 0);
                $remaining = max(0, (int)$t->capacity - $booked);
                if ($remaining < $reqGuests) {
                    $available = false;
                }
                if ($remaining <= 0) {
                    $available = false;
                    try { if ($t->is_open) { $t->is_open = false; $t->save(); } } catch (\Throwable $e2) {}
                }
                // Max bookings per slot: if set and reached, mark as unavailable
                // No per-slot booking count limit; availability is based on capacity remaining only
                if ($date === $today) {
                    $minLead = $now->copy()->addHour();
                    if ($eventAt->lt($minLead)) {
                        $available = false; // same-day but before min lead time
                    }
                }
                return [
                    'time'      => $timeStr,
                    'label'     => Carbon::createFromFormat('H:i:s', $t->time, $tz)->format('g:i A'),
                    'available' => $available,
                    'remaining' => $remaining,
                ];
            });

        return response()->json(['date'=>$date,'slots'=>$slots]);
    }

    /** ======= API: geocode por ZIP (solo ZIP->ZIP) ======= */
    public function geocode(Request $req)
    {
        // Espera ?zip=XXXXX  (desde 92562)
        $toZip = preg_replace('/\D/','', (string)$req->query('zip', ''));
        if (!$toZip) return response()->json(['ok'=>false,'msg'=>'ZIP required'], 400);

        $fromZip = '92562'; // tu base

        // Si hay API Key de Google, intentamos usar Distance Matrix (millas por carretera)
        $distance = null;
        $apiKey = config('services.google.maps_key');
        if ($apiKey) {
            try {
                $resp = \Illuminate\Support\Facades\Http::get(
                    'https://maps.googleapis.com/maps/api/distancematrix/json',
                    [
                        'origins'      => $fromZip,
                        'destinations' => $toZip,
                        'units'        => 'imperial',
                        'key'          => $apiKey,
                    ]
                );
                if ($resp->ok()) {
                    $json = $resp->json();
                    $elem = data_get($json, 'rows.0.elements.0');
                    if ($elem && ($elem['status'] ?? '') === 'OK') {
                        // distance.value viene en metros; pero pedimos units=imperial, de todas formas convertimos por seguridad
                        $meters = (float) data_get($elem, 'distance.value', 0);
                        $distance = round($meters / 1609.344, 1); // millas
                    }
                }
            } catch (\Throwable $e) {
                // ignoramos y caemos a la aproximación
            }
        }

        // Fallback aproximado si no hay key o falló la API
        if ($distance === null) {
            $distance = $this->fakeZipDistanceMiles($fromZip, $toZip);
        }

        $fee = round($distance * self::TRAVEL_RATE, 2);

        return response()->json([
            'ok'       => true,
            'miles'    => $distance,
            'travel'   => $fee,
        ]);
    }

    /** ======= Menú canonical (lee config/menu.php si existe) ======= */
    private function menu(): array
    {
        // Intentar cargar desde config/menu.php (siempre fresco, evitando cache)
        try {
            $path = base_path('config/menu.php');
            $cfg = is_file($path) ? include $path : (array) config('menu');
            if (!is_array($cfg)) { $cfg = (array) config('menu'); }
        } catch (\Throwable $e) {
            $cfg = (array) config('menu');
        }

        // Si hay configuración, aplanar a [code => [name, price, category, desc?]]
        if (!empty($cfg) && is_array($cfg)) {
            $flat = [];
            foreach ($cfg as $cat => $rows) {
                $catName = is_string($cat) ? trim($cat) : 'General';
                // Normalizar visualmente categoría (PACKAGES -> Packages, etc.)
                $catLabel = ucwords(strtolower($catName));
                foreach ((array) $rows as $row) {
                    $code  = (string) ($row['key'] ?? '');
                    if ($code === '') { continue; }

                    $nameRaw = (string) ($row['name'] ?? '');
                    $name    = MenuLabel::standardizeText($nameRaw !== '' ? $nameRaw : $code);
                    $price   = (float) ($row['price'] ?? 0);
                    $desc    = $row['desc'] ?? null;
                    $desc    = $desc !== null ? MenuLabel::standardizeText($desc) : null;

                    $flat[$code] = [
                        'name'     => $name,
                        'price'    => $price,
                        'category' => $catLabel,
                        'desc'     => $desc,
                    ];
                }
            }
            if (!empty($flat)) {
                return $flat;
            }
        }

        // Fallback: menú por defecto (si no hay config)
        $defaults = [
            // Packages (price per person)
            'PKG_CLASSIC' => [
                'name'     => 'Classic Package (pp)',
                'price'    => 85.00,
                'category' => 'Packages',
                'desc'     => 'Choose 2 proteins: NY, Chicken, Shrimp, or Tofu. Includes White Rice, hibachi vegetables, dipping sauces, and full setup.'
            ],
            'PKG_PREMIUM' => [
                'name'     => 'Premium Package (pp)',
                'price'    => 95.00,
                'category' => 'Packages',
                'desc'     => 'Choose 2 proteins: NY, Fillet Mignon, Chicken, Shrimp, Salmon, or Tofu. Includes Fried Rice or White Rice, House Salad, 1 complimentary appetizer, hibachi vegetables & sauces, full setup.'
            ],
            'PKG_DELUXE' => [
                'name'     => 'Deluxe Package (pp)',
                'price'    => 135.00,
                'category' => 'Packages',
                'desc'     => 'Choose 2–3 proteins: NY, Fillet Mignon, Rib Eye, Chicken, Shrimp, Scallops, Lobster, Halibu, or Tofu. Includes Fried Rice, House Salad + Miso Soup, 1 appetizer, hibachi vegetables & sauces, complimentary sake (per group), full setup.'
            ],
            'PKG_KIDS' => [
                'name'     => 'Kids Package (pp)',
                'price'    => 55.00,
                'category' => 'Packages',
                'desc'     => 'For kids 10 & under. Choose 1 protein: Chicken, Shrimp, NY, or Tofu. Includes White Rice (upgrade to Fried Rice +$5), hibachi vegetables & sauces, beverage.'
            ],
            'PKG_CUSTOM' => [
                'name'     => 'Custom Package (per quote)',
                'price'    => 0.00,
                'category' => 'Packages',
                'desc'     => 'Build your own menu. Final price based on selections.'
            ],

            // Some common add-ons (optional examples)
            'ADD_HOUSE_SALAD' => ['name'=>'House Salad',                 'price'=>7.00,   'category'=>'Starters & Add-ons'],
            'ADD_MISO_SOUP'   => ['name'=>'Miso Soup',                   'price'=>7.00,   'category'=>'Starters & Add-ons'],
            'ADD_GYOZA_2'     => ['name'=>'Gyoza (2 pc)',                'price'=>6.00,   'category'=>'Starters & Add-ons'],
            'ADD_NOODLES_4'   => ['name'=>'Noodles (4oz)',               'price'=>8.00,   'category'=>'Starters & Add-ons'],
            'ADD_EDAMAME'     => ['name'=>'Edamame',                     'price'=>6.00,   'category'=>'Starters & Add-ons'],
            'ADD_SPICY_EDA'   => ['name'=>'Spicy Edamame',               'price'=>7.00,   'category'=>'Starters & Add-ons'],
            'ADD_FRIED_RICE'  => ['name'=>'Extra Fried Rice',            'price'=>5.00,   'category'=>'Starters & Add-ons'],
            'ADD_FIRESHOW'    => ['name'=>'Fire Show Performance',       'price'=>380.00, 'category'=>'Starters & Add-ons'],
            'ADD_EXTRA_CHEF'  => ['name'=>'Extra Chef',                  'price'=>250.00, 'category'=>'Starters & Add-ons'],
            'ADD_CHAIRS'      => ['name'=>'Chiavari Chairs (each)',      'price'=>7.00,   'category'=>'Starters & Add-ons'],
            'ADD_HEATER'      => ['name'=>'Patio Heater & Propane',      'price'=>90.00,  'category'=>'Starters & Add-ons'],
            'ADD_GLASSWARE'   => ['name'=>'Glassware/Tableware Upgrade', 'price'=>3.00,   'category'=>'Starters & Add-ons'],
        ];

        foreach ($defaults as $code => &$entry) {
            $entry['name'] = MenuLabel::standardizeText($entry['name'] ?? $code);
            if (!empty($entry['desc'])) {
                $entry['desc'] = MenuLabel::standardizeText($entry['desc']);
            }
        }
        unset($entry);

        return $defaults;
    }


    /** ======= Distancia aproximada ZIP->ZIP (sin API externa) ======= */
    private function fakeZipDistanceMiles(string $fromZip, string $toZip): float
    {
        // Aproximación simple para desarrollo (no es preciso).
        // Puedes mejorarla con una tabla o una API real si luego quieres.
        if ($fromZip === $toZip) return 0.0;

        // Diferencia numérica "simulada"
        $d = abs((int)$fromZip - (int)$toZip);

        // escala arbitraria para tener rangos razonables 0-140mi
        $miles = min(140, max(5, $d / 100));
        return round($miles, 1);
    }
}
