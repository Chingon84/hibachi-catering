@extends('layouts.admin')

@section('title', 'Assign Event')

@push('styles')
<style>
  /* Page-specific schedule assign layout. Core chrome from app.css. */
  .wrap{width:100%;max-width:none;margin:0;padding:20px 24px}
  .sched-stack{display:grid;gap:18px}
  .head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
  .sched-title{margin:0;font-size:28px;line-height:1.05}
  .subtitle{margin:8px 0 0;color:var(--muted);max-width:760px}
  .sched-actions{display:flex;gap:8px;flex-wrap:wrap}
  .section{padding:22px}
  .filters{display:grid;grid-template-columns:minmax(280px,1fr) auto;gap:12px;align-items:end}
  .field{display:flex;flex-direction:column;gap:6px}
  .label{font-size:13px;font-weight:700;color:#374151}
  .event-card{padding:16px;border:1px solid var(--border);border-radius:14px;background:linear-gradient(180deg,#fff,#fafafb)}
  .event-title{font-size:18px;font-weight:800;color:#0f172a}
  .event-meta{margin-top:8px;color:var(--muted);font-size:13px;line-height:1.6}
  .note{padding:14px 16px;border:1px dashed #cbd5e1;border-radius:14px;background:#f8fafc;color:#475569;font-size:13px;line-height:1.6}
  .table-wrap{overflow:auto}
  .table{min-width:960px}
  .table th,.table td{padding:13px 12px;vertical-align:middle}
  .table tbody tr:hover{background:#fafafa}
  .badge{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;border:1px solid transparent;font-size:12px;font-weight:700}
  .badge.tier-a{background:#ecfdf5;border-color:#bbf7d0;color:#166534}
  .badge.tier-b{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
  .badge.tier-c{background:#fffbeb;border-color:#fcd34d;color:#92400e}
  .badge.tier-d{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
  .badge.available{background:#ecfdf5;border-color:#bbf7d0;color:#166534}
  .badge.off{background:#fff7ed;border-color:#fed7aa;color:#c2410c}
  .cell-title{font-weight:700;color:#0f172a}
  .cell-sub{margin-top:4px;font-size:12px;color:var(--muted)}
  .assign-form{display:flex;gap:8px;align-items:center;justify-content:flex-end}
  .assign-form input{min-width:180px}
  @media (max-width: 760px){
    .wrap{padding:16px}
    .filters{grid-template-columns:1fr}
    .table{min-width:900px}
  }
</style>
@endpush

@section('content')
@php
  $tierClass = fn (string $value) => 'tier-' . strtolower($value);
@endphp
<div class="wrap">
  <div class="sched-stack">
    <div class="head">
      <div>
        <h1 class="sched-title">Assign Event</h1>
        <p class="subtitle">Chefs are ordered by system priority based on seniority, consistency, and recent scheduling balance.</p>
      </div>
      <div class="sched-actions">
        <a class="btn secondary" href="{{ route('admin.schedule.index') }}">Back to Schedule</a>
        <a class="btn secondary" href="{{ route('admin.schedule.rules') }}">Priority Rules</a>
      </div>
    </div>

    <div class="card section">
      <form method="get" class="filters">
        <div class="field">
          <label class="label" for="event">Event</label>
          <select class="select" id="event" name="event">
            <option value="">Select an upcoming event</option>
            @foreach($events as $event)
              <option value="{{ $event['id'] }}" {{ (int) request('event') === $event['id'] ? 'selected' : '' }}>{{ $event['label'] }}</option>
            @endforeach
          </select>
        </div>
        <div class="sched-actions">
          <button class="btn" type="submit">Load Recommendations</button>
        </div>
      </form>
    </div>

    @if(session('ok'))
      <div class="card section" style="padding:16px;color:#166534;font-weight:600">{{ session('ok') }}</div>
    @endif

    @if($selectedEvent)
      <div class="card section">
        <div class="event-card">
          <div class="event-title">{{ $selectedEvent['code'] }}</div>
          <div class="event-meta">
            {{ $selectedEvent['date'] }} {{ $selectedEvent['time'] ? '• ' . $selectedEvent['time'] : '' }}
            @if($selectedEvent['customer_name'])
              • {{ $selectedEvent['customer_name'] }}
            @endif
            @if($selectedEvent['city'])
              • {{ $selectedEvent['city'] }}
            @endif
            @if($selectedEvent['assigned_user_name'])
              <br>Currently assigned to <strong>{{ $selectedEvent['assigned_user_name'] }}</strong>
            @endif
          </div>
        </div>
      </div>

      <div class="card section">
        <div class="note" style="margin-bottom:16px">Recommended chefs are listed from highest to lowest final priority score for the week starting {{ $weekStart->format('M d, Y') }}.</div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Chef</th>
                <th>Tier</th>
                <th>Final Score</th>
                <th>Reliability</th>
                <th>Events This Week</th>
                <th>Availability</th>
                <th>Assign</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recommendedChefs as $row)
                <tr style="{{ $highlightChefId === $row['user_id'] ? 'background:#f8fafc' : '' }}">
                  <td>
                    <div class="cell-title">{{ $row['chef_name'] }}</div>
                    <div class="cell-sub">Rank #{{ $row['seniority_rank'] }} · {{ $row['status'] }}</div>
                  </td>
                  <td><span class="badge {{ $tierClass($row['priority_tier']) }}">Tier {{ $row['priority_tier'] }}</span></td>
                  <td>{{ number_format($row['final_priority_score'], 1) }}</td>
                  <td>{{ number_format($row['reliability_score'], 1) }}</td>
                  <td>{{ number_format($row['events_this_week']) }}</td>
                  <td><span class="badge {{ $row['availability'] === 'Available' ? 'available' : 'off' }}">{{ $row['availability'] }}</span></td>
                  <td>
                    <form class="assign-form" method="post" action="{{ route('admin.schedule.assign.store') }}">
                      @csrf
                      <input type="hidden" name="reservation_id" value="{{ $selectedEvent['id'] }}">
                      <input type="hidden" name="user_id" value="{{ $row['user_id'] }}">
                      <input class="input" type="text" name="notes" placeholder="Optional assignment note">
                      <button class="btn" type="submit">Assign</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:28px 12px">No chef recommendations are available for the selected event.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    @else
      <div class="card section">
        <div class="note">Select an upcoming reservation to load chefs ordered by priority. This view uses the same fairness engine shown on the main Schedule dashboard.</div>
      </div>
    @endif
  </div>
</div>
@endsection
