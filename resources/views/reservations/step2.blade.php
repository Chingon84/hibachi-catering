@extends('layouts.app')
@section('title','Reservations - Step 2')
@section('content')
  @include('reservations.progress')

  @php
    $r = $reservation ?? null;
    $v = fn($key,$fallback='') => old($key, $state[$key] ?? ($r->$key ?? $fallback));
    $summary = [
      'Guests' => $state['guests'] ?? ($r->guests ?? ''),
      'Date'   => isset($state['date']) ? \Carbon\Carbon::parse($state['date'])->format('m/d/Y') : ($r? $r->date->format('m/d/Y'):''),
      'Time'   => isset($state['time']) ? date('g:i A', strtotime($state['time'])) : ($r? date('g:i A', strtotime($r->time)):''),
    ];
  @endphp

  <div class="rs-card rs-stack-lg">
    <div class="rs-summary">
      <div class="rs-summary-item">Guests: <b>{{ $summary['Guests'] }}</b></div>
      <div class="rs-summary-item">Date: <b>{{ $summary['Date'] }}</b></div>
      <div class="rs-summary-item">Time: <b>{{ $summary['Time'] }}</b></div>
    </div>

    @if ($errors->any())
      <p class="rs-helper" style="color:#b91c1c;font-weight:600">{{ $errors->first() }}</p>
    @endif

    <form method="post" action="{{ route('reservations.submit',['step'=>2]) }}" id="step2" class="rs-stack-lg">
      @csrf

      <section class="rs-section">
        <h3 class="rs-section-head">Client Info</h3>
        <div class="rs-grid">
          <div class="rs-field">
            <label>*Name</label>
            <input class="rs-input" type="text" name="first_name" required placeholder="First name" value="{{ $v('first_name') }}">
          </div>
          <div class="rs-field">
            <label>*Last Name</label>
            <input class="rs-input" type="text" name="last_name" required placeholder="Last name" value="{{ $v('last_name') }}">
          </div>
          <div class="rs-field">
            <label>Company (Optional)</label>
            <input class="rs-input" type="text" name="company" placeholder="Company" value="{{ $v('company') }}">
          </div>
          <div class="rs-field">
            <label>*Phone</label>
            <input class="rs-input" type="text" name="phone" placeholder="(555) 555-5555" value="{{ $v('phone') }}">
          </div>
          <div class="rs-field rs-col-span-2">
            <label>*Email</label>
            <input class="rs-input" type="email" name="email" placeholder="you@example.com" value="{{ $v('email') }}">
          </div>
        </div>
      </section>

      <section class="rs-section">
        <h3 class="rs-section-head">Event Info</h3>
        <div class="rs-grid">
          <div class="rs-field rs-col-span-2">
            <label>*Address of the event</label>
            <input class="rs-input" type="text" id="addr" name="address" placeholder="123 Main St" value="{{ $v('address') }}">
          </div>
          <div class="rs-field">
            <label>City</label>
            <input class="rs-input" type="text" id="city" name="city" placeholder="City" value="{{ $v('city') }}">
          </div>
          <div class="rs-field">
            <label>*Zip Code</label>
            <input class="rs-input" type="text" id="zip" name="zip_code" placeholder="Zip" value="{{ $v('zip_code') }}">
          </div>
          <div class="rs-field rs-col-span-2">
            <div class="rs-info">
              <div class="rs-control-row">
                <small class="rs-helper">Distance: <b id="dist" style="color:#111827">0</b> mi</small>
                <small class="rs-helper">Price: $<b id="price" style="color:#111827">0.00</b></small>
                <button type="button" class="btn" id="btnCalc">Calculate Distance</button>
                <small id="distStatus" class="rs-helper"></small>
              </div>
            </div>
          </div>
          <div class="rs-field">
            <label>*What type of event are you hosting?</label>
            <select class="rs-select" name="event_type">
              <option value="">Select…</option>
              @foreach (['Birthday','Wedding','Corporate','Graduation','Holiday','Other'] as $opt)
                <option value="{{ $opt }}" {{ $v('event_type')===$opt ? 'selected':'' }}>{{ $opt }}</option>
              @endforeach
            </select>
          </div>
          <div class="rs-field">
            <label>Setup color</label>
            @php
              $combineWithBlack = ['Red','Gold','Silver','White','Navy Blue','Baby Blue','Hot Pink','Baby Pink','Turquoise','Green','Purple','Orange'];
              $colorOptions = array_map(fn($c)=>"Black & $c", $combineWithBlack);
              $colorOptions = array_merge($colorOptions, ['All Black','White','Other']);
            @endphp
            <select class="rs-select" name="setup_color">
              @foreach ($colorOptions as $opt)
                <option value="{{ $opt }}" {{ $v('setup_color')===$opt ? 'selected':'' }}>{{ $opt }}</option>
              @endforeach
            </select>
          </div>
          <div class="rs-field rs-col-span-2">
            <label>Do we have to go up or down stairs to do the setup?</label>
            <div class="rs-radio-group">
              <label><input type="radio" name="stairs" value="1" {{ $v('stairs') ? 'checked':'' }}> <span>Yes</span></label>
              <label><input type="radio" name="stairs" value="0" {{ !$v('stairs') ? 'checked':'' }}> <span>No</span></label>
            </div>
          </div>
          <div class="rs-field">
            <label>How did you hear about us?</label>
            <select class="rs-select" name="heard_about">
              @foreach (['Returning customer','Instagram','Facebook','TikTok','Yelp','Google','Friend/Family','Other'] as $opt)
                <option value="{{ $opt }}" {{ $v('heard_about')===$opt ? 'selected':'' }}>{{ $opt }}</option>
              @endforeach
            </select>
          </div>
          <div class="rs-field">
            <label>Additional Information / Special Request (Optional)</label>
            <input class="rs-input" type="text" name="notes" value="{{ $v('notes') }}" placeholder="Tell us anything important…">
          </div>
        </div>
      </section>

      <input type="hidden" name="distance_miles" id="distance_miles" value="{{ $v('distance_miles', 0) }}">
      <input type="hidden" name="travel_fee" id="travel_fee" value="{{ $v('travel_fee', 0) }}">

      <div class="rs-actions">
        <div class="rs-actions-group">
          <a class="btn btn-secondary" href="{{ route('reservations.step',['step'=>1]) }}">Back</a>
        </div>
        <div class="rs-actions-group">
          <button class="btn" type="submit">Continue</button>
        </div>
      </div>
    </form>
  </div>
