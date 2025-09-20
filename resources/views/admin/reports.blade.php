<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Reports</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    .cards{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px}
    @media (max-width: 1100px){.cards{grid-template-columns:repeat(2,1fr)}}
    @media (max-width: 640px){.cards{grid-template-columns:1fr}}
    .metric{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:12px}
    .metric .label{color:#6b7280;font-size:12px}
    .metric .value{font-size:22px;font-weight:700}
    .chart-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:12px}
  </style>
  @php $fmt = fn($n)=>'$'.number_format((float)$n,2); @endphp
</head>
<body>
  <div class="container">
    <div class="header">
      <h1 class="title" style="margin-right:auto">Reports</h1>
      <a href="{{ route('admin.reservations') }}" class="btn secondary">Back</a>
    </div>

    <div class="card"><div class="card-body">
      <form class="toolbar" method="get" action="{{ route('admin.reports') }}">
        @php $presetVal = $preset ?? 'month'; @endphp
        <select name="preset" class="select" onchange="toggleCustom(this.value)">
          <option value="day" {{ $presetVal==='day'?'selected':'' }}>Today</option>
          <option value="week" {{ $presetVal==='week'?'selected':'' }}>This week</option>
          <option value="month" {{ $presetVal==='month'?'selected':'' }}>This month</option>
          <option value="year" {{ $presetVal==='year'?'selected':'' }}>This year</option>
          <option value="custom" {{ $presetVal==='custom'?'selected':'' }}>Custom</option>
        </select>
        <input class="input" type="date" name="from" id="rep-from" value="{{ $from }}">
        <input class="input" type="date" name="to" id="rep-to" value="{{ $to }}">
        @php $agentVal = $agent ?? 'all'; @endphp
        <select name="booked_by" class="select" style="min-width:180px">
          <option value="all" {{ $agentVal==='all' ? 'selected' : '' }}>All</option>
          @foreach(($agentOptions ?? []) as $opt)
            <option value="{{ $opt }}" {{ $agentVal===$opt ? 'selected' : '' }}>{{ $opt }}</option>
          @endforeach
        </select>
        <button class="btn secondary" type="submit">Apply</button>
        <script>
          function toggleCustom(v){
            const on = v==='custom';
            document.getElementById('rep-from').disabled = !on;
            document.getElementById('rep-to').disabled = !on;
          }
          toggleCustom('{{ $preset }}');
        </script>
      </form>
    </div></div>

    <div class="cards">
      <div class="metric"><div class="label">Total Revenue</div><div class="value">{{ $fmt($summary->total_sum ?? 0) }}</div></div>
      <div class="metric"><div class="label">Deposits</div><div class="value">{{ $fmt($summary->deposit_sum ?? 0) }}</div></div>
      <div class="metric"><div class="label">Gratuity</div><div class="value">{{ $fmt($summary->gratuity_sum ?? 0) }}</div></div>
      <div class="metric"><div class="label">Tax</div><div class="value">{{ $fmt($summary->tax_sum ?? 0) }}</div></div>
      <div class="metric"><div class="label">Reservations</div><div class="value">{{ number_format((int)($summary->count_res ?? 0)) }}</div></div>
    </div>

    <div class="chart-card" style="margin-bottom:12px">
      <canvas id="salesChart" height="110"></canvas>
    </div>

    <div class="card">
      <div class="card-body">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Deposit</th>
              <th>Gratuity</th>
              <th>Tax</th>
              <th>Invoice</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $r)
              <tr>
                <td>{{ $r->date?->format('m/d/Y') }}</td>
                <td>{{ $r->customer_name ?? '—' }}</td>
                <td>{{ $fmt($r->total ?? 0) }}</td>
                <td>{{ $fmt($r->deposit_paid ?? 0) }}</td>
                <td>{{ $fmt($r->gratuity ?? 0) }}</td>
                <td>{{ $fmt($r->tax ?? 0) }}</td>
                <td><a href="{{ route('admin.reservations.invoice',['id'=>$r->id, 'back'=>request()->fullUrl()]) }}" style="text-decoration:underline;color:#b21e27">View</a></td>
              </tr>
            @empty
              <tr><td colspan="7" class="muted">No data for selected range</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const labels = @json($labels);
    const totals = @json($totals);
    const deposits = @json($depos);
    const gratuity = @json($grats);
    const taxes = @json($taxes);
    const ctx = document.getElementById('salesChart').getContext('2d');
    const chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label: 'Revenue', data: totals, borderColor: '#b21e27', backgroundColor: 'rgba(178,30,39,.12)', tension:.25, fill:true },
          { label: 'Deposits', data: deposits, borderColor: '#1d4ed8', backgroundColor: 'rgba(29,78,216,.12)', tension:.25, fill:true },
          { label: 'Gratuity', data: gratuity, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.12)', tension:.25, fill:true },
          { label: 'Tax', data: taxes, borderColor: '#6d28d9', backgroundColor: 'rgba(109,40,217,.12)', tension:.25, fill:true },
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: true } },
        scales: { y: { beginAtZero: true } }
      }
    });
  </script>
</body>
</html>
