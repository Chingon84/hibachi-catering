@extends('layouts.app')
@section('title','Reservations - Step 1')
@section('content')
  @include('reservations.progress')

  @if ($errors->any())
    <p style="color:#b21e27; font-weight:600">{{ $errors->first() }}</p>
  @endif

  <form method="post" action="{{ route('reservations.submit',['step'=>1]) }}" id="step1">
    @csrf
    <div class="row">
      <div>
        <label>Number of Guests <small style="color:#6b7280;font-weight:500">(min 10 on Fri–Sun)</small></label>
        <input type="number" id="guests" name="guests" min="1" max="150" placeholder="e.g., 20" required value="{{ $state['guests'] ?? '' }}">
        <div id="wkndNote" style="display:none;color:#6b7280;font-size:13px;margin-top:6px">
          Weekend reservations require a 10-guest minimum. For smaller groups and availability, please call 9513269602.
        </div>
      </div>
      <div>
        <label>Date</label>
        @php $tz = config('app.timezone') ?: env('APP_TIMEZONE','America/Los_Angeles'); @endphp
        <input type="date" id="date" name="date" required value="{{ $state['date'] ?? '' }}" min="{{ \Carbon\Carbon::now($tz)->toDateString() }}">
      </div>
    </div>
    <br>
    <label>Available Time</label>
    <input type="hidden" id="time" name="time" value="{{ $state['time'] ?? '' }}">
    <div id="slots" class="slot-grid"></div>
    <br>
    <button class="btn" type="submit">Continue</button>
  </form>
@endsection

@push('scripts')
<script src="/assets/reservations/js/reservations.js"></script>
@endpush
