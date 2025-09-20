<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Staff Bookings (Step 1)</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    :root{--brand:#b21e27;--brand-hover:#9a1a22}
    .title{font-size:22px;margin:0}
    .card{background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.04)}
    .card-body{padding:16px}
    .grid{display:grid;gap:12px}
    .grid.cols-2{grid-template-columns:1fr 1fr}
    .grid.cols-3{grid-template-columns:1fr 1fr 1fr}
    @media (max-width: 760px){.grid.cols-2,.grid.cols-3{grid-template-columns:1fr}}
    .label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#374151}
    .input,.select,textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:#fff}
    .btn{appearance:none;border:0;background:var(--brand);color:#fff;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:600}
    .btn:hover{background:var(--brand-hover)}
    .btn.secondary{background:#4b5563}
    .btn.secondary:hover{background:#374151}
    .muted{color:var(--muted);font-size:13px}
    .alert{border-radius:10px;padding:10px 12px;font-size:14px}
    .alert.error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca}
    .input[type=number]{text-align:center;padding:6px 8px}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div></div>
      <a href="{{ route('admin.dashboard') }}" class="btn secondary">Back to Dashboard</a>
    </div>

    @if ($errors->any())
      <div class="card" style="margin-bottom:12px"><div class="card-body"><div class="alert error">{{ $errors->first() }}</div></div></div>
    @endif

    <div class="card">
      <div class="card-body">
        <form method="post" action="{{ route('admin.staff_bookings.step1.submit') }}">
          @csrf
          <div class="grid cols-3">
            <div>
              <label class="label">Event Date</label>
              <input class="input" type="date" name="event_date" value="{{ old('event_date', $data['event_date'] ?? '') }}" required>
            </div>
            <div>
              <label class="label">Event Time</label>
              <input class="input" type="time" name="event_time" value="{{ old('event_time', $data['event_time'] ?? '') }}" required>
            </div>
            <div>
              <label class="label">Guest Count</label>
              <input class="input" type="number" min="1" name="guest_count" value="{{ old('guest_count', $data['guest_count'] ?? '') }}" required>
            </div>
          </div>

          <div class="grid cols-2" style="margin-top:8px">
            <div>
              <label class="label">Name</label>
              <input class="input" name="first_name" value="{{ old('first_name', $data['first_name'] ?? '') }}" required>
            </div>
            <div>
              <label class="label">Last Name</label>
              <input class="input" name="last_name" value="{{ old('last_name', $data['last_name'] ?? '') }}" required>
            </div>
          </div>

          <div>
            <label class="label">Company (optional)</label>
            <input class="input" name="company" value="{{ old('company', $data['company'] ?? '') }}">
          </div>

          <div class="grid cols-2">
            <div>
              <label class="label">Phone</label>
              <input class="input" name="phone" value="{{ old('phone', $data['phone'] ?? '') }}" required>
            </div>
            <div>
              <label class="label">Email</label>
              <input class="input" type="email" name="email" value="{{ old('email', $data['email'] ?? '') }}" required>
            </div>
          </div>

          <div>
            <label class="label">Address of the event</label>
            <input class="input" name="address" value="{{ old('address', $data['address'] ?? '') }}" required>
          </div>
          <div class="grid cols-3">
            <div>
              <label class="label">City</label>
              <input class="input" name="city" value="{{ old('city', $data['city'] ?? '') }}" required>
            </div>
            <div>
              <label class="label">ZIP</label>
              <input class="input" name="zip" value="{{ old('zip', $data['zip'] ?? '') }}" required>
            </div>
            <div></div>
          </div>

          <div class="grid cols-3">
            <div>
              <label class="label">Serving Style</label>
              @php $ss = old('serving_style', $data['serving_style'] ?? 'table'); @endphp
              <select class="select" name="serving_style" required>
                <option value="table" {{ $ss==='table'?'selected':'' }}>Table</option>
                <option value="buffet" {{ $ss==='buffet'?'selected':'' }}>Buffet</option>
              </select>
            </div>
            <div>
              <label class="label">Type of Event</label>
              <input class="input" name="event_type" value="{{ old('event_type', $data['event_type'] ?? '') }}" required>
            </div>
            <div>
              <label class="label">Setup Color</label>
              <input class="input" name="setup_color" value="{{ old('setup_color', $data['setup_color'] ?? '') }}">
            </div>
          </div>

          <div class="grid cols-2">
            <div>
              <label class="label">Are there stairs to access the setup area?</label>
              @php $st = old('stairs', $data['stairs'] ?? 'no'); @endphp
              <div style="display:flex;gap:14px;align-items:center">
                <label style="display:flex;gap:6px;align-items:center"><input type="radio" name="stairs" value="yes" {{ $st==='yes'?'checked':'' }}> Yes</label>
                <label style="display:flex;gap:6px;align-items:center"><input type="radio" name="stairs" value="no" {{ $st==='no'?'checked':'' }}> No</label>
              </div>
            </div>
            <div>
              <label class="label">How did you hear about us?</label>
              <input class="input" name="heard_about" value="{{ old('heard_about', $data['heard_about'] ?? '') }}" placeholder="Google, Yelp, Instagram, Referral, etc.">
            </div>
          </div>

          <div class="grid cols-2">
            <div>
              <label class="label">Handled By</label>
              <input class="input" name="handled_by" value="{{ old('handled_by', $data['handled_by'] ?? '') }}" required>
            </div>
            <div></div>
          </div>

          <div>
            <label class="label">Additional information / special request</label>
            <textarea name="agent_notes" rows="5" placeholder="Notes, special requests, internal comments">{{ old('agent_notes', $data['agent_notes'] ?? '') }}</textarea>
          </div>

          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px">
            <button class="btn" type="submit">Next</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
