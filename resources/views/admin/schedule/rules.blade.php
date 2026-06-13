@extends('layouts.admin')

@section('title', 'Priority Rules')

@push('styles')
<style>
  /* Page-specific schedule rules layout. Core chrome from app.css. */
  .wrap{width:100%;max-width:none;margin:0;padding:20px 24px}
  .sched-stack{display:grid;gap:18px}
  .head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
  .sched-title{margin:0;font-size:28px;line-height:1.05}
  .subtitle{margin:8px 0 0;color:var(--muted);max-width:760px}
  .sched-actions{display:flex;gap:8px;flex-wrap:wrap}
  .section{padding:22px}
  .rules-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
  .rule-card{padding:16px;border:1px solid var(--border);border-radius:14px;background:linear-gradient(180deg,#fff,#fafafb)}
  .rule-title{font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
  .rule-copy{margin-top:8px;font-size:14px;line-height:1.65;color:#334155}
  .formula{padding:16px 18px;border:1px dashed #cbd5e1;border-radius:14px;background:#f8fafc;font-size:15px;font-weight:700;color:#0f172a}
  @media (max-width: 760px){.wrap{padding:16px}.rules-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="wrap">
  <div class="sched-stack">
    <div class="head">
      <div>
        <h1 class="sched-title">Priority Rules</h1>
        <p class="subtitle">The current weekly schedule engine starts from seniority, adds reliability, applies fairness rotation, and automatically lowers priority when consistency drops.</p>
      </div>
      <div class="sched-actions">
        <a class="btn secondary" href="{{ route('admin.schedule.index') }}">Back to Schedule</a>
        <a class="btn" href="{{ route('admin.schedule.assign') }}">Assign Event</a>
      </div>
    </div>

    <div class="card section">
      <div class="formula">Final Priority Score = Seniority Weight + Reliability Score + Fair Rotation Adjustment − Penalty Points</div>
      <div style="margin-top:12px;color:var(--muted);font-size:13px">Current scoring week starts {{ $weekStart->format('M d, Y') }}. Adjustments can be tuned later in the scheduling service without rewriting the UI.</div>
    </div>

    <div class="card section">
      <div class="rules-grid">
        @foreach($rules as $label => $copy)
          <div class="rule-card">
            <div class="rule-title">{{ \Illuminate\Support\Str::headline($label) }}</div>
            <div class="rule-copy">{{ $copy }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
