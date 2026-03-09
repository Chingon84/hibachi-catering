<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Financial Overview</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .page{display:grid;gap:14px}
    .subnav{display:flex;flex-wrap:wrap;gap:8px}
    .subnav a{display:inline-flex;align-items:center;padding:9px 12px;border-radius:999px;border:1px solid var(--border);background:#fff;color:#334155;text-decoration:none;font-size:12px;font-weight:700}
    .subnav a.active{background:#0f172a;border-color:#0f172a;color:#fff}
    .toolbar-grid{display:grid;grid-template-columns:180px 160px 160px auto auto;gap:12px;align-items:end}
    .kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .mini-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .kpi-card,.panel-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:16px;box-shadow:0 10px 24px rgba(15,23,42,.04)}
    .kpi-label{font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em}
    .kpi-value{margin-top:10px;font-size:28px;font-weight:800;line-height:1.05;color:#0f172a}
    .kpi-note{margin-top:8px;font-size:12px;color:#94a3b8}
    .kpi-card.positive{background:linear-gradient(180deg,#f8fffb 0%,#ecfdf5 100%);border-color:#bbf7d0}
    .kpi-card.positive .kpi-value{color:#166534}
    .kpi-card.negative{background:linear-gradient(180deg,#fff8f8 0%,#fef2f2 100%);border-color:#fecaca}
    .kpi-card.negative .kpi-value{color:#b91c1c}
    .panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}
    .panel-title{margin:0;font-size:18px;line-height:1.2}
    .panel-copy{margin:6px 0 0;color:var(--muted);font-size:13px}
    .layout-two{display:grid;grid-template-columns:minmax(0,1.8fr) minmax(320px,.9fr);gap:14px}
    .table-wrap{overflow:auto}
    .finance-table{width:100%;border-collapse:separate;border-spacing:0;min-width:980px}
    .finance-table th,.finance-table td{padding:13px 14px;text-align:left;vertical-align:top}
    .finance-table thead th{background:#f8fafc;border-bottom:1px solid #e5e7eb;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .finance-table tbody tr + tr td{border-top:1px solid #eef2f7}
    .finance-table tbody tr:hover{background:#fafcff}
    .breakdown-list{display:grid;gap:10px}
    .breakdown-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border:1px solid #e5e7eb;border-radius:14px;background:#fff}
    .breakdown-label{font-weight:700;color:#0f172a}
    .breakdown-amount{font-weight:800}
    .tone-positive{color:#166534}
    .tone-negative{color:#b91c1c}
    .tone-neutral{color:#475569}
    .empty-state{padding:26px;border:1px dashed #d6dce8;border-radius:16px;background:#fafcff;color:#64748b;text-align:center}
    @media (max-width: 1180px){.kpi-grid,.mini-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.layout-two{grid-template-columns:1fr}.toolbar-grid{grid-template-columns:1fr 1fr 1fr auto auto}}
    @media (max-width: 760px){.kpi-grid,.mini-grid,.toolbar-grid{grid-template-columns:1fr}.finance-table{min-width:860px}}
  </style>
  @php
    $money = fn($value) => '$' . number_format((float) $value, 2);
    $profitTone = $kpis['profit'] > 0 ? 'positive' : ($kpis['profit'] < 0 ? 'negative' : '');
    $marginTone = $kpis['margin'] > 0 ? 'positive' : ($kpis['margin'] < 0 ? 'negative' : '');
    $ratioTone = $kpis['expense_ratio'] > 65 ? 'tone-negative' : ($kpis['expense_ratio'] > 0 ? 'tone-neutral' : 'tone-neutral');
  @endphp
</head>
<body>
  <div class="container">
    <div class="page">
      <div class="header">
        <h1 class="title" style="margin-right:auto">Financial Overview</h1>
        <a href="{{ route('admin.expenses.create', ['back' => $backUrl]) }}" class="btn">Add Expense</a>
      </div>

      @if (session('ok'))
        <div class="card"><div class="card-body" style="color:#166534;font-weight:700">{{ session('ok') }}</div></div>
      @endif

      <div class="subnav">
        <a href="{{ route('admin.reports') }}">Reports Dashboard</a>
        <a href="{{ route('admin.reports.financial') }}" class="active">Financial Overview</a>
      </div>

      <div class="card"><div class="card-body">
        <form method="get" action="{{ route('admin.reports.financial') }}" class="toolbar-grid">
          <div>
            <label class="label" for="preset">Range</label>
            <select class="select" id="preset" name="preset" onchange="toggleFinancialCustom(this.value)">
              <option value="week" {{ $preset === 'week' ? 'selected' : '' }}>This Week</option>
              <option value="month" {{ $preset === 'month' ? 'selected' : '' }}>This Month</option>
              <option value="year" {{ $preset === 'year' ? 'selected' : '' }}>This Year</option>
              <option value="custom" {{ $preset === 'custom' ? 'selected' : '' }}>Custom</option>
            </select>
          </div>
          <div>
            <label class="label" for="from">Start Date</label>
            <input class="input" type="date" id="from" name="from" value="{{ $from }}">
          </div>
          <div>
            <label class="label" for="to">End Date</label>
            <input class="input" type="date" id="to" name="to" value="{{ $to }}">
          </div>
          <button class="btn secondary" type="submit">Apply</button>
          <a class="btn secondary" href="{{ route('admin.reports.financial') }}">Reset</a>
        </form>
      </div></div>

      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label">Total Revenue</div>
          <div class="kpi-value">{{ $money($kpis['revenue']) }}</div>
          <div class="kpi-note">{{ number_format($kpis['reservation_count']) }} counted reservations</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Total Expenses</div>
          <div class="kpi-value">{{ $money($kpis['expenses']) }}</div>
          <div class="kpi-note">Manual expenses in selected range</div>
        </div>
        <div class="kpi-card {{ $profitTone }}">
          <div class="kpi-label">Net Profit</div>
          <div class="kpi-value">{{ $money($kpis['profit']) }}</div>
          <div class="kpi-note">Revenue minus expenses</div>
        </div>
        <div class="kpi-card {{ $marginTone }}">
          <div class="kpi-label">Profit Margin</div>
          <div class="kpi-value">{{ number_format((float) $kpis['margin'], 1) }}%</div>
          <div class="kpi-note">Net profit divided by revenue</div>
        </div>
      </div>

      <div class="mini-grid">
        <div class="kpi-card">
          <div class="kpi-label">Avg Revenue / Reservation</div>
          <div class="kpi-value">{{ $money($kpis['avg_revenue_per_reservation']) }}</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Avg Expense / {{ $groupBy === 'month' ? 'Month' : 'Day' }}</div>
          <div class="kpi-value">{{ $money($kpis['avg_expense_per_period']) }}</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Largest Expense Category</div>
          <div class="kpi-value" style="font-size:22px">{{ $kpis['largest_expense_category'] ?: 'None' }}</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Expense-to-Revenue Ratio</div>
          <div class="kpi-value {{ $ratioTone }}">{{ number_format((float) $kpis['expense_ratio'], 1) }}%</div>
        </div>
      </div>

      <div class="layout-two">
        <div class="panel-card">
          <div class="panel-head">
            <div>
              <h2 class="panel-title">Profit &amp; Loss Trend</h2>
              <p class="panel-copy">Revenue, expenses, and net profit for the selected range.</p>
            </div>
          </div>
          <canvas id="financialChart" height="120"></canvas>
        </div>

        <div class="panel-card">
          <div class="panel-head">
            <div>
              <h2 class="panel-title">Expense Breakdown</h2>
              <p class="panel-copy">Category totals sorted from largest to smallest.</p>
            </div>
          </div>
          @if ($expenseBreakdown->isEmpty())
            <div class="empty-state">No expenses recorded for this range.</div>
          @else
            <div class="breakdown-list">
              @foreach ($expenseBreakdown as $row)
                <div class="breakdown-row">
                  <div class="breakdown-label">{{ $row->category }}</div>
                  <div class="breakdown-amount">{{ $money($row->total_amount) }}</div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>

      <div class="panel-card">
        <div class="panel-head">
          <div>
            <h2 class="panel-title">Monthly Trend Comparison</h2>
            <p class="panel-copy">Revenue versus expenses across the latest 12 months.</p>
          </div>
        </div>
        <canvas id="comparisonChart" height="100"></canvas>
      </div>

      <div class="panel-card">
        <div class="panel-head">
          <div>
            <h2 class="panel-title">Detailed Expenses</h2>
            <p class="panel-copy">Manual accounting entries used for the financial overview calculations.</p>
          </div>
        </div>
        <div class="table-wrap">
          <table class="finance-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Notes</th>
                <th>Created By</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($expenses as $expense)
                <tr>
                  <td>{{ $expense->expense_date?->format('m/d/Y') }}</td>
                  <td>{{ $expense->category }}</td>
                  <td>{{ $expense->description ?: '—' }}</td>
                  <td style="font-weight:800">{{ $money($expense->amount) }}</td>
                  <td style="max-width:320px;color:#64748b">{{ $expense->notes ?: '—' }}</td>
                  <td>{{ $expense->creator?->name ?: 'System' }}</td>
                  <td>
                    <div style="display:flex;gap:8px;align-items:center">
                      <a class="btn secondary" href="{{ route('admin.expenses.edit', ['id' => $expense->id, 'back' => $backUrl]) }}">Edit</a>
                      <form method="post" action="{{ route('admin.expenses.delete', $expense->id) }}" onsubmit="return confirm('Delete this expense?')">
                        @csrf
                        <input type="hidden" name="back" value="{{ $backUrl }}">
                        <button class="btn secondary" type="submit">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7"><div class="empty-state">No expenses found for the selected period.</div></td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    function toggleFinancialCustom(value) {
      const custom = value === 'custom';
      document.getElementById('from').disabled = !custom;
      document.getElementById('to').disabled = !custom;
    }
    toggleFinancialCustom(@json($preset));

    const financialCtx = document.getElementById('financialChart').getContext('2d');
    new Chart(financialCtx, {
      type: 'line',
      data: {
        labels: @json($trendLabels),
        datasets: [
          { label: 'Revenue', data: @json($revenueTrend), borderColor: '#0f766e', backgroundColor: 'rgba(15,118,110,.12)', fill: true, tension: .28 },
          { label: 'Expenses', data: @json($expenseTrend), borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,.10)', fill: true, tension: .28 },
          { label: 'Profit', data: @json($profitTrend), borderColor: '#1d4ed8', backgroundColor: 'rgba(29,78,216,.08)', fill: true, tension: .28 },
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true } }
      }
    });

    const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
    new Chart(comparisonCtx, {
      type: 'bar',
      data: {
        labels: @json($comparisonLabels),
        datasets: [
          { label: 'Revenue', data: @json($comparisonRevenue), backgroundColor: 'rgba(15,118,110,.78)', borderRadius: 6 },
          { label: 'Expenses', data: @json($comparisonExpenses), backgroundColor: 'rgba(220,38,38,.72)', borderRadius: 6 },
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true } }
      }
    });
  </script>
</body>
</html>
