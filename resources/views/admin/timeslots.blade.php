<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Timeslots</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    :root{ --warn:#d97706; }
    .title{font-size:22px;margin:0}
    .card{background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.04)}
    .card-body{padding:16px}
    .card + .card{margin-top:14px}

    .toolbar{display:flex;gap:10px;align-items:center}
    .input, .select{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff}
    .input[type=number]{text-align:center;padding:6px 8px}
    .btn{appearance:none;border:0;background:var(--brand);color:#fff;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:600;white-space:nowrap;
      box-shadow:0 1px 2px rgba(0,0,0,.05);transition:background .15s ease,box-shadow .15s ease,transform .05s ease}
    .btn:hover{background:var(--brand-hover);box-shadow:0 2px 6px rgba(0,0,0,.08)}
    .btn:active{transform:translateY(1px)}
    .btn.sm{padding:6px 10px;font-size:13px;border-radius:8px}
    .btn.xs{padding:4px 8px;font-size:12px;border-radius:8px}
    .btn.secondary{background:#4b5563}
    .btn.secondary:hover{background:#374151}
    .btn.success{background:#065f46}
    .btn.success:hover{background:#064e3b}
    .btn.danger{background:#991b1b}
    .btn.danger:hover{background:#7f1d1d}
    .btn.link{background:transparent;color:var(--brand);padding:0}
    .btn.link:hover{text-decoration:underline}

    .grid{display:grid;gap:12px}
    .grid.cols-3{grid-template-columns:1fr 1fr 1fr}
    .grid.cols-4{grid-template-columns:1fr 1fr 1fr 1fr}
    .grid.cols-2{grid-template-columns:1fr 1fr}
    @media (max-width: 720px){.grid.cols-4,.grid.cols-3,.grid.cols-2{grid-template-columns:1fr}}

    .label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#374151}

    /* Alerts */
    .alert{border-radius:10px;padding:10px 12px;font-size:14px}
    .alert.error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca}
    .alert.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}

    /* Table */
    .table{width:100%;border-collapse:separate;border-spacing:0}
    .table th, .table td{padding:10px 12px;text-align:left;font-size:14px}
    .table thead th{background:#f3f4f6;color:#374151;border-bottom:1px solid var(--border);font-weight:700}
    .table tbody tr{background:#fff}
    .table tbody tr + tr td{border-top:1px solid var(--border)}
    .actions a{color:#b91c1c;text-decoration:none}
    .actions a:hover{text-decoration:underline}

    /* Time & metrics columns */
    .time-cell{font-size:12px;font-weight:500;white-space:nowrap}
    .col-time{width:80px}
    .col-per{width:100px}
    .metric-cell{white-space:nowrap;text-align:center;font-variant-numeric:tabular-nums}
    .col-cap{width:90px}

    .badge{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:4px 8px;font-size:12px;font-weight:600}
    .badge.open{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .badge.closed{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

    /* Capacity coloring */
    .cap-remaining{font-weight:700}
    .cap-remaining.zero{color:#991b1b}

    .subtle{color:var(--muted);font-size:13px}
    .spacer{height:8px}

    /* Calendar */
    #calendar{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden}
    .cal-header{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#f3f4f6;border-bottom:1px solid var(--border)}
    .cal-month{font-weight:700;color:#111827}
    .cal-weekdays{display:grid;grid-template-columns:repeat(7,1fr);background:#065f46;color:#fff;font-weight:700}
    .cal-weekdays > div{padding:8px 10px;text-align:center;border-right:1px solid rgba(255,255,255,.2)}
    .cal-weekdays > div:last-child{border-right:0}
    .cal-grid{display:grid;grid-template-columns:repeat(7,1fr)}
    .cal-cell{min-height:64px;display:flex;align-items:flex-start;justify-content:flex-end;padding:8px 10px;border:1px solid #e5e7eb;background:#fff;font-weight:600;color:#111827}
    .cal-cell.blank{background:#fcfcfd}
    .cal-cell.day{cursor:pointer}
    .cal-cell.day:hover{background:#eef2ff}
    .cal-cell.day.today{outline:2px solid #2563eb}
    .cal-cell.day.selected{background:#d1fae5}
    .cal-cell.day.full{background:#fee2e2;border-color:#fecaca}
    /* Ensure selected state is visibly green even if the day is marked full */
    .cal-cell.day.full.selected{background:#d1fae5;border-color:#a7f3d0}

    /* Status select (single control) */
    .select.status-select{
      padding:1px 8px; /* more compact */
      font-size:10px;
      width:70px;
      min-width:70px;
      max-width:70px;
      border-radius:999px;
      line-height:1;
      height:20px;
    }
    .select.status-select.open{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .select.status-select.closed{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const filter = document.querySelector('#filterDate');
      // Unsaved changes guard for bulk capacity edits
      let hasUnsaved = false;
      const markDirty = () => { hasUnsaved = true; };
      const clearDirty = () => { hasUnsaved = false; };
      const confirmNavigate = () => (!hasUnsaved) || confirm('You have unsaved capacity changes. Leave without saving?');
      if (filter) filter.addEventListener('change', (e) => {
        if (!confirmNavigate()) { e.preventDefault(); return; }
        filter.form.submit();
      });

      // Calendar logic
      const calEl = document.getElementById('calendar');
      const monthLabel = document.getElementById('calMonth');
      const gridEl = document.getElementById('calGrid');
      const prevBtn = document.getElementById('calPrev');
      const nextBtn = document.getElementById('calNext');
      const slotsTitle = document.getElementById('slotsTitle');
      const slotsBody = document.getElementById('slotsBody');
      const slotFormDate = document.getElementById('slotFormDate');
      // const slotFormTime = document.getElementById('slotFormTime');
      // const perSlotInput = document.getElementById('perSlotInput');
      const csrf = @json(csrf_token());
      const autoMonthBtn = document.getElementById('autoMonthBtn');
      const autoMonthForm = document.getElementById('autoMonthForm');
      const clearMonthBtn = document.getElementById('clearMonthBtn');
      const clearMonthForm = document.getElementById('clearMonthForm');
      const bulkCapForm = document.getElementById('bulkCapForm');
      const bulkCapFormTop = document.getElementById('bulkCapFormTop');

      if (calEl) {
        let today = new Date();
        const _sp = @json($d).split('-').map(Number);
        let selected = new Date(_sp[0], _sp[1]-1, _sp[2]);
        let current = new Date(selected.getFullYear(), selected.getMonth(), 1);
        // no-op; admin sets Max per slot manually in the form

        function fmtYmd(d){
          const y = d.getFullYear();
          const m = String(d.getMonth()+1).padStart(2,'0');
          const day = String(d.getDate()).padStart(2,'0');
          return `${y}-${m}-${day}`;
        }

        async function renderMonth(){
          const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
          monthLabel.textContent = `${monthNames[current.getMonth()]} ${current.getFullYear()}`;
          gridEl.innerHTML = '';
          const firstDay = new Date(current.getFullYear(), current.getMonth(), 1);
          const startWeekday = (firstDay.getDay()+6)%7; // convert Sun=0 to Sun=6, Mon=0
          const daysInMonth = new Date(current.getFullYear(), current.getMonth()+1, 0).getDate();
          for (let i=0;i<startWeekday;i++){
            const cell = document.createElement('div');
            cell.className = 'cal-cell blank';
            gridEl.appendChild(cell);
          }
          for (let day=1; day<=daysInMonth; day++){
            const d = new Date(current.getFullYear(), current.getMonth(), day);
            const cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'cal-cell day';
            cell.textContent = String(day);
            cell.dataset.ymd = fmtYmd(d);
            if (fmtYmd(d) === fmtYmd(selected)) cell.classList.add('selected');
            if (fmtYmd(d) === fmtYmd(today)) cell.classList.add('today');
            cell.addEventListener('click', () => {
              if (!confirmNavigate()) return;
              selected = new Date(d);
              renderMonth();
              const ymd = fmtYmd(d);
              if (slotFormDate) slotFormDate.value = ymd;
              loadSlots(ymd);
            });
            gridEl.appendChild(cell);
          }

          // Fetch month full-booked statuses and mark days
          try {
            const y = current.getFullYear();
            const m = String(current.getMonth()+1).padStart(2,'0');
            const r = await fetch(`/admin/timeslots/month-status?y=${y}&m=${m}`);
            if (r.ok) {
              const data = await r.json();
              const full = data.full || {};
              gridEl.querySelectorAll('.cal-cell.day').forEach(btn => {
                const ymd = btn.dataset.ymd;
                btn.classList.toggle('full', !!full[ymd]);
              });
            }
          } catch (e) {}
        }

        async function loadSlots(ymd){
          try{
            const p = ymd.split('-');
            slotsTitle.textContent = `Slots for ${p[1]}/${p[2]}/${p[0]}`;
            const r = await fetch(`/admin/timeslots/json?d=${encodeURIComponent(ymd)}`);
            if (!r.ok) throw new Error('Request failed');
            const data = await r.json();
            const rows = (data.slots||[]).map(s => `
              <tr>
                <td class="time-cell">${s.time_label}</td>
                <td>
                  <form method=\"post\" action=\"/admin/timeslots/${s.id}/status\" style=\"display:inline\">
                    <input type="hidden" name="_token" value="${csrf}">
                    <input type="hidden" name="d" value="${data.date}">
                    <select name=\"status\" class=\"select status-select ${s.is_open ? 'open' : 'closed'}\" onchange=\"this.classList.toggle('open', this.value==='open'); this.classList.toggle('closed', this.value!=='open'); this.form.submit()\">
                      <option value=\"open\" ${s.is_open ? 'selected' : ''}>Open</option>
                      <option value=\"closed\" ${!s.is_open ? 'selected' : ''}>Closed</option>
                    </select>
                  </form>
                </td>
                
                <td class="col-cap">${(function(){ const g=(data.guest_sums && data.guest_sums[s.time+':00']) ? data.guest_sums[s.time+':00'] : 0; const rem=Math.max(0, (s.capacity||0) - (g||0)); const cls = rem<=0 ? 'zero' : ''; return `<span class=\\"cap-remaining ${cls}\\">${rem}</span>`; })()}</td>
                <td class="actions">
                  <form method=\"post\" action=\"/admin/timeslots/${s.id}/update\" style=\"display:inline-flex;gap:6px;align-items:center\">
                    <input type=\"hidden\" name=\"_token\" value=\"${csrf}\">
                    <input type=\"hidden\" name=\"d\" value=\"${data.date}\">
                    <input class=\"input\" type=\"number\" name=\"capacity\" value=\"${s.capacity}\" min=\"0\" style=\"width:70px;padding:4px 6px\">
                    <button class=\"btn secondary xs\" type=\"submit\">Save<\/button>
                  <\/form>
                  <a class=\"icon-btn danger\" href=\"/admin/timeslots/delete/${s.id}?d=${encodeURIComponent(data.date)}\" onclick=\"return confirm('Delete this slot?')\" style=\"margin-left:8px\" title=\"Delete\" aria-label=\"Delete\"><svg viewBox=\"0 0 24 24\" fill=\"currentColor\"><path d=\"M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z\"/><\/svg><\/a>
                </td>
              </tr>`).join('');
            slotsBody.innerHTML = rows || '<tr><td colspan="4" class="subtle">No slots for this date.</td></tr>';
            // Convert per-row inline forms to bulk inputs inside the outer bulk form
            try {
              slotsBody.querySelectorAll('td.actions').forEach(td => {
                const form = td.querySelector('form');
                if (!form) return;
                const cap = form.querySelector('input[name="capacity"]');
                const href = td.querySelector('a[href*="/delete/"]');
                const m = (form.getAttribute('action')||'').match(/\/admin\/timeslots\/(\d+)\/update/);
                const id = m ? m[1] : '';
                const capVal = cap ? cap.value : '';
                const del = href ? `<a class=\"icon-btn danger\" href=\"${href.getAttribute('href')}\" onclick=\"return confirm('Delete this slot?')\" style=\"margin-left:8px\" title=\"Delete\" aria-label=\"Delete\"><svg viewBox=\"0 0 24 24\" fill=\"currentColor\"><path d=\"M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z\"/><\/svg><\/a>` : '';
                td.innerHTML = `<input class=\"input\" type=\"number\" name=\"cap[${id}]\" value=\"${capVal}\" min=\"0\" style=\"width:70px;padding:4px 6px\"> ${del}`;
              });
            } catch (e) {}
            // Attach dirty handlers for the new bulk inputs
            try {
              slotsBody.querySelectorAll('input[name^="cap["]').forEach(inp => {
                inp.addEventListener('input', markDirty);
              });
            } catch (e) {}
            // Mark calendar date in red if fully closed (no open slots for the day)
            try {
              const all = data.slots || [];
              const fullyClosed = (all.length === 0) || all.every(s => !s.is_open);
              const btn = document.querySelector(`[data-ymd="${ymd}"]`);
              if (btn) {
                btn.classList.toggle('full', fullyClosed);
              }
            } catch (e) {}
            // Also refresh bookings panel for current date/time selection
            if (slotFormDate && slotFormTime) {
              // ensure date aligns with calendar selection
              slotFormDate.value = ymd;
              loadBookings(ymd);
            }
          }catch(e){
            slotsBody.innerHTML = '<tr><td colspan="5" class="subtle">Could not load slots.</td></tr>';
          }
        }

        async function loadBookings(ymd){
          const bookingsTitle = document.getElementById('bookingsTitle');
          const bookingsBody  = document.getElementById('bookingsBody');
          if (!bookingsBody) return;
          try {
            const r = await fetch(`/admin/timeslots/bookings?d=${encodeURIComponent(ymd)}`);
            if (!r.ok) throw new Error('bad');
            const data = await r.json();
            if (bookingsTitle) bookingsTitle.textContent = `Booked – ${new Date(ymd).toLocaleDateString()}`;
            const rows = (data.items||[]).map(it => `
              <tr>
                <td>${it.name || ('#'+it.id)}</td>
                <td>${it.time_label || ''}</td>
                <td style=\"text-align:center\">${it.guests||0}</td>
                <td>${it.status||''}</td>
              </tr>`).join('');
            bookingsBody.innerHTML = rows || '<tr><td colspan="4" class="subtle">No bookings.</td></tr>';
          } catch (e) {
            bookingsBody.innerHTML = '<tr><td colspan="4" class="subtle">Could not load.</td></tr>';
          }
        }

        // When form changes, refresh bookings
        const slotTimeEl = document.getElementById('slotFormTime');
        const slotDateEl = document.getElementById('slotFormDate');
        slotTimeEl && slotTimeEl.addEventListener('change', () => loadBookings(slotDateEl.value));
        slotDateEl && slotDateEl.addEventListener('change', () => loadBookings(slotDateEl.value));

        // No event handlers needed for max-per-slot input

        prevBtn && prevBtn.addEventListener('click', () => { if (!confirmNavigate()) return; current = new Date(current.getFullYear(), current.getMonth()-1, 1); renderMonth(); });
        nextBtn && nextBtn.addEventListener('click', () => { if (!confirmNavigate()) return; current = new Date(current.getFullYear(), current.getMonth()+1, 1); renderMonth(); });

        //

        // Auto-Fill Month: fill y/m and form defaults, then submit
        if (autoMonthBtn && autoMonthForm) {
          autoMonthBtn.addEventListener('click', () => {
            autoMonthForm.querySelector('input[name="y"]').value = String(current.getFullYear());
            autoMonthForm.querySelector('input[name="m"]').value = String(current.getMonth()+1);
            const cap = document.querySelector('input[name="capacity"]');
            const open = document.querySelector('input[name="is_open"]');
            if (cap) autoMonthForm.querySelector('input[name="capacity"]').value = cap.value || '100';
            autoMonthForm.querySelector('input[name="is_open"]').value = (open && open.checked) ? '1' : '0';
            if ((!hasUnsaved || confirm('You have unsaved capacity changes. Continue and lose them?')) && confirm('Auto-fill the entire visible month with hourly slots (07:00–22:00)?')) {
              autoMonthForm.submit();
            }
          });
        }

        if (clearMonthBtn && clearMonthForm) {
          clearMonthBtn.addEventListener('click', () => {
            clearMonthForm.querySelector('input[name="y"]').value = String(current.getFullYear());
            clearMonthForm.querySelector('input[name="m"]').value = String(current.getMonth()+1);
            if ((!hasUnsaved || confirm('You have unsaved capacity changes. Continue and lose them?')) && confirm('This will delete ALL slots for the visible month. Continue?')) {
              clearMonthForm.submit();
            }
          });
        }

        // Mark server-rendered inputs as dirty when changed
        try {
          document.querySelectorAll('#slotsBody input[name^="cap["]').forEach(inp => {
            inp.addEventListener('input', markDirty);
          });
        } catch (e) {}
        // Clear flag on bulk save
        bulkCapForm && bulkCapForm.addEventListener('submit', clearDirty);
        bulkCapFormTop && bulkCapFormTop.addEventListener('submit', clearDirty);

        renderMonth();
        // Initial bookings load
        if (slotFormDate) loadBookings(slotFormDate.value);
      }
    });
  </script>
  @php
    // Build time options 7:00–22:00 in 1-hour steps
    $defaultTime = old('time', '18:00');
    $timeOptions = [];
    for ($h = 7; $h <= 22; $h++) {
      $val = sprintf('%02d:00', $h);
      $label = \Carbon\Carbon::createFromTime($h, 0)->format('g:00 A');
      $timeOptions[$val] = $label;
    }
  @endphp
</head>
<body>
  <div class="container">
      <div class="header">
      <form method="get" class="toolbar" action="{{ route('admin.timeslots') }}">
        <label class="subtle" for="filterDate">Date</label>
        <input class="input" style="width:auto" id="filterDate" type="date" name="d" value="{{ $d }}">
        <button class="btn secondary" type="submit">Load</button>
      </form>
    </div>

    @if ($errors->any())
      <div class="card"><div class="card-body"><div class="alert error">{{ $errors->first() }}</div></div></div>
    @endif
    @if (session('ok'))
      <div class="card"><div class="card-body"><div class="alert success">{{ session('ok') }}</div></div></div>
    @endif

    <div class="card" style="padding:0">
      <div class="card-body" style="padding:0">
        <div class="grid cols-2" style="gap:0;grid-template-columns:720px 1fr">
          <div style="border-right:1px solid var(--border);padding:16px;width:720px;max-width:720px">
            <h2 style="margin:0 0 12px;font-size:18px">Calendar</h2>
            <div id="calendar">
              <div class="cal-header">
                <button id="calPrev" class="btn secondary" type="button" aria-label="Previous Month">‹</button>
                <div id="calMonth" class="cal-month"></div>
                <button id="calNext" class="btn secondary" type="button" aria-label="Next Month">›</button>
              </div>
              <div class="cal-weekdays">
                <div>MON</div><div>TUE</div><div>WED</div><div>THU</div><div>FRI</div><div>SAT</div><div>SUN</div>
              </div>
              <div id="calGrid" class="cal-grid"></div>
            </div>
            <!-- Booked panel moved under calendar -->
            <div style="margin-top:12px;border:1px solid var(--border);border-radius:12px;padding:12px;background:#fff">
              <h2 id="bookingsTitle" style="margin:0 0 8px;font-size:18px">Booked</h2>
              <table class="table" aria-label="Booked list" style="margin:0">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th style="width:80px">Time</th>
                    <th style="width:80px;text-align:center">Guests</th>
                    <th style="width:100px">Status</th>
                  </tr>
                </thead>
                <tbody id="bookingsBody"><tr><td colspan="4" class="subtle">No selection yet.</td></tr></tbody>
              </table>
            </div>
          </div>
          <div style="padding:16px">
            
            <!-- Form: Add/Update Slot -->
            <div style="border:1px solid var(--border);border-radius:12px;padding:12px;background:#fff;margin-bottom:12px">
              <h2 style="margin:0 0 8px;font-size:18px">Add / Update Slot</h2>
              <div class="subtle">Create or overwrite a slot for the selected date and time.</div>
              <div class="spacer"></div>
              <form method="post" action="{{ url('/admin/timeslots') }}" style="margin-bottom:8px">
                @csrf
                <input type="hidden" name="all_day" id="allDayFlag" value="0">
                <div class="grid cols-3">
                  <div>
                    <label class="label">Date</label>
                    <input class="input" id="slotFormDate" type="date" name="date" required value="{{ $d }}">
                  </div>
                  <div>
                    <label class="label">Time</label>
                    <select class="select" id="slotFormTime" name="time" required>
                      @foreach($timeOptions as $val => $label)
                        <option value="{{ $val }}" {{ $defaultTime === $val ? 'selected' : '' }}>{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="label">Capacity</label>
                    <input class="input" type="number" name="capacity" value="{{ old('capacity', 100) }}" min="0">
                  </div>
                  
                </div>
                <div class="grid cols-2" style="align-items:center;margin-top:10px">
                  <label class="label" style="display:flex;gap:8px;align-items:center;font-weight:500">
                    <input type="checkbox" name="is_open" {{ old('is_open', true) ? 'checked' : '' }}> Open
                  </label>
                    <div style="text-align:right; display:flex; gap:8px; justify-content:flex-end">
                      <button class="btn xs" type="submit">Save Slot</button>
                      <button class="btn success xs" type="button" onclick="(function(){document.getElementById('allDayFlag').value='1'; this.closest('form').submit();}).call(this)">All Day</button>
                      <button id="autoMonthBtn" class="btn success xs" type="button">Auto-Fill Month</button>
                      <button id="clearMonthBtn" class="btn danger xs" type="button">Clear Month</button>
                      <input type="hidden" name="close_day" id="closeDayFlag" value="0">
                      <button class="btn danger xs" type="button" onclick="(function(){document.getElementById('closeDayFlag').value='1'; this.closest('form').submit();}).call(this)">Fully Booked</button>
                    </div>
                </div>
                <div class="subtle" style="margin-top:6px">“All Day” creates/updates hourly slots from 07:00 AM to 10:00 PM.</div>
              </form>
              <!-- Hidden form for Auto-Fill Month -->
              <form id="autoMonthForm" method="post" action="{{ route('admin.timeslots.auto_fill_month') }}" style="display:none">
                @csrf
                <input type="hidden" name="y" value="">
                <input type="hidden" name="m" value="">
                <input type="hidden" name="capacity" value="{{ old('capacity', 100) }}">
                <input type="hidden" name="is_open" value="1">
              </form>
              <form id="clearMonthForm" method="post" action="{{ route('admin.timeslots.clear_month') }}" style="display:none">
                @csrf
                <input type="hidden" name="y" value="">
                <input type="hidden" name="m" value="">
              </form>
            </div>
            
            <div style="display:flex;align-items:center;justify-content:space-between;margin:0 0 8px">
              <h2 id="slotsTitle" style="margin:0;font-size:18px">Slots for {{ \Carbon\Carbon::parse($d)->format('m/d/Y') }}</h2>
              <!-- Submit the bulk form that actually holds cap[] inputs -->
              <button class="btn xs" type="button" onclick="document.getElementById('bulkCapForm') && document.getElementById('bulkCapForm').requestSubmit()">Save Changes</button>
            </div>
            <form id="bulkCapForm" method="post" action="{{ route('admin.timeslots.bulk_update') }}">
              @csrf
              <input type="hidden" name="d" value="{{ $d }}">
              <table class="table" aria-label="Timeslots list">
              <thead>
                <tr>
                  <th class="col-time">Time</th>
                  <th style="width:140px">Status</th>
                  <th class="col-cap">Capacity Left</th>
                  <th style="width:220px">Actions</th>
                </tr>
              </thead>
              <tbody id="slotsBody">
                @forelse($list as $r)
                  <tr>
                    <td class="time-cell">{{ \Carbon\Carbon::parse($r->time)->format('g:i A') }}</td>
                    <td>
                      <form method="post" action="{{ route('admin.timeslots.status', ['id'=>$r->id]) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="d" value="{{ $d }}">
                        <select name="status" class="select status-select {{ $r->is_open ? 'open' : 'closed' }}" onchange="this.classList.toggle('open', this.value==='open'); this.classList.toggle('closed', this.value!=='open'); this.form.submit()">
                          <option value="open" {{ $r->is_open ? 'selected' : '' }}>Open</option>
                          <option value="closed" {{ !$r->is_open ? 'selected' : '' }}>Closed</option>
                        </select>
                      </form>
                    </td>
                    <td class="col-cap">
                      @php $gsum = (int) ($guestSums[$r->time] ?? 0); $rem = max(0, (int)$r->capacity - $gsum); @endphp
                      <span class="cap-remaining {{ $rem <= 0 ? 'zero' : '' }}">{{ $rem }}</span>
                    </td>
                    <td class="actions">
                      <input class="input" type="number" name="cap[{{ $r->id }}]" value="{{ (int)$r->capacity }}" min="0" style="width:70px;padding:4px 6px">
                      <a href="{{ route('admin.timeslots.delete', ['id'=>$r->id, 'd'=>$d]) }}" onclick="return confirm('Delete this slot?')" style="margin-left:8px">Delete</a>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="subtle">No slots for this date.</td></tr>
                @endforelse
              </tbody>
              </table>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
