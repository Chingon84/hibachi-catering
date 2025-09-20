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

  <div style="background:#fafafa;border:1px solid #eee;border-radius:12px;padding:12px 16px;margin-bottom:16px">
    <strong>Summary:</strong>
    <span style="margin-left:10px">Guests: {{ $summary['Guests'] }}</span>
    <span style="margin-left:16px">Date: {{ $summary['Date'] }}</span>
    <span style="margin-left:16px">Time: {{ $summary['Time'] }}</span>
  </div>

  @if ($errors->any())
    <p style="color:#b21e27; font-weight:600">{{ $errors->first() }}</p>
  @endif

  <form method="post" action="{{ route('reservations.submit',['step'=>2]) }}" id="step2">
    @csrf

    <h3>Client Info</h3>
    <div class="row">
      <div>
        <label>*Name</label>
        <input type="text" name="first_name" required placeholder="First name" value="{{ $v('first_name') }}">
      </div>
      <div>
        <label>*Last Name</label>
        <input type="text" name="last_name" required placeholder="Last name" value="{{ $v('last_name') }}">
      </div>
    </div>

    <div class="row" style="margin-top:12px">
      <div>
        <label>Company (Optional)</label>
        <input type="text" name="company" placeholder="Company" value="{{ $v('company') }}">
      </div>
      <div>
        <label>*Phone</label>
        <input type="text" name="phone" placeholder="(555) 555-5555" value="{{ $v('phone') }}">
      </div>
    </div>

    <div style="margin-top:12px">
      <label>*Email</label>
      <input type="email" name="email" placeholder="you@example.com" value="{{ $v('email') }}">
    </div>

    <h3 style="margin-top:20px">Event Info</h3>
    <div>
      <label>*Address of the event</label>
      <input type="text" id="addr" name="address" placeholder="123 Main St" value="{{ $v('address') }}">
    </div>
    <div class="row" style="margin-top:12px">
      <div>
        <label>City</label>
        <input type="text" id="city" name="city" placeholder="City" value="{{ $v('city') }}">
      </div>
      <div>
        <label>*Zip Code</label>
        <input type="text" id="zip" name="zip_code" placeholder="Zip" value="{{ $v('zip_code') }}">
      </div>
    </div>

    <div style="margin-top:8px; display:flex; align-items:center; gap:12px">
  <small>Distance: <strong id="dist">0</strong> mi</small>
  <small>Price: $<strong id="price">0.00</strong></small>
  <button type="button" class="btn" id="btnCalc" style="padding:6px 10px">Calculate Distance</button>
  <small id="distStatus" style="color:#666"></small>
</div>


    <div class="row" style="margin-top:16px">
      <div>
        <label>*What type of event are you hosting?</label>
        <select name="event_type">
          <option value="">Select…</option>
          @foreach (['Birthday','Wedding','Corporate','Graduation','Holiday','Other'] as $opt)
            <option value="{{ $opt }}" {{ $v('event_type')===$opt ? 'selected':'' }}>{{ $opt }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label>Setup color</label>
        @php
          $combineWithBlack = ['Red','Gold','Silver','White','Navy Blue','Baby Blue','Hot Pink','Baby Pink','Turquoise','Green','Purple','Orange'];
          $colorOptions = array_map(fn($c)=>"Black & $c", $combineWithBlack);
          $colorOptions = array_merge($colorOptions, ['All Black','White','Other']);
        @endphp
        <select name="setup_color">
          @foreach ($colorOptions as $opt)
            <option value="{{ $opt }}" {{ $v('setup_color')===$opt ? 'selected':'' }}>{{ $opt }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div style="margin-top:12px">
      <label>Do we have to go up or down stairs to do the setup?</label>
      <div>
        <label><input type="radio" name="stairs" value="1" {{ $v('stairs') ? 'checked':'' }}> Yes</label>
        <label style="margin-left:16px"><input type="radio" name="stairs" value="0" {{ !$v('stairs') ? 'checked':'' }}> No</label>
      </div>
    </div>

    <div style="margin-top:12px">
      <label>How did you hear about us?</label>
      <select name="heard_about">
        @foreach (['Returning customer','Instagram','Facebook','TikTok','Yelp','Google','Friend/Family','Other'] as $opt)
          <option value="{{ $opt }}" {{ $v('heard_about')===$opt ? 'selected':'' }}>{{ $opt }}</option>
        @endforeach
      </select>
    </div>

    <div style="margin-top:12px">
      <label>Additional Information / Special Request (Optional)</label>
      <input type="text" name="notes" value="{{ $v('notes') }}" placeholder="Tell us anything important…">
    </div>

    <input type="hidden" name="distance_miles" id="distance_miles" value="{{ $v('distance_miles', 0) }}">
    <input type="hidden" name="travel_fee" id="travel_fee" value="{{ $v('travel_fee', 0) }}">

    <br>
    <button class="btn" type="submit">Continue</button>
    <a class="btn" href="{{ route('reservations.step',['step'=>1]) }}" style="margin-left:8px;background:#555">Back</a>
  </form>
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
