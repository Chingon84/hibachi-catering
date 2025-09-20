<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\ReservationItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\MenuLabel;
use App\Models\OrderPortionRow;

class ReservationAdminController extends Controller
{
    private const GRATUITY = 0.18;  // 18%
    private const TAX      = 0.1025; // 10.25%
    public function index(Request $req)
    {
        $d = $req->query('d');
        $status = $req->query('status');
        $sort = (string) $req->query('sort', 'newest');
        $q = trim((string)$req->query('q', ''));

        $query = Reservation::query();
        // Sorting options
        switch ($sort) {
            case 'oldest':
                $query->orderBy('date')->orderBy('time')->orderBy('created_at');
                break;
            case 'event_asc':
                $query->orderBy('date')->orderBy('time');
                break;
            case 'event_desc':
                $query->orderByDesc('date')->orderByDesc('time');
                break;
            case 'invoice_desc':
                $query->orderByDesc('invoice_number')->orderByDesc('created_at');
                break;
            case 'invoice_asc':
                $query->orderBy('invoice_number')->orderByDesc('created_at');
                break;
            case 'newest':
            default:
                // Show most recently CREATED at the top regardless of event date/time
                $query->orderByDesc('created_at')->orderByDesc('date')->orderByDesc('time');
                break;
        }

        if ($d) {
            $query->whereDate('date', $d);
        }
        if ($status && in_array($status, ['draft','pending_payment','confirmed','canceled'], true)) {
            $query->where('status', $status);
        }
        if ($q !== '') {
            $query->where(function($w) use ($q){
                $w->where('code', 'like', "%$q%")
                  ->orWhere('customer_name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%")
                  ->orWhere('phone', 'like', "%$q%")
                  ->orWhere('address', 'like', "%$q%")
                  ->orWhere('city', 'like', "%$q%");
            });
        }

        $rows = $query->limit(100)->get();

        return view('admin.reservations', [
            'd' => $d,
            'status' => $status,
            'q' => $q,
            'sort' => $sort,
            'rows' => $rows,
        ]);
    }

    public function show(int $id)
    {
        $r = Reservation::with(['items','payments'])->findOrFail($id);
        $menu = $this->flatMenu();
        return view('admin.reservation_show', ['r' => $r, 'menuOptions' => $menu]);
    }

    public function event(int $id)
    {
        $r = Reservation::with(['items','payments'])->findOrFail($id);
        $menu = $this->flatMenu();
        return view('admin.reservation_event', ['r' => $r, 'menuOptions' => $menu]);
    }

    public function update(Request $req, int $id)
    {
        $r = Reservation::findOrFail($id);

        $data = $req->validate([
            'customer_name' => 'nullable|string|max:120',
            'company'       => 'nullable|string|max:120',
            'phone'         => 'nullable|string|max:40',
            'email'         => 'nullable|email|max:120',
            'address'       => 'nullable|string|max:200',
            'city'          => 'nullable|string|max:100',
            'zip_code'      => 'nullable|string|max:10',
            'event_type'    => 'nullable|string|max:60',
            'setup_color'   => 'nullable|string|max:60',
            'stairs'        => 'nullable|boolean',
            'heard_about'   => 'nullable|string|max:60',
            'notes'         => 'nullable|string|max:500',
            'date'          => 'required|date',
            'time'          => 'required',
            'guests'        => 'required|integer|min:1',
        ]);

        // Normalize time to H:i:s
        try {
            $t = \Carbon\Carbon::parse($data['time'])->format('H:i:s');
        } catch (\Throwable $e) {
            $t = $r->time; // fallback
        }

        $r->fill([
            'customer_name' => $data['customer_name'] ?? $r->customer_name,
            'company'       => $data['company'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'email'         => $data['email'] ?? null,
            'address'       => $data['address'] ?? null,
            'city'          => $data['city'] ?? null,
            'zip_code'      => $data['zip_code'] ?? null,
            'event_type'    => $data['event_type'] ?? null,
            'setup_color'   => $data['setup_color'] ?? null,
            'stairs'        => (bool)($data['stairs'] ?? false),
            'heard_about'   => $data['heard_about'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'date'          => \Carbon\Carbon::parse($data['date'])->toDateString(),
            'time'          => $t,
            'guests'        => (int)$data['guests'],
        ])->save();

        $back = $req->input('back');
        if ($back) {
            return redirect($back)->with('ok', 'Reservation updated');
        }
        return redirect()->route('admin.reservations.show', ['id'=>$r->id])->with('ok', 'Reservation updated');
    }

    public function updateItems(Request $req, int $id)
    {
        $r = Reservation::with('items')->findOrFail($id);
        $items = (array) $req->input('items', []); // id => qty
        $desc  = (array) $req->input('desc', []);  // id => description
        $adjLabels = (array) $req->input('adj_label', []);
        $adjAmounts= (array) $req->input('adj_amount', []);
        foreach ($items as $itemId => $qty) {
            $it = $r->items->firstWhere('id', (int)$itemId);
            if (!$it) continue;
            $qty = (int) $qty;
            if ($qty <= 0) {
                $it->delete();
            } else {
                $it->qty = $qty;
                $unit = (float) ($it->unit_price_snapshot ?? 0);
                $it->line_total = round($unit * $qty, 2);
                if (array_key_exists($itemId, $desc)) {
                    $it->description = trim((string)$desc[$itemId]) ?: null;
                }
                $it->save();
            }
        }
        // Normalize and save adjustments (max 2)
        $adjustments = [];
        $max = min(2, max(count($adjLabels), count($adjAmounts)));
        for ($i=0; $i<$max; $i++) {
            $label = trim((string)($adjLabels[$i] ?? ''));
            $raw   = trim((string)($adjAmounts[$i] ?? ''));
            if ($label === '' && $raw === '') continue;
            $amount = (float) str_replace([',', '$', ' '], '', $raw);
            $amount = round($amount, 2);
            $adjustments[] = ['label' => $label ?: 'Adjustment', 'amount' => $amount];
        }
        $r->invoice_adjustments = !empty($adjustments) ? $adjustments : null;
        $r->save();

        $this->recalcTotals($r->fresh('items'));
        return redirect()->route('admin.reservations.show', ['id'=>$r->id])->with('ok','Items updated');
    }

    public function addItem(Request $req, int $id)
    {
        $r = Reservation::findOrFail($id);
        $data = $req->validate([
            'menu_key'    => 'nullable|string',
            'custom_name' => 'nullable|string|max:120',
            'custom_price'=> 'nullable|numeric|min:0',
            'qty'         => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $name = null; $price = null; $menuId = null;
        $qty = (int) $data['qty'];

        if (!empty($data['menu_key'])) {
            $flat = $this->flatMenu();
            $opt = $flat[$data['menu_key']] ?? null;
            if ($opt) {
                $name = $opt['name'];
                $price = (float) $opt['price'];
                $menuId = null; // not using FK menu table now
            }
        }
        if ($name === null) {
            $name = $data['custom_name'] ?? 'Custom Item';
            $price = (float) ($data['custom_price'] ?? 0);
        }

        $name = MenuLabel::standardize($name);
        $description = $data['description'] ?? null;
        if ($description !== null && $description !== '') {
            $description = MenuLabel::standardizeText($description);
        }

        $r->items()->create([
            'menu_id'             => $menuId,
            'name_snapshot'       => $name,
            'description'         => $description,
            'unit_price_snapshot' => $price,
            'qty'                 => $qty,
            'line_total'          => round($price * $qty, 2),
        ]);

        $this->recalcTotals($r->fresh('items'));
        return redirect()->route('admin.reservations.show', ['id'=>$r->id])->with('ok','Item added');
    }

    public function deleteItem(int $id, int $itemId)
    {
        $r = Reservation::findOrFail($id);
        ReservationItem::where('reservation_id',$r->id)->where('id',$itemId)->delete();
        $this->recalcTotals($r->fresh('items'));
        return redirect()->route('admin.reservations.show', ['id'=>$r->id])->with('ok','Item removed');
    }

    public function destroy(Request $req, int $id)
    {
        $r = Reservation::findOrFail($id);
        try {
            $r->delete();
        } catch (\Throwable $e) {
            return redirect()->route('admin.reservations')->withErrors(['delete' => 'Could not delete reservation.']);
        }
        // After moving to trash (soft delete), redirect back (keep filters) or to reservations
        $back = $req->input('back', route('admin.reservations'));
        return redirect($back)->with('ok', 'Reservation moved to trash');
    }

    private function recalcTotals(Reservation $r): void
    {
        $subtotal = (float) $r->items()->sum('line_total');
        $travel   = (float) ($r->travel_fee ?? 0);
        $adjSum   = 0.0;
        try {
            $adj = (array) ($r->invoice_adjustments ?? []);
            foreach ($adj as $a) { $adjSum += (float) ($a['amount'] ?? 0); }
        } catch (\Throwable $e) { $adjSum = 0.0; }
        $gratuity = round($subtotal * self::GRATUITY, 2);
        $taxBase  = max(0, $subtotal + $adjSum);
        $tax      = round($taxBase * self::TAX, 2);
        $total    = round($subtotal + $travel + $gratuity + $tax + $adjSum, 2);
        $r->subtotal = round($subtotal, 2);
        $r->gratuity = $gratuity;
        $r->tax      = $tax;
        $r->total    = $total;
        // Paid = Stripe/recorded deposit_paid + manual succeeded payments
        $paid = (float) ($r->deposit_paid ?? 0);
        try {
            foreach ((array)($r->manual_payments ?? []) as $mp) {
                if (strtolower((string)($mp['status'] ?? '')) === 'succeeded') {
                    $paid += (float) ($mp['amount'] ?? 0);
                }
            }
        } catch (\Throwable $e) {}
        $r->balance  = max(0, round($total - $paid, 2));
        $r->save();
    }

    // Manual payments: save (create/update)
    public function manualPaymentSave(\Illuminate\Http\Request $req, int $id)
    {
        $r = Reservation::findOrFail($id);
        $data = $req->validate([
            'id'         => 'nullable|string|max:40',
            'date'       => 'required|date',
            'provider'   => 'required|string|in:Square,Zelle,Venmo,Paypal,Cashapp,Stripe,Cash,Check,Other',
            'ref'        => 'nullable|string|max:50',
            'status'     => 'required|string|in:Succeeded,Pending,Failed',
            'amount'     => 'required|numeric|min:0.01',
            'transaction'=> 'nullable|string|max:120',
        ]);

        $list = (array) ($r->manual_payments ?? []);
        $tx = trim((string)($data['transaction'] ?? ''));
        if ($tx !== '') {
            foreach ($list as $row) {
                if (($row['transaction_id'] ?? '') === $tx && ($row['id'] ?? '') !== ($data['id'] ?? '')) {
                    return response()->json(['ok'=>false,'error'=>'Duplicate transaction id'], 422);
                }
            }
            try { if (\App\Models\Payment::query()->where('transaction_id',$tx)->exists()) return response()->json(['ok'=>false,'error'=>'Duplicate transaction id'], 422); } catch (\Throwable $e) {}
        }

        $amount = round((float)$data['amount'], 2);
        if (strtolower($data['status']) === 'succeeded') {
            $paid = (float) ($r->deposit_paid ?? 0);
            foreach ($list as $row) if (strtolower((string)($row['status'] ?? ''))==='succeeded') $paid += (float)($row['amount'] ?? 0);
            if (!empty($data['id'])) {
                foreach ($list as $row) if (($row['id'] ?? '') === $data['id'] && strtolower((string)($row['status'] ?? ''))==='succeeded') { $paid -= (float)($row['amount'] ?? 0); break; }
            }
            if ($paid + $amount > (float) ($r->total ?? 0) + 0.001) return response()->json(['ok'=>false,'error'=>'Payment exceeds total'], 422);
        }

        $row = [
            'id' => $data['id'] ?: uniqid('mp_'),
            'date' => \Carbon\Carbon::parse($data['date'])->format('Y-m-d\TH:i'),
            'provider' => $data['provider'],
            'ref' => $data['ref'] ?? null,
            'status' => $data['status'],
            'amount' => $amount,
            'transaction_id' => $tx ?: null,
        ];
        $updated = false;
        foreach ($list as &$itm) { if (($itm['id'] ?? null) === $row['id']) { $itm = $row; $updated = true; break; } }
        if (!$updated) $list[] = $row;
        $r->manual_payments = array_values($list);
        $r->save();
        $this->recalcTotals($r);
        $manualPaid = 0.0; foreach ((array)$r->manual_payments as $mp){ if (strtolower((string)($mp['status'] ?? ''))==='succeeded') $manualPaid += (float)($mp['amount'] ?? 0); }
        return response()->json(['ok'=>true,'manual'=>$r->manual_payments,'manualPaid'=>$manualPaid,'balance'=>$r->balance]);
    }

    public function manualPaymentDelete(\Illuminate\Http\Request $req, int $id)
    {
        $r = Reservation::findOrFail($id);
        $pid = (string) $req->input('id');
        if ($pid === '') return response()->json(['ok'=>false,'error'=>'Missing id'], 422);
        $list = (array) ($r->manual_payments ?? []);
        $list = array_values(array_filter($list, fn($row)=> ($row['id'] ?? '') !== $pid));
        $r->manual_payments = $list;
        $r->save();
        $this->recalcTotals($r);
        $manualPaid = 0.0; foreach ((array)$r->manual_payments as $mp){ if (strtolower((string)($mp['status'] ?? ''))==='succeeded') $manualPaid += (float)($mp['amount'] ?? 0); }
        return response()->json(['ok'=>true,'manual'=>$r->manual_payments,'manualPaid'=>$manualPaid,'balance'=>$r->balance]);
    }

    private function flatMenu(): array
    {
        $cfg = (array) config('menu');
        $out = [];
        foreach ($cfg as $cat => $items) {
            foreach ((array)$items as $it) {
                if (!isset($it['key'])) continue;
                $out[$it['key']] = [
                    'name'  => MenuLabel::standardizeText($it['name'] ?? $it['key']),
                    'price' => (float)($it['price'] ?? 0),
                    'cat'   => $cat,
                ];
            }
        }
        // Include Packages used in the public wizard as well
        foreach ($this->packagesMenu() as $code => $it) {
            $out[$code] = [
                'name'  => MenuLabel::standardizeText($it['name']),
                'price' => (float)$it['price'],
                'cat'   => 'Packages',
            ];
        }
        return $out;
    }

    private function packagesMenu(): array
    {
        // Keep in sync with ReservationController::menu() Packages section
        return [
            'PKG_CLASSIC' => [
                'name'  => 'Classic Package (pp)',
                'price' => 85.00,
            ],
            'PKG_PREMIUM' => [
                'name'  => 'Premium Package (pp)',
                'price' => 95.00,
            ],
            'PKG_DELUXE' => [
                'name'  => 'Deluxe Package (pp)',
                'price' => 135.00,
            ],
            'PKG_KIDS' => [
                'name'  => 'Kids Package (pp)',
                'price' => 55.00,
            ],
            'PKG_CUSTOM' => [
                'name'  => 'Custom Package (per quote)',
                'price' => 0.00,
            ],
        ];
    }

    public function searchOrdersBreakdown(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);

        $results = Reservation::query()
            ->with(['items' => function ($query) {
                $query->select(['id', 'reservation_id', 'name_snapshot', 'description', 'qty', 'menu_id']);
            }])
            ->select(['id', 'customer_name', 'email', 'status', 'deposit_paid', 'deposit_due', 'total', 'date', 'address', 'city', 'guests', 'time'])
            ->where(function ($query) {
                $query->where('status', 'confirmed')
                      ->orWhereRaw('COALESCE(deposit_paid, 0) > 0');
            })
            ->where(function ($query) use ($escaped) {
                $query->where('customer_name', 'like', "%{$escaped}%")
                      ->orWhere('email', 'like', "%{$escaped}%")
                      ->orWhere('phone', 'like', "%{$escaped}%");
            })
            ->orderByDesc('date')
            ->limit(15)
            ->get()
            ->map(function (Reservation $res) {
                return [
                    'id' => $res->id,
                    'name' => $res->customer_name,
                    'email' => $res->email,
                    'status' => $res->status,
                    'deposit_paid' => (float) ($res->deposit_paid ?? 0),
                    'deposit_due' => (float) ($res->deposit_due ?? 0),
                    'total' => (float) ($res->total ?? 0),
                    'date' => optional($res->date)->format('m/d/Y'),
                    'address' => $res->address,
                    'city' => $res->city,
                    'guests' => $res->guests,
                    'time' => $res->time ? \Carbon\Carbon::parse($res->time)->format('g:i A') : null,
                    'items' => $res->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name_snapshot,
                            'qty' => (float) ($item->qty ?? 0),
                            'menu_code' => $item->menu_id ? (string) $item->menu_id : null,
                            'description' => $item->description,
                        ];
                    })->values(),
                ];
            });

        return response()->json($results);
    }

    public function ordersBreakdownDetails(Request $request)
    {
        $idsParam = $request->query('ids', []);
        $ids = [];

        if (is_array($idsParam)) {
            foreach ($idsParam as $value) {
                $id = (int) $value;
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        } else {
            $chunks = preg_split('/[,\s]+/', (string) $idsParam, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($chunks as $value) {
                $id = (int) $value;
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        $ids = array_values(array_unique($ids));

        if (empty($ids)) {
            return response()->json([]);
        }

        $rows = Reservation::query()
            ->with(['items' => function ($query) {
                $query->select(['id', 'reservation_id', 'name_snapshot', 'description', 'qty', 'menu_id']);
            }])
            ->select(['id', 'customer_name', 'email', 'status', 'deposit_paid', 'deposit_due', 'total', 'date', 'address', 'city', 'guests', 'time'])
            ->whereIn('id', $ids)
            ->get();

        $payload = $rows->map(function (Reservation $res) {
            return [
                'id' => $res->id,
                'name' => $res->customer_name,
                'email' => $res->email,
                'status' => $res->status,
                'deposit_paid' => (float) ($res->deposit_paid ?? 0),
                'deposit_due' => (float) ($res->deposit_due ?? 0),
                'total' => (float) ($res->total ?? 0),
                'date' => optional($res->date)->format('m/d/Y'),
                'address' => $res->address,
                'city' => $res->city,
                'guests' => $res->guests,
                'time' => $res->time ? \Carbon\Carbon::parse($res->time)->format('g:i A') : null,
                'items' => $res->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name_snapshot,
                        'qty' => (float) ($item->qty ?? 0),
                        'menu_code' => $item->menu_id ? (string) $item->menu_id : null,
                        'description' => $item->description,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json($payload);
    }

    public function getOrderPortions()
    {
        $rows = OrderPortionRow::all();
        if ($rows->isEmpty()) {
            $this->seedDefaultOrderPortions();
            $rows = OrderPortionRow::all();
        }

        $orderMap = collect(MenuLabel::primaryItems())
            ->mapWithKeys(fn ($label, $index) => [MenuLabel::standardize($label) => $index])
            ->all();

        $sorted = $rows->sortBy(function (OrderPortionRow $row) use ($orderMap) {
            $normalized = MenuLabel::standardize($row->label ?? '');
            return $orderMap[$normalized] ?? (1000 + ($row->position ?? 0));
        })->values();

        $sorted->each(function (OrderPortionRow $row, int $index) {
            if ($row->position !== $index) {
                $row->position = $index;
                $row->save();
            }
        });

        return response()->json($sorted->map(function (OrderPortionRow $row) {
            return [
                'key' => $row->row_key,
                'label' => $row->label,
                'qty' => (float) $row->qty,
                'unit' => $row->unit,
                'total' => (float) $row->total,
                'ozs' => (float) $row->ozs,
                'lbs' => (float) $row->lbs,
                'position' => $row->position,
            ];
        }));
    }

    public function saveOrderPortions(Request $request)
    {
        $validated = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.key' => ['required', 'string', 'max:120'],
            'rows.*.label' => ['nullable', 'string', 'max:255'],
            'rows.*.qty' => ['nullable', 'numeric', 'min:0'],
            'rows.*.unit' => ['required', 'string', 'max:10'],
            'rows.*.total' => ['nullable', 'numeric', 'min:0'],
            'rows.*.ozs' => ['nullable', 'numeric', 'min:0'],
            'rows.*.lbs' => ['nullable', 'numeric', 'min:0'],
            'rows.*.position' => ['nullable', 'integer', 'min:0'],
        ]);

        $rows = collect($validated['rows'])
            ->filter(fn ($row) => !empty($row['key']))
            ->values();

        $orderMap = collect(MenuLabel::primaryItems())
            ->mapWithKeys(fn ($label, $index) => [MenuLabel::standardize($label) => $index])
            ->all();

        $rows = $rows->sortBy(function ($row, $index) use ($orderMap) {
            $normalized = MenuLabel::standardize($row['label'] ?? '');
            return $orderMap[$normalized] ?? (1000 + ($row['position'] ?? $index));
        })->values();

        DB::transaction(function () use ($rows) {
            $keys = $rows->pluck('key')->all();
            if (!empty($keys)) {
                OrderPortionRow::whereNotIn('row_key', $keys)->delete();
            } else {
                OrderPortionRow::query()->delete();
            }

            $rows->each(function ($row, $index) {
                $qty = isset($row['qty']) ? (float) $row['qty'] : 0.0;
                $unit = $row['unit'] ?? 'oz';
                $total = isset($row['total']) ? (float) $row['total'] : 0.0;
                $ozs = isset($row['ozs']) ? (float) $row['ozs'] : ($qty * $total);
                $lbs = isset($row['lbs']) ? (float) $row['lbs'] : ($ozs > 0 ? $ozs / 16 : 0);

                OrderPortionRow::updateOrCreate(
                    ['row_key' => $row['key']],
                    [
                        'label' => $row['label'] ?? null,
                        'qty' => $qty,
                        'unit' => $unit,
                        'total' => $total,
                        'ozs' => $ozs,
                        'lbs' => $lbs,
                        'position' => $row['position'] ?? $index,
                    ]
                );
            });
        });

        return $this->getOrderPortions();
    }

    private function seedDefaultOrderPortions(): void
    {
        $items = MenuLabel::primaryItems();
        DB::transaction(function () use ($items) {
            foreach ($items as $index => $label) {
                $slug = Str::slug($label ?: ('item-' . $index));
                $key = 'default-' . $slug . '-' . $index;
                OrderPortionRow::updateOrCreate(
                    ['row_key' => $key],
                    [
                        'label' => $label,
                        'qty' => 0,
                        'unit' => 'oz',
                        'total' => 0,
                        'ozs' => 0,
                        'lbs' => 0,
                        'position' => $index,
                    ]
                );
            }
        });
    }

    public function invoice(int $id)
    {
        $r = Reservation::with(['items','payments'])->findOrFail($id);
        return view('admin.invoice', ['r' => $r]);
    }

    public function updateInvoiceStatus(\Illuminate\Http\Request $req, int $id)
    {
        $r = Reservation::findOrFail($id);
        $status = strtolower((string) $req->input('invoice_status', ''));
        $allowed = ['paid','pending','overdue','cancelled','refunded'];
        if (!in_array($status, $allowed, true)) {
            return redirect()->route('admin.reservations')->withErrors(['status'=>'Invalid invoice status']);
        }
        $r->invoice_status = $status;
        $r->save();
        $back = $req->input('back', route('admin.reservations'));
        return redirect($back)->with('ok', 'Invoice status updated');
    }

    // Update reservation color (hex like #RRGGBB), accepts null to clear
    public function updateColor(\Illuminate\Http\Request $req, int $id)
    {
        $r = Reservation::findOrFail($id);
        $hex = trim((string) $req->input('color', ''));
        if ($hex === '' || strtolower($hex) === 'clear') {
            $r->color = null;
        } else {
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $hex)) {
                return response()->json(['ok'=>false,'error'=>'Invalid color'], 422);
            }
            $r->color = $hex;
        }
        $r->save();
        return response()->json(['ok'=>true,'color'=>$r->color]);
    }

}
