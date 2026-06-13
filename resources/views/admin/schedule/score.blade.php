@extends('layouts.admin')

@section('title', 'Update Schedule Score')

@push('styles')
<style>
  /* Page-specific schedule score layout. Core chrome from app.css. */
  .wrap{width:100%;max-width:none;margin:0;padding:20px 24px}
  .sched-stack{display:grid;gap:18px}
  .head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
  .sched-title{margin:0;font-size:28px;line-height:1.05}
  .subtitle{margin:8px 0 0;color:var(--muted);max-width:720px}
  .sched-actions{display:flex;gap:8px;flex-wrap:wrap}
  .section{padding:22px}
  .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
  .field{display:flex;flex-direction:column;gap:6px}
  .label{font-size:13px;font-weight:700;color:#374151}
  .summary{padding:16px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc}
  .summary strong{display:block;font-size:22px;color:#0f172a}
  .summary span{display:block;margin-top:6px;color:var(--muted);font-size:13px}
  @media (max-width: 760px){.wrap{padding:16px}.grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="wrap">
  <div class="sched-stack">
    <div class="head">
      <div>
        <h1 class="sched-title">Update Score</h1>
        <p class="subtitle">Weekly score controls for {{ $user->name }}. Use this to reflect reliability, requested time off, missed shifts, and operational notes for the week starting {{ $weekStart->format('M d, Y') }}.</p>
      </div>
      <div class="sched-actions">
        <a class="btn secondary" href="{{ route('admin.schedule.index') }}">Back to Schedule</a>
      </div>
    </div>

    <div class="card section">
      <div class="summary">
        <strong>{{ $row['chef_name'] ?? $user->name }}</strong>
        <span>Current tier {{ $row['priority_tier'] ?? '—' }} · Final score {{ isset($row['final_priority_score']) ? number_format($row['final_priority_score'], 1) : '—' }}</span>
      </div>
    </div>

    <div class="card section">
      <form method="post" class="sched-stack" action="{{ route('admin.schedule.score.update', $user->id) }}">
        @csrf
        <div class="grid">
          <div class="field">
            <label class="label" for="status">Status</label>
            <select class="select" id="status" name="status">
              @foreach($statuses as $status)
                <option value="{{ $status }}" {{ ($row['status'] ?? 'Active') === $status ? 'selected' : '' }}>{{ $status }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="availability_status">Availability</label>
            <select class="select" id="availability_status" name="availability_status">
              @foreach($availabilityOptions as $availability)
                <option value="{{ $availability }}" {{ ($row['availability'] ?? 'Available') === $availability ? 'selected' : '' }}>{{ $availability }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label class="label" for="reliability_score">Reliability Score</label>
            <input class="input" id="reliability_score" name="reliability_score" type="number" min="0" max="100" step="0.1" value="{{ old('reliability_score', $row['reliability_score'] ?? 82) }}">
          </div>
          <div class="field">
            <label class="label" for="requested_days_off">Requested Days Off</label>
            <input class="input" id="requested_days_off" name="requested_days_off" type="number" min="0" max="7" value="{{ old('requested_days_off', $row['requested_days_off'] ?? 0) }}">
          </div>
          <div class="field">
            <label class="label" for="missed_days">Missed Days</label>
            <input class="input" id="missed_days" name="missed_days" type="number" min="0" max="7" value="{{ old('missed_days', $row['missed_days'] ?? 0) }}">
          </div>
          <div class="field">
            <label class="label" for="late_cancellations">Late Cancellations</label>
            <input class="input" id="late_cancellations" name="late_cancellations" type="number" min="0" max="7" value="{{ old('late_cancellations', $row['late_cancellations'] ?? 0) }}">
          </div>
        </div>
        <div class="field">
          <label class="label" for="notes">Weekly Notes</label>
          <textarea class="textarea" id="notes" name="notes" rows="4" placeholder="Document reliability concerns, exceptions, or leadership notes for this chef.">{{ old('notes', $row['notes'] ?? '') }}</textarea>
        </div>
        <div class="sched-actions">
          <button class="btn" type="submit">Save Score Inputs</button>
          <a class="btn secondary" href="{{ route('admin.schedule.index') }}">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
