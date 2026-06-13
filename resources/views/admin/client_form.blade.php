@extends('layouts.admin')

@section('title', $mode === 'edit' ? 'Edit Client' : 'New Client')

@push('styles')
<style>
  /* Page-specific layout only — visual styling comes from the shared app.css */
  .grid{display:grid;gap:12px}
  .grid.cols-2{grid-template-columns:1fr 1fr}
  .grid.cols-3{grid-template-columns:1fr 1fr 1fr}
  @media (max-width: 760px){.grid.cols-2,.grid.cols-3{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="container">
  <div class="header">
    <div></div>
    <a href="{{ $mode==='edit' ? route('admin.clients.show', ['id'=>$client->id]) : route('admin.clients') }}" class="btn secondary" style="margin-left:auto">Back</a>
  </div>

  @if ($errors->any())
    <div class="card" style="margin-bottom:12px"><div class="card-body"><div class="alert error">{{ $errors->first() }}</div></div></div>
  @endif
  @if (session('ok'))
    <div class="card" style="margin-bottom:12px"><div class="card-body"><div class="alert success">{{ session('ok') }}</div></div></div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="post" action="{{ $mode==='edit' ? route('admin.clients.update', ['id'=>$client->id]) : route('admin.clients.store') }}">
        @csrf
        <div class="grid cols-2">
          <div>
            <label class="label">First Name</label>
            <input class="input" name="first_name" value="{{ old('first_name', $client->first_name) }}">
          </div>
          <div>
            <label class="label">Last Name</label>
            <input class="input" name="last_name" value="{{ old('last_name', $client->last_name) }}">
          </div>
        </div>
        <div class="grid cols-3">
          <div>
            <label class="label">Company</label>
            <input class="input" name="company" value="{{ old('company', $client->company) }}">
          </div>
          <div>
            <label class="label">Date</label>
            <input class="input" type="date" name="last_event_date" value="{{ old('last_event_date', optional($client->last_event_date)->format('Y-m-d')) }}">
          </div>
          <div>
            <label class="label">Guests</label>
            <input class="input" type="number" min="0" name="last_guests" value="{{ old('last_guests', $client->last_guests) }}">
          </div>
        </div>

        <div class="grid cols-2">
          <div>
            <label class="label">Primary Phone</label>
            <input class="input" name="phone_primary" value="{{ old('phone_primary', $client->phone_primary) }}">
          </div>
          <div>
            <label class="label">Alternate Phone</label>
            <input class="input" name="phone_alt" value="{{ old('phone_alt', $client->phone_alt) }}">
          </div>
        </div>

        <div class="grid cols-2">
          <div>
            <label class="label">Primary Email</label>
            <input class="input" name="email_primary" type="email" value="{{ old('email_primary', $client->email_primary) }}">
          </div>
          <div>
            <label class="label">Alternate Email</label>
            <input class="input" name="email_alt" type="email" value="{{ old('email_alt', $client->email_alt) }}">
          </div>
        </div>

        <div class="grid cols-2">
          <div>
            <label class="label">Address 1 – Street</label>
            <input class="input" name="address1_street" value="{{ old('address1_street', $client->address1_street) }}">
          </div>
          <div class="grid cols-3">
            <div>
              <label class="label">City</label>
              <input class="input" name="address1_city" value="{{ old('address1_city', $client->address1_city) }}">
            </div>
            <div>
              <label class="label">State</label>
              <input class="input" name="address1_state" value="{{ old('address1_state', $client->address1_state) }}">
            </div>
            <div>
              <label class="label">ZIP</label>
              <input class="input" name="address1_zip" value="{{ old('address1_zip', $client->address1_zip) }}">
            </div>
          </div>
        </div>

        <div class="grid cols-2">
          <div>
            <label class="label">Address 2 – Street</label>
            <input class="input" name="address2_street" value="{{ old('address2_street', $client->address2_street) }}">
          </div>
          <div class="grid cols-3">
            <div>
              <label class="label">City</label>
              <input class="input" name="address2_city" value="{{ old('address2_city', $client->address2_city) }}">
            </div>
            <div>
              <label class="label">State</label>
              <input class="input" name="address2_state" value="{{ old('address2_state', $client->address2_state) }}">
            </div>
            <div>
              <label class="label">ZIP</label>
              <input class="input" name="address2_zip" value="{{ old('address2_zip', $client->address2_zip) }}">
            </div>
          </div>
        </div>

        @php $social = (array) old('social', (array) ($client->social_links ?? [])); @endphp
        <div>
          <label class="label">Social Media</label>
          <input class="input" name="social[social_media]" value="{{ $social['social_media'] ?? '' }}" placeholder="Handle or URL">
        </div>

        <div>
          <label class="label">Website</label>
          <input class="input" name="website" type="text" value="{{ old('website', $client->website) }}" placeholder="https://example.com">
        </div>

        <div class="grid cols-2">
          <div>
            <label class="label">Referral Source</label>
            <input class="input" name="referral_source" value="{{ old('referral_source', $client->referral_source) }}" placeholder="Google, Yelp, Instagram, Referral, etc.">
          </div>
          <div>
            <label class="label">Status</label>
            <select name="status" class="select">
              @php $st = strtolower(old('status', $client->status ?? 'regular')); @endphp
              @foreach(($statusOptions ?? ['regular','vip','celebrity','blacklist','preferred']) as $opt)
                <option value="{{ $opt }}" {{ $st===$opt?'selected':'' }}>{{ ucfirst($opt) }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div>
          <label class="label">Internal Notes</label>
          <textarea name="internal_notes" rows="5">{{ old('internal_notes', $client->internal_notes) }}</textarea>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px">
          <a class="btn secondary" href="{{ $mode==='edit' ? route('admin.clients.show', ['id'=>$client->id]) : route('admin.clients') }}">Cancel</a>
          <button class="btn" type="submit">{{ $mode==='edit' ? 'Save Changes' : 'Create Client' }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
