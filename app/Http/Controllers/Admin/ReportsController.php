<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportRevenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Reservation;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function __construct(private readonly ReportRevenueService $revenueService)
    {
    }

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

        $summary = $this->revenueService->summary($start, $end, $agent);
        [$labels, $totals, $depos, $grats, $taxes] = $this->revenueService->series($start, $end, $gran, $agent);
        $rows = $this->revenueService->rows($start, $end, $agent);

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
