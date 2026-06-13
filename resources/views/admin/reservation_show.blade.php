<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Reservation Details</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    :root{--bg:#f7f7fb;--text:#111827;--muted:#6b7280;--card:#fff;--border:#e5e7eb;--brand:#b21e27}
    *{box-sizing:border-box}
    [x-cloak]{display:none!important}
    body{margin:0;background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .page-shell{width:100%;max-width:none;margin:0;padding:20px 24px!important}
    .btn{appearance:none;border:0;background:var(--brand);color:#fff;border-radius:10px;padding:9px 13px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-block;font-size:14px;line-height:1.2}
    .btn.secondary{background:#4b5563}
    .badge{display:inline-block;border:1px solid var(--border);background:#f3f4f6;color:#374151;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:700}
    .chip{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid var(--border);border-radius:999px;padding:6px 10px;font-size:12px}
    .chip svg{width:14px;height:14px;color:#6b7280}
    .card{background:var(--card);border:1px solid var(--border);border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.035);margin-bottom:12px}
    .input{width:100%;padding:10px 12px;border:1px solid #e6e8ec;border-radius:10px;background:#fff;transition:border-color .12s ease, box-shadow .12s ease}
    .input:focus{outline:none;border-color:#d1d5db;box-shadow:0 0 0 3px rgba(178,30,39,.08)}
    select.input{appearance:none;background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="%236b7280"><path d="M5.23 7.21a.75.75 0 011.06.02L10 10.207l3.71-2.977a.75.75 0 111.06 1.06l-4.24 3.4a.75.75 0 01-.94 0l-4.24-3.4a.75.75 0 01.02-1.06z"/></svg>');background-repeat:no-repeat;background-position:right 10px center;background-size:16px;padding-right:34px}
    textarea.input{resize:vertical}
    .icon-btn{appearance:none;border:1px solid #e5e7eb;background:#fff;color:#475569;border-radius:8px;padding:6px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .icon-btn:hover{background:#f8fafc;color:#111827}
    .icon-btn.danger{background:#fff;color:#b91c1c;border-color:#fecaca}
    .icon-btn.danger:hover{background:#fef2f2;color:#991b1b;border-color:#fca5a5}
    .icon-btn svg{width:16px;height:16px;display:block}
    /* Compact controls for adjustments */
    .adj-plus{width:32px;height:32px;border-radius:9999px;border:1px solid #ddd;background:#eee;color:#666;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .adj-plus:hover{background:#e5e7eb;border-color:#ccc;color:#444}
    .adj-plus:disabled{opacity:.4;cursor:not-allowed}
    .adj-remove{width:28px;height:28px;border-radius:8px;border:1px solid #ddd;background:#fff;color:#444;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .adj-remove:hover{background:#f9fafb}
    .color-dot{width:14px;height:14px;border-radius:999px;border:1px solid #d1d5db;cursor:pointer}
    .color-pop{position:absolute;top:100%;right:0;margin-top:6px;z-index:2000;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:8px}
    .swatches{display:grid;grid-template-columns:repeat(5,16px);gap:8px}
    .sw{width:16px;height:16px;border-radius:999px;border:2px solid #fff;box-shadow:0 0 0 1px #d1d5db;position:relative;cursor:pointer}
    .sw .check{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px}
    .summary-hero{background:linear-gradient(180deg,#ffffff 0%,#fbfdff 100%);border:1px solid var(--border);border-radius:14px;padding:11px 14px;box-shadow:0 10px 24px rgba(15,23,42,.045);margin-bottom:12px !important}
    .summary-hero > .flex{gap:10px !important}
    .summary-main{display:flex;flex-direction:column;gap:7px}
    .summary-identity{display:flex;flex-direction:column;gap:4px}
    .summary-name{margin:0;font-size:24px;line-height:1.08;font-weight:800;letter-spacing:0;color:#0f172a}
    .summary-name-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .name-markers{display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap}
    .name-marker{display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 8px;border-radius:999px;border:1px solid #e5e7eb;background:#fff;color:#0f172a;font-size:16px;font-weight:900;line-height:1}
    .name-marker.vip{font-size:11px;letter-spacing:.08em;text-transform:uppercase;padding:0 10px}
    .summary-meta{font-size:12px;color:#64748b;display:flex;gap:7px;flex-wrap:wrap;align-items:center}
    .summary-chips{display:flex;flex-wrap:wrap;gap:5px;align-items:center}
    .metric-chip{padding:5px 8px;border-radius:999px;border:1px solid #e5e7eb;background:#fff;font-size:11px;font-weight:700;color:#334155}
    .metric-chip svg{width:13px;height:13px}
    .summary-actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap;justify-content:flex-end}
    .summary-actions a,.summary-actions button{min-height:34px !important;padding:7px 11px !important;border-radius:9px !important;font-size:13px !important;line-height:1.15}
    .summary-actions svg{width:15px;height:15px}
    .resv-grid{display:grid;grid-template-columns:minmax(0,1fr);gap:12px}
    .resv-main,.resv-side{display:flex;flex-direction:column;gap:12px}
    .section-card{border-radius:12px;box-shadow:0 10px 26px rgba(15,23,42,.04);margin-bottom:0}
    .form-pane{background:#fcfdff;border:1px solid #edf0f4;border-radius:12px;padding:12px}
    .pane-title{font-size:14px;font-weight:800;color:#0f172a;margin:0 0 8px 0}
    .summary-kv{display:grid;grid-template-columns:1fr;gap:12px}
    .summary-kv .card{margin-bottom:0}
    .items-card table thead tr,.payments-card table thead tr{background:#f8fafc}
    .items-card table thead th,.payments-card table thead th{color:#475569;padding:7px 9px !important;font-size:12px !important}
    .items-card table tbody td,.payments-card table tbody td{padding:7px 9px !important}
    .items-card table tbody tr{transition:background-color .16s ease}
    .items-card table tbody tr:hover{background:#f8fafc}
    .payments-card table tbody tr:hover{background:#f8fbff}
    .line-items-header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
    .line-items-title{font-size:18px;font-weight:800;color:#111827;margin:0}
    .line-items-collapse{width:30px;height:30px;border:0;background:transparent;color:#111827;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .line-items-collapse:hover{background:#f3f4f6}
    .line-items-table{width:100%;border-collapse:separate;border-spacing:0}
    .line-items-table thead th{font-size:12px;font-weight:800;color:#111827;background:#fff !important;border-bottom:1px solid #9ca3af;padding:9px 12px !important}
    .line-items-table tbody td{padding:10px 12px !important;border-bottom:1px solid #eef0f3;vertical-align:middle}
    .line-items-table tbody tr:hover{background:#fafafa}
    .line-item-name{font-size:13px;font-weight:800;color:#111827;text-decoration:underline;text-underline-offset:2px;text-transform:uppercase}
    .line-item-desc{font-size:11px;color:#6b7280;margin-top:4px}
    .line-item-desc-input{height:30px;padding:5px 8px;font-size:12px;border-radius:8px}
    .line-item-qty{width:78px;height:38px;border:1px solid #111827;border-radius:8px;text-align:center;font-size:14px;background:#fff}
    .line-item-qty.compact{width:68px;height:34px}
    .line-item-price-input{width:92px;height:34px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:13px;padding:6px 9px}
    .line-item-money{text-align:right;white-space:nowrap;font-size:13px;color:#111827}
    .line-item-empty{border:1px dashed #d1d5db;border-radius:12px;padding:14px;color:#6b7280;font-size:13px;background:#fafafa}
    .line-item-grip{color:#9ca3af;font-size:18px;line-height:1}
    .line-item-search-wrap{position:relative;margin-top:16px}
    .line-item-search{height:56px;border:1px solid #d1d5db;background:#fff;border-radius:10px;display:flex;align-items:center;gap:14px;padding:0 12px;transition:border-color .14s ease, box-shadow .14s ease}
    .line-item-search.active{border-color:#111827;box-shadow:0 0 0 1px #111827}
    .line-item-plus{width:28px;height:28px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;color:#111827;font-size:26px;font-weight:400;line-height:1;flex:0 0 auto}
    .line-item-search-field{border:0;outline:0;width:100%;font-size:16px;color:#111827;background:transparent}
    .line-item-search-field::placeholder{color:#6b7280}
    .line-item-info{width:20px;height:20px;border:1px solid #9ca3af;border-radius:999px;color:#6b7280;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex:0 0 auto}
    .line-item-dropdown{position:absolute;left:0;right:0;top:calc(100% + 8px);z-index:50;background:#fff;border:1px solid #eef0f3;border-radius:12px;box-shadow:0 18px 34px rgba(15,23,42,.12);padding:8px;max-height:330px;overflow-y:auto}
    .line-item-category{padding:9px 12px 5px;font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
    .line-item-option{width:100%;border:0;background:#fff;display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:14px;align-items:center;text-align:left;padding:9px 10px;border-radius:8px;cursor:pointer}
    .line-item-option:hover,.line-item-option.active{background:#f5f5f5}
    .line-item-code{width:40px;height:40px;border-radius:7px;background:#eef0f2;color:#6b7280;font-weight:800;font-size:14px;display:inline-flex;align-items:center;justify-content:center;text-transform:uppercase}
    .line-item-option-name{font-size:14px;font-weight:800;color:#111827;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .line-item-option-cat{font-size:11px;color:#6b7280;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .line-item-one-time{background:#f7f7f7;grid-template-columns:42px minmax(0,1fr) 28px}
    .line-item-return{color:#8b8f96;font-size:22px;text-align:right}
    .totals-card{background:#f8fafc;border:1px solid #e6ecf5;border-radius:12px;padding:10px 13px;max-width:440px;margin-left:auto}
    .totals-card .totals-row{display:flex;justify-content:space-between;align-items:center;padding:2px 0;gap:18px}
    .totals-card .totals-row.total{font-size:16px;font-weight:800;color:#0f172a;border-top:1px solid #e5e7eb;margin-top:5px;padding-top:7px}
    .totals-card .totals-row.total span{font-size:17px;font-weight:900}
    .totals-card .totals-row.balance{font-size:15px;font-weight:800;color:#b21e27}
    .status-pill-select{min-width:126px !important;height:28px !important;padding:4px 28px 4px 9px !important;border-radius:999px !important;font-size:11px !important;font-weight:800;border:1px solid #e5e7eb !important;box-shadow:none !important}
    .status-pill-select.status-confirmed{background:#ecfdf5;color:#166534;border-color:#bbf7d0 !important}
    .status-pill-select.status-pending{background:#fffbeb;color:#92400e;border-color:#fde68a !important}
    .status-pill-select.status-canceled{background:#fef2f2;color:#991b1b;border-color:#fecaca !important}
    .assigned-staff-card{border-radius:12px;box-shadow:0 10px 26px rgba(15,23,42,.04);margin-bottom:12px}
    .assigned-staff-card > .p-6{padding:12px 14px !important}
    .assigned-staff-head{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:9px}
    .assigned-staff-title{font-size:15px;font-weight:800;color:#0f172a;margin:0}
    .assigned-staff-meta{font-size:12px;font-weight:700;color:#64748b}
    .assigned-staff-grid{display:flex;flex-wrap:wrap;gap:10px;align-items:stretch;justify-content:flex-start}
    .assigned-staff-item{flex:0 0 auto;min-width:112px;max-width:170px;border:1px solid #e7ebf0;background:#f9fafb;border-radius:10px;padding:8px 12px}
    .assigned-staff-item.van{min-width:80px;max-width:110px}
    .assigned-staff-label{font-size:10px;line-height:1.1;color:#64748b;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
    .assigned-staff-value{margin-top:4px;font-size:14px;line-height:1.2;color:#111827;font-weight:800;overflow-wrap:anywhere}
    .assigned-staff-value.empty{color:#94a3b8;font-weight:700}
    .assigned-staff-confirmation{display:inline-flex;margin-top:6px;border-radius:999px;padding:3px 7px;font-size:10px;font-weight:900;line-height:1;border:1px solid #e2e8f0;background:#f1f5f9;color:#64748b}
    .assigned-staff-confirmation.viewed{background:#dbeafe;border-color:#bfdbfe;color:#1d4ed8}
    .assigned-staff-confirmation.confirmed{background:#dcfce7;border-color:#bbf7d0;color:#15803d}
    .assigned-staff-empty{border:1px dashed #d8dee8;background:#f8fafc;border-radius:12px;padding:12px;color:#64748b;font-size:13px;font-weight:700}
    .btn{box-shadow:0 4px 12px rgba(178,30,39,.12)}
    .btn.save{background:#16a34a;box-shadow:0 4px 12px rgba(22,163,74,.16)}
    .btn.save:hover{background:#15803d}
    .btn.secondary{box-shadow:none;background:#e5e7eb;color:#334155}
    .btn.secondary:hover{background:#dbe1e8}
    .page-shell{max-width:none;margin:0;padding:20px 24px !important}
    .form-card > .p-6,.items-card > .p-6,.payments-card > .p-6{padding:14px !important}
    .form-card form > .grid{gap:12px !important;margin-bottom:12px !important}
    .form-pane.space-y-4 > :not([hidden]) ~ :not([hidden]){margin-top:8px !important}
    .form-pane .grid{gap:6px !important}
    .form-pane label{font-size:13px !important;line-height:1.18;color:#111827}
    .form-pane .input{height:36px;padding:7px 10px;border-radius:8px;font-size:13px;line-height:1.25}
    .form-pane textarea.input{height:72px;min-height:72px;padding-top:8px}
    .marker-picker{display:flex;flex-wrap:wrap;gap:8px}
    .marker-toggle{position:relative;display:inline-flex}
    .marker-toggle input{position:absolute;opacity:0;pointer-events:none}
    .marker-toggle span{display:inline-flex;align-items:center;gap:8px;min-height:34px;padding:7px 11px;border:1px solid #e5e7eb;border-radius:999px;background:#fff;color:#334155;font-size:12px;font-weight:800;line-height:1;cursor:pointer;transition:border-color .14s ease, background-color .14s ease, color .14s ease, box-shadow .14s ease}
    .marker-toggle .marker-icon{font-size:15px;line-height:1}
    .marker-toggle input:checked + span{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;box-shadow:0 0 0 3px rgba(59,130,246,.10)}
    .marker-help{font-size:11px;color:#64748b;line-height:1.4;margin-top:6px}
    .items-card h3,.payments-card h3{font-size:16px;margin-bottom:10px !important}
    .items-card table{font-size:13px}
    .items-card table tbody td{vertical-align:middle}
    .items-card [aria-label="Click to edit description"]{min-height:28px !important;padding:4px 8px !important}
    .items-card input[name^="items["]{height:34px;padding:6px 8px;border-radius:8px}
    .items-card .icon-btn.danger{width:28px;height:28px;border-radius:8px}
    .items-card .mt-5{margin-top:14px !important}
    .items-card .mt-2{margin-top:8px !important}
    .items-card button[aria-describedby="save-items-help"]{min-height:36px;padding:7px 13px}
    @media (min-width: 1024px){
      .page-shell{max-width:none;padding:20px 24px !important}
      .resv-grid{grid-template-columns:minmax(0,1.2fr) minmax(0,.95fr)}
    }
    @media (max-width: 760px){
      .page-shell{padding:16px!important}
      .summary-actions{justify-content:flex-start}
      .summary-actions a,.summary-actions button{min-height:32px !important}
      .line-items-table{min-width:640px}
      .line-item-search{height:52px}
      .totals-card{max-width:none}
    }
  </style>
  @php
    $fmt = fn($n)=>'$'.number_format((float)$n,2);
    $statusCurrent = strtolower((string) ($r->status ?? 'pending'));
    if (in_array($statusCurrent, ['draft', 'pending_payment'], true)) {
      $statusCurrent = 'pending';
    }
    $statusSelected = strtolower((string) old('status', $statusCurrent));
    if ($statusSelected === 'pending_payment') {
      $statusSelected = 'pending';
    }
    $statusTone = $statusSelected === 'confirmed'
      ? 'status-confirmed'
      : ($statusSelected === 'canceled' ? 'status-canceled' : 'status-pending');
    $adminPaymentTotals = \App\Support\ReservationTotals::compute($r);
    $adminBalanceDue = max(0, (float) ($adminPaymentTotals['balance'] ?? 0));
    $adminPaidTotal = max(0, (float) ($adminPaymentTotals['paid_total'] ?? 0));
    $adminManualPaid = max(0, (float) ($adminPaymentTotals['manual_paid'] ?? 0));
    $adminBasePaidWithoutManual = max(0, round($adminPaidTotal - $adminManualPaid, 2));
    $eventMarkerOptions = \App\Models\Reservation::eventMarkerOptions();
    $selectedEventMarkers = old('event_markers', $r->normalizedEventMarkers());
    $assignedStaffRows = ($r->scheduleAssignment?->assignedStaffSummaryRows() ?? collect())
      ->whereIn('label', ['Chef 1', 'Chef 2', 'Chef 3', 'Van'])
      ->values();
    $hasAssignedStaff = $assignedStaffRows->contains(fn ($row) => ($row['value'] ?? 'N/A') !== 'N/A');
  @endphp
</head>
<body>
  <div class="page-shell p-4 lg:p-8">
    <!-- Header -->
    <div class="summary-hero mb-6">
      <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
      <div class="summary-main">
        <div class="summary-identity">
          <div class="summary-name-row">
            <h1 class="summary-name">{{ $r->customer_name ?: 'Reservation Details' }}</h1>
            @if(!empty($selectedEventMarkers))
              <div class="name-markers" aria-label="Reservation markers">
                @foreach($selectedEventMarkers as $markerKey)
                  @php $marker = $eventMarkerOptions[$markerKey] ?? null; @endphp
                  @if($marker)
                    <span class="name-marker {{ $markerKey === 'vip' ? 'vip' : '' }}" title="{{ $marker['label'] }}">{{ $marker['icon'] }}</span>
                  @endif
                @endforeach
              </div>
            @endif
          </div>
          <div class="summary-meta">
            <span>Reservation #{{ $r->code ?? $r->id }}</span>
            <span>•</span>
            <span>Invoice #{{ $r->invoice_number ?? '—' }}</span>
          </div>
        </div>
        <div class="summary-chips">
          <span class="chip metric-chip" title="Date">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1zm12 7H5v10h14V9z"/></svg>
            {{ $r->date?->format('m/d/Y') ?? '—' }}
          </span>
          <span class="chip metric-chip" title="Time">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2zm1 10.586V7a1 1 0 0 0-2 0v6a1 1 0 0 0 .293.707l3 3a1 1 0 1 0 1.414-1.414z"/></svg>
            {{ \Carbon\Carbon::parse($r->time)->format('g:i A') }}
          </span>
          <span class="chip metric-chip" title="Guests">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 11c1.654 0 3-1.346 3-3S17.654 5 16 5s-3 1.346-3 3 1.346 3 3 3zM8 11c1.654 0 3-1.346 3-3S9.654 5 8 5 5 6.346 5 8s1.346 3 3 3zm0 2c-2.673 0-8 1.337-8 4v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.663-5.327-4-8-4zm8 0c-.29 0-.62.017-.98.047A6.6 6.6 0 0 1 18 17v1a1 1 0 0 1-1 1h6a1 1 0 0 0 1-1v-1c0-2.273-3.876-4-6-4z"/></svg>
            {{ $r->guests }} guests
          </span>
          @php $balTop = $adminBalanceDue; $col = $r->color ?? '#6b7280'; @endphp
          <span class="chip metric-chip" title="Balance" style="display:inline-flex;align-items:center;gap:8px">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 1a11 11 0 1 0 11 11A11.013 11.013 0 0 0 12 1zm1 17.93V20a1 1 0 0 1-2 0v-1a4.005 4.005 0 0 1-3-3.87 1 1 0 0 1 2 0 2 2 0 0 0 2 2h2a2 2 0 0 0 0-4h-2a4 4 0 0 1 0-8h1V4a1 1 0 0 1 2 0v1a4.005 4.005 0 0 1 3 3.87 1 1 0 0 1-2 0 2 2 0 0 0-2-2h-2a2 2 0 0 0 0 4h2a4 4 0 0 1 0 8h-1.05A10.027 10.027 0 0 1 12 21a10.013 10.013 0 0 1-1-.07z"/></svg>
            Balance: {{ '$'.number_format($balTop,2) }}
          </span>
          <span class="chip metric-chip" title="Reservation Status" style="display:inline-flex;align-items:center;gap:8px">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm-1 5a1 1 0 112 0v5.17l3.24 1.62a1 1 0 01-.9 1.79l-3.79-1.9A1 1 0 0111 12.8V7z"/></svg>
            <select name="status" form="resvForm" class="input status-pill-select {{ $statusTone }}" onchange="syncStatusPill(this)">
              <option value="confirmed" {{ $statusSelected==='confirmed' ? 'selected' : '' }}>Confirmed</option>
              <option value="pending" {{ $statusSelected==='pending' ? 'selected' : '' }}>Pending</option>
              <option value="canceled" {{ $statusSelected==='canceled' ? 'selected' : '' }}>Canceled</option>
            </select>
          </span>
          <!-- Color picker button (separate from chip) -->
          <span class="inline-block relative" x-data="colorPicker('{{ $col }}')" style="margin-left:10px;display:inline-block;vertical-align:middle">
            <button type="button" class="color-dot" :style="{ background: color }" @click.stop="toggle()" aria-label="Pick color"></button>
            <div class="color-pop" x-show="open" @click.away="open=false" @keydown.escape.window="open=false" x-transition>
              <div class="swatches">
                <template x-for="c in colors" :key="c">
                  <button type="button" class="sw" :class="{'none': c==='none'}" :style="c!=='none'?{background:c}:{background:'#fff'}" @click="pick(c)" :aria-label="c==='none' ? 'None' : ('Pick '+c)">
                    <span x-show="color===c" class="check">✓</span>
                    <svg x-show="c==='none'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" style="position:absolute;inset:0;margin:auto;color:#374151"><path d="M5 5l14 14M19 5L5 19" stroke="currentColor" stroke-width="2"/></svg>
                  </button>
                </template>
              </div>
            </div>
          </span>
        </div>
      </div>
      <div class="summary-actions">
        <a class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md" href="{{ route('admin.reservations') }}">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2" aria-hidden="true">
            <path d="M11.03 3.97a.75.75 0 0 1 0 1.06l-6.22 6.22H21a.75.75 0 0 1 0 1.5H4.81l6.22 6.22a.75.75 0 1 1-1.06 1.06l-7.5-7.5a.75.75 0 0 1 0-1.06l7.5-7.5a.75.75 0 0 1 1.06 0z"/>
          </svg>
          Back
        </a>
        <a class="inline-flex items-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md border border-blue-200" href="{{ route('admin.reservations.invoice',['id'=>$r->id]) }}">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2" aria-hidden="true">
            <path d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625Z"/>
            <path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z"/>
          </svg>
          Invoice
        </a>
        <form method="POST" action="{{ route('payments.checkout') }}" target="_top" class="inline-flex m-0">
          @csrf
          <input type="hidden" name="reservation_id" value="{{ $r->id }}">
          <input type="hidden" name="payment_type" value="full">
          <input type="hidden" name="deposit_amount" value="{{ number_format($adminBalanceDue, 2, '.', '') }}">
          <button class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:text-gray-500 disabled:shadow-none text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg focus:ring-2 focus:ring-green-500 focus:ring-offset-2" type="submit" title="Pay balance {{ $fmt($adminBalanceDue) }}" {{ $adminBalanceDue <= 0.009 ? 'disabled' : '' }}>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2" aria-hidden="true">
              <path d="M3 6.75A2.25 2.25 0 0 1 5.25 4.5h13.5A2.25 2.25 0 0 1 21 6.75v6.75a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 13.5V6.75Zm2.25-.25a.25.25 0 0 0-.25.25v1h16v-1a.25.25 0 0 0-.25-.25H5.25ZM5 10.5v3a.25.25 0 0 0 .25.25h13.5a.25.25 0 0 0 .25-.25v-3H5Z"/>
              <path d="M6 18.75A.75.75 0 0 1 6.75 18h10.5a.75.75 0 0 1 0 1.5H6.75A.75.75 0 0 1 6 18.75Z"/>
            </svg>
            Pay
          </button>
        </form>
        <a class="inline-flex items-center p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md border border-blue-200" 
           href="{{ route('admin.reservations.show',['id'=>$r->id, 'print'=>'menu', 'back'=>request()->fullUrl()]) }}" 
           title="Print menu" aria-label="Print menu">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4" aria-hidden="true">
            <circle cx="5" cy="7" r="1.5" />
            <circle cx="5" cy="12" r="1.5" />
            <circle cx="5" cy="17" r="1.5" />
            <path d="M9 7h10M9 12h10M9 17h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
          </svg>
        </a>
        <button class="inline-flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg focus:ring-2 focus:ring-green-500 focus:ring-offset-2" type="submit" form="resvForm">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2" aria-hidden="true">
            <path d="M7.5 3.375c0-1.036.84-1.875 1.875-1.875h.375a3.75 3.75 0 0 1 3.75 3.75v1.875C13.5 8.161 14.34 9 15.375 9h1.875A3.75 3.75 0 0 1 21 12.75v3.375C21 17.16 20.16 18 19.125 18h-9.75A1.875 1.875 0 0 1 7.5 16.125V3.375Z"/>
            <path d="M15 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963A5.23 5.23 0 0 0 17.25 7.5h-1.875a.375.375 0 0 1-.375-.375V5.25Z"/>
          </svg>
          Save Reservation
        </button>
      </div>
    </div>
    </div>

    <div class="card assigned-staff-card">
      <div class="p-6">
        <div class="assigned-staff-head">
          <h2 class="assigned-staff-title">Assigned Staff</h2>
          @if($hasAssignedStaff)
            <span class="assigned-staff-meta">Synced from Schedule</span>
          @endif
        </div>
        @if($hasAssignedStaff)
          <div class="assigned-staff-grid">
            @foreach($assignedStaffRows as $staffRow)
              <div class="assigned-staff-item {{ ($staffRow['label'] ?? '') === 'Van' ? 'van' : '' }}">
                <div class="assigned-staff-label">{{ $staffRow['label'] }}</div>
                <div class="assigned-staff-value {{ ($staffRow['value'] ?? 'N/A') === 'N/A' ? 'empty' : '' }}">{{ $staffRow['value'] }}</div>
                @if(filled($staffRow['user_id'] ?? null))
                  @php $staffConfirmation = $r->staffConfirmationSummaryFor((int) $staffRow['user_id']); @endphp
                  <div class="assigned-staff-confirmation {{ $staffConfirmation['tone'] }}">
                    {{ $staffConfirmation['label'] }}
                    @if($staffConfirmation['timestamp'])
                      {{ $staffConfirmation['timestamp'] }}
                    @endif
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        @else
          <div class="assigned-staff-empty">No staff assigned yet.</div>
        @endif
      </div>
    </div>

    <div class="resv-grid">
    <div class="resv-main">
    <!-- Reservation Form -->
    <div class="card section-card form-card">
      <div class="p-6">
        @if (session('ok'))
          <div class="bg-green-50 text-green-700 border border-green-200 rounded-lg p-3 mb-4">
            {{ session('ok') }}
          </div>
        @endif
        @if ($errors->any())
          <div class="bg-red-50 text-red-700 border border-red-200 rounded-lg p-3 mb-4">
            {{ $errors->first() }}
          </div>
        @endif
        
        <form method="post" action="{{ route('admin.reservations.update',['id'=>$r->id]) }}" id="resvForm">
          @csrf
          <!-- Reordered Form: Left/Right columns -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Left column fields -->
            <div class="form-pane space-y-4">
              <h3 class="pane-title">Customer Information</h3>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Customer name</label>
                <input name="customer_name" value="{{ old('customer_name',$r->customer_name) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Company</label>
                <input name="company" value="{{ old('company',$r->company) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Date</label>
                <input type="date" name="date" value="{{ old('date',$r->date?->toDateString()) }}" class="input col-span-2" required>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Phone</label>
                <input name="phone" value="{{ old('phone',$r->phone) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Email</label>
                <input type="email" name="email" value="{{ old('email',$r->email) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Address</label>
                <input name="address" value="{{ old('address',$r->address) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">City</label>
                <input name="city" value="{{ old('city',$r->city) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">ZIP</label>
                <input name="zip_code" value="{{ old('zip_code',$r->zip_code) }}" class="input col-span-2">
              </div>
            </div>

            <!-- Right column fields -->
            <div class="form-pane space-y-4">
              <h3 class="pane-title">Event Information</h3>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Invoice #</label>
                <input name="invoice_number" value="{{ $r->invoice_number ?? '' }}" class="input col-span-2" disabled>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Guests</label>
                <input type="number" min="1" name="guests" value="{{ old('guests',$r->guests) }}" class="input col-span-2" required>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Time</label>
                <input type="time" name="time" value="{{ old('time', \Carbon\Carbon::parse($r->time)->format('H:i')) }}" class="input col-span-2" required>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Setup color</label>
                <input name="setup_color" value="{{ old('setup_color',$r->setup_color) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Event type</label>
                @php $eventOpts = ['Birthday','Wedding','Corporate','Graduation','Holiday','Other']; @endphp
                <select name="event_type" class="input col-span-2" aria-label="Select event type">
                  <option value="">Select…</option>
                  @foreach ($eventOpts as $opt)
                    <option value="{{ $opt }}" {{ old('event_type', $r->event_type) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                  @endforeach
                </select>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Stairs</label>
                <select name="stairs" class="input col-span-2" aria-label="Stairs required">
                  <option value="0" {{ old('stairs',$r->stairs) ? '' : 'selected' }}>No</option>
                  <option value="1" {{ old('stairs',$r->stairs) ? 'selected' : '' }}>Yes</option>
                </select>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">How did you hear about us?</label>
                @php $heardOpts = ['Returning customer','Instagram','Facebook','TikTok','Yelp','Google','Friend/Family','Other']; @endphp
                <select name="heard_about" class="input col-span-2" aria-label="How customer found us">
                  <option value="">Select…</option>
                  @foreach ($heardOpts as $opt)
                    <option value="{{ $opt }}" {{ old('heard_about',$r->heard_about) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                  @endforeach
                </select>
              </div>
              <div class="grid grid-cols-3 gap-2 items-start">
                <label class="font-semibold text-sm">Notes</label>
                <textarea name="notes" rows="3" class="input col-span-2">{{ old('notes',$r->notes) }}</textarea>
              </div>
              <div class="grid grid-cols-3 gap-2 items-start">
                <label class="font-semibold text-sm">Event markers</label>
                <div class="col-span-2">
                  <div class="marker-picker">
                    @foreach($eventMarkerOptions as $markerKey => $marker)
                      <label class="marker-toggle">
                        <input type="checkbox" name="event_markers[]" value="{{ $markerKey }}" {{ in_array($markerKey, $selectedEventMarkers, true) ? 'checked' : '' }}>
                        <span>
                          <span class="marker-icon">{{ $marker['icon'] }}</span>
                          <span>{{ $marker['label'] }}</span>
                        </span>
                      </label>
                    @endforeach
                  </div>
                  <div class="marker-help">Shown in Calendar before the customer name for quick visual scanning.</div>
                </div>
              </div>
            </div>
          </div>

        </form>
      </div>
    </div>

    <!-- Details Summary -->
    <div class="summary-kv">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-0">
      <!-- Left column -->
      <div class="card">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4">Customer</h3>
          <div class="space-y-2 text-sm">
            <div><strong>Customer name:</strong> {{ $r->customer_name ?? '—' }}</div>
            <div><strong>Company:</strong> {{ $r->company ?? '—' }}</div>
            <div><strong>Date:</strong> {{ $r->date?->format('m/d/Y') ?? '—' }}</div>
            <div><strong>Phone:</strong> {{ $r->phone ?? '—' }}</div>
            <div><strong>Email:</strong> {{ $r->email ?? '—' }}</div>
            <div><strong>Address:</strong> {{ $r->address ?? '—' }}</div>
            <div><strong>City:</strong> {{ $r->city ?? '—' }}</div>
            <div><strong>ZIP:</strong> {{ $r->zip_code ?? '—' }}</div>
          </div>
        </div>
      </div>
      <!-- Right column -->
      <div class="card">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4">Event</h3>
          <div class="space-y-2 text-sm">
            <div><strong>Invoice #:</strong> {{ $r->invoice_number ?? '—' }}</div>
            <div><strong>Guests:</strong> {{ $r->guests ?? '—' }}</div>
            <div><strong>Time:</strong> {{ \Carbon\Carbon::parse($r->time)->format('g:i A') ?? '—' }}</div>
            <div><strong>Setup color:</strong> {{ $r->setup_color ?? '—' }}</div>
            <div><strong>Event type:</strong> {{ $r->event_type ?? '—' }}</div>
            <div><strong>Stairs:</strong> {{ $r->stairs ? 'Yes' : 'No' }}</div>
            <div><strong>How did you hear about us:</strong> {{ $r->heard_about ?? '—' }}</div>
            <div><strong>Notes:</strong> {{ $r->notes ?? '—' }}</div>
          </div>
        </div>
      </div>
    </div>
    </div>

    </div>
    <aside class="resv-side">

    

    <!-- Items Section -->
    <div class="card section-card items-card" x-data="itemsManager(@js([
      'menuOptions' => $lineMenuOptions ?? [],
      'items' => $lineItems ?? [],
      'totals' => $lineTotals ?? [],
      'urls' => [
        'add' => route('admin.reservations.items.add', ['id' => $r->id]),
        'update' => route('admin.reservations.items.update', ['id' => $r->id]),
        'delete' => route('admin.reservations.items.delete', ['id' => $r->id, 'itemId' => '__ITEM__']),
      ],
    ]))">
      <div class="p-6">
        <div class="line-items-header">
          <h3 class="line-items-title">Line items</h3>
          <button type="button" class="line-items-collapse" title="Collapse line items" aria-label="Collapse line items">
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4" aria-hidden="true"><path d="M4 12l6-6 6 6"/></svg>
          </button>
        </div>
        <form method="post" action="{{ route('admin.reservations.items.update',['id'=>$r->id]) }}" id="itemsUpdateForm">
          @csrf
        </form>
        <div class="overflow-x-auto" x-show="items.length" x-cloak>
          <table class="line-items-table">
            <thead>
              <tr>
                <th class="w-8"></th>
                <th class="text-left">Item</th>
                <th class="text-left">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
                <th class="w-12"></th>
              </tr>
            </thead>
            <tbody>
              <template x-for="item in items" :key="item.id">
                <tr :class="{ 'opacity-60': item.saving }">
                  <td class="text-center"><span class="line-item-grip">⁙</span></td>
                  <td>
                    <div class="line-item-name" x-text="item.name"></div>
                    <input type="text"
                           x-model="item.description"
                           @change="saveItem(item)"
                           class="input line-item-desc-input mt-1"
                           placeholder="Regular"
                           aria-label="Item description">
                  </td>
                  <td>
                    <input type="number"
                           min="1"
                           x-model.number="item.qty"
                           @input="normalizeItem(item); recalcLocalTotals()"
                           @change="saveItem(item)"
                           class="line-item-qty"
                           :aria-label="'Quantity for ' + item.name">
                  </td>
                  <td class="text-right">
                    <input type="number"
                           min="0"
                           step="0.01"
                           x-model="item.price"
                           @input="normalizeItem(item); recalcLocalTotals()"
                           @change="saveItem(item)"
                           class="line-item-price-input"
                           :aria-label="'Price for ' + item.name">
                  </td>
                  <td class="line-item-money" x-text="fmt(rowTotal(item))"></td>
                  <td class="text-right">
                    <button class="icon-btn danger" type="button" @click="deleteLineItem(item)" :disabled="item.saving" :title="'Delete ' + item.name" :aria-label="'Delete ' + item.name">
                      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z"/></svg>
                    </button>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
        <div class="line-item-empty" x-show="!items.length" x-cloak>No line items recorded.</div>

        <div class="line-item-search-wrap" @click.outside="closeSearch()">
          <div class="line-item-search" :class="{ 'active': searchOpen }" @click="focusSearch()">
            <span class="line-item-plus">+</span>
            <input type="text"
                   x-ref="searchInput"
                   x-model="searchQuery"
                   @focus="openSearch()"
                   @input="openSearch()"
                   @keydown.enter.prevent="chooseFirstResult()"
                   @keydown.escape.prevent="closeSearch()"
                   class="line-item-search-field"
                   placeholder="Add an item"
                   aria-label="Add an item">
            <span class="line-item-info" title="Search menu items or add a one-time item">i</span>
          </div>

          <div x-show="searchOpen" x-cloak class="line-item-dropdown">
            <template x-if="showOneTimeOption()">
              <button type="button" class="line-item-option line-item-one-time" @click="addOneTimeItem()">
                <span class="line-item-code">+</span>
                <span>
                  <span class="line-item-option-name">Use one-time item: "<span x-text="searchQuery.trim()"></span>"</span>
                </span>
                <span class="line-item-return">↵</span>
              </button>
            </template>
            <template x-for="group in groupedMenuItems()" :key="group.category">
              <div>
                <div class="line-item-category" x-text="group.category"></div>
                <template x-for="item in group.items" :key="item.key">
                  <button type="button" class="line-item-option" @click="selectMenuItem(item)">
                    <span class="line-item-code" x-text="item.code"></span>
                    <span>
                      <span class="line-item-option-name" x-text="item.name"></span>
                      <span class="line-item-option-cat" x-text="item.cat || 'Menu item'"></span>
                    </span>
                    <span class="line-item-money" x-text="fmt(item.price)"></span>
                  </button>
                </template>
              </div>
            </template>
            <template x-if="filteredMenuItems().length === 0 && !showOneTimeOption()">
              <div class="px-3 py-2 text-sm text-gray-500">No menu items found.</div>
            </template>
          </div>
        </div>

        <div class="mt-5">
          <div class="totals-card text-sm text-gray-700 space-y-1">
            <div class="flex items-center justify-end mb-2">
              <button type="button"
                      class="adj-plus"
                      :disabled="adjustments.length>=2 || saving"
                      title="Add custom adjustment"
                      aria-label="Add custom adjustment"
                      @click="addAdjustment()">+</button>
            </div>
            <div class="totals-row"><strong>Subtotal</strong> <span x-text="fmt(totals.subtotal)"></span></div>
            <div class="totals-row"><strong>Travel fee</strong> <span x-text="fmt(totals.travel)"></span></div>
            <template x-for="(row,idx) in adjustments" :key="idx">
              <div class="flex items-center gap-2 justify-end" style="margin:4px 0">
                <input class="input" type="text" x-model="row.label" @change="saveAdjustments()" placeholder="Adjustment" aria-label="Adjustment label" style="width:160px;padding:4px 8px;font-size:13px;height:28px">
                <input class="input" type="number" step="0.01" x-model="row.amount" @input="recalcLocalTotals()" @change="saveAdjustments()" aria-label="Adjustment amount" style="width:120px;text-align:right;padding:4px 8px;font-size:13px;height:28px">
                <button type="button" class="adj-remove" title="Remove" aria-label="Remove adjustment" @click="removeAdjustment(idx)">×</button>
              </div>
            </template>
            <div class="totals-row"><strong>Gratuity</strong> <span x-text="fmt(totals.gratuity)"></span></div>
            <div class="totals-row"><strong>Tax</strong> <span x-text="fmt(totals.tax)"></span></div>
            <div class="totals-row total"><strong>Total</strong> <span x-text="fmt(totals.total)"></span></div>
            <div class="totals-row"><strong>Paid</strong> <span x-text="fmt(totals.paid_total)"></span></div>
            <div class="totals-row balance"><strong>Balance</strong> <span x-text="fmt(totals.balance)"></span></div>
          </div>
        </div>

        <script>
          function itemsManager(config = {}) {
            return {
              menuOptions: Array.isArray(config.menuOptions) ? config.menuOptions : [],
              items: Array.isArray(config.items) ? config.items : [],
              totals: Object.assign({
                subtotal: 0,
                travel: 0,
                gratuity: 0,
                tax: 0,
                total: 0,
                paid_total: 0,
                balance: 0,
                tax_rate: 10.25,
                adjustments: [],
              }, config.totals || {}),
              adjustments: Array.isArray(config.totals?.adjustments)
                ? config.totals.adjustments.map(row => ({
                    label: row.label || 'Adjustment',
                    amount: Number(row.amount || 0),
                  }))
                : [],
              urls: config.urls || {},
              searchQuery: '',
              searchOpen: false,
              saving: false,

              fmt(n) {
                return '$' + Number(n || 0).toFixed(2);
              },

              csrf() {
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
              },

              normalizeText(value) {
                return String(value || '').trim().toLowerCase();
              },

              openSearch() {
                this.searchOpen = true;
              },

              closeSearch() {
                this.searchOpen = false;
              },

              focusSearch() {
                this.openSearch();
                this.$nextTick(() => this.$refs.searchInput?.focus());
              },

              filteredMenuItems() {
                const query = this.normalizeText(this.searchQuery);
                const items = Array.isArray(this.menuOptions) ? this.menuOptions : [];

                if (!query) {
                  return items;
                }

                return items
                  .filter(item => {
                    const name = this.normalizeText(item.name);
                    const cat = this.normalizeText(item.cat);
                    return name.includes(query) || cat.includes(query);
                  });
              },

              groupedMenuItems() {
                const groups = [];
                const lookup = new Map();

                this.filteredMenuItems().forEach(item => {
                  const category = String(item.cat || item.category || 'Uncategorized').trim() || 'Uncategorized';
                  if (!lookup.has(category)) {
                    const group = { category, items: [] };
                    lookup.set(category, group);
                    groups.push(group);
                  }

                  lookup.get(category).items.push(item);
                });

                return groups;
              },

              showOneTimeOption() {
                const query = this.searchQuery.trim();
                if (!query) return false;

                return this.filteredMenuItems().length === 0;
              },

              chooseFirstResult() {
                const first = this.filteredMenuItems()[0];
                if (first) {
                  this.selectMenuItem(first);
                  return;
                }

                if (this.showOneTimeOption()) {
                  this.addOneTimeItem();
                }
              },

              async selectMenuItem(item) {
                await this.addMenuItem(item);
              },

              async addOneTimeItem() {
                const name = this.searchQuery.trim();
                if (!name) return;

                await this.addCustomItem({
                  custom_name: name,
                  qty: 1,
                  custom_price: 0,
                  description: '',
                });
              },

              resetSearch() {
                this.searchQuery = '';
                this.closeSearch();
                this.$nextTick(() => this.$refs.searchInput?.focus());
              },

              rowTotal(row) {
                return Number(row.qty || 0) * Number(row.price || 0);
              },

              normalizeItem(item) {
                item.qty = Math.max(1, Number.parseInt(item.qty || 1, 10));
                item.price = Math.max(0, Number.parseFloat(item.price || 0));
                item.total = this.rowTotal(item);
              },

              recalcLocalTotals() {
                const subtotal = this.items.reduce((sum, item) => {
                  this.normalizeItem(item);
                  return sum + this.rowTotal(item);
                }, 0);
                const adjustmentsSum = this.adjustments.reduce((sum, row) => sum + Number(row.amount || 0), 0);
                const travel = Number(this.totals.travel || 0);
                const paid = Number(this.totals.paid_total || 0);
                const taxRate = Number(this.totals.tax_rate || 10.25);
                const gratuity = Math.round(subtotal * 0.18 * 100) / 100;
                // California catering tax: taxable base includes food/items subtotal, travel fee,
                // and mandatory gratuity/service charge. Voluntary tips are excluded.
                const taxableBase = Math.max(0, subtotal + travel + gratuity + adjustmentsSum);
                const tax = Math.round(Math.round(taxableBase * 100) * (taxRate / 100)) / 100;
                const total = Math.round((subtotal + travel + gratuity + tax + adjustmentsSum) * 100) / 100;

                this.totals.subtotal = Math.round(subtotal * 100) / 100;
                this.totals.gratuity = gratuity;
                this.totals.tax = tax;
                this.totals.total = total;
                this.totals.balance = Math.max(0, Math.round((total - paid) * 100) / 100);
              },

              applyPayload(payload) {
                if (!payload || payload.ok === false) return;
                if (Array.isArray(payload.items)) {
                  this.items = payload.items;
                }
                if (payload.totals) {
                  this.totals = Object.assign(this.totals, payload.totals);
                  this.adjustments = Array.isArray(payload.totals.adjustments)
                    ? payload.totals.adjustments.map(row => ({
                        label: row.label || 'Adjustment',
                        amount: Number(row.amount || 0),
                      }))
                    : [];
                }
              },

              appendAdjustments(formData) {
                this.adjustments.forEach(row => {
                  const label = String(row.label || '').trim();
                  const amount = Number(row.amount || 0);
                  if (label === '' && Math.abs(amount) < 0.005) return;
                  formData.append('adj_label[]', label || 'Adjustment');
                  formData.append('adj_amount[]', amount);
                });
              },

              async postForm(url, formData) {
                const response = await fetch(url, {
                  method: 'POST',
                  headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                  },
                  body: formData,
                });

                if (!response.ok) {
                  throw new Error('Request failed');
                }

                return response.json();
              },

              async addMenuItem(item) {
                const formData = new FormData();
                formData.append('_token', this.csrf());
                formData.append('menu_key', item.key);
                formData.append('description', '');
                formData.append('qty', 1);

                this.saving = true;
                try {
                  const payload = await this.postForm(this.urls.add, formData);
                  this.applyPayload(payload);
                  this.resetSearch();
                } catch (error) {
                  console.error('Error adding menu item:', error);
                  alert('Failed to add item. Please try again.');
                } finally {
                  this.saving = false;
                }
              },

              async addCustomItem(row) {
                const formData = new FormData();
                formData.append('_token', this.csrf());
                formData.append('custom_name', row.custom_name);
                formData.append('description', row.description || '');
                formData.append('qty', row.qty);
                formData.append('custom_price', row.custom_price);

                this.saving = true;
                try {
                  const payload = await this.postForm(this.urls.add, formData);
                  this.applyPayload(payload);
                  this.resetSearch();
                } catch (error) {
                  console.error('Error adding one-time item:', error);
                  alert('Failed to add one-time item. Please try again.');
                } finally {
                  this.saving = false;
                }
              },

              async saveItem(item) {
                this.normalizeItem(item);
                const formData = new FormData();
                formData.append('_token', this.csrf());
                formData.append(`items[${item.id}]`, item.qty);
                formData.append(`desc[${item.id}]`, item.description || '');
                formData.append(`prices[${item.id}]`, item.price);
                this.appendAdjustments(formData);

                item.saving = true;
                try {
                  const payload = await this.postForm(this.urls.update, formData);
                  this.applyPayload(payload);
                } catch (error) {
                  console.error('Error saving item:', error);
                  alert('Failed to save item. Please try again.');
                } finally {
                  item.saving = false;
                }
              },

              async deleteLineItem(item) {
                if (!confirm('Delete this item?')) return;

                item.saving = true;
                const formData = new FormData();
                formData.append('_token', this.csrf());

                try {
                  const payload = await this.postForm(String(this.urls.delete).replace('__ITEM__', item.id), formData);
                  this.applyPayload(payload);
                } catch (error) {
                  console.error('Error deleting item:', error);
                  alert('Failed to delete item. Please try again.');
                } finally {
                  item.saving = false;
                }
              },

              addAdjustment() {
                if (this.adjustments.length >= 2) return;
                this.adjustments.push({ label: 'Adjustment', amount: 0 });
                this.recalcLocalTotals();
                this.saveAdjustments();
              },

              removeAdjustment(index) {
                this.adjustments.splice(index, 1);
                this.recalcLocalTotals();
                this.saveAdjustments();
              },

              async saveAdjustments() {
                const formData = new FormData();
                formData.append('_token', this.csrf());
                this.appendAdjustments(formData);

                try {
                  const payload = await this.postForm(this.urls.update, formData);
                  this.applyPayload(payload);
                } catch (error) {
                  console.error('Error saving adjustments:', error);
                  alert('Failed to save adjustment. Please try again.');
                }
              }
            }
          }
        </script>
      </div>
    </div>

    <!-- Hidden Print Content (moved near end of body in print) -->
    <div id="printContent" class="hidden"></div>

    <!-- Hidden Print Menu-Only Content (moved near end of body in print) -->
    <div id="printMenuContent" class="hidden"></div>


    <!-- Payments Section -->
    <div class="card section-card payments-card" x-data="manualPayments({
        list: @js($r->manual_payments ?? []),
        basePaid: {{ number_format($adminBasePaidWithoutManual,2,'.','') }},
        total: {{ number_format($r->total ?? 0,2,'.','') }}
      })">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Payments</h3>
          <button type="button" class="adj-plus" :disabled="rows.length>=3" aria-label="Add manual payment" title="Add manual payment" @click="addRow()">+</button>
        </div>
        @php $pays = $r->payments ?? collect(); @endphp
        @if($pays && $pays->count())
          <div class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="text-left p-3 font-semibold text-sm">Date</th>
                  <th class="text-left p-3 font-semibold text-sm">Provider</th>
                  <th class="text-left p-3 font-semibold text-sm">Card</th>
                  <th class="text-left p-3 font-semibold text-sm">Status</th>
                  <th class="text-right p-3 font-semibold text-sm">Amount</th>
                  <th class="text-left p-3 font-semibold text-sm">Transaction</th>
                  <th class="text-left p-3 font-semibold text-sm">Actions</th>
                </tr>
              </thead>
              <tbody>
              @foreach($pays as $p)
                <tr class="border-b border-gray-100">
                  <td class="p-3 text-sm">{{ $p->created_at?->format('m/d/Y H:i') }}</td>
                  <td class="p-3 text-sm">{{ strtoupper($p->provider) }}</td>
                  <td class="p-3 text-sm">
                    @php
                      $brand = strtoupper((string)($p->card_brand ?? ''));
                      $last4 = (string)($p->card_last4 ?? '');
                    @endphp
                    @if($brand && $last4)
                      {{ $brand }} •••• {{ $last4 }}
                    @else
                      —
                    @endif
                  </td>
                  <td class="p-3 text-sm">{{ ucfirst($p->status) }}</td>
                  <td class="p-3 text-right text-sm">{{ $fmt($p->amount ?? 0) }}</td>
                  <td class="p-3 text-gray-500 text-sm">{{ $p->transaction_id ?? '' }}</td>
                  <td class="p-3 text-sm"></td>
                </tr>
              @endforeach
              <!-- Manual payment rows -->
              <template x-for="(row, idx) in rows" :key="row.id || idx">
                <tr class="border-b border-gray-100">
                  <td class="p-2 text-sm"><input type="datetime-local" class="input" x-model="row.date" style="padding:6px 8px;font-size:13px;height:30px"></td>
                  <td class="p-2 text-sm">
                    <select class="input" x-model="row.provider" style="padding:6px 8px;font-size:13px;height:30px">
                      <template x-for="opt in providers"><option :value="opt" x-text="opt"></option></template>
                    </select>
                  </td>
                  <td class="p-2 text-sm"><input type="text" class="input" x-model="row.ref" placeholder="Last4 / Ref" style="padding:6px 8px;font-size:13px;height:30px"></td>
                  <td class="p-2 text-sm">
                    <select class="input" x-model="row.status" style="padding:6px 8px;font-size:13px;height:30px">
                      <template x-for="s in statuses"><option :value="s" x-text="s"></option></template>
                    </select>
                  </td>
                  <td class="p-2 text-sm" style="text-align:right"><input type="number" step="0.01" min="0.01" class="input" x-model.number="row.amount" @blur="formatAmount(idx)" style="padding:6px 8px;font-size:13px;height:30px;text-align:right;width:120px"></td>
                  <td class="p-2 text-sm"><input type="text" class="input" x-model="row.transaction_id" placeholder="Txn id (optional)" style="padding:6px 8px;font-size:13px;height:30px"></td>
                  <td class="p-2 text-sm">
                    <div class="flex items-center gap-2">
                      <button type="button" class="btn secondary" @click="save(idx)" style="padding:6px 10px">💾 Save</button>
                      <button type="button" class="icon-btn danger" @click="remove(idx)" title="Delete" aria-label="Delete" style="width:30px;height:30px">✕</button>
                    </div>
                    <div class="text-red-600 text-xs mt-1" x-text="row.error"></div>
                  </td>
                </tr>
              </template>
              </tbody>
            </table>
          </div>
        @else
          <p class="text-gray-500 text-sm">No payments recorded.</p>
        @endif
        </div>
      </div>
    </div>
    </aside>
    </div>

  <style>
    @media print {
      /* Hide only direct children of body by default */
      body > * { display: none !important; }

      /* Default print block visible */
      body:not(.print-menu) > #printContent { display: block !important; }

      /* Menu-only mode: show menu block */
      body.print-menu > #printMenuContent { display: block !important; }
      /* Override preview min-height on print to prevent blank pages in Safari */
      body.print-menu #printMenuContent { min-height: auto !important; }
      /* Avoid blank trailing page for menu print */
      @page { margin: 12mm; }
      body.print-menu #printMenuContent, 
      body.print-menu #printMenuContent .wrap { page-break-after: avoid; page-break-before: avoid; }
      body.print-menu #printMenuContent .box { page-break-inside: avoid; }
      
      
      .print-container {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        line-height: 1.4;
      }
      
      .print-title {
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #333;
        padding-bottom: 10px;
      }
      
      .print-section {
        margin-bottom: 25px;
        page-break-inside: avoid;
      }
      
      .print-section h2 {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #333;
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
      }
      
      .print-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 20px;
        font-size: 14px;
      }
      
      .print-grid div {
        padding: 4px 0;
      }
      
      .print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin-top: 10px;
      }
      
      .print-table th,
      .print-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
      }
      
      .print-table th {
        background-color: #f5f5f5;
        font-weight: bold;
      }
      
      .print-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
      }
      
      strong {
        font-weight: bold;
      }
    }
    /* Invoice-like styles scoped to menu print block */
    @media print {
      #printMenuContent .wrap{max-width:900px;margin:0 auto}
      #printMenuContent .brand{display:flex;align-items:center;gap:12px;margin-bottom:12px}
      #printMenuContent .brand img{height:48px;width:auto}
      #printMenuContent .muted{color:#6b7280}
      #printMenuContent .box{border:1px solid #e5e7eb;border-radius:12px;padding:12px;margin-top:10px}
      #printMenuContent .kv{font-size:13px}
      #printMenuContent .kv-row{display:flex;align-items:baseline;justify-content:flex-start;gap:6px;padding:2px 0}
      #printMenuContent .kv-label{color:#374151}
      #printMenuContent .kv-label::after{content: ":"; margin: 0 6px 0 2px}
      #printMenuContent .kv-val{text-align:left}
      #printMenuContent table{width:100%;border-collapse:collapse}
      #printMenuContent th,#printMenuContent td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:14px}
      #printMenuContent th{text-align:left}
      #printMenuContent .right{text-align:right}
      #printMenuContent .two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    }
    /* Screen preview styles for menu print block (mirror invoice styles) */
    body.show-menu-preview{background:#fff !important}
    body.show-menu-preview #printMenuContent{background:#fff;min-height:100vh}
    body.show-menu-preview #printMenuContent .wrap{max-width:900px;margin:0 auto;padding:24px}
    body.show-menu-preview #printMenuContent .brand{display:flex;align-items:center;gap:12px;margin-bottom:12px}
    body.show-menu-preview #printMenuContent .brand img{height:48px;width:auto}
    body.show-menu-preview #printMenuContent .muted{color:#6b7280}
    body.show-menu-preview #printMenuContent .box{border:1px solid #e5e7eb;border-radius:12px;padding:12px;margin-top:10px}
    body.show-menu-preview #printMenuContent .kv{font-size:13px}
    body.show-menu-preview #printMenuContent .kv-row{display:flex;align-items:baseline;justify-content:flex-start;gap:6px;padding:2px 0}
    body.show-menu-preview #printMenuContent .kv-label{color:#374151}
    body.show-menu-preview #printMenuContent .kv-label::after{content: ":"; margin: 0 6px 0 2px}
    body.show-menu-preview #printMenuContent .kv-val{text-align:left}
    body.show-menu-preview #printMenuContent table{width:100%;border-collapse:collapse}
    body.show-menu-preview #printMenuContent th, 
    body.show-menu-preview #printMenuContent td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:14px}
    body.show-menu-preview #printMenuContent th{text-align:left}
    body.show-menu-preview #printMenuContent .right{text-align:right}
    body.show-menu-preview #printMenuContent .two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    /* Screen preview: show only menu print block */
    body.show-menu-preview > * { display: none !important; }
    body.show-menu-preview > #printMenuContent { display: block !important; }
    @media print { .no-print { display: none !important; } }
  </style>

  <!-- Hidden Print Content (root-level) -->
  <div id="printContent" class="hidden">
    <div class="print-container">
      <h1 class="print-title">Reservation Details</h1>
      
      <div class="print-section">
        <h2>Customer Information</h2>
        <div class="print-grid">
          <div><strong>Invoice #:</strong> {{ $r->invoice_number ?? '—' }}</div>
          <div><strong>Customer name:</strong> {{ $r->customer_name ?? '—' }}</div>
          <div><strong>Phone:</strong> {{ $r->phone ?? '—' }}</div>
          <div><strong>Email:</strong> {{ $r->email ?? '—' }}</div>
          <div><strong>Company:</strong> {{ $r->company ?? '—' }}</div>
          <div><strong>Address:</strong> {{ $r->address ?? '—' }}</div>
          <div><strong>City:</strong> {{ $r->city ?? '—' }}</div>
          <div><strong>ZIP:</strong> {{ $r->zip_code ?? '—' }}</div>
        </div>
      </div>

      <div class="print-section">
        <h2>Event Details</h2>
        <div class="print-grid">
          <div><strong>Date:</strong> {{ $r->date?->format('m/d/Y') ?? '—' }}</div>
          <div><strong>Time:</strong> {{ substr((string)$r->time,0,5) ?? '—' }}</div>
          <div><strong>Guests:</strong> {{ $r->guests ?? '—' }}</div>
          <div><strong>Event type:</strong> {{ $r->event_type ?? '—' }}</div>
          <div><strong>Setup color:</strong> {{ $r->setup_color ?? '—' }}</div>
          <div><strong>Stairs:</strong> {{ $r->stairs ? 'Yes' : 'No' }}</div>
          <div><strong>How did you hear about us:</strong> {{ $r->heard_about ?? '—' }}</div>
          <div><strong>Notes:</strong> {{ $r->notes ?? '—' }}</div>
        </div>
      </div>

      @php $its = $r->items ?? collect(); @endphp
      @if($its && $its->count())
      <div class="print-section">
        <h2>Items</h2>
        <table class="print-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Description</th>
              <th>Qty</th>
              <th>Unit Price</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($its as $it)
            <tr>
              <td>{{ $it->name_snapshot ?? '—' }}</td>
              <td>{{ $it->description ?? '—' }}</td>
              <td>{{ $it->qty ?? 0 }}</td>
              <td>{{ $fmt($it->unit_price_snapshot ?? 0) }}</td>
              <td>{{ $fmt($it->line_total ?? 0) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  <!-- Hidden Print Menu-Only Content (root-level) -->
  <div id="printMenuContent" class="hidden">
    <div class="wrap">
      @php $backUrl = request('back') ?? url()->previous() ?? route('admin.reservations'); @endphp
      <div class="no-print" style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:8px">
        <a href="{{ $backUrl }}" class="btn" style="background:#4b5563">Back</a>
        <button class="btn" onclick="document.body.classList.remove('show-menu-preview'); document.body.classList.add('print-menu'); window.print(); setTimeout(()=>{document.body.classList.remove('print-menu'); document.body.classList.add('show-menu-preview');}, 400)">Print</button>
      </div>
      <div class="brand">
        <img src="/assets/brand/logo.png" alt="Hibachi Catering" onerror="this.style.display='none'">
        <div>
          <div style="font-weight:700">Hibachi Catering</div>
          <div class="muted">9022 Pulsar Ct, Corona, CA 92883</div>
          <div class="muted">
            Email: <a href="mailto:info@hibachicater.com" style="color:inherit">info@hibachicater.com</a>
            &nbsp;|&nbsp; Phone: <a href="tel:+19513269602" style="color:inherit">951-326-9602</a>
            &nbsp;|&nbsp; <a href="https://hibachicater.com" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline">hibachicater.com</a>
          </div>
        </div>
      </div>

      @php $dateFmt = $r->date?->format('m/d/Y'); @endphp
      <div class="head" style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;position:relative">
        <div></div>
        <div class="title-center" style="position:absolute;left:50%;top:0;transform:translateX(-50%);text-align:center;width:auto">
          <h2 style="margin:0;font-size:18px">Invoice #{{ $r->invoice_number ?? ($r->code ?? ('#'.$r->id)) }}
            @if($dateFmt)
              <span style="color:#b21e27"> · {{ $dateFmt }}</span>
            @endif
          </h2>
        </div>
      </div>

      <div class="two-col">
        <div class="box">
          <h3 style="margin:0 0 6px; font-size:14px">Customer</h3>
          <div class="kv">
            <div class="kv-row"><div class="kv-label">Name</div><div class="kv-val">{{ $r->customer_name ?? '—' }}</div></div>
            @if(!empty($r->company))
              <div class="kv-row"><div class="kv-label">Company</div><div class="kv-val">{{ $r->company }}</div></div>
            @endif
            <div class="kv-row"><div class="kv-label">Date</div><div class="kv-val">{{ $r->date?->format('m/d/Y') ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Address</div><div class="kv-val">{{ trim(($r->address ?? '')) ?: '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">City</div><div class="kv-val">{{ $r->city ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">ZIP</div><div class="kv-val">{{ $r->zip_code ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Email</div><div class="kv-val">{{ $r->email ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Phone</div><div class="kv-val">{{ $r->phone ?? '—' }}</div></div>
          </div>
        </div>

        <div class="box">
          <h3 style="margin:0 0 6px; font-size:14px">Event</h3>
          <div class="kv">
            <div class="kv-row"><div class="kv-label">Invoice #</div><div class="kv-val">{{ $r->invoice_number ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Guests</div><div class="kv-val">{{ $r->guests ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Time</div><div class="kv-val">{{ \Carbon\Carbon::parse($r->time)->format('g:i A') }}</div></div>
            <div class="kv-row"><div class="kv-label">Setup color</div><div class="kv-val">{{ $r->setup_color ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Event type</div><div class="kv-val">{{ $r->event_type ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Stairs</div><div class="kv-val">{{ $r->stairs ? 'Yes' : 'No' }}</div></div>
            <div class="kv-row"><div class="kv-label">How did you hear</div><div class="kv-val">{{ $r->heard_about ?? '—' }}</div></div>
            @if($r->notes)
              <div class="kv-row"><div class="kv-label">Notes</div><div class="kv-val" style="max-width:60ch;text-align:left">{{ $r->notes }}</div></div>
            @endif
          </div>
        </div>
      </div>

      <div class="box">
        <h3 style="margin:0 0 6px; font-size:14px">Menu</h3>
        @php $items = $r->items ?? collect(); @endphp
        <table>
          <thead><tr><th>Item</th><th>Description</th><th class="right">Qty</th></tr></thead>
          <tbody>
            @forelse($items as $it)
              <tr>
                <td>
                  <div>{{ $it->name_snapshot }}</div>
                </td>
                <td style="color:#6b7280;font-size:12px">{{ $it->description }}</td>
                <td class="right">{{ $it->qty }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="muted">No items</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    function syncStatusPill(el){
      if (!el) return;
      el.classList.remove('status-confirmed','status-pending','status-canceled');
      if (el.value === 'confirmed') el.classList.add('status-confirmed');
      else if (el.value === 'canceled') el.classList.add('status-canceled');
      else el.classList.add('status-pending');
    }
    window.addEventListener('DOMContentLoaded', function(){
      const statusSelect = document.querySelector('select[name="status"][form="resvForm"]');
      if (statusSelect) syncStatusPill(statusSelect);
    });

    function printReservation() {
      window.print();
    }
    // Preview menu print (no auto-print)
    (function(){
      try {
        var params = new URLSearchParams(window.location.search);
        if (params.get('print') === 'menu') {
          document.body.classList.add('show-menu-preview');
          var el = document.getElementById('printMenuContent');
          if (el) el.classList.remove('hidden');
        }
      } catch (e) {}
    })();
  </script>
  <script>
    function colorPicker(initial){
      return {
        open:false,
        color: initial || '#6b7280',
        colors: ['#ef4444','#f97316','#f59e0b','#22c55e','#10b981','#3b82f6','#6366f1','#a855f7','#0ea5e9','#6b7280','none'],
        toggle(){ this.open=!this.open; },
        async pick(c){
          this.color = (c && c!=='none') ? c : '#6b7280';
          this.open=false;
          try{
            const fd=new FormData();
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            if(c && c!=='none') fd.append('color', c); else fd.append('color','clear');
            const resp=await fetch('{{ route('admin.reservations.color',['id'=>$r->id]) }}',{method:'POST', body:fd});
            const js=await resp.json();
            if(!resp.ok||!js.ok){ alert(js.error||'Failed to save color'); return; }
            try { localStorage.setItem('resv_color_update', JSON.stringify({ id: {{ $r->id }}, color: js.color || null, ts: Date.now() })); } catch(e){}
          }catch(e){}
        }
      }
    }
  </script>
  <script>
    function colorPicker(initial){
      return {
        open:false,
        color: initial || '#6b7280',
        colors: ['#ef4444','#f97316','#f59e0b','#22c55e','#10b981','#3b82f6','#6366f1','#a855f7','#0ea5e9','#6b7280','none'],
        toggle(){ this.open=!this.open; },
        async pick(c){ this.color = (c && c!=='none') ? c : '#6b7280'; this.open=false; try{ const fd=new FormData(); fd.append('_token', document.querySelector('meta[name="csrf-token"]').content); if(c && c!=='none') fd.append('color', c); else fd.append('color','clear'); const resp=await fetch('{{ route('admin.reservations.color',['id'=>$r->id]) }}',{method:'POST', body:fd}); const js=await resp.json(); if(!resp.ok||!js.ok){ alert(js.error||'Failed to save color'); } }catch(e){} }
      }
    }
  </script>
  <script>
    function manualPayments(init){
      return {
        providers: ['Square','Zelle','Venmo','Paypal','Cashapp','Stripe','Cash','Check','Other'],
        statuses: ['Succeeded','Pending','Failed'],
        rows: (init.list||[]).slice(0,3).map(r=>({ id:r.id, date:r.date || new Date().toISOString().slice(0,16), provider:r.provider||'Square', ref:r.ref||'', status:r.status||'Succeeded', amount:Number(r.amount||0).toFixed(2), transaction_id:r.transaction_id||'', error:'' })),
        addRow(){ if (this.rows.length>=3) return; this.rows.unshift({id:'',date:new Date().toISOString().slice(0,16),provider:'Square',ref:'',status:'Succeeded',amount:'0.00',transaction_id:'',error:''}); },
        formatAmount(i){ const r=this.rows[i]; const n=parseFloat(r.amount||'0'); r.amount = isNaN(n)?'0.00':n.toFixed(2); },
        async save(i){ const r=this.rows[i]; r.error=''; const amt=parseFloat(r.amount||'0'); if(!(amt>0)){ r.error='Amount must be greater than 0'; return; }
          const fd = new FormData(); fd.append('_token', document.querySelector('meta[name="csrf-token"]').content); if(r.id) fd.append('id', r.id); fd.append('date', r.date); fd.append('provider', r.provider); fd.append('ref', r.ref); fd.append('status', r.status); fd.append('amount', parseFloat(r.amount||'0').toFixed(2)); if(r.transaction_id) fd.append('transaction', r.transaction_id);
          try { const resp = await fetch('{{ route('admin.reservations.payments.manual.save',['id'=>$r->id]) }}', { method:'POST', body: fd }); const json=await resp.json(); if(!resp.ok||!json.ok){ r.error=json.error||'Error saving payment'; return; }
            this.rows = (json.manual||[]).map(x=>({ id:x.id, date:x.date, provider:x.provider, ref:x.ref||'', status:x.status, amount:Number(x.amount||0).toFixed(2), transaction_id:x.transaction_id||'', error:'' }));
            if (window.adjustmentsSetPaidExtra) { window.adjustmentsSetPaidExtra(Number(json.manualPaid||0)); }
          } catch(e){ r.error='Network error'; }
        },
        async remove(i){ const r=this.rows[i]; r.error=''; if(!r.id){ this.rows.splice(i,1); return; }
          const fd = new FormData(); fd.append('_token', document.querySelector('meta[name="csrf-token"]').content); fd.append('id', r.id);
          try { const resp = await fetch('{{ route('admin.reservations.payments.manual.delete',['id'=>$r->id]) }}', { method:'POST', body: fd }); const json=await resp.json(); if(!resp.ok||!json.ok){ r.error=json.error||'Error deleting payment'; return; }
            this.rows = (json.manual||[]).map(x=>({ id:x.id, date:x.date, provider:x.provider, ref:x.ref||'', status:x.status, amount:Number(x.amount||0).toFixed(2), transaction_id:x.transaction_id||'', error:'' }));
            if (window.adjustmentsSetPaidExtra) { window.adjustmentsSetPaidExtra(Number(json.manualPaid||0)); }
          } catch(e){ r.error='Network error'; }
        }
      }
    }
    // Hook to update line-item totals after manual payment changes.
    window.adjustmentsSetPaidExtra = function(extra){ try { document.querySelectorAll('[x-data^="itemsManager"]').forEach(el=>{ const comp = Alpine.$data(el); comp.totals.paid_total = Number({{ number_format($adminBasePaidWithoutManual,2,'.','') }}) + Number(extra||0); comp.recalcLocalTotals(); }); } catch(e){} }
  </script>
</body>
</html>
