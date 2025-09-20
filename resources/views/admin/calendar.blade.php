<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Calendar</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    /* Expand page width for calendar view */
    .container{max-width:100%;margin:12px auto;padding:0 12px}
    .cal-wrap{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden}
    .cal-head{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-bottom:1px solid var(--border)}
    .cal-title{font-size:18px;font-weight:700}
    .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);}
    .cal-dow{padding:6px 10px;background:#f3f4f6;color:#374151;font-weight:700;border-bottom:1px solid var(--border)}
    .cal-cell{min-height:140px;border-right:1px solid var(--border);border-bottom:1px solid var(--border);padding:16px 6px 6px;position:relative}
    .cal-cell:nth-child(7n){border-right:none}
    .cal-date{position:absolute;top:6px;right:8px;font-size:12px;color:#6b7280}
    .cal-other{background:#fafafa}
    .pill{display:flex;align-items:center;gap:6px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:3px 6px;margin:3px 0;font-size:12px;color:#111;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;width:100%}
    .pill:hover{border-color:#d1d5db;box-shadow:0 2px 8px rgba(0,0,0,.06)}
    .pill .time{color:#374151;font-weight:700;margin-right:6px}
    .pill .guests{margin-left:auto;color:#374151;font-weight:700}
    .legend{display:flex;gap:8px;align-items:center;color:#6b7280;font-size:12px}
    .legend .dot{width:10px;height:10px;border-radius:999px;display:inline-block}
    .st-confirmed{border-color:#a7f3d0;background:#ecfdf5}
    .st-draft{border-color:var(--border);background:#fff}
    .st-pending_payment{border-color:#fed7aa;background:#fff7ed}
    .st-canceled{border-color:#fecaca;background:#fef2f2}
    /* Position the standard icon button inside cells */
    .add-icon{position:absolute;top:6px;left:8px;z-index:2}
    .icon-btn.add-icon{width:22px;height:22px;font-size:14px;border-radius:8px;opacity:.85}
    .cal-cell:hover .icon-btn.add-icon{opacity:1}
    /* Ensure icon buttons have no underline */
    .icon-btn, .icon-btn:hover, .icon-btn:focus, .icon-btn:visited { text-decoration: none; }

    /* Popover styles */
    .popover-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.15);opacity:0;pointer-events:none;transition:opacity .18s ease}
    .popover-backdrop.shown{opacity:1;pointer-events:auto}
    .event-pop{position:fixed;z-index:50;background:#fff;border:1px solid var(--border);border-radius:16px;box-shadow:0 12px 30px rgba(0,0,0,.16);width:360px;max-width:92vw;max-height:70vh;overflow:auto;opacity:0;transform:translateY(-6px);transition:opacity .18s ease, transform .18s ease}
    .event-pop.shown{opacity:1;transform:translateY(0)}
    .event-pop .head{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid var(--border)}
    .event-pop .title{font-size:16px;font-weight:700;margin:0}
    .event-pop .icon-only{width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:#fff;color:#374151;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .event-pop .icon-only:hover{background:#f9fafb}
    .event-pop .body{padding:12px 14px}
    .kv{display:flex;gap:8px;font-size:13px;margin:4px 0;color:#374151}
    .kv .k{min-width:88px;color:#6b7280}
    .badges{display:flex;gap:6px;flex-wrap:wrap;margin:6px 0}
    .badge{display:inline-block;border:1px solid var(--border);border-radius:999px;padding:2px 8px;font-size:12px}
    .items, .payments{margin-top:10px}
    .items .row, .payments .row{display:flex;justify-content:space-between;font-size:13px;margin:2px 0}
    .sum{font-size:12px}
    .sum .row{display:flex;justify-content:space-between;font-weight:600;font-size:12px}
    /* Desktop arrow tip */
    .event-pop.tip:before{content:"";position:absolute;width:12px;height:12px;background:#fff;border:1px solid var(--border);border-right:none;border-bottom:none;transform:rotate(45deg);top:-6px;right:18px}

    @media (max-width: 768px){
      .event-pop{width:min(540px,96vw);left:50%!important;top:50%!important;transform:translate(-50%,-50%);}
      .event-pop:before{display:none}
    }
  </style>
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
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="legend" style="margin-right:auto">
        <span class="dot" style="background:#ecfdf5;border:1px solid #a7f3d0"></span> Confirmed
        <span class="dot" style="background:#fff7ed;border:1px solid #fed7aa"></span> Pending
        <span class="dot" style="background:#fef2f2;border:1px solid #fecaca"></span> Canceled
      </div>
      <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
        <a href="{{ route('admin.staff_bookings.step1', $view==='month' ? [] : ['date'=>$start->toDateString()]) }}" class="icon-btn" title="Add Event">+</a>
      </div>
    </div>

    <div class="cal-wrap">
      <div class="cal-head">
        <div class="cal-title">{{ $titleLabel }}</div>
        <div style="display:flex;gap:8px;align-items:center">
          @if($view==='month')
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'month','m'=>$prevKey]) }}" title="Prev" aria-label="Previous">‹</a>
            <a class="btn secondary" href="{{ route('admin.calendar', ['view'=>'month']) }}">Today</a>
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'month','m'=>$nextKey]) }}" title="Next" aria-label="Next">›</a>
          @elseif($view==='week')
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'week','d'=>$prevKey]) }}" title="Prev" aria-label="Previous">‹</a>
            <a class="btn secondary" href="{{ route('admin.calendar', ['view'=>'week']) }}">Today</a>
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'week','d'=>$nextKey]) }}" title="Next" aria-label="Next">›</a>
          @else
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'day','d'=>$prevKey]) }}" title="Prev" aria-label="Previous">‹</a>
            <a class="btn secondary" href="{{ route('admin.calendar', ['view'=>'day']) }}">Today</a>
            <a class="icon-btn" href="{{ route('admin.calendar', ['view'=>'day','d'=>$nextKey]) }}" title="Next" aria-label="Next">›</a>
          @endif
          <form method="get" action="{{ route('admin.calendar') }}" style="margin-left:8px;display:inline-flex;gap:6px;align-items:center">
            <select name="view" class="select" onchange="this.form.submit()">
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
          @endphp
          <div class="cal-cell {{ $isOther ? 'cal-other':'' }}">
            <div class="cal-date">{{ $cursor->day }}</div>
            <a href="{{ route('admin.staff_bookings.step1', ['date'=>$cursor->toDateString()]) }}" class="icon-btn add-icon" title="Add event on {{ $cursor->toFormattedDateString() }}">+</a>
            @foreach($items as $r)
              @php
                $st = $r->status ?? 'draft';
                $cls = 'st-'.str_replace(' ','_',$st);
                try { $tm = \Carbon\Carbon::parse($r->time)->format('g:i A'); } catch (\Throwable $e) { $tm = substr((string)$r->time,0,5); }
              @endphp
              @php $col = $r->color ?? '#6b7280'; $bg = $col.(strlen($col)===7?'20':''); @endphp
              <button type="button" class="pill {{ $cls }}" title="{{ $r->customer_name ?? '' }}" data-event-id="{{ $r->id }}" style="border-color: {{ $col }}; background: {{ $bg }}">
                <span class="time">{{ $tm }}</span>
                <span class="name">{{ \Illuminate\Support\Str::limit($r->customer_name ?? '—', 22) }}</span>
                @if((int)($r->guests ?? 0) > 0)
                  <span class="guests">{{ (int) $r->guests }}</span>
                @endif
              </button>
            @endforeach
          </div>
          @php $cursor->addDay(); @endphp
        @endwhile
      </div>
    </div>
    <div id="event-popover-root"></div>
  </div>
  <script>
    (function(){
      const root = document.getElementById('event-popover-root');
      let openForId = null; let pop = null; let backdrop = null; let lastTrigger = null;
      function $e(html){ const t=document.createElement('template'); t.innerHTML=html.trim(); return t.content.firstChild; }
      function esc(s){ return String(s??'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c])); }
      function fmtUSD(n){ const v = Number(n||0); return v.toLocaleString('en-US', {style:'currency', currency:'USD'}); }
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
      function position(anchor){ if (!pop) return; const rect = anchor.getBoundingClientRect(); const vw = window.innerWidth; if (vw <= 768) return; const top = Math.max(12, rect.top - pop.offsetHeight - 10); const left = Math.min(window.innerWidth - pop.offsetWidth - 12, rect.right - pop.offsetWidth + 10); pop.style.top = (top + window.scrollY) + 'px'; pop.style.left = (left + window.scrollX) + 'px'; pop.classList.add('tip'); }
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
          const head = $e(`<div class="head"><h3 class="title" id="event-pop-title" tabindex="-1">${esc(d.title)}</h3><div style="display:flex;gap:6px"><a class="icon-only" href="${esc(d.links.edit)}" title="Edit" aria-label="Edit">✎</a><button class="icon-only" title="Close" aria-label="Close">✕</button></div></div>`);
          head.querySelector('button[aria-label="Close"]').addEventListener('click', close);
          const body = document.createElement('div'); body.className='body';
          // badges
          const badges = $e(`<div class='badges'><span class='badge'>${esc(d.status)}</span>${d.invoice_status?`<span class='badge'>${esc(d.invoice_status)}</span>`:''}</div>`);
          body.appendChild(badges);
          // sections
          const dateLabel = formatDateForPopover(d.date);
          const timeLabel = formatTimeForPopover(d.date, d.time);
          const sec = [
            ['Date', esc(dateLabel)],
            ['Time', esc(timeLabel)],
            ['📍 Address', `${esc(d.address||'')} ${esc(d.city||'')} ${esc(d.zip_code||'')}`],
            ['Guests', esc(d.guests)],
            ['Organizer', esc(d.booked_by||'—')],
            ['Contact', `${esc(d.email||'—')} · ${esc(d.phone||'—')}`],
            ['Setup', `${esc(d.setup_color||'—')}`],
            ['Event type', `${esc(d.event_type||'—')}`],
            ['Stairs', d.stairs? 'Yes':'No'],
            ['Notes', esc(d.notes||'—')],
          ];
          sec.forEach(([k,v])=>{ body.appendChild($e(`<div class='kv'><div class='k'>${k}</div><div class='v'>${v}</div></div>`)); });
          // items
          if (Array.isArray(d.items) && d.items.length){
            const wrap = $e('<div class="items"><div style="font-weight:600;margin-bottom:4px">Order</div></div>');
            d.items.forEach(it=>{ wrap.appendChild($e(`<div class='row'><div>${esc(it.qty)} × ${esc(it.name)}</div><div>${fmtUSD(it.line_total)}</div></div>`)); });
            body.appendChild(wrap);
          }
          // payments / totals
          const t = d.totals||{}; const sum = $e('<div class="sum" style="margin-top:10px"><div style="font-weight:600;margin-bottom:4px">Summary</div></div>');
          [['Subtotal',t.subtotal],['Travel',t.travel_fee],['Gratuity',t.gratuity],['Tax',t.tax],['Total',t.total],['Deposit',t.deposit_paid],['Balance',t.balance]].forEach(([k,v])=>{
            sum.appendChild($e(`<div class='row'><div>${k}</div><div>${fmtUSD(v)}</div></div>`));
          });
          body.appendChild(sum);
          // adjustments (if any)
          if (Array.isArray(d.adjustments) && d.adjustments.length){
            const adjWrap = $e('<div class="items"><div style="font-weight:600;margin:8px 0 4px">Adjustments</div></div>');
            d.adjustments.forEach(a=>{ adjWrap.appendChild($e(`<div class='row'><div>${esc(a.label||'Adjustment')}</div><div>${fmtUSD(a.amount||0)}</div></div>`)); });
            body.appendChild(adjWrap);
          }
          // attachments
          const links = $e(`<div style="margin-top:10px;display:flex;gap:8px"><a href="${esc(d.links.invoice)}" class="btn secondary" style="padding:6px 10px;font-size:12px">Invoice</a></div>`);
          pop.innerHTML=''; pop.appendChild(head); pop.appendChild(body); setTimeout(()=>{ pop.classList.add('shown'); head.querySelector('.title').focus(); }, 10);
          position(anchor);
        } catch(e){ pop.innerHTML = '<div class="body">Failed to load event.</div>'; setTimeout(()=>pop.classList.add('shown'), 10); position(anchor); }
      }
      // Delegate clicks on .pill
      document.addEventListener('click', (e)=>{
        const btn = e.target.closest('.pill[data-event-id]');
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
          const pills = document.querySelectorAll(`[data-event-id="${id}"]`);
          const bg = /^#[0-9a-fA-F]{6}$/.test(color) ? (color + '20') : '#6b728020';
          pills.forEach(el => { el.style.borderColor = color; el.style.background = bg; });
        } catch(_){}
      });
      // Format date as "Month mm-dd-yyyy" with a graceful fallback
      function formatDateForPopover(dateStr){
        try {
          if (!dateStr) return '—';
          const dt = new Date(`${dateStr}T00:00:00`);
          if (Number.isNaN(dt.getTime())) return dateStr;
          const month = new Intl.DateTimeFormat('en-US', { month: 'long', timeZone: 'America/Los_Angeles' }).format(dt);
          const parts = dateStr.split('-');
          if (parts.length === 3) {
            const [y, m, d] = parts;
            return `${month} ,${d}-${y}`;
          }
          return `${month} ${dateStr}`;
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
</body>
</html>
