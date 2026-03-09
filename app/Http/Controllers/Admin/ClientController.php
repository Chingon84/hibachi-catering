<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class ClientController extends Controller
{
    private const STATUS = ['regular','vip','celebrity','blacklist','preferred'];
    public function index(Request $req)
    {
        $q = trim((string)$req->query('q', ''));
        $status = $req->query('status');
        $city = trim((string) $req->query('city', ''));
        $date = trim((string) $req->query('date', ''));
        $eventsInput = $req->query('events');
        $events = null;
        if ($eventsInput !== null && $eventsInput !== '') {
            $parsed = (int) $eventsInput;
            if ($parsed >= 1 && $parsed <= 50) {
                $events = $parsed;
            }
        }

        $query = Client::query()
            ->orderByDesc('last_event_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($q !== '') {
            $query->where(function($w) use ($q){
                $w->where('first_name','like',"%$q%")
                  ->orWhere('last_name','like',"%$q%")
                  ->orWhere('company','like',"%$q%")
                  ->orWhere('email_primary','like',"%$q%")
                  ->orWhere('phone_primary','like',"%$q%");
            });
        }
        if ($status && in_array(strtolower($status), self::STATUS, true)) {
            $query->where('status', $status);
        }
        if ($city !== '') {
            $query->where(function($w) use ($city){
                $w->where('address1_city', $city)
                  ->orWhere('address2_city', $city);
            });
        }
        if ($date !== '') {
            // filter by last_event_date exact date
            $query->whereDate('last_event_date', $date);
        }
        if (!is_null($events)) {
            $query->where('events_count', '=', $events);
        }

        $list = $query->paginate(50)->withQueryString();

        // Build unique city options from both address lines
        $c1 = Client::whereNotNull('address1_city')->where('address1_city','<>','')
            ->distinct()->pluck('address1_city')->all();
        $c2 = Client::whereNotNull('address2_city')->where('address2_city','<>','')
            ->distinct()->pluck('address2_city')->all();
        $cityOptions = array_values(array_unique(array_map('trim', array_merge($c1,$c2))));
        sort($cityOptions, SORT_NATURAL|SORT_FLAG_CASE);

        return view('admin.clients', [
            'list' => $list,
            'q' => $q,
            'status' => $status,
            'statusOptions' => self::STATUS,
            'city' => $city,
            'cityOptions' => $cityOptions,
            'date' => $date,
            'events' => $events,
        ]);
    }

    public function create()
    {
        return view('admin.client_form', ['client' => new Client(['status'=>'regular']), 'mode' => 'create', 'statusOptions' => self::STATUS]);
    }

    public function show(Request $req, int $id)
    {
        $query = Client::query();
        if (Schema::hasTable('client_photos')) {
            $query->with('photos');
        }
        $client = $query->findOrFail($id);

        $tab = strtolower((string) $req->query('tab', 'overview'));
        if (!in_array($tab, ['overview', 'activities'], true)) {
            $tab = 'overview';
        }
        $activityTab = strtoupper((string) $req->query('activity_tab', 'ACTIVITY'));
        if (!in_array($activityTab, ['ACTIVITY', 'NOTES', 'TASKS', 'EVENTS'], true)) {
            $activityTab = 'ACTIVITY';
        }

        $search = trim((string) $req->query('search', ''));
        $filterType = strtoupper((string) $req->query('filter_type', 'ALL'));
        if (!in_array($filterType, ['ALL', 'NOTE', 'TASK', 'EVENT'], true)) {
            $filterType = 'ALL';
        }

        $filterUser = (int) $req->query('filter_user', 0);
        $from = trim((string) $req->query('from', ''));
        $to = trim((string) $req->query('to', ''));

        $activities = $this->buildActivitiesPaginator(
            client: $client,
            req: $req,
            activityTab: $activityTab,
            search: $search,
            filterType: $filterType,
            filterUser: $filterUser,
            from: $from,
            to: $to
        );

        $lastEventAt = $client->last_event_at();
        $nextEventAt = $client->next_event_at();

        return view('admin.client_show', [
            'client' => $client,
            'tab' => $tab,
            'activityTab' => $activityTab,
            'activities' => $activities,
            'activityFilters' => [
                'search' => $search,
                'filter_type' => $filterType,
                'filter_user' => $filterUser,
                'from' => $from,
                'to' => $to,
            ],
            'activityCounts' => [
                'notes' => (int) $client->activities()->where('type', 'NOTE')->count(),
                'tasks' => (int) $client->activities()->where('type', 'TASK')->count(),
                'events' => $client->total_events(),
            ],
            'activityUsers' => User::query()->orderBy('name')->get(['id', 'name']),
            'overview' => [
                'total_events' => $client->total_events(),
                'total_events_booked' => $client->total_events_booked(),
                'cancelled_events' => $client->cancelled_events_count(),
                'total_spent' => $client->total_spent(),
                'outstanding_balance' => $client->outstanding_balance(),
                'last_event_at' => $lastEventAt,
                'next_event_at' => $nextEventAt,
                'client_since' => $client->created_at,
                'days_since_client' => $client->created_at ? $client->created_at->startOfDay()->diffInDays(now()->startOfDay()) : null,
                'days_since_last_event' => $client->days_since_last_event(),
            ],
        ]);
    }

    public function storeNote(Request $req, int $id)
    {
        $client = Client::findOrFail($id);
        $data = $req->validate([
            'body' => ['required', 'string', 'max:10000'],
            'create_followup' => ['nullable', 'boolean'],
        ]);

        ClientActivity::create([
            'client_id' => $client->id,
            'type' => 'NOTE',
            'title' => 'Note',
            'body' => trim((string) $data['body']),
            'meta' => [
                'create_followup' => (bool) ($data['create_followup'] ?? false),
            ],
            'created_by' => Auth::id(),
            'occurred_at' => now(),
        ]);

        return redirect()->route('admin.clients.show', [
            'id' => $client->id,
            'tab' => 'activities',
            'activity_tab' => 'NOTES',
        ])->with('ok', 'Note created.');
    }

    public function storeTask(Request $req, int $id)
    {
        $client = Client::findOrFail($id);
        $data = $req->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:10000'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'due_at' => ['nullable', 'date'],
        ]);

        $dueAt = !empty($data['due_at']) ? Carbon::parse($data['due_at']) : null;

        ClientActivity::create([
            'client_id' => $client->id,
            'type' => 'TASK',
            'title' => trim((string) $data['title']),
            'body' => trim((string) ($data['description'] ?? '')),
            'created_by' => Auth::id(),
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_at' => $dueAt,
            'occurred_at' => $dueAt ?? now(),
        ]);

        return redirect()->route('admin.clients.show', [
            'id' => $client->id,
            'tab' => 'activities',
            'activity_tab' => 'TASKS',
        ])->with('ok', 'Task created.');
    }

    public function store(Request $req)
    {
        $data = $this->validateData($req);
        $data['social_links'] = $this->collectSocial($req);
        $c = Client::create($data);
        return redirect()->route('admin.clients.edit', ['id' => $c->id])->with('ok','Client created');
    }

    public function edit(int $id)
    {
        $client = Client::findOrFail($id);
        return view('admin.client_form', ['client' => $client, 'mode' => 'edit', 'statusOptions' => self::STATUS]);
    }

    public function update(Request $req, int $id)
    {
        $client = Client::findOrFail($id);
        $data = $this->validateData($req);
        $data['social_links'] = $this->collectSocial($req);
        $client->fill($data)->save();
        return redirect()->route('admin.clients.edit', ['id'=>$client->id])->with('ok','Client updated');
    }

    public function destroy(int $id)
    {
        Client::where('id',$id)->delete();
        return redirect()->route('admin.clients')->with('ok','Client removed');
    }

    public function updateStatus(Request $req, int $id)
    {
        $client = Client::findOrFail($id);
        $status = strtolower((string) $req->input('status', 'regular'));
        if (!in_array($status, self::STATUS, true)) {
            return back()->withErrors(['status'=>'Invalid status']);
        }
        $client->status = $status;
        $client->save();
        return back()->with('ok','Status updated');
    }

    private function buildActivitiesPaginator(
        Client $client,
        Request $req,
        string $activityTab,
        string $search,
        string $filterType,
        int $filterUser,
        string $from,
        string $to
    ): LengthAwarePaginator {
        $allowedTypes = $this->resolveAllowedActivityTypes($activityTab, $filterType);

        $stored = $this->buildStoredActivities($client, $allowedTypes, $search, $filterUser, $from, $to);
        $events = $this->buildEventActivities($client, $allowedTypes, $search, $filterUser, $from, $to);

        $feed = $stored->concat($events)->sortByDesc(function ($row) {
            return optional($row->occurred_at)->timestamp ?? 0;
        })->values();

        $page = max(1, (int) $req->query('page', 1));
        $perPage = 20;
        $items = $feed->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            items: $items,
            total: $feed->count(),
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => $req->url(),
                'query' => $req->query(),
            ]
        );
    }

    private function resolveAllowedActivityTypes(string $activityTab, string $filterType): array
    {
        $base = match ($activityTab) {
            'NOTES' => ['NOTE'],
            'TASKS' => ['TASK'],
            'EVENTS' => ['EVENT'],
            default => ['NOTE', 'TASK', 'EVENT'],
        };

        if ($filterType !== 'ALL') {
            return in_array($filterType, $base, true) ? [$filterType] : [];
        }

        return $base;
    }

    private function buildStoredActivities(
        Client $client,
        array $allowedTypes,
        string $search,
        int $filterUser,
        string $from,
        string $to
    ): Collection {
        $dbTypes = array_values(array_intersect($allowedTypes, ['NOTE', 'TASK']));
        if (empty($dbTypes)) {
            return collect();
        }

        $query = ClientActivity::query()
            ->with(['creator:id,name', 'assignee:id,name'])
            ->where('client_id', $client->id)
            ->whereIn('type', $dbTypes);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($filterUser > 0) {
            $query->where(function ($q) use ($filterUser) {
                $q->where('created_by', $filterUser)
                    ->orWhere('assigned_to', $filterUser);
            });
        }

        if ($from !== '') {
            $query->whereDate('occurred_at', '>=', $from);
        }
        if ($to !== '') {
            $query->whereDate('occurred_at', '<=', $to);
        }

        return $query->get()->map(function (ClientActivity $a) {
            return (object) [
                'feed_key' => "client_activity_{$a->id}",
                'source' => 'client_activity',
                'type' => $a->type,
                'title' => $a->title ?: ($a->type === 'TASK' ? 'Task' : 'Note'),
                'body' => $a->body,
                'status' => null,
                'status_key' => null,
                'occurred_at' => $a->occurred_at,
                'created_by' => $a->created_by,
                'created_by_name' => optional($a->creator)->name,
                'assigned_to' => $a->assigned_to,
                'assigned_to_name' => optional($a->assignee)->name,
                'due_at' => $a->due_at,
                'event_id' => null,
                'invoice_number' => null,
                'event_code' => null,
                'guests' => null,
                'total' => null,
                'paid' => null,
                'balance' => null,
            ];
        });
    }

    private function buildEventActivities(
        Client $client,
        array $allowedTypes,
        string $search,
        int $filterUser,
        string $from,
        string $to
    ): Collection {
        if (!in_array('EVENT', $allowedTypes, true)) {
            return collect();
        }

        $query = $client->reservationsQuery()
            ->select([
                'id', 'invoice_number', 'code', 'date', 'time', 'status', 'invoice_status',
                'guests', 'total', 'deposit_paid', 'balance', 'notes', 'booked_by',
            ]);

        if ($from !== '') {
            $query->whereDate('date', '>=', $from);
        }
        if ($to !== '') {
            $query->whereDate('date', '<=', $to);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $rows = $query->get();

        return $rows->map(function (Reservation $r) use ($filterUser) {
            $occurredAt = $r->date ? Carbon::parse($r->date->toDateString().' '.($r->time ?: '00:00:00')) : now();
            $paid = (float) ($r->deposit_paid ?? 0);
            foreach ((array)($r->manual_payments ?? []) as $row) {
                if (strtolower((string)($row['status'] ?? '')) === 'succeeded') {
                    $paid += (float)($row['amount'] ?? 0);
                }
            }
            $balance = max(0, (float)($r->total ?? 0) - $paid);

            $statusKey = strtolower((string)($r->status ?? 'draft'));
            if (strtolower((string)($r->invoice_status ?? '')) === 'paid') {
                $statusKey = 'paid';
            }

            $status = match ($statusKey) {
                'pending_payment' => 'Pending payment',
                'canceled' => 'Cancelled',
                'paid' => 'Paid',
                default => ucfirst($statusKey),
            };

            $bookedBy = trim((string)($r->booked_by ?? ''));

            return (object) [
                'feed_key' => "reservation_{$r->id}",
                'source' => 'reservation',
                'type' => 'EVENT',
                'title' => $r->invoice_number ? "Event #INV-{$r->invoice_number}" : "Reservation #{$r->id}",
                'body' => $r->notes,
                'status' => $status,
                'status_key' => $statusKey,
                'occurred_at' => $occurredAt,
                'created_by' => ctype_digit($bookedBy) ? (int) $bookedBy : null,
                'created_by_name' => $bookedBy !== '' ? $bookedBy : null,
                'assigned_to' => null,
                'assigned_to_name' => null,
                'due_at' => null,
                'event_id' => $r->id,
                'invoice_number' => $r->invoice_number,
                'event_code' => $r->code,
                'guests' => (int)($r->guests ?? 0),
                'total' => (float)($r->total ?? 0),
                'paid' => $paid,
                'balance' => $balance,
            ];
        })->filter(function ($row) use ($filterUser) {
            if ($filterUser <= 0) {
                return true;
            }
            return (int)($row->created_by ?? 0) === $filterUser;
        })->values();
    }

    private function validateData(Request $req): array
    {
        return $req->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'company'    => 'nullable|string|max:150',

            'phone_primary' => 'nullable|string|max:30',
            'phone_alt'     => 'nullable|string|max:30',

            'email_primary' => 'nullable|email|max:150',
            'email_alt'     => 'nullable|email|max:150',

            'address1_street' => 'nullable|string|max:200',
            'address1_city'   => 'nullable|string|max:120',
            'address1_state'  => 'nullable|string|max:120',
            'address1_zip'    => 'nullable|string|max:16',

            'address2_street' => 'nullable|string|max:200',
            'address2_city'   => 'nullable|string|max:120',
            'address2_state'  => 'nullable|string|max:120',
            'address2_zip'    => 'nullable|string|max:16',

            'referral_source' => 'nullable|string|max:120',
            'internal_notes'  => 'nullable|string',
            'status'          => 'required|in:regular,vip,celebrity,blacklist,preferred',
            'website'         => 'nullable|string|max:255',
            'last_event_date' => 'nullable|date',
            'last_guests'     => 'nullable|integer|min:0',
        ]);
    }

    private function collectSocial(Request $req): array
    {
        $social = [
            'social_media' => trim((string)$req->input('social.social_media', '')),
        ];
        // Remove empty keys
        return array_filter($social, fn($v) => $v !== null && $v !== '');
    }

    // Export clients to CSV (respects current filters: q, status, city)
    public function exportCsv(Request $req)
    {
        $q = trim((string)$req->query('q', ''));
        $status = $req->query('status');
        $city = trim((string) $req->query('city', ''));

        $query = Client::query()->orderBy('last_name')->orderBy('first_name');
        if ($q !== '') {
            $query->where(function($w) use ($q){
                $w->where('first_name','like',"%$q%")
                  ->orWhere('last_name','like',"%$q%")
                  ->orWhere('company','like',"%$q%")
                  ->orWhere('email_primary','like',"%$q%")
                  ->orWhere('phone_primary','like',"%$q%");
            });
        }
        if ($status && in_array(strtolower($status), self::STATUS, true)) {
            $query->where('status', $status);
        }
        if ($city !== '') {
            $query->where(function($w) use ($city){
                $w->where('address1_city', $city)->orWhere('address2_city', $city);
            });
        }

        $rows = $query->get();
        $columns = [
            'first_name','last_name','company','phone_primary','phone_alt','email_primary','email_alt',
            'address1_street','address1_city','address1_state','address1_zip',
            'address2_street','address2_city','address2_state','address2_zip',
            'social_media','referral_source','internal_notes','status','website','last_event_date','last_guests'
        ];
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="clients_export_'.date('Ymd_His').'.csv"'
        ];

        return response()->stream(function() use ($rows,$columns){
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $columns);
            foreach ($rows as $c) {
                $social = is_array($c->social_links ?? null) ? ($c->social_links['social_media'] ?? '') : '';
                fputcsv($out, [
                    $c->first_name,$c->last_name,$c->company,$c->phone_primary,$c->phone_alt,$c->email_primary,$c->email_alt,
                    $c->address1_street,$c->address1_city,$c->address1_state,$c->address1_zip,
                    $c->address2_street,$c->address2_city,$c->address2_state,$c->address2_zip,
                    $social,$c->referral_source,$c->internal_notes,$c->status,$c->website,
                    optional($c->last_event_date)->format('Y-m-d'),$c->last_guests,
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    // Import clients from CSV – updates existing by email_primary or phone_primary
    public function importCsv(Request $req)
    {
        $file = $req->file('file');
        if (!$file || !$file->isValid()) {
            return back()->withErrors(['file'=>'Please upload a valid CSV file']);
        }

        $allowed = self::STATUS;
        $count = 0; $updated = 0;
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $bom = fread($handle, 3); if ($bom !== "\xEF\xBB\xBF") rewind($handle);
            $header = fgetcsv($handle);
            $map = [];
            foreach ((array)$header as $i => $h) { $map[strtolower(trim($h))] = $i; }
            $get = function($row,$key) use ($map){ $i=$map[$key]??null; return $i!==null ? trim((string)($row[$i]??'')) : ''; };
            while (($row = fgetcsv($handle)) !== false) {
                $email = strtolower($get($row,'email_primary'));
                $phone = $get($row,'phone_primary');
                $where = [];
                if ($email !== '') $where['email_primary'] = $email; elseif ($phone !== '') $where['phone_primary'] = $phone;
                $payload = [
                    'first_name'=>$get($row,'first_name'),'last_name'=>$get($row,'last_name'),'company'=>$get($row,'company'),
                    'phone_primary'=>$phone,'phone_alt'=>$get($row,'phone_alt'),'email_primary'=>$email,'email_alt'=>$get($row,'email_alt'),
                    'address1_street'=>$get($row,'address1_street'),'address1_city'=>$get($row,'address1_city'),'address1_state'=>$get($row,'address1_state'),'address1_zip'=>$get($row,'address1_zip'),
                    'address2_street'=>$get($row,'address2_street'),'address2_city'=>$get($row,'address2_city'),'address2_state'=>$get($row,'address2_state'),'address2_zip'=>$get($row,'address2_zip'),
                    'referral_source'=>$get($row,'referral_source'),'internal_notes'=>$get($row,'internal_notes'),'website'=>$get($row,'website'),
                ];
                $status = strtolower($get($row,'status')); if (in_array($status,$allowed,true)) $payload['status']=$status;
                $sm = $get($row,'social_media'); if ($sm!=='') $payload['social_links']=['social_media'=>$sm];
                $ld = $get($row,'last_event_date'); if ($ld!=='') { try { $payload['last_event_date']=\Carbon\Carbon::parse($ld)->format('Y-m-d'); } catch (\Throwable $e) {} }
                $lg = $get($row,'last_guests'); if ($lg!=='') $payload['last_guests']=(int)$lg;

                $client = !empty($where) ? Client::where($where)->first() : null;
                if ($client) { $client->fill($payload)->save(); $updated++; }
                else { Client::create($payload); }
                $count++;
            }
            fclose($handle);
        }
        return redirect()->route('admin.clients')->with('ok', "Import completed: $count rows (updated $updated)");
    }

    // Download a CSV template with header only
    public function templateCsv()
    {
        $columns = [
            'first_name','last_name','company','phone_primary','phone_alt','email_primary','email_alt',
            'address1_street','address1_city','address1_state','address1_zip',
            'address2_street','address2_city','address2_state','address2_zip',
            'social_media','referral_source','internal_notes','status','website','last_event_date','last_guests'
        ];
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="clients_template.csv"',
        ];
        return response()->stream(function() use ($columns){
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $columns);
            fclose($out);
        }, 200, $headers);
    }
}
