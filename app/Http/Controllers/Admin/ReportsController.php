<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Reservation;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index(Request $req)
    {
        $preset = (string) $req->query('preset', 'month'); // day, week, month, year, custom
        $from = $req->query('from');
        $to   = $req->query('to');
        $agent = trim((string) $req->query('booked_by', 'all')) ?: 'all';

        // Resolve date range
        $today = Carbon::today();
        switch ($preset) {
            case 'day':
                $start = $today->copy();
                $end   = $today->copy();
                $gran  = 'day';
                break;
            case 'week':
                $start = $today->copy()->startOfWeek();
                $end   = $today->copy()->endOfWeek();
                $gran  = 'day'; // show daily within the week
                break;
            case 'year':
                $start = $today->copy()->startOfYear();
                $end   = $today->copy()->endOfYear();
                $gran  = 'month'; // aggregate by month for yearly view
                break;
            case 'custom':
                try { $start = Carbon::parse($from ?: $today->copy()->subDays(29)); } catch (\Throwable $e) { $start = $today->copy()->subDays(29); }
                try { $end   = Carbon::parse($to ?: $today); } catch (\Throwable $e) { $end   = $today; }
                $gran = $start->diffInDays($end) > 92 ? 'month' : 'day';
                break;
            case 'month':
            default:
                $start = $today->copy()->startOfMonth();
                $end   = $today->copy()->endOfMonth();
                $gran  = 'day';
                break;
        }

        // Base query (exclude soft deleted by default)
        $base = Reservation::query()->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        if ($agent !== 'all') {
            $base->whereRaw('LOWER(booked_by) = ?', [strtolower($agent)]);
        }

        // Summary totals
        $summary = (clone $base)
            ->selectRaw('COALESCE(SUM(total),0) as total_sum')
            ->selectRaw('COALESCE(SUM(deposit_paid),0) as deposit_sum')
            ->selectRaw('COALESCE(SUM(gratuity),0) as gratuity_sum')
            ->selectRaw('COALESCE(SUM(tax),0) as tax_sum')
            ->selectRaw('COUNT(*) as count_res')
            ->first();

        // Aggregation for charts
        if ($gran === 'month') {
            $groupRows = (clone $base)
                ->selectRaw("DATE_FORMAT(`date`, '%Y-%m') as period")
                ->selectRaw('COALESCE(SUM(total),0) as total_sum')
                ->selectRaw('COALESCE(SUM(deposit_paid),0) as deposit_sum')
                ->selectRaw('COALESCE(SUM(gratuity),0) as gratuity_sum')
                ->selectRaw('COALESCE(SUM(tax),0) as tax_sum')
                ->groupBy('period')
                ->orderBy('period')
                ->get();
            // Build full list of months between start and end
            $labels = [];
            $totals = [];
            $depos  = [];
            $grats  = [];
            $cursor = $start->copy()->startOfMonth();
            $endMon = $end->copy()->startOfMonth();
            $byKey = $groupRows->keyBy('period');
            $taxes = [];
            while ($cursor <= $endMon) {
                $key = $cursor->format('Y-m');
                $labels[] = $cursor->format('M Y');
                $row = $byKey->get($key);
                $totals[] = $row->total_sum ?? 0;
                $depos[]  = $row->deposit_sum ?? 0;
                $grats[]  = $row->gratuity_sum ?? 0;
                $taxes[]  = $row->tax_sum ?? 0;
                $cursor->addMonth();
            }
        } else { // day granularity
            $groupRows = (clone $base)
                ->selectRaw('DATE(`date`) as period')
                ->selectRaw('COALESCE(SUM(total),0) as total_sum')
                ->selectRaw('COALESCE(SUM(deposit_paid),0) as deposit_sum')
                ->selectRaw('COALESCE(SUM(gratuity),0) as gratuity_sum')
                ->selectRaw('COALESCE(SUM(tax),0) as tax_sum')
                ->groupBy('period')
                ->orderBy('period')
                ->get();
            // Build full list of days between start and end
            $labels = [];
            $totals = [];
            $depos  = [];
            $grats  = [];
            $cursor = $start->copy();
            $byKey = $groupRows->keyBy('period');
            $taxes = [];
            while ($cursor <= $end) {
                $key = $cursor->toDateString();
                $labels[] = $cursor->format('m/d');
                $row = $byKey->get($key);
                $totals[] = $row->total_sum ?? 0;
                $depos[]  = $row->deposit_sum ?? 0;
                $grats[]  = $row->gratuity_sum ?? 0;
                $taxes[]  = $row->tax_sum ?? 0;
                $cursor->addDay();
            }
        }

        // Recent reservations table (limit for performance)
        $rows = (clone $base)->orderByDesc('date')->orderByDesc('time')->limit(200)->get();

        // Build agent options (case-insensitive unique, normalize "Online")
        try {
            $map = [];
            $push = function($name) use (&$map){
                $n = trim((string)$name);
                if ($n === '') return;
                $key = strtolower($n);
                $display = ($key === 'online') ? 'Online' : $n;
                if (!array_key_exists($key, $map)) $map[$key] = $display;
            };
            if (Schema::hasTable('users')) {
                foreach (DB::table('users')->select('name')->pluck('name') as $n) { $push($n); }
            }
            foreach (Reservation::query()->distinct()->whereNotNull('booked_by')->where('booked_by','!=','')->pluck('booked_by') as $n) { $push($n); }
            $push('Online'); // ensure present
            $agentOptions = array_values($map);
            usort($agentOptions, fn($a,$b) => strcasecmp($a,$b));
        } catch (\Throwable $e) {
            $map = [];
            foreach (Reservation::query()->distinct()->whereNotNull('booked_by')->where('booked_by','!=','')->pluck('booked_by') as $n) {
                $k = strtolower(trim((string)$n));
                if ($k==='') continue;
                $map[$k] = ($k==='online') ? 'Online' : trim((string)$n);
            }
            $map['online'] = 'Online';
            $agentOptions = array_values($map);
            usort($agentOptions, fn($a,$b) => strcasecmp($a,$b));
        }

        return view('admin.reports', [
            'preset' => $preset,
            'from'   => $start->toDateString(),
            'to'     => $end->toDateString(),
            'agent'  => $agent,
            'agentOptions' => $agentOptions,
            'summary' => $summary,
            'labels'  => $labels,
            'totals'  => $totals,
            'depos'   => $depos,
            'grats'   => $grats,
            'taxes'   => $taxes,
            'rows'    => $rows,
        ]);
    }

}
