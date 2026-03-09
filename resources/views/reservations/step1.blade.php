@extends('layouts.app')
@section('title','Reservations - Step 1')
@section('content')
  @include('reservations.progress')

  <div class="rs-card rs-stack-lg">
    <div class="rs-summary">
      <div class="rs-summary-item"><b>Start your reservation</b> by selecting guest count, date and time.</div>
      <div class="rs-summary-item">Availability updates in real time.</div>
    </div>

    @if ($errors->any())
      <p class="rs-helper" style="color:#b91c1c;font-weight:600">{{ $errors->first() }}</p>
    @endif

    <form method="post" action="{{ route('reservations.submit',['step'=>1]) }}" id="step1" class="rs-stack-lg">
      @csrf

      <section class="rs-section">
        <h3 class="rs-section-head">Date & Time</h3>
        <div class="rs-grid">
          <div class="rs-field">
            <label>Number of Guests <small class="rs-helper" style="font-weight:500">(min 10 on Fri–Sun)</small></label>
            <input class="rs-input" type="number" id="guests" name="guests" min="1" max="150" placeholder="e.g., 20" required value="{{ $state['guests'] ?? '' }}">
            <div id="wkndNote" class="rs-helper" style="display:none;margin-top:6px">
              Weekend reservations require a 10-guest minimum. For smaller groups and availability, please call 9513269602.
            </div>
          </div>
          <div class="rs-field">
            <label>Date</label>
            @php $tz = config('app.timezone') ?: env('APP_TIMEZONE','America/Los_Angeles'); @endphp
            <input class="rs-input" type="date" id="date" name="date" required value="{{ $state['date'] ?? '' }}" min="{{ \Carbon\Carbon::now($tz)->toDateString() }}">
          </div>
          <div class="rs-field rs-col-span-2">
            <label>Available Time</label>
            <input type="hidden" id="time" name="time" value="{{ $state['time'] ?? '' }}">
            <div id="slots" class="slot-grid"></div>
          </div>
        </div>
      </section>

      <div class="rs-actions">
        <div></div>
        <div class="rs-actions-group">
          <button class="btn" type="submit">Continue</button>
        </div>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
<script src="/assets/reservations/js/reservations.js"></script>
@endpush
