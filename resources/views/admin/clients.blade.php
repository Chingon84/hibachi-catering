@extends('layouts.admin')

@section('title', 'Clients')

@push('styles')
<style>
  /* Page-specific layout only — visual styling comes from the shared app.css */
  .clients-filter .filter-search{width:min(360px,100%)}
  .clients-filter .filter-city{width:190px}
  .clients-filter .filter-status{width:170px}
  .clients-filter .filter-events{width:92px}
  .clients-table .table{table-layout:fixed}
  .clients-table .table th,.clients-table .table td{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  @media (min-width:1200px){
    .clients-table .table-wrap{overflow-x:visible}
    .clients-table .table th:nth-child(1),.clients-table .table td:nth-child(1){width:60px}
    .clients-table .table th:nth-child(2),.clients-table .table td:nth-child(2){width:180px}
    .clients-table .table th:nth-child(3),.clients-table .table td:nth-child(3){width:170px}
    .clients-table .table th:nth-child(4),.clients-table .table td:nth-child(4){width:120px}
    .clients-table .table th:nth-child(5),.clients-table .table td:nth-child(5){width:100px}
    .clients-table .table th:nth-child(6),.clients-table .table td:nth-child(6){width:78px}
    .clients-table .table th:nth-child(7),.clients-table .table td:nth-child(7){width:78px}
    .clients-table .table th:nth-child(8),.clients-table .table td:nth-child(8){width:210px}
    .clients-table .table th:nth-child(9),.clients-table .table td:nth-child(9){width:140px}
    .clients-table .table th:nth-child(10),.clients-table .table td:nth-child(10){width:160px}
    .clients-table .table th:nth-child(11),.clients-table .table td:nth-child(11){width:116px}
  }
  .client-row{cursor:pointer}
</style>
@endpush

@section('content')
@php
  $status = $status ?? null;
  $q = $q ?? '';
@endphp
<div class="container">
  <x-page-header subtitle="Customer directory — statuses, contacts and event history.">
    <x-slot:actions>
      <x-button href="{{ route('admin.clients.create') }}">New Client</x-button>
    </x-slot:actions>
  </x-page-header>

  <div class="stack">
    @if (session('ok'))
      <div class="alert success">{{ session('ok') }}</div>
    @endif

    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-label">Total Clients</div>
        <div class="metric-value">{{ number_format($totalClients ?? 0) }}</div>
        <div class="metric-copy">All client records in the CRM.</div>
      </div>
      <div class="metric-card">
        <div class="metric-label">Active Clients</div>
        <div class="metric-value">{{ number_format($activeClients ?? 0) }}</div>
        <div class="metric-copy">Regular, VIP, celebrity, and preferred clients.</div>
      </div>
      <div class="metric-card">
        <div class="metric-label">Inactive Clients</div>
        <div class="metric-value">{{ number_format($inactiveClients ?? 0) }}</div>
        <div class="metric-copy">Clients not currently considered active.</div>
      </div>
      <div class="metric-card">
        <div class="metric-label">Total Events</div>
        <div class="metric-value">{{ number_format($totalEvents ?? 0) }}</div>
        <div class="metric-copy">Sum of recorded client event counts.</div>
      </div>
    </div>

    <x-card>
      <form method="get" class="filter-bar clients-filter" action="{{ route('admin.clients') }}">
        <input class="input filter-control filter-search" id="q" name="q" placeholder="Search name, company, email, phone" value="{{ $q }}">
        <select name="city" class="select filter-control filter-city">
          <option value="">All cities</option>
          @foreach(($cityOptions ?? []) as $copt)
            <option value="{{ $copt }}" {{ ($city ?? '')===$copt ? 'selected' : '' }}>{{ $copt }}</option>
          @endforeach
        </select>
        <select name="status" class="select client-status {{ $status ? strtolower($status) : '' }} filter-control filter-status">
          <option value="">All statuses</option>
          @php $opts = $statusOptions ?? ['regular','vip','celebrity','blacklist','preferred']; @endphp
          @foreach($opts as $opt)
            <option value="{{ $opt }}" {{ $status===$opt?'selected':'' }}>{{ ucfirst($opt) }}</option>
          @endforeach
        </select>
        <input
          class="input filter-control filter-events"
          type="number"
          min="1"
          max="50"
          name="events"
          value="{{ $events ?? '' }}"
          placeholder="Events"
        >
        <div class="filter-actions">
          <x-button variant="secondary" type="submit">Filter</x-button>
          <x-button variant="secondary" href="{{ route('admin.clients') }}">Reset</x-button>
        </div>
        <div class="toolbar-tools">
          <a href="{{ route('admin.clients.template') }}" class="icon-btn" title="Download CSV template" aria-label="Download CSV template">T</a>
          <a href="{{ route('admin.clients.export', request()->query()) }}" class="icon-btn" title="Export CSV" aria-label="Export CSV">⇩</a>
          <form method="post" action="{{ route('admin.clients.import') }}" enctype="multipart/form-data" id="importForm" style="display:inline">
            @csrf
            <input type="file" name="file" id="importFile" accept=".csv" style="display:none" onchange="if(this.files.length){document.getElementById('importForm').submit();}">
            <button type="button" class="icon-btn" title="Import CSV" aria-label="Import CSV" onclick="document.getElementById('importFile').click()">⇧</button>
          </form>
        </div>
      </form>
    </x-card>

    <x-card class="clients-table">
      <div class="table-wrap">
        <table class="table" aria-label="Clients list">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Company</th>
              <th>City</th>
              <th>Date</th>
              <th class="num">EVENTS</th>
              <th class="num">Guests</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($list as $c)
              <tr class="client-row" data-href="{{ route('admin.clients.show', ['id'=>$c->id]) }}" tabindex="0" aria-label="Open client {{ $c->full_name ?: $c->id }}">
                <td class="muted">#{{ $c->id }}</td>
                <td style="font-weight:600">{{ $c->full_name ?: '—' }}</td>
                <td>{{ $c->company ?: '—' }}</td>
                <td>{{ $c->address1_city ?? $c->address2_city ?? '—' }}</td>
                <td class="muted">
                  @php $ld = $c->last_event_date ?? null; @endphp
                  {{ $ld ? \Carbon\Carbon::parse($ld)->format('m/d/Y') : '—' }}
                </td>
                <td class="num"><span class="count-badge">{{ (int) ($c->events_count ?? 0) }}</span></td>
                <td class="num">{{ !is_null($c->last_guests) ? (int) $c->last_guests : '—' }}</td>
                <td>{{ $c->email_primary ?: '—' }}</td>
                <td>{{ $c->phone_primary ?: '—' }}</td>
                <td>
                  <form method="post" action="{{ route('admin.clients.status', ['id'=>$c->id]) }}" style="display:flex;align-items:center" data-row-action-ignore>
                    @csrf
                    @php $cur = strtolower($c->status); @endphp
                    <select name="status" class="select client-status {{ $cur }}" onchange="this.form.submit()" style="min-width:140px">
                      @foreach($opts as $opt)
                        <option value="{{ $opt }}" {{ strtolower($c->status)===strtolower($opt)?'selected':'' }}>{{ ucfirst($opt) }}</option>
                      @endforeach
                    </select>
                  </form>
                </td>
                <td class="actions" data-row-action-ignore>
                  <a href="{{ route('admin.clients.show', ['id'=>$c->id]) }}">Open</a>
                  <a href="{{ route('admin.clients.edit', ['id'=>$c->id]) }}">Edit</a>
                </td>
              </tr>
            @empty
              <tr><td colspan="11" class="muted">No clients found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @include('admin.partials.pagination', ['paginator' => $list])
    </x-card>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const q = document.querySelector('#q');
    if (q) {
      q.addEventListener('keydown', (e) => { if (e.key === 'Enter') q.form.submit(); });
    }
  });

  const clientRowActionSelector = 'a,button,input,select,textarea,form,[data-row-action-ignore]';

  document.querySelectorAll('.client-row[data-href]').forEach(row => {
    row.addEventListener('mousedown', event => {
      if (event.target.closest(clientRowActionSelector)) return;
      row.classList.add('is-clicking');
    });

    row.addEventListener('mouseup', () => row.classList.remove('is-clicking'));
    row.addEventListener('mouseleave', () => row.classList.remove('is-clicking'));

    row.addEventListener('click', event => {
      if (event.target.closest(clientRowActionSelector)) return;
      window.location.href = row.dataset.href;
    });

    row.addEventListener('keydown', event => {
      if (!['Enter', ' '].includes(event.key)) return;
      if (event.target.closest(clientRowActionSelector)) return;
      event.preventDefault();
      row.classList.add('is-clicking');
      window.location.href = row.dataset.href;
    });
  });

  document.querySelectorAll(clientRowActionSelector).forEach(el => {
    el.addEventListener('click', event => event.stopPropagation());
  });
</script>
@endpush
