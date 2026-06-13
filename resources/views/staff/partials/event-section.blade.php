<section>
  <div class="section-title">{{ $title }}</div>

  @if($events->isEmpty())
    <div class="empty">{{ $emptyMessage ?? 'No assigned events in this section.' }}</div>
  @else
    <div class="event-grid">
      @foreach($events as $event)
        @php
          $assignment = $event->scheduleAssignment;
          $rows = $staffRows($event);
          $address = $addressLine($event);
          $state = $sectionState ?? 'upcoming';
          $stateLabel = $state === 'today' ? 'Today' : ($state === 'past' ? 'Past' : 'Upcoming');
          $roleLabels = $assignment ? $assignment->roleLabelsForUser($user) : collect();
          $mapLink = $mapsUrl($event);
          $payment = $event->staffPaymentSummary();
          $balanceDue = (float) ($payment['balance_due'] ?? 0);
          $gratuity = (float) ($payment['gratuity'] ?? 0);
          $confirmation = $event->staffConfirmationSummaryFor($user);
        @endphp

        <article class="event-card {{ $state === 'past' ? 'past' : '' }}">
          <h2>{{ $event->customer_name ?: 'Unnamed customer' }}</h2>
          <div class="event-code">
            {{ $event->code ?: 'No code' }}
            @if(filled($event->invoice_number))
              · Invoice #{{ $event->invoice_number }}
            @endif
          </div>

          <div class="event-date {{ $state === 'past' ? 'past' : '' }}">{{ $fmtDate($event->date) }}</div>

          <div class="badge-row">
            <span class="badge {{ $state }}">{{ $stateLabel }}</span>
            <span class="badge invoice {{ $payment['status_key'] }}">Invoice: {{ $payment['status_label'] }}</span>
            <span class="badge balance {{ $balanceDue <= 0.009 ? 'paid' : 'due' }}">Balance Due: {{ $balanceDue <= 0.009 ? '$0.00' : $fmtMoney($balanceDue) }}</span>
            <span class="badge tip">Gratuity: {{ $fmtMoney($gratuity) }}</span>
            <span class="badge confirmation {{ $confirmation['tone'] }}">Confirmation: {{ $confirmation['label'] }}</span>
            <span class="badge">{{ $fmtTime($event->time) }}</span>
            <span class="badge">{{ (int) ($event->guests ?? 0) }} guests</span>
          </div>

          <dl class="info-list">
            <div class="info-row"><dt>Your Role</dt><dd>{{ $roleLabels->isNotEmpty() ? $roleLabels->implode(', ') : 'Staff' }}</dd></div>
            <div class="info-row"><dt>Address</dt><dd>{{ $address !== '' ? $address : 'N/A' }}</dd></div>
            <div class="info-row"><dt>City</dt><dd>{{ filled($event->city) ? $event->city : 'N/A' }}</dd></div>
            <div class="info-row"><dt>ZIP</dt><dd>{{ filled($event->zip_code) ? $event->zip_code : 'N/A' }}</dd></div>
            <div class="info-row"><dt>Setup Color</dt><dd>{{ filled($event->setup_color) ? $event->setup_color : 'N/A' }}</dd></div>
            <div class="info-row"><dt>Event Type</dt><dd>{{ filled($event->event_type) ? $event->event_type : 'N/A' }}</dd></div>
            <div class="info-row"><dt>Stairs</dt><dd>{{ $event->stairs ? 'Yes' : 'No' }}</dd></div>
            <div class="info-row"><dt>Van #</dt><dd>{{ filled($assignment?->van) ? $assignment->van : 'N/A' }}</dd></div>
          </dl>

          @if($rows->isNotEmpty())
            <div class="mini-title">Assigned Staff</div>
            <dl class="info-list">
              @foreach($rows as $row)
                <div class="info-row"><dt>{{ $row['label'] }}</dt><dd>{{ $row['value'] ?: 'N/A' }}</dd></div>
              @endforeach
            </dl>
          @endif

          @if($event->items->isNotEmpty())
            <div class="menu-table" aria-label="Event menu items">
              <div class="menu-table-head">
                <span>Menu</span>
                <span>Qty</span>
              </div>
              @foreach($event->items->take(4) as $item)
                <div class="menu-table-row">
                  <span>{{ $item->name_snapshot }}</span>
                  <span>{{ (int) $item->qty }}</span>
                </div>
              @endforeach
            </div>
          @endif

          <div class="card-actions">
            <a class="btn-primary" href="{{ route('staff.events.show', ['reservation' => $event]) }}">View Details</a>
            @if(!($confirmation['confirmed'] ?? false))
              <form method="post" action="{{ route('staff.events.confirm', ['reservation' => $event]) }}" style="margin:0">
                @csrf
                <button class="btn-confirm" type="submit">Confirm Event</button>
              </form>
            @endif
            @if($mapLink)
              <a class="btn-secondary" href="{{ $mapLink }}" target="_blank" rel="noopener">Open in Maps</a>
            @endif
          </div>
        </article>
      @endforeach
    </div>
  @endif
</section>
