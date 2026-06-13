@extends('layouts.admin')

@section('title', 'Calendar')

@push('styles')
<style>
    /* Modern Google-style calendar surface */
    .container{width:100%;max-width:none;margin:0;padding:20px 24px}
    .cal-page-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:14px;padding:0 2px}
    .cal-page-title{font-size:26px;line-height:1.18;font-weight:750;letter-spacing:-.015em;color:#111827;margin:0}
    .cal-page-meta{margin-top:4px;color:#64748b;font-size:13px}
    .cal-wrap{background:#fff;border:1px solid #dfe3ea;border-radius:18px;overflow:hidden;box-shadow:0 14px 38px rgba(15,23,42,.06)}
    .cal-head{display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #e8edf3;background:linear-gradient(180deg,#fff 0%,#fbfcfe 100%)}
    .cal-title{font-size:21px;font-weight:720;letter-spacing:-.01em;color:#111827}
    .cal-controls{display:flex;gap:14px;align-items:center}
    .cal-nav{display:flex;gap:8px;align-items:center}
    .cal-nav .icon-btn{border-radius:50%;width:36px;height:36px;color:#475569;border-color:#dbe2ea;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.04)}
    .cal-nav .icon-btn:hover{box-shadow:0 5px 14px rgba(15,23,42,.10);background:#f8fafc;border-color:#cbd5e1;transform:translateY(-1px)}
    .cal-today-btn{background:#fff !important;color:#334155 !important;border:1px solid #dbe2ea !important;padding:9px 14px;border-radius:999px;font-size:13px;font-weight:750;box-shadow:0 1px 2px rgba(15,23,42,.04)}
    .cal-today-btn:hover{background:#f8fafc !important;border-color:#cbd5e1 !important;box-shadow:0 5px 14px rgba(15,23,42,.09)}
    .cal-view-select{min-height:36px;padding:8px 34px 8px 13px;border-radius:999px;font-size:13px;font-weight:650;border-color:#dbe2ea;background:#fff;color:#334155;box-shadow:0 1px 2px rgba(15,23,42,.04)}
    .cal-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));background:#eef2f7;border-top:0}
    .cal-dow{padding:11px 12px;background:#f8fafc;color:#64748b;font-weight:750;font-size:11px;letter-spacing:.04em;text-transform:uppercase;border-bottom:1px solid #dfe3ea}
    .cal-cell{min-height:148px;border-right:1px solid #e3e8ef;border-bottom:1px solid #e3e8ef;padding:32px 10px 10px;position:relative;background:#fff;transition:background .14s ease, box-shadow .14s ease}
    .cal-cell:nth-child(7n){border-right:none}
    .cal-cell:hover{background:#fbfdff;box-shadow:inset 0 0 0 1px rgba(59,130,246,.08)}
    .cal-date{position:absolute;top:10px;right:12px;display:inline-flex;align-items:center;justify-content:center;min-width:26px;height:26px;border-radius:999px;font-size:13px;font-weight:650;color:#334155}
    .cal-other{background:#f8fafc}
    .cal-other .cal-date{color:#94a3b8}
    .cal-today{background:linear-gradient(180deg,#eff6ff 0%,#f8fbff 100%);box-shadow:inset 0 0 0 1px rgba(37,99,235,.20)}
    .cal-today .cal-date{font-weight:850;color:#fff;background:#2563eb;box-shadow:0 5px 12px rgba(37,99,235,.22)}
    .calendar-event{display:block;border:1px solid transparent;border-left:4px solid var(--event-accent,#22c55e);border-radius:9px;padding:6px 8px;margin:4px 0;color:#111827;text-align:left;background:#fff;text-decoration:none;width:100%;box-shadow:0 2px 6px rgba(15,23,42,.06);cursor:pointer;transition:transform .14s ease, box-shadow .14s ease, border-color .14s ease, filter .14s ease}
    .calendar-event:hover{transform:translateY(-1px);box-shadow:0 8px 18px rgba(15,23,42,.12);border-color:rgba(148,163,184,.35);filter:saturate(1.03)}
    .calendar-event .event-top{display:flex;justify-content:space-between;align-items:center;gap:8px;min-width:0}
    .calendar-event .event-main{display:flex;align-items:center;gap:6px;min-width:0}
    .calendar-event .event-markers{display:inline-flex;align-items:center;gap:3px;flex-shrink:0}
    .calendar-event .event-marker{display:inline-flex;align-items:center;justify-content:center;min-width:14px;height:14px;font-size:11px;line-height:1}
    .calendar-event .event-marker.vip{font-size:8px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .calendar-event .event-client{font-size:13px;font-weight:750;color:#1f2937;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-transform:capitalize;min-width:0}
    .calendar-event .event-guests{font-size:11px;font-weight:800;color:#334155;line-height:1;white-space:nowrap;margin-left:8px;flex-shrink:0;padding:3px 7px;border-radius:999px;background:rgba(255,255,255,.86);border:1px solid rgba(148,163,184,.28);min-width:24px;text-align:center}
    .calendar-event .event-time{font-size:11px;font-weight:600;color:#64748b;line-height:1.1;margin-top:2px}
    .legend{display:flex;gap:10px;align-items:center;color:#64748b;font-size:12px;background:#fff;border:1px solid #e6ebf2;border-radius:999px;padding:7px 10px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
    .legend .dot{width:10px;height:10px;border-radius:999px;display:inline-block}
    .status-confirmed{background:#e9f8ef;border-left-color:var(--event-accent,#22c55e)}
    .status-pending{background:#fff6dc;border-left-color:var(--event-accent,#f59e0b)}
    .status-canceled{background:#fff0f0;border-left-color:var(--event-accent,#ef4444)}
    /* Position the standard icon button inside cells */
    .add-icon{position:absolute;top:10px;left:10px;z-index:2}
    .icon-btn.add-icon{width:24px;height:24px;font-size:15px;border-radius:999px;opacity:.08;transition:opacity .15s ease, transform .15s ease, background .15s ease, box-shadow .15s ease;border-color:#dbe2ea;background:#fff;color:#64748b}
    .icon-btn.add-icon:hover{background:#fff;color:#2563eb;box-shadow:0 5px 12px rgba(15,23,42,.10);transform:scale(1.04)}
    .cal-cell:hover .add-event-btn{opacity:1}
    /* Ensure icon buttons have no underline */
    .icon-btn, .icon-btn:hover, .icon-btn:focus, .icon-btn:visited { text-decoration: none; }

    /* Popover styles */
    .popover-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.16);backdrop-filter:blur(1px);opacity:0;pointer-events:none;transition:opacity .18s ease}
    .popover-backdrop.shown{opacity:1;pointer-events:auto}
    .event-pop{position:fixed;z-index:50;display:flex;flex-direction:column;background:#fff;border:1px solid #dfe6ef;border-radius:18px;box-shadow:0 22px 55px rgba(15,23,42,.18);width:628px;max-width:min(628px,calc(100vw - 18px));max-height:min(88vh,920px);overflow:hidden;opacity:0;transform:translateY(-6px);transition:opacity .18s ease, transform .18s ease}
    .event-pop.shown{opacity:1;transform:translateY(0)}
    .event-pop .head{display:flex;align-items:flex-start;justify-content:space-between;padding:14px 14px 12px;border-bottom:1px solid #edf1f5;background:linear-gradient(180deg,#fff 0%,#fbfcfe 100%);gap:12px}
    .event-pop .head-main{min-width:0;display:grid;gap:6px}
    .event-pop .title{font-size:15px;font-weight:800;margin:0;color:#111827;line-height:1.2;letter-spacing:-.01em;overflow-wrap:anywhere}
    .event-pop .title-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .event-pop .title-markers{display:inline-flex;align-items:center;gap:5px;flex-wrap:wrap}
    .event-pop .title-marker{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 6px;border-radius:999px;background:#fff;border:1px solid #e5e7eb;color:#0f172a;font-size:13px;font-weight:900;line-height:1}
    .event-pop .title-marker.vip{font-size:9px;letter-spacing:.08em;text-transform:uppercase;padding:0 7px}
    .event-pop .head-sub{font-size:11px;font-weight:800;color:#64748b;letter-spacing:.08em;text-transform:uppercase}
    .event-pop .icon-only{width:34px;height:34px;border-radius:10px;border:1px solid #dbe2ea;background:#fff;color:#374151;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .14s ease, box-shadow .14s ease}
    .event-pop .icon-only:hover{background:#f8fafc;box-shadow:0 4px 10px rgba(15,23,42,.08)}
    .event-pop .body{display:grid;gap:10px;padding:12px 14px;overflow:auto;flex:1 1 auto;min-height:0;overscroll-behavior:contain}
    .event-pop .body::-webkit-scrollbar{width:10px}
    .event-pop .body::-webkit-scrollbar-thumb{background:#d6deea;border-radius:999px;border:2px solid #fff}
    .pop-badges,.hero-pills{display:flex;gap:6px;flex-wrap:wrap}
    .badge{display:inline-flex;align-items:center;border:1px solid var(--border);border-radius:999px;padding:4px 8px;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;line-height:1}
    .badge.status-confirmed{background:#ecfdf5;border-color:#bbf7d0;color:#166534}
    .badge.status-pending{background:#fff7ed;border-color:#fed7aa;color:#c2410c}
    .badge.status-canceled{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
    .badge.status-default{background:#f8fafc;border-color:#e2e8f0;color:#475569}
    .badge.invoice-paid{background:#ecfdf5;border-color:#bbf7d0;color:#166534}
    .badge.invoice-pending{background:#fff7ed;border-color:#fed7aa;color:#c2410c}
    .badge.invoice-partial{background:#fff7ed;border-color:#fdba74;color:#c2410c}
    .badge.invoice-unpaid,.badge.invoice-overdue,.badge.invoice-canceled{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
    .badge.invoice-default{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
    .badge.balance-paid{background:#ecfdf5;border-color:#bbf7d0;color:#166534}
    .badge.balance-due{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
    .badge.balance-partial{background:#fff7ed;border-color:#fdba74;color:#c2410c}
    .hero-pill{display:grid;gap:2px;min-width:84px;padding:8px 10px;border:1px solid #e7ecf2;border-radius:12px;background:#f8fafc}
    .hero-pill-label{font-size:10px;font-weight:800;color:#64748b;letter-spacing:.08em;text-transform:uppercase}
    .hero-pill-value{font-size:13px;font-weight:800;color:#111827;line-height:1.1}
    .top-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.15fr);gap:10px;align-items:start}
    .pop-section{display:grid;gap:8px;padding:11px 12px;border:1px solid #e7ecf2;border-radius:14px;background:#fff}
    .pop-section.staff-section{gap:6px;padding:9px 10px}
    .pop-section-title{font-size:11px;font-weight:800;color:#64748b;letter-spacing:.1em;text-transform:uppercase}
    .detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 10px}
    .detail-grid.customer-stack{grid-template-columns:1fr}
    .detail-item{display:grid;gap:3px;min-width:0}
    .detail-item.full{grid-column:1 / -1}
    .detail-label{font-size:10px;font-weight:800;color:#64748b;letter-spacing:.08em;text-transform:uppercase}
    .detail-value{font-size:12px;font-weight:600;color:#1f2937;line-height:1.35;overflow-wrap:anywhere}
    .customer-name{font-size:14px;font-weight:800;color:#111827;line-height:1.2}
    .staff-grid{display:flex;flex-wrap:wrap;gap:8px}
    .staff-pill{display:grid;gap:2px;min-width:82px;max-width:140px;padding:6px 8px;border:1px solid #e7ecf2;border-radius:10px;background:#fbfcfe}
    .staff-pill.van{min-width:64px;max-width:90px}
    .staff-pill-label{font-size:10px;font-weight:800;color:#64748b;letter-spacing:.08em;text-transform:uppercase}
    .staff-pill-value{font-size:11px;font-weight:800;color:#111827;line-height:1.1;overflow-wrap:anywhere}
    .empty-note{font-size:12px;color:#64748b;line-height:1.5}
    .event-list{display:grid;gap:0}
    .event-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;align-items:center;padding:6px 9px;border:1px solid #edf1f5;border-radius:10px;background:#fbfcfe}
    .event-row-title{font-size:11px;font-weight:800;color:#111827;line-height:1.2}
    .event-row-meta{margin-top:1px;font-size:10px;color:#64748b;line-height:1.25}
    .event-row-value{font-size:11px;font-weight:800;color:#0f172a;white-space:nowrap}
    .event-row-value.qty{font-size:10px;color:#475569;background:#fff;border:1px solid #e2e8f0;border-radius:999px;padding:2px 6px;min-width:26px;text-align:center}
    .sum{display:grid;gap:6px}
    .sum .row{display:flex;justify-content:space-between;gap:12px;font-weight:600;font-size:12px;color:#334155}
    .sum .row.total{padding-top:8px;border-top:1px solid #e7ecf2;font-size:13px;font-weight:800;color:#0f172a}
    .finance-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
    .finance-card{display:grid;gap:5px;padding:9px 10px;border:1px solid #e7ecf2;border-radius:11px;background:#fbfcfe}
    .finance-card.is-paid{background:#f0fdf4;border-color:#bbf7d0}
    .finance-card.is-due{background:#fff7ed;border-color:#fed7aa}
    .finance-card.is-partial{background:#fff7ed;border-color:#fdba74}
    .finance-card.is-canceled{background:#fef2f2;border-color:#fecaca}
    .finance-label{font-size:10px;font-weight:800;color:#64748b;letter-spacing:.08em;text-transform:uppercase}
    .finance-value{font-size:12px;font-weight:800;color:#0f172a;line-height:1.15}
    .finance-value.due{color:#b91c1c}
    .finance-value.ok{color:#166534}
    .finance-badge{display:inline-flex;align-items:center;justify-content:flex-start;width:max-content;border-radius:999px;padding:4px 8px;border:1px solid transparent;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;line-height:1}
    .finance-badge.is-paid{background:#dcfce7;border-color:#bbf7d0;color:#166534}
    .finance-badge.is-due{background:#fee2e2;border-color:#fecaca;color:#b91c1c}
    .finance-badge.is-partial{background:#ffedd5;border-color:#fdba74;color:#c2410c}
    .finance-badge.is-canceled{background:#fee2e2;border-color:#fecaca;color:#b91c1c}
    .finance-badge.is-neutral{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
    .event-pop .foot{display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap;padding:10px 14px;border-top:1px solid #edf1f5;background:rgba(251,252,254,.98);backdrop-filter:blur(8px);flex:0 0 auto}
    .event-pop .foot .btn{min-height:34px;padding:7px 11px;font-size:12px}
    .event-pop .foot .btn.is-disabled{pointer-events:none;opacity:.55}
    /* Desktop arrow tip */
    .event-pop.tip:before{content:"";position:absolute;width:12px;height:12px;background:#fff;border:1px solid var(--border);border-right:none;border-bottom:none;transform:rotate(45deg);top:-6px;right:18px}

    @media (max-width: 768px){
      .container{padding:16px}
      .cal-page-head{flex-direction:column;align-items:flex-start}
      .cal-page-title{font-size:21px}
      .legend{border-radius:14px;align-items:flex-start;flex-wrap:wrap}
      .cal-head{flex-wrap:wrap;gap:12px;padding:14px}
      .cal-title{font-size:19px}
      .cal-controls{width:100%;justify-content:space-between;gap:10px}
      .cal-nav{gap:6px}
      .cal-grid{min-width:760px}
      .cal-cell{min-height:132px}
      .cal-dow{padding:9px 10px}
      .calendar-event{padding:5px 7px}
      .calendar-event .event-client{font-size:12px}
      .event-pop{width:min(540px,96vw);max-height:90vh;left:50%!important;top:50%!important;transform:translate(-50%,-50%)}
      .event-pop .head{padding:13px}
      .event-pop .body{padding:11px 13px}
      .event-pop .foot{padding:10px 13px;justify-content:stretch}
      .event-pop .foot .btn{flex:1 1 0;text-align:center;justify-content:center}
      .top-grid{grid-template-columns:1fr}
      .detail-grid{grid-template-columns:1fr}
      .finance-grid{grid-template-columns:1fr}
      .event-pop:before{display:none}
    }
  </style>
@endpush

@section('content')
  @php
    $view = $view ?? 'month';
    $dow = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $first = $start->copy();
    $last  = $end->copy();
    $cursor = $first->copy();
    if ($view==='month') {
      $titleLabel = $month->format('F Y');
      $prevKey = $month->copy()->subMonth()->format('Y-m');
      $nextKey = $month->copy()->addMonth()->format('Y-m');
    } elseif ($view==='week') {
      $titleLabel = $start->format('M j').' – '.$end->format('M j, Y');
      $prevKey = $start->copy()->subWeek()->toDateString();
      $nextKey = $start->copy()->addWeek()->toDateString();
    } else { // day
      $titleLabel = $start->toFormattedDateString();
      $prevKey = $start->copy()->subDay()->toDateString();
      $nextKey = $start->copy()->addDay()->toDateString();
    }
  @endphp
  <div class="container">
    <div class="cal-page-head">
      <div>
        <div class="cal-page-meta">Reservation schedule overview</div>
      </div>
      <div class="header" style="margin-bottom:0">
        <div class="legend" style="margin-right:auto">
        <span class="dot" style="background:#ecfdf5;border:1px solid #a7f3d0"></span> Confirmed
        <span class="dot" style="background:#fff7ed;border:1px solid #fed7aa"></span> Pending
        <span class="dot" style="background:#fef2f2;border:1px solid #fecaca"></span> Canceled
        </div>
        <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
          <a href="{{ route('admin.staff_bookings.step1', $view==='month' ? [] : ['date'=>$start->toDateString()]) }}" class="icon-btn" title="Add Event">+</a>
        </div>
      </div>
    </div>

    <div class="cal-wrap">
      <div class="cal-head">
        <div class="cal-title">{{ $titleLabel }}</div>
        <div class="cal-controls">
          <div class="cal-nav">
          @if($view==='month')
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'month','m'=>$prevKey]) }}" title="Prev" aria-label="Previous">‹</a>
            <a class="btn secondary cal-today-btn" href="{{ route('admin.calendar', ['view'=>'month']) }}">Today</a>
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'month','m'=>$nextKey]) }}" title="Next" aria-label="Next">›</a>
          @elseif($view==='week')
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'week','d'=>$prevKey]) }}" title="Prev" aria-label="Previous">‹</a>
            <a class="btn secondary cal-today-btn" href="{{ route('admin.calendar', ['view'=>'week']) }}">Today</a>
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'week','d'=>$nextKey]) }}" title="Next" aria-label="Next">›</a>
          @else
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'day','d'=>$prevKey]) }}" title="Prev" aria-label="Previous">‹</a>
            <a class="btn secondary cal-today-btn" href="{{ route('admin.calendar', ['view'=>'day']) }}">Today</a>
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'day','d'=>$nextKey]) }}" title="Next" aria-label="Next">›</a>
          @endif
          </div>
          <form method="get" action="{{ route('admin.calendar') }}" style="margin-left:8px;display:inline-flex;gap:6px;align-items:center">
            <select name="view" class="select cal-view-select" onchange="this.form.submit()">
              <option value="month" {{ $view==='month'?'selected':'' }}>Month</option>
              <option value="week"  {{ $view==='week'?'selected':'' }}>Week</option>
              <option value="day"   {{ $view==='day'?'selected':'' }}>Day</option>
            </select>
            @if($view==='month')
              <input type="hidden" name="m" value="{{ $month->format('Y-m') }}">
            @else
              <input type="hidden" name="d" value="{{ $start->toDateString() }}">
            @endif
          </form>
        </div>
      </div>
      <div class="cal-grid">
        @if($view!=='day')
          @foreach($dow as $d)
            <div class="cal-dow">{{ $d }}</div>
          @endforeach
        @endif
        @while($cursor <= $last)
          @php
            $key = $cursor->toDateString();
            $items = $byDate[$key] ?? [];
            $isOther = $cursor->month !== $month->month;
            $isToday = $cursor->isToday();
          @endphp
          <div class="cal-cell {{ $isOther ? 'cal-other':'' }} {{ $isToday ? 'cal-today':'' }}">
            <div class="cal-date">{{ $cursor->day }}</div>
            <a href="{{ route('admin.staff_bookings.step1', ['date'=>$cursor->toDateString()]) }}" class="icon-btn add-icon add-event-btn" title="Add event on {{ $cursor->toFormattedDateString() }}">+</a>
            @foreach($items as $r)
              @php
                $st = $r->status ?? 'draft';
                $cls = in_array($st, ['canceled','cancelled'], true)
                  ? 'status-canceled'
                  : (in_array($st, ['confirmed'], true) ? 'status-confirmed' : 'status-pending');
                try { $tm = \Carbon\Carbon::parse($r->time)->format('g:i A'); } catch (\Throwable $e) { $tm = substr((string)$r->time,0,5); }
              @endphp
              @php $col = $r->color ?? null; @endphp
              <button type="button" class="calendar-event {{ $cls }}" title="{{ $r->customer_name ?? '' }}" data-event-id="{{ $r->id }}" @if($col) style="--event-accent: {{ $col }};" @endif>
                <div class="event-top">
                  <div class="event-main">
                    @if(!empty($r->normalizedEventMarkers()))
                      <div class="event-markers" aria-label="Reservation markers">
                        @foreach($r->eventMarkerMeta() as $marker)
                          <span class="event-marker {{ $marker['key'] === 'vip' ? 'vip' : '' }}" title="{{ $marker['label'] }}">{{ $marker['icon'] }}</span>
                        @endforeach
                      </div>
                    @endif
                    <div class="event-client">{{ \Illuminate\Support\Str::limit($r->customer_name ?? '—', 24) }}</div>
                  </div>
                  @if((int)($r->guests ?? 0) > 0)
                    <div class="event-guests">{{ (int) $r->guests }}</div>
                  @endif
                </div>
                <div class="event-time">{{ $tm }}</div>
              </button>
            @endforeach
          </div>
          @php $cursor->addDay(); @endphp
        @endwhile
      </div>
    </div>
    <div id="event-popover-root"></div>
  </div>
@endsection

@push('scripts')
  <script>
    (function(){
      const root = document.getElementById('event-popover-root');
      let openForId = null; let pop = null; let backdrop = null; let lastTrigger = null;
      function $e(html){ const t=document.createElement('template'); t.innerHTML=html.trim(); return t.content.firstChild; }
      function esc(s){ return String(s??'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c])); }
      function fmtUSD(n){ const v = Number(n||0); return v.toLocaleString('en-US', {style:'currency', currency:'USD'}); }
      function compactText(value, fallback = '—'){ const out = String(value ?? '').trim(); return out === '' ? fallback : out; }
      function titleText(value, fallback = '—'){ return esc(compactText(value, fallback)); }
      function sentence(value, fallback = '—'){ return esc(compactText(value, fallback)); }
      function joinParts(parts){ return parts.map(v => String(v ?? '').trim()).filter(Boolean).join(', '); }
      function fmtCount(value){ const n = Number(value || 0); return Number.isFinite(n) ? String(n) : '0'; }
      function normalizeInvoiceTone(statusValue, balance){
        const raw = compactText(statusValue, 'pending').toLowerCase().replace(/\s+/g, '_');
        if (['canceled', 'cancelled'].includes(raw)) return 'canceled';
        if (balance <= 0 || raw === 'paid') return 'paid';
        if (raw === 'partial' || raw === 'deposit_paid') return 'partial';
        if (['pending', 'pending_payment', 'unpaid', 'overdue'].includes(raw)) return 'due';
        return balance > 0 ? 'due' : 'neutral';
      }
      function buildStatusBadge(value, type = 'status'){
        const raw = compactText(value, 'pending').toLowerCase().replace(/\s+/g, '_');
        const label = raw.replace(/_/g, ' ');
        const tone = ['confirmed','paid'].includes(raw) ? (type === 'status' ? 'status-confirmed' : 'invoice-paid')
          : ['pending','pending_payment'].includes(raw) ? (type === 'status' ? 'status-pending' : 'invoice-pending')
          : ['partial','deposit_paid'].includes(raw) ? (type === 'status' ? 'status-pending' : 'invoice-partial')
          : ['canceled','cancelled','overdue','unpaid'].includes(raw) ? (type === 'status' ? 'status-canceled' : `invoice-${raw === 'cancelled' ? 'canceled' : raw}`)
          : `${type}-default`;
        return `<span class="badge ${tone}">${esc(label)}</span>`;
      }
      function close(){ if(!pop) return; pop.classList.remove('shown'); backdrop.classList.remove('shown'); setTimeout(()=>{ pop.remove(); backdrop.remove(); pop=null; backdrop=null; openForId=null; lastTrigger=null; }, 160); document.removeEventListener('keydown', onKey); }
      function onKey(e){
        if (e.key==='Escape'){ e.preventDefault(); close(); return; }
        if (!pop) return;
        if (e.key==='Tab'){
          const f = pop.querySelectorAll('a,button,[tabindex]:not([tabindex="-1"])');
          const focusables = Array.prototype.filter.call(f, el=>!el.hasAttribute('disabled'));
          if (focusables.length){
            const first = focusables[0], last = focusables[focusables.length-1];
            if (e.shiftKey && document.activeElement === first){ e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last){ e.preventDefault(); first.focus(); }
          }
        }
      }
      function position(anchor){
        if (!pop) return;
        const rect = anchor.getBoundingClientRect();
        const vw = window.innerWidth;
        if (vw <= 768) return;
        const popHeight = pop.offsetHeight;
        const popWidth = pop.offsetWidth;
        const hasSpaceAbove = rect.top > popHeight + 24;
        const top = hasSpaceAbove
          ? rect.top - popHeight - 10
          : Math.min(window.innerHeight - popHeight - 12, rect.bottom + 10);
        const left = Math.min(window.innerWidth - popWidth - 12, Math.max(12, rect.right - popWidth + 10));
        pop.style.top = (top + window.scrollY) + 'px';
        pop.style.left = (left + window.scrollX) + 'px';
        pop.classList.toggle('tip', hasSpaceAbove);
      }
      async function openEventPopover(eventId, anchor){
        if (openForId === eventId) { close(); return; }
        if (pop) close();
        lastTrigger = anchor; openForId = eventId;
        backdrop = $e('<div class="popover-backdrop"></div>');
        backdrop.addEventListener('click', close);
        pop = $e('<div class="event-pop" role="dialog" aria-modal="true" aria-labelledby="event-pop-title"></div>');
        root.appendChild(backdrop); root.appendChild(pop);
        setTimeout(()=>{ backdrop.classList.add('shown'); }, 10);
        document.addEventListener('keydown', onKey);
        try{
          const resp = await fetch(`/events/${eventId}`);
          if(!resp.ok) throw new Error('Failed to load');
          const d = await resp.json();
          const fullAddress = joinParts([d.address, d.city, d.zip_code]);
          const mapUrl = fullAddress ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(fullAddress)}` : null;
          const t = d.totals||{};
          const balance = Number(t.balance || 0);
          const depositPaid = Number(t.deposit_paid || 0);
          const invoiceLabel = compactText(d.invoice_status || d.status, 'Pending');
          const invoiceTone = normalizeInvoiceTone(invoiceLabel, balance);
          const balanceBadge = balance <= 0
            ? `<span class="badge balance-paid">Balance Paid</span>`
            : `<span class="badge ${invoiceTone === 'partial' ? 'balance-partial' : 'balance-due'}">Balance Due ${esc(fmtUSD(balance))}</span>`;
          const titleMarkers = Array.isArray(d.marker_meta) && d.marker_meta.length
            ? `<div class="title-markers">${d.marker_meta.map(marker => `<span class="title-marker ${marker.key === 'vip' ? 'vip' : ''}" title="${esc(marker.label || '')}">${esc(marker.icon || '')}</span>`).join('')}</div>`
            : '';
          const head = $e(`<div class="head"><div class="head-main"><div class="head-sub">Reservation Details</div><div class="title-row">${titleMarkers}<h3 class="title" id="event-pop-title" tabindex="-1">${titleText(d.title)}</h3></div><div class="pop-badges">${buildStatusBadge(d.status, 'status')}${buildStatusBadge(invoiceLabel, 'invoice')}${balanceBadge}</div></div><div style="display:flex;gap:6px"><button class="icon-only" title="Close" aria-label="Close">✕</button></div></div>`);
          head.querySelector('button[aria-label="Close"]').addEventListener('click', close);
          const body = document.createElement('div'); body.className='body';
          const dateLabel = formatDateForPopover(d.date);
          const timeLabel = formatTimeForPopover(d.date, d.time);
          const hero = $e(`<div class="hero-pills">
            <div class="hero-pill"><div class="hero-pill-label">Date</div><div class="hero-pill-value">${esc(dateLabel)}</div></div>
            <div class="hero-pill"><div class="hero-pill-label">Time</div><div class="hero-pill-value">${esc(timeLabel)}</div></div>
            <div class="hero-pill"><div class="hero-pill-label">Guests</div><div class="hero-pill-value">${esc(d.guests)}</div></div>
          </div>`);
          body.appendChild(hero);

          const customer = $e(`<section class="pop-section">
            <div class="pop-section-title">Customer</div>
            <div class="detail-grid customer-stack">
              <div class="detail-item">
                <div class="detail-label">Name</div>
                <div class="customer-name">${titleText(d.title)}</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Phone</div>
                <div class="detail-value">${sentence(d.phone || '—')}</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value">${sentence(d.email || '—')}</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Booked By</div>
                <div class="detail-value">${sentence(d.booked_by || '—')}</div>
              </div>
            </div>
          </section>`);

          const details = $e(`<section class="pop-section">
            <div class="pop-section-title">Event Information</div>
            <div class="detail-grid">
              <div class="detail-item full"><div class="detail-label">Address</div><div class="detail-value">${sentence(fullAddress || '—')}</div></div>
              <div class="detail-item"><div class="detail-label">Setup Color</div><div class="detail-value">${sentence(d.setup_color || '—')}</div></div>
              <div class="detail-item"><div class="detail-label">Event Type</div><div class="detail-value">${sentence(d.event_type || '—')}</div></div>
              <div class="detail-item"><div class="detail-label">Stairs</div><div class="detail-value">${d.stairs ? 'Yes' : 'No'}</div></div>
              ${compactText(d.notes, '') ? `<div class="detail-item full"><div class="detail-label">Notes</div><div class="detail-value">${sentence(d.notes)}</div></div>` : ''}
            </div>
          </section>`);
          const topGrid = $e('<div class="top-grid"></div>');
          topGrid.appendChild(customer);
          topGrid.appendChild(details);
          body.appendChild(topGrid);

          const staffRows = Array.isArray(d.assigned_staff) ? d.assigned_staff : [];
          if (staffRows.length) {
            const staffWrap = $e('<section class="pop-section staff-section"><div class="pop-section-title">Assigned Staff</div><div class="staff-grid"></div></section>');
            const staffGrid = staffWrap.querySelector('.staff-grid');
            staffRows.forEach(row => {
              const label = compactText(row.label, 'Staff');
              const value = compactText(row.value, 'N/A');
              staffGrid.appendChild($e(`<div class="staff-pill ${label.toLowerCase() === 'van' ? 'van' : ''}"><div class="staff-pill-label">${esc(label)}</div><div class="staff-pill-value">${esc(value)}</div></div>`));
            });
            body.appendChild(staffWrap);
          } else {
            body.appendChild($e('<section class="pop-section staff-section"><div class="pop-section-title">Assigned Staff</div><div class="empty-note">No staff assigned yet.</div></section>'));
          }

          const finance = $e(`<section class="pop-section">
            <div class="pop-section-title">Invoice Snapshot</div>
            <div class="finance-grid">
              <div class="finance-card is-${invoiceTone}">
                <div class="finance-label">Invoice</div>
                <div class="finance-badge is-${invoiceTone}">${sentence(invoiceLabel)}</div>
              </div>
              <div class="finance-card">
                <div class="finance-label">Deposit Paid</div>
                <div class="finance-value">${fmtUSD(depositPaid)}</div>
              </div>
              <div class="finance-card ${balance > 0 ? `is-${invoiceTone === 'partial' ? 'partial' : 'due'}` : 'is-paid'}">
                <div class="finance-label">Balance Due</div>
                <div class="finance-value ${balance > 0 ? 'due' : 'ok'}">${balance > 0 ? fmtUSD(balance) : 'Paid'}</div>
              </div>
            </div>
          </section>`);

          if (Array.isArray(d.items) && d.items.length){
            const wrap = $e('<section class="pop-section"><div class="pop-section-title">Order Items</div><div class="event-list"></div></section>');
            const list = wrap.querySelector('.event-list');
            d.items.forEach(it=>{
              list.appendChild($e(`<div class='event-row'><div><div class='event-row-title'>${titleText(it.name)}</div>${compactText(it.description, '') ? `<div class='event-row-meta'>${sentence(it.description)}</div>` : ''}</div><div class='event-row-value qty'>${fmtCount(it.qty)}</div></div>`));
            });
            body.appendChild(wrap);
          }

          body.appendChild(finance);

          const foot = $e(`<div class="foot">
            ${mapUrl
              ? `<a href="${esc(mapUrl)}" target="_blank" rel="noopener" class="btn ghost">Open Maps</a>`
              : `<span class="btn ghost is-disabled" aria-disabled="true">Open Maps</span>`}
            <a href="${esc(d.links.invoice)}" class="btn ghost">Invoice</a>
            <a href="${esc(d.links.edit)}" class="btn secondary">View Details</a>
          </div>`);

          pop.innerHTML=''; pop.appendChild(head); pop.appendChild(body); pop.appendChild(foot); setTimeout(()=>{ pop.classList.add('shown'); head.querySelector('.title').focus(); }, 10);
          position(anchor);
        } catch(e){ pop.innerHTML = '<div class="body">Failed to load event.</div>'; setTimeout(()=>pop.classList.add('shown'), 10); position(anchor); }
      }
      // Delegate clicks on event cards
      document.addEventListener('click', (e)=>{
        const btn = e.target.closest('.calendar-event[data-event-id]');
        if (!btn) return;
        e.preventDefault();
        const id = parseInt(btn.getAttribute('data-event-id'),10);
        openEventPopover(id, btn);
      });
      window.addEventListener('resize', ()=>{ if (pop && lastTrigger) position(lastTrigger); });
      // Listen for color updates from other tabs via localStorage
      window.addEventListener('storage', (e) => {
        if (e.key !== 'resv_color_update' || !e.newValue) return;
        try {
          const data = JSON.parse(e.newValue);
          const id = String(data.id);
          const color = data.color || '#6b7280';
          const cards = document.querySelectorAll(`[data-event-id="${id}"]`);
          cards.forEach(el => el.style.setProperty('--event-accent', color));
        } catch(_){}
      });
      // Format date as "Month d, yyyy" with a graceful fallback
      function formatDateForPopover(dateStr){
        try {
          if (!dateStr) return '—';
          const dt = new Date(`${dateStr}T00:00:00`);
          if (Number.isNaN(dt.getTime())) return dateStr;
          return new Intl.DateTimeFormat('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
            timeZone: 'America/Los_Angeles',
          }).format(dt);
        } catch (e) {
          return dateStr || '—';
        }
      }
      // Format time as 12-hour with AM/PM
      function formatTimeForPopover(dateStr, timeStr){
        try {
          const raw = (timeStr || '').toString().trim();
          const directMatch = raw.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
          if (directMatch) {
            const hour = directMatch[1];
            const minute = directMatch[2];
            const period = directMatch[3].toUpperCase();
            return `${hour}:${minute} ${period}`;
          }
          const twentyFourMatch = raw.match(/^(\d{1,2}):(\d{2})$/);
          let dt;
          if (twentyFourMatch) {
            const hour = twentyFourMatch[1].padStart(2, '0');
            dt = new Date(`${dateStr}T${hour}:${twentyFourMatch[2]}:00`);
          } else if (raw) {
            dt = new Date(`${dateStr} ${raw}`);
          }
          if (!dt || Number.isNaN(dt.getTime())) return raw || '—';
          return new Intl.DateTimeFormat('en-US', { hour: 'numeric', minute: '2-digit', hour12: true, timeZone: 'America/Los_Angeles' }).format(dt);
        } catch (e) {
          return timeStr || '—';
        }
      }
    })();
  </script>
@endpush
