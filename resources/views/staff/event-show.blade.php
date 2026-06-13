@extends('layouts.staff')

@section('title', 'Event Details')

@php
  $event = $reservation;
  $assignment = $event->scheduleAssignment;
  $fmtDate = fn($date) => $date ? $date->format('l, F j, Y') : 'N/A';
  $fmtTime = function ($time) {
      if (blank($time)) return 'N/A';
      try { return \Carbon\Carbon::parse($time)->format('g:i A'); } catch (\Throwable $e) { return (string) $time; }
  };
  $fmtMoney = fn($amount) => '$'.number_format((float) $amount, 2);
  $address = collect([$event->address, $event->city, $event->zip_code])->filter(fn($v) => filled($v))->implode(', ');
  $mapLink = $address !== '' ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address) : null;
  $roleLabels = $assignment ? $assignment->roleLabelsForUser($user) : collect();
  $payment = $event->staffPaymentSummary();
  $balanceDue = (float) ($payment['balance_due'] ?? 0);
  $gratuity = (float) ($payment['gratuity'] ?? 0);
  $confirmation = $event->staffConfirmationSummaryFor($user);
  $isCancelled = str_contains(strtolower((string) ($event->status ?? '')), 'cancel') || str_contains(strtolower((string) ($event->status ?? '')), 'void');
  $staffRows = $assignment ? $assignment->assignedStaffSummaryRows() : collect();
  if ($assignment?->assistant) {
      $staffRows->push(['label' => 'Assistant', 'value' => $assignment->assistant->name]);
  }
  $notes = collect([$event->notes, $assignment?->schedule_notes, $assignment?->notes])
      ->filter(fn($value) => filled($value))
      ->implode("\n\n");
@endphp

@section('content')
  <div class="page-head">
    <div>
      <h1>Event Details</h1>
      <p>{{ $event->customer_name ?: 'Unnamed customer' }} · {{ $event->code ?: 'No code' }}</p>
    </div>
    <div class="card-actions">
      @if($mapLink)
        <a class="btn-secondary" href="{{ $mapLink }}" target="_blank" rel="noopener">Open in Maps</a>
      @endif
      <a class="btn-secondary" href="{{ route('staff.dashboard') }}">Back</a>
    </div>
  </div>

  <section class="detail-card">
    <h2>Customer / Event</h2>
    <div class="detail-grid">
      <dl class="info-list">
        <div class="info-row"><dt>Customer</dt><dd>{{ $event->customer_name ?: 'N/A' }}</dd></div>
        <div class="info-row"><dt>Code</dt><dd>{{ $event->code ?: 'N/A' }}</dd></div>
        <div class="info-row"><dt>Invoice #</dt><dd>{{ filled($event->invoice_number) ? $event->invoice_number : 'N/A' }}</dd></div>
        <div class="info-row"><dt>Your Role</dt><dd>{{ $roleLabels->isNotEmpty() ? $roleLabels->implode(', ') : 'Staff' }}</dd></div>
        <div class="info-row"><dt>Date</dt><dd>{{ $fmtDate($event->date) }}</dd></div>
        <div class="info-row"><dt>Time</dt><dd>{{ $fmtTime($event->time) }}</dd></div>
        <div class="info-row"><dt>Guests</dt><dd>{{ (int) ($event->guests ?? 0) }}</dd></div>
      </dl>
      <dl class="info-list">
        <div class="info-row"><dt>Address</dt><dd>{{ $address !== '' ? $address : 'N/A' }}</dd></div>
        <div class="info-row"><dt>City</dt><dd>{{ filled($event->city) ? $event->city : 'N/A' }}</dd></div>
        <div class="info-row"><dt>ZIP</dt><dd>{{ filled($event->zip_code) ? $event->zip_code : 'N/A' }}</dd></div>
        <div class="info-row"><dt>Setup Color</dt><dd>{{ filled($event->setup_color) ? $event->setup_color : 'N/A' }}</dd></div>
        <div class="info-row"><dt>Event Type</dt><dd>{{ filled($event->event_type) ? $event->event_type : 'N/A' }}</dd></div>
        <div class="info-row"><dt>Stairs</dt><dd>{{ $event->stairs ? 'Yes' : 'No' }}</dd></div>
      </dl>
    </div>
  </section>

  <section class="detail-card">
    <h2>Invoice Information</h2>
    <dl class="info-list">
      <div class="info-row"><dt>Status</dt><dd><span class="badge invoice {{ $payment['status_key'] }}">{{ $payment['status_label'] }}</span></dd></div>
      <div class="info-row"><dt>Balance Due</dt><dd><span class="badge balance {{ $balanceDue <= 0.009 ? 'paid' : 'due' }}">{{ $balanceDue <= 0.009 ? '$0.00' : $fmtMoney($balanceDue) }}</span></dd></div>
      <div class="info-row"><dt>Gratuity</dt><dd><span class="badge tip">{{ $fmtMoney($gratuity) }}</span></dd></div>
    </dl>
  </section>

  <section class="detail-card">
    <h2>Event Confirmation</h2>
    <dl class="info-list">
      <div class="info-row">
        <dt>Status</dt>
        <dd>
          <span class="badge confirmation {{ $confirmation['tone'] }}">{{ $confirmation['label'] }}</span>
          @if($confirmation['timestamp'])
            <span style="color:#64748b;font-size:13px;font-weight:800;margin-left:8px">{{ $confirmation['timestamp'] }}</span>
          @endif
        </dd>
      </div>
    </dl>
    @if(!($confirmation['confirmed'] ?? false) && !$isCancelled)
      <form method="post" action="{{ route('staff.events.confirm', ['reservation' => $event]) }}" class="card-actions">
        @csrf
        <button class="btn-confirm" type="submit">Confirm Event</button>
      </form>
    @elseif($isCancelled)
      <div class="empty" style="margin-top:12px">Cancelled events cannot be newly confirmed.</div>
    @endif
  </section>

  <section class="detail-card">
    <h2>Assigned Staff</h2>
    @if($staffRows->isEmpty())
      <div class="empty">No staff assigned yet.</div>
    @else
      <dl class="info-list">
        @foreach($staffRows as $row)
          <div class="info-row"><dt>{{ $row['label'] }}</dt><dd>{{ $row['value'] ?: 'N/A' }}</dd></div>
        @endforeach
        <div class="info-row"><dt>Van #</dt><dd>{{ filled($assignment?->van) ? $assignment->van : 'N/A' }}</dd></div>
      </dl>
    @endif
  </section>

  <section class="detail-card">
    <h2>Menu</h2>
    @if($event->items->isEmpty())
      <div class="empty">No menu items recorded.</div>
    @else
      <div class="menu-table" aria-label="Event menu items">
        <div class="menu-table-head">
          <span>Menu</span>
          <span>Qty</span>
        </div>
        @foreach($event->items as $item)
          <div class="menu-table-row">
            <span>
              {{ $item->name_snapshot }}
              @if(filled($item->description))
                <small class="menu-item-meta">{{ $item->description }}</small>
              @endif
            </span>
            <span>{{ (int) $item->qty }}</span>
          </div>
        @endforeach
      </div>
    @endif
  </section>

  @if($notes !== '')
    <section class="detail-card">
      <h2>Notes</h2>
      <div class="notes-box">{{ $notes }}</div>
    </section>
  @endif
@endsection
