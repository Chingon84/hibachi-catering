<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportRevenueService
{
    public function baseQuery(Carbon $start, Carbon $end, string $bookedBy = 'all'): Builder
    {
        $query = Reservation::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        if ($bookedBy !== 'all') {
            $query->whereRaw('LOWER(booked_by) = ?', [strtolower($bookedBy)]);
        }

        return $query;
    }

    public function summary(Carbon $start, Carbon $end, string $bookedBy = 'all')
    {
        return $this->baseQuery($start, $end, $bookedBy)
            ->selectRaw('COALESCE(SUM(total),0) as total_sum')
            ->selectRaw('COALESCE(SUM(deposit_paid),0) as deposit_sum')
            ->selectRaw('COALESCE(SUM(gratuity),0) as gratuity_sum')
            ->selectRaw('COALESCE(SUM(tax),0) as tax_sum')
            ->selectRaw('COUNT(*) as count_res')
            ->first();
    }

    public function series(Carbon $start, Carbon $end, string $granularity = 'day', string $bookedBy = 'all'): array
    {
        $base = $this->baseQuery($start, $end, $bookedBy);

        if ($granularity === 'month') {
            $groupRows = (clone $base)
                ->selectRaw("DATE_FORMAT(`date`, '%Y-%m') as period")
                ->selectRaw('COALESCE(SUM(total),0) as total_sum')
                ->selectRaw('COALESCE(SUM(deposit_paid),0) as deposit_sum')
                ->selectRaw('COALESCE(SUM(gratuity),0) as gratuity_sum')
                ->selectRaw('COALESCE(SUM(tax),0) as tax_sum')
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->keyBy('period');

            $labels = [];
            $totals = [];
            $deposits = [];
            $gratuity = [];
            $taxes = [];
            $cursor = $start->copy()->startOfMonth();
            $endMonth = $end->copy()->startOfMonth();

            while ($cursor <= $endMonth) {
                $key = $cursor->format('Y-m');
                $row = $groupRows->get($key);
                $labels[] = $cursor->format('M Y');
                $totals[] = (float) ($row->total_sum ?? 0);
                $deposits[] = (float) ($row->deposit_sum ?? 0);
                $gratuity[] = (float) ($row->gratuity_sum ?? 0);
                $taxes[] = (float) ($row->tax_sum ?? 0);
                $cursor->addMonth();
            }

            return [$labels, $totals, $deposits, $gratuity, $taxes];
        }

        $groupRows = (clone $base)
            ->selectRaw('DATE(`date`) as period')
            ->selectRaw('COALESCE(SUM(total),0) as total_sum')
            ->selectRaw('COALESCE(SUM(deposit_paid),0) as deposit_sum')
            ->selectRaw('COALESCE(SUM(gratuity),0) as gratuity_sum')
            ->selectRaw('COALESCE(SUM(tax),0) as tax_sum')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $labels = [];
        $totals = [];
        $deposits = [];
        $gratuity = [];
        $taxes = [];
        $cursor = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();

        while ($cursor <= $endDay) {
            $key = $cursor->toDateString();
            $row = $groupRows->get($key);
            $labels[] = $cursor->format('m/d');
            $totals[] = (float) ($row->total_sum ?? 0);
            $deposits[] = (float) ($row->deposit_sum ?? 0);
            $gratuity[] = (float) ($row->gratuity_sum ?? 0);
            $taxes[] = (float) ($row->tax_sum ?? 0);
            $cursor->addDay();
        }

        return [$labels, $totals, $deposits, $gratuity, $taxes];
    }

    public function rows(Carbon $start, Carbon $end, string $bookedBy = 'all', int $limit = 200): Collection
    {
        return $this->baseQuery($start, $end, $bookedBy)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit($limit)
            ->get();
    }

    public function reservationIds(Carbon $start, Carbon $end, string $bookedBy = 'all'): Collection
    {
        return $this->baseQuery($start, $end, $bookedBy)
            ->orderBy('id')
            ->pluck('id');
    }
}
