<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Schedule</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    html,body{max-width:100%;overflow-x:hidden}
    body{background:var(--bg)}
    .wrap{width:100%;max-width:none;margin:0;padding:20px 24px;overflow-x:hidden}
    .stack{display:grid;gap:10px;min-width:0}
    .head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .title{margin:0;font-size:22px;line-height:1.05}
    .subtitle{margin:0;color:var(--muted);font-size:13px;font-weight:600}
    .actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .date-tools{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-left:auto}
    .date-label{font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase}
    .date-picker{width:148px;height:30px;border:1px solid #dfe3ea;border-radius:8px;background:#fff;color:#111827;font-size:12px;padding:4px 8px}
    .card,.section{min-width:0;max-width:100%}
    .section{padding:10px}
    .summary-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:8px}
    .summary-pill{display:inline-flex;align-items:center;gap:6px;min-height:28px;padding:5px 9px;border:1px solid #e5e7eb;border-radius:999px;background:#fff;color:#334155;font-size:11px;font-weight:800}
    .summary-pill strong{color:#111827;font-variant-numeric:tabular-nums}
    .week-bar{display:grid;grid-template-columns:30px repeat(7,minmax(0,1fr)) 30px;gap:5px;align-items:stretch;min-width:0}
    .week-nav,.day-card{display:flex;align-items:center;justify-content:center;text-decoration:none;border:1px solid #e4e8ef;background:linear-gradient(180deg,#fff,#fbfcfe);color:#374151;border-radius:8px;min-width:0;min-height:42px;transition:background .14s ease,border-color .14s ease,box-shadow .14s ease,color .14s ease,transform .14s ease}
    .week-nav{font-size:16px;font-weight:800;padding:0}
    .week-nav:hover,.day-card:hover{background:#fff;border-color:#d1d5db;box-shadow:0 5px 12px rgba(15,23,42,.06);transform:translateY(-1px)}
    .day-card{flex-direction:column;gap:0;padding:5px 4px}
    .day-card.selected{background:linear-gradient(180deg,#fff5f5,#fff);border-color:#ef4444;box-shadow:inset 0 0 0 1px rgba(239,68,68,.16),0 8px 18px rgba(178,30,39,.1);color:#991b1b}
    .day-card.today:not(.selected){background:#f8fafc;border-color:#cbd5e1}
    .day-name{font-size:11px;font-weight:800;text-transform:uppercase;color:inherit}
    .day-date{font-size:13px;font-weight:800;color:#111827}
    .day-meta{font-size:10px;color:var(--muted);line-height:1.1}
    .day-card.selected .day-date,.day-card.selected .day-meta{color:#991b1b}
    .selected-date{display:flex;align-items:center;gap:10px;flex-wrap:wrap;color:#374151;font-size:14px}
    .table-wrap{width:100%;max-width:100%;min-width:0;overflow-x:auto;overflow-y:visible;border:1px solid #dfe4ec;border-radius:12px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.04);-webkit-overflow-scrolling:touch}
    .schedule-table{width:max-content;min-width:0;border-collapse:separate;border-spacing:0;table-layout:auto}
    .schedule-table th,.schedule-table td{padding:4px 6px;border-bottom:1px solid #edf0f4;font-size:11px;vertical-align:middle;white-space:nowrap}
    .schedule-table th{position:sticky;top:0;z-index:1;background:#f8fafc;color:#475569;font-weight:900;text-transform:uppercase;font-size:9.5px;letter-spacing:.04em}
    .schedule-table tbody tr{transition:background .12s ease,box-shadow .12s ease}
    .schedule-table tbody tr:hover{background:#fbfdff}
    .schedule-table tbody tr.is-canceled{background:#fff7f7}
    .schedule-table tbody tr.is-missing-chef td:first-child{box-shadow:inset 3px 0 0 #f59e0b}
    .schedule-table tbody tr:last-child td{border-bottom:0}
    .row-number{font-weight:800;color:#475569;text-align:center}
    .customer{font-weight:800;color:#111827}
    .muted-small{display:block;margin-top:2px;color:var(--muted);font-size:10px}
    .status-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 7px;border:1px solid #fde68a;border-radius:999px;background:#fffbeb;color:#92400e;font-size:10px;font-weight:900;line-height:1}
    .status-pill.confirmed{background:#ecfdf5;border-color:#bbf7d0;color:#166534}
    .status-pill.canceled{background:#fef2f2;border-color:#fecaca;color:#991b1b}
    .status-pill.partial{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
    .status-pill.pending{background:#fffbeb;border-color:#fde68a;color:#92400e}
    .status-pill.warning{background:#fff7ed;border-color:#fed7aa;color:#c2410c}
    .status-pill.viewed{background:#dbeafe;border-color:#bfdbfe;color:#1d4ed8}
    .status-pill.not-viewed{background:#f1f5f9;border-color:#e2e8f0;color:#64748b}
    .status-dot{width:6px;height:6px;border-radius:999px;background:currentColor}
    .schedule-input,.schedule-select,.schedule-note{width:100%;min-width:0;border:1px solid #dce3ec;background:#fff;border-radius:7px;color:#111827;font-size:11px;line-height:1.2}
    .schedule-input,.schedule-select{height:25px;padding:3px 6px}
    .schedule-select.chef-selected{background:#ecfdf3;border-color:#86efac;color:#166534;font-weight:700}
    .schedule-note{height:26px;min-height:26px;max-height:52px;min-width:0;padding:5px 6px;resize:vertical;white-space:normal}
    .schedule-input:focus,.schedule-select:focus,.schedule-note:focus{outline:none;border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.1)}
    .col-xs{width:46px}
    .col-sm{width:78px}
    .col-md{width:112px}
    .col-chefs{width:auto}
    .col-van{width:56px}
    .col-leave{width:96px}
    .col-lg{width:160px}
    .reservation-note-preview{max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#64748b;font-size:10px;line-height:1.1;margin-bottom:2px}
    .chef-chain-cell{white-space:normal;width:106px;min-width:106px;max-width:106px}
    .chef-chain-cell.has-extra{width:var(--chef-chain-width,205px);min-width:var(--chef-chain-width,205px);max-width:var(--chef-chain-width,205px)}
    .chef-chain{display:inline-flex;align-items:flex-start;gap:5px;min-width:0;width:auto}
    .chef-base-field{width:94px;flex:0 0 94px}
    .chef-confirmation{margin-top:3px;max-width:94px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .extra-chef-grid{display:flex;flex-wrap:wrap;gap:5px;flex:0 0 auto;min-width:0;max-width:490px}
    .extra-chef-grid:empty{display:none}
    .chef-chain.has-extra{display:inline-flex;width:var(--chef-chain-width,205px)}
    .extra-chef-field{min-width:0;width:94px}
    .extra-chef-label{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}
    .extra-chef-field .schedule-select{height:26px;padding:4px 6px;font-size:11px}
    .chef-menu-target{border-radius:9px;padding:2px;cursor:default}
    .chef-menu-target.is-selected{outline:2px dotted #1d9bf0;outline-offset:1px;background:#eff6ff}
    .chef-menu{position:fixed;z-index:50;min-width:150px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 12px 24px rgba(15,23,42,.15);padding:6px 0;display:none}
    .chef-menu.open{display:block}
    .chef-menu button{display:flex;width:100%;align-items:center;gap:8px;border:0;background:#fff;color:#111827;padding:9px 12px;font-size:13px;text-align:left;cursor:pointer}
    .chef-menu button:hover{background:#f8fafc}
    .chef-menu button.danger{color:#b91c1c}
    .chef-menu-separator{height:1px;background:#e5e7eb;margin:5px 0}
    .staff-count-widget{position:fixed;right:22px;bottom:22px;z-index:44}
    .staff-count-toggle{display:inline-flex;align-items:center;gap:7px;min-height:38px;border:1px solid #243b53;border-radius:999px;background:#243b53;color:#fff;padding:8px 13px;font-size:12px;font-weight:800;box-shadow:0 12px 24px rgba(36,59,83,.2);cursor:pointer}
    .staff-count-toggle:hover{background:#1b2f45;border-color:#1b2f45}
    .staff-count-badge{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:#fff;color:#243b53;font-size:11px;font-weight:900}
    .staff-count-panel{position:absolute;right:0;bottom:48px;width:270px;max-height:min(420px,calc(100vh - 96px));overflow:auto;border:1px solid #e5e7eb;border-radius:12px;background:#fff;box-shadow:0 18px 42px rgba(15,23,42,.18);padding:12px;display:none}
    .staff-count-widget.open .staff-count-panel{display:block}
    .staff-count-title{font-size:14px;font-weight:900;color:#111827;line-height:1.1}
    .staff-count-range{margin-top:3px;color:#64748b;font-size:11px;font-weight:700}
    .staff-count-list{display:grid;gap:2px;margin-top:10px}
    .staff-count-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:7px 2px;border-top:1px solid #f1f5f9;font-size:12px}
    .staff-count-row:first-child{border-top:0}
    .staff-count-name{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#111827;font-weight:800;letter-spacing:.02em}
    .staff-count-value{font-variant-numeric:tabular-nums;color:#991b1b;font-weight:900}
    .staff-count-empty{margin-top:12px;color:#64748b;font-size:12px;font-weight:700}
    .payment-cell{font-variant-numeric:tabular-nums}
    .payment-cell .status-pill{padding:3px 7px;font-size:10px}
    .money{font-weight:800;color:#111827}
    .save-state{display:inline-flex;align-items:center;min-width:0;color:#166534;font-size:11px;font-weight:700}
    .save-state.error{color:#b91c1c}
    .empty{padding:42px 12px;text-align:center;color:var(--muted);font-weight:700}
    @media (max-width: 980px){
      .week-bar{grid-template-columns:repeat(2,minmax(0,1fr))}
      .week-nav{min-height:42px}
    }
    @media (max-width: 760px){
      .wrap{padding:16px}
      .section{padding:10px}
      .week-bar{grid-template-columns:repeat(2,minmax(0,1fr));gap:6px}
      .day-card{min-height:46px}
    }
  </style>
</head>
<body>
  @php
    $statusClass = function (?string $status): string {
        $status = strtolower((string) $status);
        if (str_contains($status, 'cancel') || str_contains($status, 'void')) return 'canceled';
        if (str_contains($status, 'confirm') || str_contains($status, 'paid')) return 'confirmed';
        if (str_contains($status, 'partial')) return 'partial';
        return 'pending';
    };
    $timeValue = function ($value): string {
        if (blank($value)) return '';
        try { return \Carbon\CarbonImmutable::parse((string) $value)->format('H:i'); } catch (\Throwable $e) { return substr((string) $value, 0, 5); }
    };
    $timeLabel = function ($value): string {
        if (blank($value)) return '';
        try { return \Carbon\CarbonImmutable::parse((string) $value)->format('g:i A'); } catch (\Throwable $e) { return (string) $value; }
    };
    $paymentLabel = function ($reservation): array {
        $totals = $reservation->schedule_totals ?? [];
        $total = (float) ($totals['total'] ?? $reservation->total ?? 0);
        $paid = (float) ($totals['paid_total'] ?? $reservation->amount_paid_total ?? 0);
        $balance = (float) ($totals['balance'] ?? $reservation->balance ?? max(0, $total - $paid));
        if ($total > 0 && $paid >= ($total - 0.01)) return ['Paid', 'confirmed', '$' . number_format($paid, 2)];
        if ($paid > 0) return ['Partial', 'partial', '$' . number_format($balance, 2) . ' due'];
        return ['Pending', 'pending', '$' . number_format($total, 2)];
    };
    $extraChefIdsFor = function ($assignment): array {
        $extra = (array) ($assignment?->extra_chef_ids ?? []);
        if (!array_key_exists('4', $extra) && !blank($assignment?->chef_4_id)) {
            $extra['4'] = (int) $assignment->chef_4_id;
        }
        $extra = array_filter($extra, fn ($value) => $value !== '' && $value !== null, ARRAY_FILTER_USE_BOTH);
        ksort($extra, SORT_NUMERIC);

        return $extra;
    };
    $confirmationPillFor = function ($reservation, $userId): string {
        if (blank($userId)) {
            return '';
        }

        $summary = $reservation->staffConfirmationSummaryFor((int) $userId);
        $text = e($summary['label'] . ($summary['timestamp'] ? ' ' . $summary['timestamp'] : ''));
        $tone = e($summary['tone']);

        return '<span class="status-pill '.$tone.'">'.$text.'</span>';
    };
    $dailyEventCount = $reservations->count();
    $dailyGuestCount = (int) $reservations->sum(fn ($reservation) => (int) ($reservation->guests ?? 0));
    $dailyAssignedStaffCount = $reservations
        ->flatMap(function ($reservation) use ($extraChefIdsFor) {
            $assignment = $reservation->scheduleAssignment;
            $ids = [
                $assignment?->chef_1_id,
                $assignment?->chef_2_id,
                $assignment?->chef_3_id,
            ];

            return array_merge($ids, array_values($extraChefIdsFor($assignment)));
        })
        ->filter(fn ($id) => !blank($id))
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->count();
    $chefOptionsJson = $chefOptions->map(fn ($member) => ['id' => $member->id, 'name' => $member->name])->values();
  @endphp

  <div class="wrap">
    <div class="stack">
      <div class="head">
        <div>
          <p class="subtitle">Daily event dispatch from reservations, with internal schedule assignments saved separately.</p>
        </div>
        <div class="actions">
          <span class="save-state" id="globalSaveState"></span>
          <a class="btn secondary" href="{{ route('admin.schedule.rules') }}">Priority Rules</a>
          <a class="btn secondary" href="{{ route('admin.schedule.assign') }}">Assign Event</a>
        </div>
      </div>

      <div class="card section">
        <div class="selected-date">
          <strong>{{ $selectedDate->format('l, F j, Y') }}</strong>
          @if($selectedDate->isSameDay($today))
            <span class="status-pill confirmed"><span class="status-dot"></span>Today</span>
          @endif
          <form class="date-tools" method="get" action="{{ route('admin.schedule.index') }}">
            <label class="date-label" for="schedule-date">Date</label>
            <input class="date-picker" id="schedule-date" type="date" name="date" value="{{ $selectedDate->toDateString() }}" onchange="this.form.submit()">
          </form>
        </div>
        <div class="summary-row">
          <span class="summary-pill">Date <strong>{{ $selectedDate->format('M j') }}</strong></span>
          <span class="summary-pill">Events <strong>{{ $dailyEventCount }}</strong></span>
          <span class="summary-pill">Guests <strong>{{ $dailyGuestCount }}</strong></span>
          <span class="summary-pill">Assigned Staff <strong>{{ $dailyAssignedStaffCount }}</strong></span>
        </div>
        <div class="week-bar" style="margin-top:12px">
          <a class="week-nav" href="{{ route('admin.schedule.index', ['date' => $previousWeekDate->toDateString()]) }}" aria-label="Previous week">&lt;</a>
          @foreach($weekDays as $day)
            <a class="day-card {{ $day['is_selected'] ? 'selected' : '' }} {{ $day['is_today'] ? 'today' : '' }}" href="{{ route('admin.schedule.index', ['date' => $day['date']->toDateString()]) }}">
              <span class="day-name">{{ $day['date']->format('D') }}</span>
              <span class="day-date">{{ $day['date']->format('M j') }}</span>
              <span class="day-meta">{{ $day['count'] }} {{ \Illuminate\Support\Str::plural('event', $day['count']) }}</span>
            </a>
          @endforeach
          <a class="week-nav" href="{{ route('admin.schedule.index', ['date' => $nextWeekDate->toDateString()]) }}" aria-label="Next week">&gt;</a>
        </div>
      </div>

      <div class="card section">
        @if(session('ok'))
          <div style="margin-bottom:12px;color:#166534;font-weight:700">{{ session('ok') }}</div>
        @endif

        <div class="table-wrap">
          <table class="schedule-table">
            <thead>
              <tr>
                <th class="col-sm">Status</th>
                <th class="col-xs">#</th>
                <th class="col-md">Name</th>
                <th class="col-md">City</th>
                <th class="col-xs">Guests</th>
                <th class="col-sm">Time to Drive</th>
                <th class="col-sm">Start</th>
                <th class="col-md">Chef 1</th>
                <th class="col-md">Chef 2</th>
                <th class="col-chefs">Chef 3</th>
                <th class="col-van">Van</th>
                <th class="col-leave">Leave At</th>
                <th class="col-sm">Color</th>
                <th class="col-xs">Stairs</th>
                <th class="col-lg">Notes</th>
                <th class="col-sm">Payment</th>
                <th class="col-sm">Chef Tip</th>
                <th class="col-md">Assistant</th>
                <th class="col-md">Confirm By</th>
              </tr>
            </thead>
            <tbody>
              @forelse($reservations as $reservation)
                @php
                  $assignment = $reservation->scheduleAssignment;
                  $reservationStatus = $statusClass($reservation->status);
                  [$paymentText, $paymentClass, $paymentSubtext] = $paymentLabel($reservation);
                  $extraChefIds = $extraChefIdsFor($assignment);
                  $hasChefAssignment = !blank($assignment?->chef_1_id)
                    || !blank($assignment?->chef_2_id)
                    || !blank($assignment?->chef_3_id)
                    || count($extraChefIds) > 0;
                  $chefTipValue = $assignment?->chef_tip !== null
                    ? (float) $assignment->chef_tip
                    : (float) ($reservation->schedule_totals['gratuity'] ?? $reservation->gratuity ?? 0);
                  $saveUrl = route('admin.schedule.assignment.update', $reservation);
                @endphp
                <tr class="{{ $reservationStatus === 'canceled' ? 'is-canceled' : '' }} {{ !$hasChefAssignment ? 'is-missing-chef' : '' }}" data-reservation-id="{{ $reservation->id }}">
                  <td>
                    <span class="status-pill {{ $reservationStatus }}"><span class="status-dot"></span>{{ $reservation->status ?: 'Pending' }}</span>
                    @if(!$hasChefAssignment)
                      <span class="muted-small"><span class="status-pill warning">Need chef</span></span>
                    @endif
                    <span class="muted-small save-state" data-save-state></span>
                  </td>
                  <td class="row-number">{{ $loop->iteration }}</td>
                  <td>
                    <div class="customer">{{ $reservation->customer_name ?: 'Reservation #' . $reservation->id }}</div>
                    <span class="muted-small">{{ $reservation->code }}</span>
                  </td>
                  <td>{{ $reservation->city ?: '-' }}</td>
                  <td>{{ (int) $reservation->guests }}</td>
                  <td>
                    <input class="schedule-input js-schedule-save" data-url="{{ $saveUrl }}" data-field="time_to_drive" value="{{ $assignment?->time_to_drive }}" placeholder="2 hrs">
                  </td>
                  <td>{{ $timeLabel($reservation->time) ?: '-' }}</td>
                  @foreach(['chef_1_id', 'chef_2_id'] as $chefField)
                    <td>
                      <select class="schedule-select js-schedule-save" data-url="{{ $saveUrl }}" data-field="{{ $chefField }}">
                        <option value=""></option>
                        @foreach($chefOptions as $member)
                          <option value="{{ $member->id }}" {{ (string) ($assignment?->{$chefField} ?? '') === (string) $member->id ? 'selected' : '' }}>
                            {{ $member->name }}
                          </option>
                        @endforeach
                      </select>
                      @if(filled($assignment?->{$chefField}))
                        <span class="muted-small chef-confirmation">{!! $confirmationPillFor($reservation, $assignment->{$chefField}) !!}</span>
                      @endif
                    </td>
                  @endforeach
                  <td class="chef-chain-cell {{ count($extraChefIds) > 0 ? 'has-extra' : '' }}" style="{{ count($extraChefIds) > 0 ? '--chef-chain-width:' . (106 + (min(count($extraChefIds), 5) * 99)) . 'px' : '' }}">
                    <div class="chef-chain {{ count($extraChefIds) > 0 ? 'has-extra' : '' }}" data-extra-chefs data-url="{{ $saveUrl }}" style="{{ count($extraChefIds) > 0 ? '--chef-chain-width:' . (106 + (min(count($extraChefIds), 5) * 99)) . 'px' : '' }}">
                      <div class="chef-base-field chef-menu-target" data-chef-menu-target data-chef-number="3" data-base-chef="1">
                        <select class="schedule-select js-schedule-save" data-url="{{ $saveUrl }}" data-field="chef_3_id">
                          <option value=""></option>
                          @foreach($chefOptions as $member)
                            <option value="{{ $member->id }}" {{ (string) ($assignment?->chef_3_id ?? '') === (string) $member->id ? 'selected' : '' }}>
                              {{ $member->name }}
                            </option>
                          @endforeach
                        </select>
                        @if(filled($assignment?->chef_3_id))
                          <span class="muted-small chef-confirmation">{!! $confirmationPillFor($reservation, $assignment->chef_3_id) !!}</span>
                        @endif
                      </div>
                      <div class="extra-chef-grid" data-extra-chef-grid>
                        @foreach($extraChefIds as $chefNumber => $chefId)
                          <div class="extra-chef-field chef-menu-target" data-chef-menu-target data-chef-number="{{ $chefNumber }}">
                            <label class="extra-chef-label">Chef {{ $chefNumber }}</label>
                            <select class="schedule-select js-schedule-save" data-url="{{ $saveUrl }}" data-field="extra_chef_ids" data-chef-number="{{ $chefNumber }}">
                              <option value=""></option>
                              @foreach($chefOptions as $member)
                                <option value="{{ $member->id }}" {{ (string) $chefId === (string) $member->id ? 'selected' : '' }}>
                                  {{ $member->name }}
                                </option>
                              @endforeach
                            </select>
                            @if(filled($chefId))
                              <span class="muted-small chef-confirmation">{!! $confirmationPillFor($reservation, $chefId) !!}</span>
                            @endif
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </td>
                  <td>
                    <input class="schedule-input js-schedule-save" data-url="{{ $saveUrl }}" data-field="van" value="{{ $assignment?->van }}" placeholder="# 7">
                  </td>
                  <td>
                    <input class="schedule-input js-schedule-save" type="time" data-url="{{ $saveUrl }}" data-field="leave_at" value="{{ $timeValue($assignment?->leave_at) }}">
                  </td>
                  <td>{{ $reservation->setup_color ?: $reservation->color ?: '-' }}</td>
                  <td>{{ $reservation->stairs ? 'Yes' : 'No' }}</td>
                  <td>
                    @if($reservation->notes)
                      <div class="reservation-note-preview" title="{{ $reservation->notes }}">{{ $reservation->notes }}</div>
                    @endif
                    <textarea class="schedule-note js-schedule-save" data-url="{{ $saveUrl }}" data-field="schedule_notes" placeholder="Schedule notes">{{ $assignment?->schedule_notes }}</textarea>
                  </td>
                  <td class="payment-cell">
                    <span class="status-pill {{ $paymentClass }}"><span class="status-dot"></span>{{ $paymentText }}</span>
                    <span class="muted-small">{{ $paymentSubtext }}</span>
                  </td>
                  <td>
                    <input class="schedule-input js-schedule-save" type="number" min="0" step="0.01" data-url="{{ $saveUrl }}" data-field="chef_tip" value="{{ $chefTipValue > 0 ? number_format($chefTipValue, 2, '.', '') : '' }}" placeholder="0.00">
                  </td>
                  <td>
                    <select class="schedule-select js-schedule-save" data-url="{{ $saveUrl }}" data-field="assistant_id">
                      <option value=""></option>
                      @foreach($officeOptions as $member)
                        <option value="{{ $member->id }}" {{ (string) ($assignment?->assistant_id ?? '') === (string) $member->id ? 'selected' : '' }}>
                          {{ $member->name }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <select class="schedule-select js-schedule-save" data-url="{{ $saveUrl }}" data-field="confirm_by_id">
                      <option value=""></option>
                      @foreach($officeOptions as $member)
                        <option value="{{ $member->id }}" {{ (string) ($assignment?->confirm_by_id ?? '') === (string) $member->id ? 'selected' : '' }}>
                          {{ $member->name }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="19" class="empty">No scheduled events for this date.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="staff-count-widget" id="staffCountWidget">
    <div class="staff-count-panel" id="staffCountPanel" aria-hidden="true">
      <div class="staff-count-title">Weekly Staff Count</div>
      <div class="staff-count-range" id="staffCountRange">{{ $weekStart->format('M j') }} - {{ $weekEnd->format('M j') }}</div>
      <div class="staff-count-empty" id="staffCountEmpty" style="{{ $weeklyStaffCounts->isEmpty() ? '' : 'display:none' }}">No staff assignments this week.</div>
      <div class="staff-count-list" id="staffCountList">
        @foreach($weeklyStaffCounts as $staffCount)
          <div class="staff-count-row">
            <span class="staff-count-name">{{ \Illuminate\Support\Str::upper($staffCount['name']) }}</span>
            <span class="staff-count-value">{{ $staffCount['count'] }}</span>
          </div>
        @endforeach
      </div>
    </div>
    <button class="staff-count-toggle" id="staffCountToggle" type="button" aria-expanded="false" aria-controls="staffCountPanel">
      Staff Count
      <span class="staff-count-badge" id="staffCountBadge">{{ $weeklyStaffCounts->count() }}</span>
    </button>
  </div>

  <div class="chef-menu" id="chefContextMenu" role="menu" aria-hidden="true">
    <button type="button" data-chef-action="copy" role="menuitem">Copy</button>
    <div class="chef-menu-separator" data-delete-separator></div>
    <button class="danger" type="button" data-chef-action="delete" role="menuitem">Delete</button>
  </div>

  <script>
    (() => {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const globalState = document.getElementById('globalSaveState');
      const timers = new WeakMap();
      const chefOptions = @json($chefOptionsJson);
      const staffCountUrl = @json(route('admin.schedule.staff_counts', ['date' => $selectedDate->toDateString()]));
      const chefMenu = document.getElementById('chefContextMenu');
      const staffCountWidget = document.getElementById('staffCountWidget');
      const staffCountToggle = document.getElementById('staffCountToggle');
      const staffCountPanel = document.getElementById('staffCountPanel');
      const staffCountRange = document.getElementById('staffCountRange');
      const staffCountBadge = document.getElementById('staffCountBadge');
      const staffCountList = document.getElementById('staffCountList');
      const staffCountEmpty = document.getElementById('staffCountEmpty');
      const countableAssignmentFields = new Set(['chef_1_id', 'chef_2_id', 'chef_3_id', 'extra_chef_ids']);
      let activeChefField = null;
      let staffCountRefreshTimer = null;

      function setState(row, message, isError = false) {
        const state = row?.querySelector('[data-save-state]');
        if (state) {
          state.textContent = message;
          state.classList.toggle('error', isError);
        }
        if (globalState) {
          globalState.textContent = message;
          globalState.classList.toggle('error', isError);
        }
      }

      async function save(input) {
        const row = input.closest('tr');
        const payload = {
          field: input.dataset.field,
          chef_number: input.dataset.chefNumber || null,
          value: input.value,
        };

        return savePayload(input.dataset.url, row, payload);
      }

      function isChefSelect(input) {
        return input?.tagName === 'SELECT' && ['chef_1_id', 'chef_2_id', 'chef_3_id', 'extra_chef_ids'].includes(input.dataset.field || '');
      }

      function syncChefSelectState(input) {
        if (!isChefSelect(input)) {
          return;
        }

        input.classList.toggle('chef-selected', input.value !== '');
      }

      async function savePayload(url, row, payload) {
        setState(row, 'Saving');

        try {
          const response = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify(payload),
          });

          if (!response.ok) {
            throw new Error('Unable to save');
          }

          setState(row, 'Saved');
          if (row && countableAssignmentFields.has(payload.field)) {
            queueStaffCountRefresh();
          }
          window.setTimeout(() => setState(row, ''), 1600);
        } catch (error) {
          setState(row, 'Save error', true);
        }
      }

      function queueStaffCountRefresh() {
        window.clearTimeout(staffCountRefreshTimer);
        staffCountRefreshTimer = window.setTimeout(refreshStaffCounts, 450);
      }

      async function refreshStaffCounts() {
        if (!staffCountUrl) {
          return;
        }

        try {
          const url = new URL(staffCountUrl, window.location.origin);
          url.searchParams.set('_', String(Date.now()));

          const response = await fetch(url.toString(), {
            cache: 'no-store',
            headers: {
              'Accept': 'application/json',
            },
          });

          if (!response.ok) {
            throw new Error('Unable to refresh staff count');
          }

          const data = await response.json();
          renderStaffCounts(data.counts || []);

          if (staffCountRange && data.week?.label) {
            staffCountRange.textContent = data.week.label;
          }
        } catch (error) {
          // Keep the last good count visible if the refresh request fails.
        }
      }

      function renderStaffCounts(counts) {
        const rows = (Array.isArray(counts) ? counts : [])
          .map((item) => ({
            id: String(item.id),
            name: item.name || `User #${item.id}`,
            count: Number(item.count || 0),
          }))
          .filter((item) => item.count > 0)
          .sort((a, b) => b.count - a.count || a.name.localeCompare(b.name));

        if (staffCountBadge) {
          staffCountBadge.textContent = String(rows.length);
        }
        if (staffCountEmpty) {
          staffCountEmpty.style.display = rows.length === 0 ? '' : 'none';
        }
        if (!staffCountList) {
          return;
        }

        staffCountList.innerHTML = rows.map((item) => `
          <div class="staff-count-row">
            <span class="staff-count-name">${escapeHtml(item.name.toUpperCase())}</span>
            <span class="staff-count-value">${item.count}</span>
          </div>
        `).join('');
      }

      function escapeHtml(value) {
        return String(value)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function bindSave(input) {
        if (input.dataset.bound === '1') {
          return;
        }

        input.dataset.bound = '1';
        const eventName = input.tagName === 'SELECT' || input.type === 'time' || input.type === 'number' ? 'change' : 'input';
        input.addEventListener(eventName, () => {
          syncChefSelectState(input);
          window.clearTimeout(timers.get(input));
          timers.set(input, window.setTimeout(() => save(input), eventName === 'input' ? 450 : 0));
        });
      }

      function nextChefNumber(container) {
        const used = Array.from(container.querySelectorAll('.extra-chef-field[data-chef-number]'))
          .map((node) => Number(node.dataset.chefNumber))
          .filter((number) => Number.isInteger(number) && number >= 4 && number <= 27);

        if (used.length === 0) {
          return 4;
        }

        return Math.max(...used) + 1;
      }

      function buildExtraChefField(container, chefNumber) {
        const url = container.dataset.url;
        const field = document.createElement('div');
        field.className = 'extra-chef-field chef-menu-target';
        field.dataset.chefMenuTarget = '1';
        field.dataset.chefNumber = String(chefNumber);

        const label = document.createElement('label');
        label.className = 'extra-chef-label';
        label.textContent = `Chef ${chefNumber}`;
        label.setAttribute('aria-label', `Chef ${chefNumber}`);

        const select = document.createElement('select');
        select.className = 'schedule-select js-schedule-save';
        select.dataset.url = url;
        select.dataset.field = 'extra_chef_ids';
        select.dataset.chefNumber = String(chefNumber);

        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = '';
        select.appendChild(empty);

        chefOptions.forEach((member) => {
          const option = document.createElement('option');
          option.value = String(member.id);
          option.textContent = member.name;
          select.appendChild(option);
        });

        field.append(label, select);
        bindSave(select);
        syncChefSelectState(select);

        return field;
      }

      function serializeExtraChefs(container) {
        const values = {};
        Array.from(container.querySelectorAll('.extra-chef-field')).forEach((field) => {
          const number = field.dataset.chefNumber;
          const select = field.querySelector('select');
          if (number && select) {
            values[number] = select.value || null;
          }
        });

        return values;
      }

      function renumberExtraChefs(container) {
        Array.from(container.querySelectorAll('.extra-chef-field')).forEach((field, index) => {
          const chefNumber = index + 4;
          field.dataset.chefNumber = String(chefNumber);
          const label = field.querySelector('.extra-chef-label');
          const select = field.querySelector('select');
          if (label) {
            label.textContent = `Chef ${chefNumber}`;
          }
          if (select) {
            select.dataset.chefNumber = String(chefNumber);
          }
        });
      }

      function syncExtraChefLayout(container) {
        const extraCount = container.querySelectorAll('.extra-chef-field').length;
        const visibleCount = Math.min(extraCount, 5);
        const width = 106 + (visibleCount * 99);
        const cell = container.closest('.chef-chain-cell');
        const hasExtra = extraCount > 0;

        container.classList.toggle('has-extra', hasExtra);
        cell?.classList.toggle('has-extra', hasExtra);

        if (hasExtra) {
          container.style.setProperty('--chef-chain-width', `${width}px`);
          cell?.style.setProperty('--chef-chain-width', `${width}px`);
        } else {
          container.style.removeProperty('--chef-chain-width');
          cell?.style.removeProperty('--chef-chain-width');
        }
      }

      function saveExtraChefSet(container) {
        return savePayload(container.dataset.url, container.closest('tr'), {
          field: 'extra_chef_ids',
          value: serializeExtraChefs(container),
        });
      }

      function hideChefMenu() {
        chefMenu?.classList.remove('open');
        chefMenu?.setAttribute('aria-hidden', 'true');
        activeChefField?.classList.remove('is-selected');
        activeChefField = null;
      }

      function showChefMenu(target, event) {
        if (!chefMenu) {
          return;
        }

        event.preventDefault();
        event.stopPropagation();
        activeChefField?.classList.remove('is-selected');
        activeChefField = target;
        activeChefField.classList.add('is-selected');

        const canDelete = !target.dataset.baseChef;
        chefMenu.querySelector('[data-chef-action="delete"]').style.display = canDelete ? 'flex' : 'none';
        chefMenu.querySelector('[data-delete-separator]').style.display = canDelete ? 'block' : 'none';

        const x = Math.min(event.clientX, window.innerWidth - 170);
        const y = Math.min(event.clientY + 8, window.innerHeight - (canDelete ? 100 : 54));
        chefMenu.style.left = `${Math.max(8, x)}px`;
        chefMenu.style.top = `${Math.max(8, y)}px`;
        chefMenu.classList.add('open');
        chefMenu.setAttribute('aria-hidden', 'false');
      }

      function copyChefField(field) {
        const container = field.closest('[data-extra-chefs]');
        const grid = container?.querySelector('[data-extra-chef-grid]');
        if (!container || !grid) {
          return;
        }

        const chefNumber = nextChefNumber(container);
        if (chefNumber > 27) {
          setState(container.closest('tr'), 'Max chefs');
          window.setTimeout(() => setState(container.closest('tr'), ''), 1600);
          return;
        }

        const newField = buildExtraChefField(container, chefNumber);
        grid.appendChild(newField);
        syncExtraChefLayout(container);
        newField.querySelector('select')?.focus();
        saveExtraChefSet(container);
      }

      function deleteChefField(field) {
        if (field.dataset.baseChef) {
          return;
        }

        const container = field.closest('[data-extra-chefs]');
        field.remove();
        if (container) {
          renumberExtraChefs(container);
          syncExtraChefLayout(container);
          saveExtraChefSet(container);
        }
      }

      document.querySelectorAll('.js-schedule-save').forEach((input) => {
        bindSave(input);
        syncChefSelectState(input);
      });

      staffCountToggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = staffCountWidget?.classList.toggle('open') || false;
        staffCountToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        staffCountPanel?.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
      });

      staffCountWidget?.addEventListener('click', (event) => {
        event.stopPropagation();
      });

      document.addEventListener('click', (event) => {
        const target = event.target.closest('[data-chef-menu-target]');
        if (target) {
          if (event.target.closest('select')) {
            hideChefMenu();
            return;
          }
          showChefMenu(target, event);
          return;
        }

        if (!event.target.closest('#chefContextMenu')) {
          hideChefMenu();
        }

        if (!event.target.closest('#staffCountWidget')) {
          staffCountWidget?.classList.remove('open');
          staffCountToggle?.setAttribute('aria-expanded', 'false');
          staffCountPanel?.setAttribute('aria-hidden', 'true');
        }
      });

      document.addEventListener('contextmenu', (event) => {
        const target = event.target.closest('[data-chef-menu-target]');
        if (target) {
          showChefMenu(target, event);
        }
      });

      chefMenu?.addEventListener('click', (event) => {
        const action = event.target.closest('[data-chef-action]')?.dataset.chefAction;
        if (!action || !activeChefField) {
          return;
        }

        const field = activeChefField;
        hideChefMenu();

        if (action === 'copy') {
          copyChefField(field);
        } else if (action === 'delete') {
          deleteChefField(field);
        }
      });
    })();
  </script>
</body>
</html>
