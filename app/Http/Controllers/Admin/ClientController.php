<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    private const STATUS = ['regular','vip','celebrity','blacklist','preferred'];
    public function index(Request $req)
    {
        $q = trim((string)$req->query('q', ''));
        $status = $req->query('status');
        $city = trim((string) $req->query('city', ''));
        $date = trim((string) $req->query('date', ''));

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
                $w->where('address1_city', $city)
                  ->orWhere('address2_city', $city);
            });
        }
        if ($date !== '') {
            // filter by last_event_date exact date
            $query->whereDate('last_event_date', $date);
        }

        $list = $query->paginate(25)->withQueryString();

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
        ]);
    }

    public function create()
    {
        return view('admin.client_form', ['client' => new Client(['status'=>'regular']), 'mode' => 'create', 'statusOptions' => self::STATUS]);
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
