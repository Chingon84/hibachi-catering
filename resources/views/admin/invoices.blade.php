@extends('layouts.admin')

@section('title', 'Invoices')

@push('styles')
<style>
  /* ── Layout ── */
  .topbar{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px}
  .global-search{position:relative;width:min(320px,100%)}
  .global-search svg{position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--muted);pointer-events:none}
  .global-search .input{padding-left:35px;background:var(--surface-2);border-color:#eef2f7;font-size:13px;height:34px}
  .create-btn{display:inline-flex;align-items:center;gap:7px;background:var(--brand);color:#fff;text-decoration:none;border:0;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:800;line-height:1;white-space:nowrap;box-shadow:0 6px 16px rgba(178,30,39,.18)}
  .create-btn:hover{background:var(--brand-hover);color:#fff}

  /* ── Status tabs ── */
  .tabs{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:5px;margin-bottom:10px}
  .tab{display:flex;align-items:center;justify-content:space-between;gap:6px;border:1px solid #d6ddea;background:#fff;border-radius:8px;padding:9px 12px;text-decoration:none;color:#374151;font-size:13px;font-weight:650;min-height:40px}
  .tab.active{border-color:var(--brand);box-shadow:0 0 0 1px var(--brand);color:var(--brand)}
  .tab-count{color:var(--muted);font-size:11px;font-weight:800;background:var(--surface-2);border-radius:999px;padding:1px 6px}
  .tab.active .tab-count{background:#fef2f2;color:var(--brand)}

  /* ── Filter bar ── */
  .filter-row{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;border-bottom:1px solid #e2e8f0;padding-bottom:8px;margin-bottom:0}
  .chips,.tools{display:flex;align-items:center;gap:5px;flex-wrap:wrap}
  .chip{display:inline-flex;align-items:center;gap:5px;height:26px;border:1px dashed #cbd5e1;background:#fff;color:#374151;border-radius:999px;padding:4px 9px;font-size:11px;font-weight:700;cursor:default}
  .tool-btn{display:inline-flex;align-items:center;gap:5px;height:28px;border:1px solid #e2e8f0;background:#fff;color:#374151;border-radius:7px;padding:4px 10px;font-size:11px;font-weight:700;cursor:pointer;text-decoration:none}
  .tool-btn:hover{background:var(--surface-2)}

  /* ── Table ── */
  .table-wrap{overflow-x:auto}
  .invoice-table{width:100%;border-collapse:collapse;min-width:860px}
  .invoice-table th{padding:7px 10px;border-bottom:2px solid #e2e8f0;font-size:11px;color:#64748b;font-weight:800;text-transform:uppercase;letter-spacing:.04em;text-align:left;background:#fff;white-space:nowrap}
  .invoice-table td{padding:8px 10px;border-bottom:1px solid #f1f5f9;font-size:13px;vertical-align:middle;white-space:nowrap}
  .invoice-table tbody tr{transition:background .12s}
  .invoice-table tbody tr:hover{background:#f8fafc}
  .invoice-table .col-total{text-align:right}
  .invoice-table .col-actions{text-align:right;width:72px}

  /* ── Money ── */
  .money{font-weight:800;color:#1e293b}
  .money .cur{font-size:11px;font-weight:600;color:#94a3b8;margin-left:2px}

  /* ── Status badge ── */
  .badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:5px;font-size:11px;font-weight:750;border:1px solid transparent;white-space:nowrap}
  .badge.draft,.badge.void{background:#f1f5f9;color:#64748b;border-color:#e2e8f0}
  .badge.open{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}
  .badge.past_due{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
  .badge.paid{background:#ecfdf5;color:#15803d;border-color:#a7f3d0}

  /* ── Invoice # cell ── */
  .inv-num{font-weight:700;color:#1e293b;font-size:13px}
  .inv-sub{font-size:11px;color:#94a3b8;margin-top:1px;white-space:normal;max-width:180px;overflow:hidden;text-overflow:ellipsis;display:block}

  /* ── Name / email ── */
  .cust-name{font-weight:650;color:#334155}
  .cust-email{font-size:11px;color:#94a3b8;margin-top:1px;display:block}

  /* ── Actions ── */
  .inv-actions{display:flex;justify-content:flex-end;align-items:center;gap:4px}
  .icon-btn{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border:1px solid #e2e8f0;background:#fff;border-radius:7px;color:#475569;cursor:pointer;transition:background .12s,border-color .12s,color .12s;text-decoration:none}
  .icon-btn:hover{background:var(--surface-2);border-color:#cbd5e1;color:#1e293b}
  .icon-btn.view:hover{color:var(--brand);border-color:#fecdd3;background:#fff5f5}

  /* ── 3-dot dropdown ── */
  .row-menu{position:relative;display:inline-block}
  .dots-btn{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border:1px solid #e2e8f0;background:#fff;border-radius:7px;color:#475569;cursor:pointer;font-size:18px;letter-spacing:0;line-height:1;transition:background .12s,border-color .12s}
  .dots-btn:hover,.dots-btn.open{background:var(--surface-2);border-color:#cbd5e1}
  .dropdown-menu{display:none;position:absolute;right:0;top:calc(100% + 4px);z-index:200;min-width:168px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(15,23,42,.12);padding:4px 0;animation:fadeIn .1s ease}
  .dropdown-menu.open{display:block}
  @keyframes fadeIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
  .dropdown-item{display:flex;align-items:center;gap:9px;width:100%;padding:8px 13px;font-size:13px;font-weight:600;color:#334155;background:none;border:0;cursor:pointer;text-decoration:none;text-align:left;white-space:nowrap;transition:background .1s}
  .dropdown-item:hover{background:#f8fafc;color:#1e293b}
  .dropdown-item svg{flex-shrink:0;width:15px;height:15px;color:#94a3b8}
  .dropdown-item:hover svg{color:#64748b}
  .dropdown-divider{border:0;border-top:1px solid #f1f5f9;margin:3px 0}
  .dropdown-item.danger{color:#b91c1c}
  .dropdown-item.danger:hover{background:#fef2f2;color:#991b1b}
  .dropdown-item.danger svg{color:#fca5a5}
  .dropdown-item.danger:hover svg{color:#b91c1c}

  /* ── Empty / warning ── */
  .empty{padding:20px;color:var(--muted);font-size:13px}
  .warning{border:1px solid #fde68a;background:#fffbeb;color:#854d0e;border-radius:9px;padding:9px 12px;margin-bottom:12px;font-size:13px;font-weight:650}

  /* ── Responsive ── */
  @media (max-width:1100px){.tabs{grid-template-columns:repeat(3,minmax(0,1fr))}}
  @media (max-width:640px){.tabs{grid-template-columns:1fr 1fr}.topbar{flex-direction:column;align-items:flex-start}.create-btn{width:100%;justify-content:center}}
</style>
@endpush

@section('content')
@php
  $fmt    = fn($n) => '$'.number_format((float) $n, 2);
  $canMgr = auth()->user()?->hasPermission('reservations.manage');
  $tabs   = [
    'all'      => 'All invoices',
    'draft'    => 'Draft',
    'open'     => 'Open',
    'past_due' => 'Past due',
    'paid'     => 'Paid',
    'void'     => 'Void',
  ];
@endphp

<div class="container">

  {{-- Top bar: search + create --}}
  <div class="topbar">
    <form method="get" action="{{ route('admin.invoices') }}" class="global-search">
      <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 0 1 4.37 8.84l3.34 3.35-1.42 1.41-3.34-3.34A5.5 5.5 0 1 1 8.5 3Zm0 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z" clip-rule="evenodd"/></svg>
      <input class="input" type="search" name="q" placeholder="Search name, email, invoice…" value="{{ $q }}">
      <input type="hidden" name="status" value="{{ $status }}">
    </form>
    @if($canMgr)
      <a class="create-btn" href="{{ route('admin.invoices.create') }}">
        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M10 4a1 1 0 0 1 1 1v4h4a1 1 0 1 1 0 2h-4v4a1 1 0 1 1-2 0v-4H5a1 1 0 1 1 0-2h4V5a1 1 0 0 1 1-1Z"/></svg>
        Create invoice
      </a>
    @endif
  </div>

  @if(!$standaloneReady)
    <div class="warning">Standalone invoice tables are not migrated yet. Run <code>php artisan migrate</code> to enable creating new invoices.</div>
  @endif

  {{-- Status tabs --}}
  <nav class="tabs" aria-label="Invoice status">
    @foreach($tabs as $key => $label)
      <a class="tab {{ $status === $key ? 'active' : '' }}"
         href="{{ route('admin.invoices', array_filter(['q' => $q, 'status' => $key === 'all' ? null : $key, 'per_page' => $perPage ?? 25])) }}">
        <span>{{ $label }}</span>
        <span class="tab-count">{{ $counts[$key] ?? 0 }}</span>
      </a>
    @endforeach
  </nav>

  {{-- Filter bar --}}
  <div class="filter-row">
    <div class="chips">
      <span class="chip">
        <svg width="10" height="10" viewBox="0 0 20 20" fill="currentColor"><circle cx="10" cy="10" r="8"/></svg>
        Status
      </span>
      <span class="chip">⊕ Created</span>
      <span class="chip">⊕ Due date</span>
      <span class="chip">⊕ Total</span>
      <span class="chip">⊕ More filters</span>
    </div>
    <div class="tools">
      <a class="tool-btn" href="#">
        <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path d="M3 5h14M6 10h8M9 15h2"/><path stroke="currentColor" stroke-width="1.5" d="M3 5h14M6 10h8M9 15h2" fill="none" stroke-linecap="round"/></svg>
        Export
      </a>
      <a class="tool-btn" href="#">
        <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><rect x="3" y="3" width="6" height="6" rx="1"/><rect x="11" y="3" width="6" height="6" rx="1"/><rect x="3" y="11" width="6" height="6" rx="1"/><rect x="11" y="11" width="6" height="6" rx="1"/></svg>
        Analyze
      </a>
    </div>
  </div>

  {{-- Table --}}
  <div class="table-wrap">
    <table class="invoice-table" aria-label="Invoices list">
      <thead>
        <tr>
          <th class="col-total" style="text-align:right">Total</th>
          <th>Status</th>
          <th>Invoice #</th>
          <th>Name</th>
          <th>Email</th>
          <th>Due</th>
          <th>Created</th>
          <th class="col-actions" style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $row)
          @php
            $status    = $row['status'];
            $isStand   = $row['kind'] === 'standalone';
            $canVoid   = $canMgr && !empty($row['void_url']);
            $canDelete = $canMgr && !empty($row['delete_url']);
            $custName  = trim((string) ($row['customer_name'] ?? ''));
          @endphp
          <tr>
            {{-- Total --}}
            <td class="col-total">
              <span class="money">{{ $fmt($row['total'] ?? 0) }}<span class="cur">USD</span></span>
            </td>

            {{-- Status --}}
            <td><span class="badge {{ $status }}">{{ $row['status_label'] }}</span></td>

            {{-- Invoice # + description --}}
            <td>
              <span class="inv-num">{{ $row['invoice_number'] }}</span>
              @if(!empty($row['description']))
                <span class="inv-sub" title="{{ $row['description'] }}">{{ $row['description'] }}</span>
              @endif
            </td>

            {{-- Name --}}
            <td>
              <span class="cust-name">{{ $custName ?: '—' }}</span>
            </td>

            {{-- Email --}}
            <td>{{ $row['customer_email'] ?: '—' }}</td>

            {{-- Due --}}
            <td>{{ !empty($row['due']) ? \Carbon\Carbon::parse($row['due'])->format('M j, Y') : '—' }}</td>

            {{-- Created --}}
            <td style="color:#64748b;font-size:12px">
              {{ !empty($row['created']) ? \Carbon\Carbon::parse($row['created'])->format('M j, g:i A') : '—' }}
            </td>

            {{-- Actions --}}
            <td class="col-actions">
              <div class="inv-actions">

                {{-- 👁 View icon --}}
                <a class="icon-btn view" href="{{ $row['view_url'] }}" title="View invoice">
                  <svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 10s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/>
                    <circle cx="10" cy="10" r="2.5"/>
                  </svg>
                </a>

                {{-- ⋯ More actions --}}
                <div class="row-menu">
                  <button class="dots-btn" type="button" title="More actions" aria-haspopup="true">
                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor">
                      <circle cx="4" cy="10" r="1.6"/><circle cx="10" cy="10" r="1.6"/><circle cx="16" cy="10" r="1.6"/>
                    </svg>
                  </button>
                  <div class="dropdown-menu" role="menu">

                    {{-- Edit --}}
                    <a class="dropdown-item" href="{{ $row['edit_url'] }}" role="menuitem">
                      <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14.3 3.7a1 1 0 0 1 2 2L7 15l-3 1 1-3 9.3-9.3Z"/></svg>
                      {{ $row['kind'] === 'reservation' ? 'Edit reservation' : 'Edit invoice' }}
                    </a>

                    @if($canVoid)
                      <hr class="dropdown-divider">
                      <form method="post" action="{{ $row['void_url'] }}" onsubmit="return confirm('Void this invoice? This cannot be undone.')">
                        @csrf
                        <button class="dropdown-item" type="submit" role="menuitem">
                          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="8"/><path d="M7 7l6 6M13 7l-6 6"/></svg>
                          Void invoice
                        </button>
                      </form>
                    @endif

                    @if($canDelete)
                      @if(!$canVoid)<hr class="dropdown-divider">@endif
                      <form method="post" action="{{ $row['delete_url'] }}" onsubmit="return confirm('Permanently delete this invoice? This cannot be undone.')">
                        @csrf
                        <button class="dropdown-item danger" type="submit" role="menuitem">
                          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h14M8 6V4h4v2M6 6l1 11h6l1-11"/></svg>
                          Delete invoice
                        </button>
                      </form>
                    @endif

                  </div>
                </div>

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
@endsection

@push('scripts')
<script>
(function () {
  // Toggle dropdown on dots-btn click; close all others first
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.dots-btn');
    if (btn) {
      e.stopPropagation();
      const menu  = btn.closest('.row-menu').querySelector('.dropdown-menu');
      const isOpen = menu.classList.contains('open');

      // Close every open dropdown
      document.querySelectorAll('.dropdown-menu.open').forEach(m => {
        m.classList.remove('open');
        m.previousElementSibling?.classList.remove('open');
      });

      if (!isOpen) {
        menu.classList.add('open');
        btn.classList.add('open');
      }
      return;
    }

    // Click outside → close all
    if (!e.target.closest('.row-menu')) {
      document.querySelectorAll('.dropdown-menu.open').forEach(m => {
        m.classList.remove('open');
        m.previousElementSibling?.classList.remove('open');
      });
    }
  });

  // Esc key closes all dropdowns
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.dropdown-menu.open').forEach(m => {
        m.classList.remove('open');
        m.previousElementSibling?.classList.remove('open');
      });
    }
  });
})();
</script>
@endpush
