<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoice Status #{{ $reservation->invoice_number ?? ($reservation->code ?? $reservation->id) }}</title>
  <style>
    :root{--ink:#111827;--muted:#6b7280;--line:#e5e7eb;--red:#b21e27;--gold:#b8872f;--green:#047857;--amber:#b45309;--bg:#f7f7f8}
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--ink);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px 16px}
    .card{width:100%;max-width:760px;background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:0 24px 70px rgba(17,24,39,.10);overflow:hidden}
    .top{padding:28px;border-bottom:1px solid var(--line);display:flex;align-items:flex-start;justify-content:space-between;gap:18px}
    .brand{display:flex;align-items:center;gap:12px}
    .brand img{width:54px;height:54px;object-fit:contain}
    .brand-name{font-weight:800;font-size:18px;letter-spacing:.02em}
    .muted{color:var(--muted)}
    .invoice-no{text-align:right}
    .invoice-no b{display:block;font-size:20px}
    .body{padding:28px}
    .status-row{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px}
    .badge{display:inline-flex;align-items:center;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;border:1px solid}
    .badge.paid{background:#ecfdf5;color:var(--green);border-color:#a7f3d0}
    .badge.partial{background:#fffbeb;color:var(--amber);border-color:#fcd34d}
    .badge.pending{background:#fef2f2;color:#991b1b;border-color:#fecaca}
    .message{border-left:4px solid var(--red);background:#fff7f7;padding:14px 16px;border-radius:10px;margin:0 0 24px;color:#3f1114;font-weight:650}
    .message.paid{border-color:var(--green);background:#f0fdf4;color:#064e3b}
    .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .item{border:1px solid var(--line);border-radius:12px;padding:14px 16px;background:#fff}
    .label{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
    .value{font-size:17px;font-weight:750}
    .balance .value{color:var(--red);font-size:22px}
    .balance.paid .value{color:var(--green)}
    .footer{padding:18px 28px;border-top:1px solid var(--line);font-size:13px;color:var(--muted);display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
    a{color:inherit}
    @media (max-width:640px){
      .top,.status-row{display:block}
      .invoice-no{text-align:left;margin-top:18px}
      .grid{grid-template-columns:1fr}
      .body,.top,.footer{padding:22px}
    }
  </style>
  @php
    $fmt = fn($n) => '$'.number_format((float) $n, 2);
    $balance = (float) ($totals['balance'] ?? 0);
    $paidTotal = (float) ($totals['paid_total'] ?? 0);
    $statusKey = $invoiceStatus === 'Paid' ? 'paid' : ($invoiceStatus === 'Partially Paid' ? 'partial' : 'pending');
  @endphp
</head>
<body>
  <main class="page">
    <section class="card" aria-label="Invoice payment status">
      <div class="top">
        <div class="brand">
          <img src="/assets/brand/logo.png" alt="Hibachi Catering" onerror="this.style.display='none'">
          <div>
            <div class="brand-name">Hibachi Catering</div>
            <div class="muted">Invoice payment status</div>
          </div>
        </div>
        <div class="invoice-no">
          <span class="muted">Invoice</span>
          <b>#{{ $reservation->invoice_number ?? ($reservation->code ?? $reservation->id) }}</b>
        </div>
      </div>

      <div class="body">
        <div class="status-row">
          <div>
            <div class="label">Current status</div>
            <div class="value">{{ $invoiceStatus }}</div>
          </div>
          <span class="badge {{ $statusKey }}">{{ $invoiceStatus }}</span>
        </div>

        @if($balance <= 0.009)
          <p class="message paid">This invoice is fully paid. No balance is currently due.</p>
        @else
          <p class="message">A balance of {{ $fmt($balance) }} is still due for this invoice.</p>
        @endif

        <div class="grid">
          <div class="item">
            <div class="label">Event date</div>
            <div class="value">{{ $reservation->date?->format('m/d/Y') ?? 'Pending' }}</div>
          </div>
          <div class="item">
            <div class="label">Customer</div>
            <div class="value">{{ $reservation->customer_name ?? 'Customer' }}</div>
          </div>
          <div class="item">
            <div class="label">Total</div>
            <div class="value">{{ $fmt($totals['total'] ?? 0) }}</div>
          </div>
          <div class="item">
            <div class="label">Total paid</div>
            <div class="value">{{ $fmt($paidTotal) }}</div>
          </div>
          <div class="item balance {{ $balance <= 0.009 ? 'paid' : '' }}">
            <div class="label">Balance</div>
            <div class="value">{{ $fmt($balance) }}</div>
          </div>
          <div class="item">
            <div class="label">Payment status</div>
            <div class="value">{{ $invoiceStatus }}</div>
          </div>
        </div>
      </div>

      <div class="footer">
        <span>Hibachi Catering</span>
        <span>hibachicater.com</span>
      </div>
    </section>
  </main>
</body>
</html>
