<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Invoices</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    body{background:#f8fafc}
    .container{width:100%;max-width:none;margin:0;padding:20px 24px}
    .topbar{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:22px}
    .global-search{position:relative;width:min(360px,100%)}
    .global-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#64748b}
    .global-search .input{padding-left:38px;background:#f8fafc;border-color:#eef2f7}
    .title-row{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:16px}
    .title-row.actions-only{justify-content:flex-end;margin-bottom:12px}
    .title{font-size:30px;font-weight:800;letter-spacing:-.02em;margin:0;color:#2f3340}
    .header-actions{display:flex;align-items:center;gap:8px}
    .create-btn{display:inline-flex;align-items:center;gap:8px;background:#4f35ff;color:#fff;text-decoration:none;border:0;border-radius:8px;padding:9px 14px;font-weight:800;line-height:1;box-shadow:0 8px 18px rgba(79,53,255,.16)}
    .create-btn:hover{background:#432de1}
    .tabs{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:6px;margin-bottom:10px}
    .tab{display:flex;align-items:center;justify-content:space-between;gap:8px;border:1px solid #d6ddea;background:#fff;border-radius:9px;padding:12px 14px;text-decoration:none;color:#1f2937;font-weight:650;min-height:44px}
    .tab.active{border-color:#5339ff;box-shadow:0 0 0 1px #5339ff;color:#4230d8}
    .tab-count{color:#64748b;font-size:12px;font-weight:800}
    .filter-row{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border-bottom:1px solid #cbd5e1;padding-bottom:10px;margin-bottom:0}
    .chips,.tools{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
    .chip,.tool-btn{display:inline-flex;align-items:center;gap:6px;height:28px;border:1px dashed #cbd5e1;background:#fff;color:#253247;border-radius:999px;padding:5px 9px;text-decoration:none;font-size:12px;font-weight:800}
    .tool-btn{border-style:solid;border-radius:7px;height:32px}
    .invoice-table{width:100%;border-collapse:collapse}
    .invoice-table th,.invoice-table td{padding:9px 8px;border-bottom:1px solid #e6ebf2;font-size:13px;vertical-align:middle;white-space:nowrap}
    .invoice-table th{font-size:12px;color:#1f2a3d;font-weight:800;background:#fff;text-align:left}
    .invoice-table tbody tr{transition:background-color .15s ease}
    .invoice-table tbody tr:hover{background:#f8fafc}
    .money{font-weight:850;color:#1e293b;text-align:right}
    .status-badge{display:inline-flex;align-items:center;padding:3px 8px;border-radius:6px;border:1px solid #dbe2ea;background:#f8fafc;color:#475569;font-size:12px;font-weight:750}
    .status-badge.draft,.status-badge.void{background:#f8fafc;color:#475569;border-color:#dbe2ea}
    .status-badge.open{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}
    .status-badge.past_due{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
    .status-badge.paid{background:#ecfdf5;color:#15803d;border-color:#bbf7d0}
    .muted{color:#64748b}
    .actions{display:flex;justify-content:flex-end;gap:6px;align-items:center}
    .action-link{display:inline-flex;align-items:center;justify-content:center;border:1px solid #dbe2ea;background:#fff;border-radius:7px;color:#334155;text-decoration:none;padding:6px 8px;font-size:12px;font-weight:800;min-height:30px}
    .action-link:hover{background:#f8fafc;border-color:#cbd5e1}
    .action-link.primary{color:#b21e27;background:#fff8f8;border-color:#fecdd3}
    .action-form{display:inline}
    .empty{padding:24px;color:#64748b}
    .warning{border:1px solid #fde68a;background:#fffbeb;color:#854d0e;border-radius:10px;padding:10px 12px;margin-bottom:12px;font-size:13px;font-weight:650}
    @media (max-width:1000px){.tabs{grid-template-columns:repeat(3,minmax(0,1fr))}.invoice-table{min-width:980px}.table-wrap{overflow-x:auto}}
    @media (max-width:640px){.container{padding:16px}.tabs{grid-template-columns:1fr}.topbar,.title-row{align-items:flex-start;flex-direction:column}.header-actions{width:100%;justify-content:flex-start}}
  </style>
  @php
    $fmt = fn($n) => '$'.number_format((float) $n, 2);
    $tabs = [
      'all' => 'All invoices',
      'draft' => 'Draft',
      'open' => 'Open',
      'past_due' => 'Past due',
      'paid' => 'Paid',
      'void' => 'Void',
    ];
  @endphp
</head>
<body>
  <div class="container">
    <div class="topbar">
      <form method="get" action="{{ route('admin.invoices') }}" class="global-search">
        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 0 1 4.37 8.84l3.34 3.35-1.42 1.41-3.34-3.34A5.5 5.5 0 1 1 8.5 3Zm0 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z" clip-rule="evenodd"/></svg>
        <input class="input" type="search" name="q" placeholder="Search" value="{{ $q }}">
        <input type="hidden" name="status" value="{{ $status }}">
      </form>
    </div>

    <div class="title-row actions-only">
      <div class="header-actions">
        @if(auth()->user()?->hasPermission('reservations.manage'))
          <a class="create-btn" href="{{ route('admin.invoices.create') }}">+ Create invoice</a>
        @endif
      </div>
    </div>

    @if(!$standaloneReady)
      <div class="warning">Standalone invoice tables are not migrated yet. Existing reservation invoices are still visible; run migrations to create new invoices.</div>
    @endif

    <nav class="tabs" aria-label="Invoice status">
      @foreach($tabs as $key => $label)
        <a class="tab {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.invoices', array_filter(['q' => $q, 'status' => $key === 'all' ? null : $key, 'per_page' => $perPage ?? 25])) }}">
          <span>{{ $label }}</span>
          <span class="tab-count">{{ $counts[$key] ?? 0 }}</span>
        </a>
      @endforeach
    </nav>

    <div class="filter-row">
      <div class="chips">
        <span class="chip">⊕ Status</span>
        <span class="chip">⊕ Created</span>
        <span class="chip">⊕ Due date</span>
        <span class="chip">⊕ Total</span>
        <span class="chip">⊕ More filters</span>
      </div>
      <div class="tools">
        <a class="tool-btn" href="#">⇩ Export</a>
        <a class="tool-btn" href="#">▥ Analyze</a>
        <a class="tool-btn" href="#">⚙ Edit columns</a>
      </div>
    </div>

    <div class="table-wrap">
      <table class="invoice-table" aria-label="Invoices list">
        <thead>
          <tr>
            <th style="text-align:right">Total</th>
            <th>Status</th>
            <th>Invoice number</th>
            <th>Customer email</th>
            <th>Due</th>
            <th>Created</th>
            <th>Customer description</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $row)
            <tr>
              <td class="money">{{ $fmt($row['total'] ?? 0) }} <span class="muted">USD</span></td>
              <td><span class="status-badge {{ $row['status'] }}">{{ $row['status_label'] }}</span></td>
              <td>{{ $row['invoice_number'] }}</td>
              <td>{{ $row['customer_email'] ?: '-' }}</td>
              <td>{{ !empty($row['due']) ? \Carbon\Carbon::parse($row['due'])->format('M j, Y') : '-' }}</td>
              <td>{{ !empty($row['created']) ? \Carbon\Carbon::parse($row['created'])->format('M j, g:i A') : '-' }}</td>
              <td>{{ $row['description'] ?: '-' }}</td>
              <td>
                <div class="actions">
                  <a class="action-link primary" href="{{ $row['view_url'] }}">View invoice</a>
                  <a class="action-link" href="{{ $row['edit_url'] }}">{{ $row['kind'] === 'reservation' ? 'Edit reservation' : 'Edit invoice' }}</a>
                  @if(!empty($row['reservation_url']) && $row['kind'] !== 'reservation')
                    <a class="action-link" href="{{ $row['reservation_url'] }}">View reservation</a>
                  @endif
                  @if(!empty($row['void_url']) && auth()->user()?->hasPermission('reservations.manage'))
                    <form class="action-form" method="post" action="{{ $row['void_url'] }}" onsubmit="return confirm('Void this invoice?')">
                      @csrf
                      <button class="action-link" type="submit">Void</button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="empty">No invoices found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $rows])
  </div>
</body>
</html>
