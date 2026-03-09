<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Services\ReportRevenueService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FinancialOverviewController extends Controller
{
    public function __construct(private readonly ReportRevenueService $revenueService)
    {
    }

    public function index(Request $request)
    {
        [$preset, $start, $end, $groupBy] = $this->resolveRange($request);

        $expenseBase = Expense::query()->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);

        $revenueSummary = $this->revenueService->summary($start, $end);
        $totalRevenue = (float) ($revenueSummary->total_sum ?? 0);
        $totalExpenses = (float) (clone $expenseBase)->sum('amount');
        $reservationCount = (int) ($revenueSummary->count_res ?? 0);
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        [$trendLabels, $revenueTrend, $expenseTrend, $profitTrend] = $this->buildTrendSeries($start, $end, $groupBy);

        $expenseBreakdown = (clone $expenseBase)
            ->select('category')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get();

        [$comparisonLabels, $comparisonRevenue, $comparisonExpenses] = $this->buildMonthlyComparison($end);

        $expenses = (clone $expenseBase)
            ->with('creator:id,name')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        $periodCount = max(1, count($trendLabels));
        $expenseRatio = $totalRevenue > 0 ? ($totalExpenses / $totalRevenue) * 100 : 0;
        $largestExpenseCategory = $expenseBreakdown->first();

        return view('admin.financial_overview', [
            'preset' => $preset,
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'groupBy' => $groupBy,
            'kpis' => [
                'revenue' => $totalRevenue,
                'expenses' => $totalExpenses,
                'profit' => $netProfit,
                'margin' => $profitMargin,
                'avg_revenue_per_reservation' => $reservationCount > 0 ? $totalRevenue / $reservationCount : 0,
                'avg_expense_per_period' => $totalExpenses / $periodCount,
                'largest_expense_category' => $largestExpenseCategory?->category,
                'expense_ratio' => $expenseRatio,
                'reservation_count' => $reservationCount,
            ],
            'trendLabels' => $trendLabels,
            'revenueTrend' => $revenueTrend,
            'expenseTrend' => $expenseTrend,
            'profitTrend' => $profitTrend,
            'expenseBreakdown' => $expenseBreakdown,
            'comparisonLabels' => $comparisonLabels,
            'comparisonRevenue' => $comparisonRevenue,
            'comparisonExpenses' => $comparisonExpenses,
            'expenses' => $expenses,
            'backUrl' => $request->fullUrl(),
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.expense_form', [
            'expense' => new Expense(),
            'categories' => Expense::CATEGORIES,
            'backUrl' => (string) $request->query('back', route('admin.reports.financial')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateExpense($request);
        $validated['created_by'] = Auth::id();

        Expense::create($validated);

        return redirect($this->backUrl($request))->with('ok', 'Expense added successfully.');
    }

    public function edit(Request $request, $id)
    {
        return view('admin.expense_form', [
            'expense' => Expense::findOrFail($id),
            'categories' => Expense::CATEGORIES,
            'backUrl' => (string) $request->query('back', route('admin.reports.financial')),
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $expense = Expense::findOrFail($id);
        $expense->update($this->validateExpense($request));

        return redirect($this->backUrl($request))->with('ok', 'Expense updated successfully.');
    }

    public function destroy(Request $request, $id): RedirectResponse
    {
        Expense::findOrFail($id)->delete();

        return redirect($this->backUrl($request))->with('ok', 'Expense deleted successfully.');
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function backUrl(Request $request): string
    {
        return (string) $request->input('back', route('admin.reports.financial'));
    }

    private function resolveRange(Request $request): array
    {
        $preset = (string) $request->query('preset', 'month');
        $today = Carbon::today();

        switch ($preset) {
            case 'week':
                $start = $today->copy()->startOfWeek();
                $end = $today->copy()->endOfWeek();
                break;
            case 'year':
                $start = $today->copy()->startOfYear();
                $end = $today->copy()->endOfYear();
                break;
            case 'custom':
                try {
                    $start = Carbon::parse((string) $request->query('from', $today->copy()->startOfMonth()->toDateString()));
                } catch (\Throwable) {
                    $start = $today->copy()->startOfMonth();
                }
                try {
                    $end = Carbon::parse((string) $request->query('to', $today->toDateString()));
                } catch (\Throwable) {
                    $end = $today->copy();
                }
                break;
            case 'month':
            default:
                $preset = 'month';
                $start = $today->copy()->startOfMonth();
                $end = $today->copy()->endOfMonth();
                break;
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy(), $start->copy()];
        }

        $groupBy = $start->diffInDays($end) > 92 ? 'month' : 'day';

        return [$preset, $start->startOfDay(), $end->endOfDay(), $groupBy];
    }

    private function buildTrendSeries(Carbon $start, Carbon $end, string $groupBy): array
    {
        [$labels, $revenue] = $this->revenueService->series($start, $end, $groupBy);

        if ($groupBy === 'month') {
            $expenseRows = Expense::query()
                ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw("DATE_FORMAT(`expense_date`, '%Y-%m') as period")
                ->selectRaw('SUM(amount) as total_amount')
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->keyBy('period');

            $cursor = $start->copy()->startOfMonth();
            $last = $end->copy()->startOfMonth();
            $expenses = [];

            while ($cursor <= $last) {
                $key = $cursor->format('Y-m');
                $expenses[] = round((float) ($expenseRows->get($key)->total_amount ?? 0), 2);
                $cursor->addMonth();
            }
        } else {
            $expenseRows = Expense::query()
                ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('DATE(`expense_date`) as period')
                ->selectRaw('SUM(amount) as total_amount')
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->keyBy('period');

            $cursor = $start->copy()->startOfDay();
            $last = $end->copy()->startOfDay();
            $expenses = [];

            while ($cursor <= $last) {
                $key = $cursor->toDateString();
                $expenses[] = round((float) ($expenseRows->get($key)->total_amount ?? 0), 2);
                $cursor->addDay();
            }
        }

        $profit = collect($revenue)
            ->zip($expenses)
            ->map(fn (Collection $pair) => round(((float) $pair[0]) - ((float) $pair[1]), 2))
            ->all();

        return [$labels, $revenue, $expenses, $profit];
    }

    private function buildMonthlyComparison(Carbon $end): array
    {
        $start = $end->copy()->subMonths(11)->startOfMonth();
        $finish = $end->copy()->endOfMonth();

        [, $revenueSeries] = $this->revenueService->series($start, $finish, 'month');

        $expenseRows = Expense::query()
            ->whereBetween('expense_date', [$start->toDateString(), $finish->toDateString()])
            ->selectRaw("DATE_FORMAT(`expense_date`, '%Y-%m') as period")
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $cursor = $start->copy();
        $labels = [];
        $revenue = [];
        $expenses = [];
        $index = 0;

        while ($cursor <= $finish) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->format('M');
            $revenue[] = round((float) ($revenueSeries[$index] ?? 0), 2);
            $expenses[] = round((float) ($expenseRows->get($key)->total_amount ?? 0), 2);
            $cursor->addMonth();
            $index++;
        }

        return [$labels, $revenue, $expenses];
    }
}
