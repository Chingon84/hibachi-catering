<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Services\NotificationService;
use App\Services\TaxRateResolver;
use App\Support\AdminMenuCatalog;
use App\Support\CaliforniaCateringTax;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class InvoiceAdminController extends Controller
{
    private const STATUS_FILTERS = ['all', 'draft', 'open', 'past_due', 'paid', 'void'];

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = strtolower(trim((string) $request->query('status', 'all')));
        if (!in_array($status, self::STATUS_FILTERS, true)) {
            $status = 'all';
        }

        $perPage = $this->adminPerPage($request);
        $rowsQuery = $this->invoiceRowsQuery($q);
        $counts = $this->invoiceCounts($rowsQuery);

        if ($status !== 'all') {
            $rowsQuery->where('status', $status);
        }

        $paginatedRows = $rowsQuery
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $paginatedRows->setCollection(
            $this->decorateInvoiceRows($paginatedRows->getCollection())
        );

        return view('admin.invoices', [
            'rows' => $paginatedRows,
            'q' => $q,
            'status' => $status,
            'counts' => $counts,
            'standaloneReady' => $this->standaloneReady(),
            'perPage' => $perPage,
        ]);
    }

    private function adminPerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);

        return in_array($perPage, [10, 15, 25], true) ? $perPage : 25;
    }

    public function create()
    {
        return view('admin.invoices_create', [
            'invoice' => null,
            'recentClients' => $this->recentClients(),
            'menuItems' => $this->menuItems(),
            'standaloneReady' => $this->standaloneReady(),
            'mode' => 'create',
            'defaultTaxRate' => app(TaxRateResolver::class)->defaultRate(),
            'customTaxRates' => app(TaxRateResolver::class)->frontendRates(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->standaloneReady(), 503, 'Invoice tables have not been migrated yet.');

        $data = $this->validatedInvoiceData($request);
        $invoice = DB::transaction(function () use ($data) {
            $invoice = Invoice::create($this->invoicePayload($data));
            $invoice->invoice_number = $this->invoiceNumber($invoice);
            $invoice->save();
            $this->syncItems($invoice, $data['items']);

            return $invoice->fresh('items');
        });

        return redirect()->route('admin.invoices.review', ['invoice' => $invoice]);
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');

        return view('admin.invoices_create', [
            'invoice' => $invoice,
            'recentClients' => $this->recentClients(),
            'menuItems' => $this->menuItems(),
            'standaloneReady' => $this->standaloneReady(),
            'mode' => 'edit',
            'defaultTaxRate' => app(TaxRateResolver::class)->defaultRate(),
            'customTaxRates' => app(TaxRateResolver::class)->frontendRates(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 403, 'Paid invoices cannot be edited.');
        $data = $this->validatedInvoiceData($request);

        DB::transaction(function () use ($invoice, $data) {
            $invoice->fill($this->invoicePayload($data));
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = $this->invoiceNumber($invoice);
            }
            $invoice->save();
            $this->syncItems($invoice, $data['items']);
        });

        return redirect()->route('admin.invoices.review', ['invoice' => $invoice]);
    }

    public function show(Invoice $invoice)
    {
        return view('admin.invoice_standalone', ['invoice' => $invoice->load('items')]);
    }

    public function download(Invoice $invoice)
    {
        $invoice->load('items');
        $fileName = 'invoice-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $invoice->invoice_number) . '.pdf';

        return Pdf::loadView('admin.invoice_standalone', [
            'invoice' => $invoice,
            'pdfMode' => true,
        ])
            ->setPaper('letter', 'portrait')
            ->download($fileName);
    }

    public function review(Invoice $invoice)
    {
        return view('admin.invoices_review', ['invoice' => $invoice->load('items')]);
    }

    public function finalize(Invoice $invoice)
    {
        abort_if($invoice->status === 'paid' || $invoice->status === 'void', 403);
        $invoice->status = 'open';
        $invoice->issue_date = $invoice->issue_date ?: now()->toDateString();
        $invoice->balance = max(0, round((float) $invoice->total - (float) $invoice->amount_paid, 2));
        $invoice->save();

        return redirect()->route('admin.invoices.show', ['invoice' => $invoice])->with('ok', 'Invoice finalized.');
    }

    public function void(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 403, 'Paid invoices cannot be voided.');
        $invoice->status = 'void';
        $invoice->balance = 0;
        $invoice->save();
        app(NotificationService::class)->notifyInvoiceCancelled($invoice, $request->user());

        return redirect()->route('admin.invoices')->with('ok', 'Invoice voided.');
    }

    public function destroy(Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 403, 'Paid invoices cannot be deleted.');
        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('admin.invoices')->with('ok', 'Invoice deleted.');
    }

    private function invoiceRowsQuery(string $q)
    {
        $queries = [];

        if ($this->standaloneReady()) {
            $queries[] = $this->standaloneInvoiceSource($q);
        }

        $queries[] = $this->reservationInvoiceSource($q);

        $combined = array_shift($queries);
        foreach ($queries as $query) {
            $combined->unionAll($query);
        }

        return DB::query()->fromSub($combined, 'invoice_rows')->select('invoice_rows.*');
    }

    private function invoiceCounts($query): array
    {
        $counts = [
            'all' => 0,
            'draft' => 0,
            'open' => 0,
            'past_due' => 0,
            'paid' => 0,
            'void' => 0,
        ];

        $grouped = (clone $query)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        foreach ($grouped as $status => $aggregate) {
            if (array_key_exists($status, $counts)) {
                $counts[$status] = (int) $aggregate;
                $counts['all'] += (int) $aggregate;
            }
        }

        return $counts;
    }

    private function decorateInvoiceRows(Collection $rows): Collection
    {
        $standaloneDescriptions = $this->standaloneDescriptions(
            $rows->where('kind', 'standalone')->pluck('id')->map(fn ($id) => (int) $id)->all()
        );

        return $rows->map(function ($row) use ($standaloneDescriptions) {
            $kind = (string) $row->kind;
            $status = (string) $row->status;
            $invoiceNumber = $row->invoice_number;

            if ($invoiceNumber === null || $invoiceNumber === '') {
                $invoiceNumber = $kind === 'standalone'
                    ? 'INV-' . $row->id
                    : ($row->reservation_code ?: $row->id);
            }

            $description = trim((string) ($row->description ?? ''));
            if ($kind === 'standalone' && $description === '') {
                $description = $standaloneDescriptions[(int) $row->id] ?? '';
            }
            if ($kind === 'reservation' && $description === '' && !empty($row->due)) {
                $description = 'Reservation ' . Carbon::parse($row->due)->format('m/d/Y');
            }

            return [
                'kind' => $kind,
                'id' => (int) $row->id,
                'total' => (float) ($row->total ?? 0),
                'status' => $status,
                'status_label' => $this->statusLabel($status),
                'invoice_number' => $invoiceNumber,
                'customer_email' => $row->customer_email,
                'customer_name' => $row->customer_name,
                'due' => $row->due,
                'created' => $row->created_at,
                'description' => $description,
                'view_url' => $kind === 'reservation'
                    ? route('admin.reservations.invoice', ['id' => $row->id, 'back' => request()->fullUrl()])
                    : route('admin.invoices.show', ['invoice' => $row->id]),
                'edit_url' => $kind === 'reservation'
                    ? route('admin.reservations.show', ['id' => $row->id])
                    : route('admin.invoices.edit', ['invoice' => $row->id]),
                'reservation_url' => $kind === 'reservation' || empty($row->reservation_id)
                    ? ($kind === 'reservation' ? route('admin.reservations.show', ['id' => $row->id]) : null)
                    : route('admin.reservations.show', ['id' => $row->reservation_id]),
                'void_url' => $kind === 'standalone' && !in_array($status, ['paid', 'void'], true)
                    ? route('admin.invoices.void', ['invoice' => $row->id])
                    : null,
                'delete_url' => $kind === 'standalone' && $status !== 'paid'
                    ? route('admin.invoices.destroy', ['invoice' => $row->id])
                    : null,
            ];
        })->values();
    }

    private function standaloneInvoiceSource(string $q)
    {
        $query = Invoice::query()->selectRaw("
            'standalone' as kind,
            invoices.id as id,
            invoices.reservation_id as reservation_id,
            NULL as reservation_code,
            COALESCE(invoices.total, 0) as total,
            CASE
                WHEN LOWER(COALESCE(invoices.status, '')) IN ('void', 'cancelled', 'canceled', 'refunded') THEN 'void'
                WHEN LOWER(COALESCE(invoices.status, '')) = 'draft' THEN 'draft'
                WHEN LOWER(COALESCE(invoices.status, '')) = 'paid' OR (COALESCE(invoices.balance, 0) <= 0.009 AND LOWER(COALESCE(invoices.status, '')) NOT IN ('draft', 'void')) THEN 'paid'
                WHEN invoices.due_date IS NOT NULL AND invoices.due_date < ? AND LOWER(COALESCE(invoices.status, 'open')) = 'open' THEN 'past_due'
                ELSE 'open'
            END as status,
            invoices.invoice_number as invoice_number,
            invoices.customer_email as customer_email,
            invoices.customer_name as customer_name,
            invoices.due_date as due,
            invoices.created_at as created_at,
            invoices.memo as description
        ", [now()->toDateString()]);

        if ($q !== '') {
            $query->where(function ($nested) use ($q) {
                $nested->where('invoice_number', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_email', 'like', "%{$q}%");
            });
        }

        return $query->toBase();
    }

    private function reservationInvoiceSource(string $q)
    {
        $query = Reservation::query()->selectRaw("
            'reservation' as kind,
            reservations.id as id,
            reservations.id as reservation_id,
            reservations.code as reservation_code,
            COALESCE(reservations.total, 0) as total,
            CASE
                WHEN COALESCE(reservations.balance, COALESCE(reservations.total, 0)) <= 0.009 THEN 'paid'
                WHEN LOWER(COALESCE(reservations.invoice_status, '')) IN ('paid') THEN 'paid'
                WHEN LOWER(COALESCE(reservations.invoice_status, '')) IN ('overdue', 'past_due') THEN 'past_due'
                WHEN LOWER(COALESCE(reservations.invoice_status, '')) IN ('cancelled', 'canceled', 'refunded', 'void') THEN 'void'
                ELSE 'open'
            END as status,
            reservations.invoice_number as invoice_number,
            reservations.email as customer_email,
            reservations.customer_name as customer_name,
            reservations.date as due,
            reservations.created_at as created_at,
            NULL as description
        ");

        if ($q !== '') {
            $query->where(function ($nested) use ($q) {
                $nested->where('invoice_number', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        return $query->toBase();
    }

    private function standaloneDescriptions(array $invoiceIds): array
    {
        if (empty($invoiceIds)) {
            return [];
        }

        return Invoice::query()
            ->with(['items' => fn ($query) => $query->select(['id', 'invoice_id', 'description'])->orderBy('id')])
            ->whereIn('id', $invoiceIds)
            ->get(['id', 'memo'])
            ->mapWithKeys(function (Invoice $invoice) {
                $description = trim((string) ($invoice->memo ?? ''));
                if ($description === '') {
                    $description = $invoice->items->pluck('description')->filter()->take(2)->implode(', ');
                }

                return [$invoice->id => $description];
            })
            ->all();
    }

    private function validatedInvoiceData(Request $request): array
    {
        if (is_array($request->input('item_menu_id'))) {
            $request->merge([
                'item_menu_id' => collect($request->input('item_menu_id'))
                    ->map(fn ($value) => filter_var($value, FILTER_VALIDATE_INT) !== false ? (int) $value : null)
                    ->all(),
            ]);
        }

        $data = $request->validate([
            'customer_name' => 'required|string|max:160',
            'customer_email' => 'required|email|max:190',
            'customer_phone' => 'nullable|string|max:40',
            'customer_address' => 'nullable|string|max:255',
            'customer_city' => 'nullable|string|max:120',
            'event_date' => 'nullable|date',
            'event_time' => 'nullable|string|max:20',
            'event_guests' => 'nullable|integer|min:0|max:10000',
            'event_type' => 'nullable|string|max:80',
            'setup_color' => 'nullable|string|max:80',
            'payment_collection' => 'nullable|string|in:request_payment',
            'due_option' => 'nullable|string|max:20',
            'custom_due_date' => 'nullable|date',
            'tax_enabled' => 'nullable|boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'travel_fee' => 'nullable|numeric|min:0|max:10000',
            'gratuity_enabled' => 'nullable|boolean',
            'gratuity_amount' => 'nullable|numeric|min:0|max:10000',
            'deposit_enabled' => 'nullable|boolean',
            'deposit_amount' => 'nullable|numeric|min:0|max:10000',
            'service_charge_enabled' => 'nullable|boolean',
            'service_charge_rate' => 'nullable|numeric|min:0|max:100',
            'discount_enabled' => 'nullable|boolean',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'memo' => 'nullable|string|max:1000',
            'footer_note' => 'nullable|string|max:1000',
            'internal_note' => 'nullable|string|max:1000',
            'item_menu_id' => 'array',
            'item_menu_id.*' => 'nullable|integer',
            'item_description' => 'required|array|min:1',
            'item_description.*' => 'nullable|string|max:255',
            'item_qty' => 'required|array|min:1',
            'item_qty.*' => 'nullable|numeric|min:0',
            'item_unit_price' => 'required|array|min:1',
            'item_unit_price.*' => 'nullable|numeric|min:0',
        ]);

        $items = [];
        foreach (($data['item_description'] ?? []) as $i => $description) {
            $description = trim((string) $description);
            $qty = (float) ($data['item_qty'][$i] ?? 0);
            $unit = (float) ($data['item_unit_price'][$i] ?? 0);
            if ($description === '' && $qty <= 0 && $unit <= 0) {
                continue;
            }
            if ($description === '' || $qty <= 0 || $unit < 0) {
                continue;
            }
            $items[] = [
                'menu_item_id' => !empty($data['item_menu_id'][$i]) ? (int) $data['item_menu_id'][$i] : null,
                'description' => $description,
                'quantity' => $qty,
                'unit_price' => $unit,
                'amount' => round($qty * $unit, 2),
            ];
        }

        if (empty($items)) {
            throw ValidationException::withMessages(['items' => 'At least one valid invoice item is required.']);
        }
        if (round(collect($items)->sum('amount'), 2) <= 0) {
            throw ValidationException::withMessages(['items' => 'Invoice total must be greater than $0.00.']);
        }
        $data['items'] = $items;
        $data['due_date'] = $this->dueDate((string) ($data['due_option'] ?? '30'), $data['custom_due_date'] ?? null);
        $data['payment_collection'] = 'request_payment';

        return $data;
    }

    private function invoicePayload(array $data): array
    {
        $subtotal = round(collect($data['items'])->sum('amount'), 2);
        $taxEnabled = $this->enabled($data, 'tax_enabled');
        $taxRate = $taxEnabled ? app(TaxRateResolver::class)->rateForInvoiceData($data) : 0;
        $travelFee = $this->money($data['travel_fee'] ?? 0, 10000);
        $gratuityEnabled = $this->enabled($data, 'gratuity_enabled');
        $gratuity = $gratuityEnabled ? $this->money($data['gratuity_amount'] ?? 0, 10000) : 0;
        $depositEnabled = $this->enabled($data, 'deposit_enabled');
        $depositRequested = $depositEnabled ? $this->money($data['deposit_amount'] ?? 0, 10000) : 0;
        $serviceChargeEnabled = $this->enabled($data, 'service_charge_enabled');
        $serviceChargeRate = $serviceChargeEnabled ? $this->rate($data['service_charge_rate'] ?? 0) : 0;
        $discountEnabled = $this->enabled($data, 'discount_enabled');
        $discountRate = $discountEnabled ? $this->rate($data['discount_rate'] ?? 0) : 0;

        $discount = $discountEnabled ? round($subtotal * ($discountRate / 100), 2) : 0;
        $serviceCharge = $serviceChargeEnabled ? round($subtotal * ($serviceChargeRate / 100), 2) : 0;
        // California catering tax: taxable base includes food/items subtotal, travel fee,
        // and mandatory gratuity/service charge. Voluntary tips are excluded.
        $taxableBase = CaliforniaCateringTax::taxableBase(
            $subtotal,
            $travelFee,
            $gratuity,
            $serviceCharge,
            0,
            $discount
        );
        $tax = $taxEnabled ? CaliforniaCateringTax::tax($taxableBase, $taxRate) : 0;
        $total = max(0, round($taxableBase + $tax, 2));
        $amountPaid = $depositEnabled ? min($depositRequested, $total) : 0;
        $balance = max(0, round($total - $amountPaid, 2));

        if ($total <= 0) {
            throw ValidationException::withMessages(['items' => 'Invoice total must be greater than $0.00 after adjustments.']);
        }

        $payload = [
            'customer_name' => trim((string) $data['customer_name']),
            'customer_email' => trim((string) $data['customer_email']),
            'customer_phone' => trim((string) ($data['customer_phone'] ?? '')) ?: null,
            'customer_address' => trim((string) ($data['customer_address'] ?? '')) ?: null,
            'customer_city' => app(TaxRateResolver::class)->invoiceCityForStorage($data),
            'event_date' => !empty($data['event_date']) ? Carbon::parse($data['event_date'])->toDateString() : null,
            'event_time' => trim((string) ($data['event_time'] ?? '')) ?: null,
            'event_guests' => isset($data['event_guests']) && $data['event_guests'] !== '' ? (int) $data['event_guests'] : null,
            'event_type' => trim((string) ($data['event_type'] ?? '')) ?: null,
            'setup_color' => trim((string) ($data['setup_color'] ?? '')) ?: null,
            'status' => 'draft',
            'issue_date' => now()->toDateString(),
            'due_date' => $data['due_date'],
            'payment_collection' => $data['payment_collection'],
            'subtotal' => $subtotal,
            'travel_fee' => $travelFee,
            'tax' => $tax,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'balance' => $balance,
            'memo' => trim((string) ($data['memo'] ?? '')) ?: null,
            'footer_note' => trim((string) ($data['footer_note'] ?? '')) ?: null,
            'internal_note' => trim((string) ($data['internal_note'] ?? '')) ?: null,
            'created_by' => auth()->id(),
        ];

        $adjustments = [
            'tax_enabled' => $taxEnabled,
            'tax_rate' => $taxRate,
            'gratuity_enabled' => $gratuityEnabled,
            'gratuity' => $gratuity,
            'deposit_enabled' => $depositEnabled,
            'deposit_amount' => $depositRequested,
            'service_charge_enabled' => $serviceChargeEnabled,
            'service_charge_rate' => $serviceChargeRate,
            'service_charge' => $serviceCharge,
            'discount_enabled' => $discountEnabled,
            'discount_rate' => $discountRate,
            'discount' => $discount,
        ];

        foreach ($adjustments as $column => $value) {
            if ($this->invoiceColumnExists($column)) {
                $payload[$column] = $value;
            }
        }

        return $payload;
    }

    private function enabled(array $data, string $key): bool
    {
        return filter_var($data[$key] ?? false, FILTER_VALIDATE_BOOL);
    }

    private function rate(mixed $value): float
    {
        return round(min(100, max(0, (float) $value)), 2);
    }

    private function money(mixed $value, float $max): float
    {
        return round(min($max, max(0, (float) $value)), 2);
    }

    private function invoiceColumnExists(string $column): bool
    {
        static $columns = null;

        if ($columns === null) {
            $columns = Schema::hasTable('invoices')
                ? collect(Schema::getColumnListing('invoices'))->flip()
                : collect();
        }

        return $columns->has($column);
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();
        foreach ($items as $item) {
            $invoice->items()->create($item);
        }
    }

    private function dueDate(string $option, ?string $custom): ?string
    {
        if ($option === 'custom' && $custom) {
            return Carbon::parse($custom)->toDateString();
        }

        $days = match ($option) {
            'today' => 0,
            'tomorrow' => 1,
            '7', '14', '30', '45', '60', '90' => (int) $option,
            default => 30,
        };

        return now()->addDays($days)->toDateString();
    }

    private function invoiceNumber(Invoice $invoice): string
    {
        return 'INV-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT);
    }

    private function standaloneReady(): bool
    {
        return Schema::hasTable('invoices') && Schema::hasTable('invoice_items');
    }

    private function recentClients(): Collection
    {
        if (!Schema::hasTable('clients')) {
            return collect();
        }

        return Client::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->orderBy('company')
            ->limit(80)
            ->get([
                'id',
                'first_name',
                'last_name',
                'company',
                'email_primary',
                'phone_primary',
                'address1_street',
                'address1_city',
                'address1_state',
                'address1_zip',
                'address2_street',
                'address2_city',
                'address2_state',
                'address2_zip',
                'last_event_date',
                'last_guests',
            ])
            ->map(function (Client $client) {
                $details = $this->clientInvoiceDetails($client);
                foreach ($details as $key => $value) {
                    $client->setAttribute('invoice_'.$key, $value);
                }

                return $client;
            });
    }

    private function clientInvoiceDetails(Client $client): array
    {
        $address = $this->clientAddress($client);
        $event = null;

        if (Schema::hasTable('reservations')) {
            $base = $client->reservationsQuery()
                ->select(['date', 'time', 'guests', 'address', 'city', 'event_type', 'setup_color']);

            $event = (clone $base)
                ->whereDate('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->orderBy('time')
                ->first();

            if (!$event) {
                $event = $base->orderByDesc('date')->orderByDesc('time')->first();
            }
        }

        if ($event && trim((string) ($event->address ?? '')) !== '') {
            $address = trim((string) $event->address.($event->city ? ', '.$event->city : ''));
        }

        return [
            'address' => $address,
            'city' => $event?->city ?? $this->clientCity($client),
            'event_date' => $event?->date ? Carbon::parse($event->date)->toDateString() : optional($client->last_event_date)->format('Y-m-d'),
            'event_time' => $event?->time ? Carbon::parse($event->time)->format('H:i') : '',
            'event_guests' => $event?->guests ?? $client->last_guests,
            'event_type' => $event?->event_type ?? '',
            'setup_color' => $event?->setup_color ?? '',
        ];
    }

    private function clientAddress(Client $client): string
    {
        $primary = array_filter([
            $client->address1_street,
            $client->address1_city,
            $client->address1_state,
            $client->address1_zip,
        ], fn ($value) => filled($value));

        if (!empty($primary)) {
            return implode(', ', $primary);
        }

        $secondary = array_filter([
            $client->address2_street,
            $client->address2_city,
            $client->address2_state,
            $client->address2_zip,
        ], fn ($value) => filled($value));

        return implode(', ', $secondary);
    }

    private function clientCity(Client $client): ?string
    {
        return trim((string) ($client->address1_city ?: $client->address2_city)) ?: null;
    }

    private function menuItems(): Collection
    {
        return app(AdminMenuCatalog::class)->itemsCollection();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'past_due' => 'Past due',
            default => ucfirst($status),
        };
    }
}