@endsection


@push('scripts')
<script>
const $zip  = document.querySelector('#zip');
const $dist = document.querySelector('#dist');
const $price = document.querySelector('#price');
const $distMiles = document.querySelector('#distance_miles');
const $travelFee = document.querySelector('#travel_fee');
const $status = document.querySelector('#distStatus');
const $btn = document.querySelector('#btnCalc');

let lastQuery = '';
let aborter = null;

function setStatus(msg){ if ($status) $status.textContent = msg; }

function validZip(){
  const v = ($zip?.value || '').trim();
  return /^\d{5}$/.test(v);
}

function buildQuery(){
  return validZip() ? $zip.value.trim() : '';
}

async function calcDistance(){
  if (!validZip()){
    setStatus('Enter 5-digit ZIP');
    return;
  }
  const q = buildQuery();
  if (q === lastQuery) return;
  lastQuery = q;

  if (aborter) aborter.abort();
  aborter = new AbortController();

  setStatus('Calculating…');

  try {
    // 👉 Back-end returns miles/travel for ?zip=
    const res = await fetch(`/api/geocode?zip=${encodeURIComponent(q)}`, { signal: aborter.signal });
    const j = await res.json();

    if (!res.ok || !j?.ok) throw new Error(j?.msg || 'geocode error');

    const m = Number(j.miles || 0);
    const cost = Number(j.travel || 0);

    $dist.textContent = m.toFixed(1);
    $price.textContent = cost.toFixed(2);
    $distMiles.value = m.toFixed(1);
    $travelFee.value  = cost.toFixed(2);
    setStatus('OK ✓');
  } catch (e) {
    console.error(e);
    setStatus('Could not calculate. Try again.');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if ($btn) $btn.addEventListener('click', (e)=>{ e.preventDefault(); calcDistance(); });
  if ($zip){
    $zip.addEventListener('blur', ()=>{ calcDistance(); });
    $zip.addEventListener('keydown', (ev)=>{
      if (ev.key === 'Enter') { ev.preventDefault(); calcDistance(); }
    });
  }
  setStatus('Enter 5-digit ZIP and click Calculate.');
});
</script>
@endpush
