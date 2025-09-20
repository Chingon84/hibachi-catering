<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Reservation Details</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    :root{--bg:#f7f7fb;--text:#111827;--muted:#6b7280;--card:#fff;--border:#e5e7eb;--brand:#b21e27}
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .btn{appearance:none;border:0;background:var(--brand);color:#fff;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block}
    .btn.secondary{background:#4b5563}
    .badge{display:inline-block;border:1px solid var(--border);background:#f3f4f6;color:#374151;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:700}
    .chip{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid var(--border);border-radius:999px;padding:6px 10px;font-size:12px}
    .chip svg{width:14px;height:14px;color:#6b7280}
    .card{background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 6px 18px rgba(0,0,0,.04);margin-bottom:14px}
    .input{width:100%;padding:10px 12px;border:1px solid #e6e8ec;border-radius:10px;background:#fff;transition:border-color .12s ease, box-shadow .12s ease}
    .input:focus{outline:none;border-color:#d1d5db;box-shadow:0 0 0 3px rgba(178,30,39,.08)}
    select.input{appearance:none;background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="%236b7280"><path d="M5.23 7.21a.75.75 0 011.06.02L10 10.207l3.71-2.977a.75.75 0 111.06 1.06l-4.24 3.4a.75.75 0 01-.94 0l-4.24-3.4a.75.75 0 01.02-1.06z"/></svg>');background-repeat:no-repeat;background-position:right 10px center;background-size:16px;padding-right:34px}
    textarea.input{resize:vertical}
    .icon-btn{appearance:none;border:0;background:#4b5563;color:#fff;border-radius:8px;padding:6px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .icon-btn:hover{background:#374151}
    .icon-btn.danger{background:#b21e27}
    .icon-btn.danger:hover{background:#9a1a22}
    .icon-btn svg{width:16px;height:16px;display:block}
    /* Compact controls for adjustments */
    .adj-plus{width:32px;height:32px;border-radius:9999px;border:1px solid #ddd;background:#eee;color:#666;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .adj-plus:hover{background:#e5e7eb;border-color:#ccc;color:#444}
    .adj-plus:disabled{opacity:.4;cursor:not-allowed}
    .adj-remove{width:28px;height:28px;border-radius:8px;border:1px solid #ddd;background:#fff;color:#444;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .adj-remove:hover{background:#f9fafb}
    .color-dot{width:14px;height:14px;border-radius:999px;border:1px solid #d1d5db;cursor:pointer}
    .color-pop{position:absolute;top:100%;right:0;margin-top:6px;z-index:2000;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:8px}
    .swatches{display:grid;grid-template-columns:repeat(5,16px);gap:8px}
    .sw{width:16px;height:16px;border-radius:999px;border:2px solid #fff;box-shadow:0 0 0 1px #d1d5db;position:relative;cursor:pointer}
    .sw .check{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px}
  </style>
  @php $fmt = fn($n)=>'$'.number_format((float)$n,2); @endphp
</head>
<body>
  <div class="max-w-7xl mx-auto p-4 lg:p-8">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-6 gap-4">
      <div>
        <h1 class="text-2xl font-bold flex items-center gap-3 mb-2">
          Reservation Details
          @php $inv = $r->invoice_number ?? null; @endphp
          @if($inv)<span class="badge">Invoice #{{ $inv }}</span>@endif
        </h1>
        <div class="flex flex-wrap gap-2 items-center">
          <span class="chip" title="Date">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1zm12 7H5v10h14V9z"/></svg>
            {{ $r->date?->format('m/d/Y') ?? '—' }}
          </span>
          <span class="chip" title="Time">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2zm1 10.586V7a1 1 0 0 0-2 0v6a1 1 0 0 0 .293.707l3 3a1 1 0 1 0 1.414-1.414z"/></svg>
            {{ \Carbon\Carbon::parse($r->time)->format('g:i A') }}
          </span>
          <span class="chip" title="Guests">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 11c1.654 0 3-1.346 3-3S17.654 5 16 5s-3 1.346-3 3 1.346 3 3 3zM8 11c1.654 0 3-1.346 3-3S9.654 5 8 5 5 6.346 5 8s1.346 3 3 3zm0 2c-2.673 0-8 1.337-8 4v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.663-5.327-4-8-4zm8 0c-.29 0-.62.017-.98.047A6.6 6.6 0 0 1 18 17v1a1 1 0 0 1-1 1h6a1 1 0 0 0 1-1v-1c0-2.273-3.876-4-6-4z"/></svg>
            {{ $r->guests }} guests
          </span>
          @php $balTop = max(0, (float)($r->total ?? 0) - (float)($r->deposit_paid ?? 0)); $col = $r->color ?? '#6b7280'; @endphp
          <span class="chip" title="Balance" style="display:inline-flex;align-items:center;gap:8px">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 1a11 11 0 1 0 11 11A11.013 11.013 0 0 0 12 1zm1 17.93V20a1 1 0 0 1-2 0v-1a4.005 4.005 0 0 1-3-3.87 1 1 0 0 1 2 0 2 2 0 0 0 2 2h2a2 2 0 0 0 0-4h-2a4 4 0 0 1 0-8h1V4a1 1 0 0 1 2 0v1a4.005 4.005 0 0 1 3 3.87 1 1 0 0 1-2 0 2 2 0 0 0-2-2h-2a2 2 0 0 0 0 4h2a4 4 0 0 1 0 8h-1.05A10.027 10.027 0 0 1 12 21a10.013 10.013 0 0 1-1-.07z"/></svg>
            Balance: {{ '$'.number_format($balTop,2) }}
          </span>
          <!-- Color picker button (separate from chip) -->
          <span class="inline-block relative" x-data="colorPicker('{{ $col }}')" style="margin-left:10px;display:inline-block;vertical-align:middle">
            <button type="button" class="color-dot" :style="{ background: color }" @click.stop="toggle()" aria-label="Pick color"></button>
            <div class="color-pop" x-show="open" @click.away="open=false" @keydown.escape.window="open=false" x-transition>
              <div class="swatches">
                <template x-for="c in colors" :key="c">
                  <button type="button" class="sw" :class="{'none': c==='none'}" :style="c!=='none'?{background:c}:{background:'#fff'}" @click="pick(c)" :aria-label="c==='none' ? 'None' : ('Pick '+c)">
                    <span x-show="color===c" class="check">✓</span>
                    <svg x-show="c==='none'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" style="position:absolute;inset:0;margin:auto;color:#374151"><path d="M5 5l14 14M19 5L5 19" stroke="currentColor" stroke-width="2"/></svg>
                  </button>
                </template>
              </div>
            </div>
          </span>
        </div>
      </div>
      <div class="flex gap-3 items-center">
        <a class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md" href="{{ route('admin.reservations') }}">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2" aria-hidden="true">
            <path d="M11.03 3.97a.75.75 0 0 1 0 1.06l-6.22 6.22H21a.75.75 0 0 1 0 1.5H4.81l6.22 6.22a.75.75 0 1 1-1.06 1.06l-7.5-7.5a.75.75 0 0 1 0-1.06l7.5-7.5a.75.75 0 0 1 1.06 0z"/>
          </svg>
          Back
        </a>
        <a class="inline-flex items-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md border border-blue-200" href="{{ route('admin.reservations.invoice',['id'=>$r->id]) }}">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2" aria-hidden="true">
            <path d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625Z"/>
            <path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z"/>
          </svg>
          Invoice
        </a>
        <a class="inline-flex items-center p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md border border-blue-200" 
           href="{{ route('admin.reservations.show',['id'=>$r->id, 'print'=>'menu', 'back'=>request()->fullUrl()]) }}" 
           title="Print menu" aria-label="Print menu">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4" aria-hidden="true">
            <circle cx="5" cy="7" r="1.5" />
            <circle cx="5" cy="12" r="1.5" />
            <circle cx="5" cy="17" r="1.5" />
            <path d="M9 7h10M9 12h10M9 17h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
          </svg>
        </a>
        <button class="inline-flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg focus:ring-2 focus:ring-green-500 focus:ring-offset-2" type="submit" form="resvForm">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2" aria-hidden="true">
            <path d="M7.5 3.375c0-1.036.84-1.875 1.875-1.875h.375a3.75 3.75 0 0 1 3.75 3.75v1.875C13.5 8.161 14.34 9 15.375 9h1.875A3.75 3.75 0 0 1 21 12.75v3.375C21 17.16 20.16 18 19.125 18h-9.75A1.875 1.875 0 0 1 7.5 16.125V3.375Z"/>
            <path d="M15 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963A5.23 5.23 0 0 0 17.25 7.5h-1.875a.375.375 0 0 1-.375-.375V5.25Z"/>
          </svg>
          Save
        </button>
      </div>
    </div>

    <!-- Reservation Form -->
    <div class="card">
      <div class="p-6">
        @if (session('ok'))
          <div class="bg-green-50 text-green-700 border border-green-200 rounded-lg p-3 mb-4">
            {{ session('ok') }}
          </div>
        @endif
        @if ($errors->any())
          <div class="bg-red-50 text-red-700 border border-red-200 rounded-lg p-3 mb-4">
            {{ $errors->first() }}
          </div>
        @endif
        
        <form method="post" action="{{ route('admin.reservations.update',['id'=>$r->id]) }}" id="resvForm">
          @csrf
          <!-- Reordered Form: Left/Right columns -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Left column fields -->
            <div class="space-y-4">
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Customer name</label>
                <input name="customer_name" value="{{ old('customer_name',$r->customer_name) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Company</label>
                <input name="company" value="{{ old('company',$r->company) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Date</label>
                <input type="date" name="date" value="{{ old('date',$r->date?->toDateString()) }}" class="input col-span-2" required>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Phone</label>
                <input name="phone" value="{{ old('phone',$r->phone) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Email</label>
                <input type="email" name="email" value="{{ old('email',$r->email) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Address</label>
                <input name="address" value="{{ old('address',$r->address) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">City</label>
                <input name="city" value="{{ old('city',$r->city) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">ZIP</label>
                <input name="zip_code" value="{{ old('zip_code',$r->zip_code) }}" class="input col-span-2">
              </div>
            </div>

            <!-- Right column fields -->
            <div class="space-y-4">
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Invoice #</label>
                <input name="invoice_number" value="{{ $r->invoice_number ?? '' }}" class="input col-span-2" disabled>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Guests</label>
                <input type="number" min="1" name="guests" value="{{ old('guests',$r->guests) }}" class="input col-span-2" required>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Time</label>
                <input type="time" name="time" value="{{ old('time', \Carbon\Carbon::parse($r->time)->format('H:i')) }}" class="input col-span-2" required>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Setup color</label>
                <input name="setup_color" value="{{ old('setup_color',$r->setup_color) }}" class="input col-span-2">
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Event type</label>
                @php $eventOpts = ['Birthday','Wedding','Corporate','Graduation','Holiday','Other']; @endphp
                <select name="event_type" class="input col-span-2" aria-label="Select event type">
                  <option value="">Select…</option>
                  @foreach ($eventOpts as $opt)
                    <option value="{{ $opt }}" {{ old('event_type', $r->event_type) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                  @endforeach
                </select>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">Stairs</label>
                <select name="stairs" class="input col-span-2" aria-label="Stairs required">
                  <option value="0" {{ old('stairs',$r->stairs) ? '' : 'selected' }}>No</option>
                  <option value="1" {{ old('stairs',$r->stairs) ? 'selected' : '' }}>Yes</option>
                </select>
              </div>
              <div class="grid grid-cols-3 gap-2 items-center">
                <label class="font-semibold text-sm">How did you hear about us?</label>
                @php $heardOpts = ['Returning customer','Instagram','Facebook','TikTok','Yelp','Google','Friend/Family','Other']; @endphp
                <select name="heard_about" class="input col-span-2" aria-label="How customer found us">
                  <option value="">Select…</option>
                  @foreach ($heardOpts as $opt)
                    <option value="{{ $opt }}" {{ old('heard_about',$r->heard_about) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                  @endforeach
                </select>
              </div>
              <div class="grid grid-cols-3 gap-2 items-start">
                <label class="font-semibold text-sm">Notes</label>
                <textarea name="notes" rows="3" class="input col-span-2">{{ old('notes',$r->notes) }}</textarea>
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <button class="btn" type="submit">Save Changes</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Details Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Left column -->
      <div class="card">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4">Customer</h3>
          <div class="space-y-2 text-sm">
            <div><strong>Customer name:</strong> {{ $r->customer_name ?? '—' }}</div>
            <div><strong>Company:</strong> {{ $r->company ?? '—' }}</div>
            <div><strong>Date:</strong> {{ $r->date?->format('m/d/Y') ?? '—' }}</div>
            <div><strong>Phone:</strong> {{ $r->phone ?? '—' }}</div>
            <div><strong>Email:</strong> {{ $r->email ?? '—' }}</div>
            <div><strong>Address:</strong> {{ $r->address ?? '—' }}</div>
            <div><strong>City:</strong> {{ $r->city ?? '—' }}</div>
            <div><strong>ZIP:</strong> {{ $r->zip_code ?? '—' }}</div>
          </div>
        </div>
      </div>
      <!-- Right column -->
      <div class="card">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4">Event</h3>
          <div class="space-y-2 text-sm">
            <div><strong>Invoice #:</strong> {{ $r->invoice_number ?? '—' }}</div>
            <div><strong>Guests:</strong> {{ $r->guests ?? '—' }}</div>
            <div><strong>Time:</strong> {{ \Carbon\Carbon::parse($r->time)->format('g:i A') ?? '—' }}</div>
            <div><strong>Setup color:</strong> {{ $r->setup_color ?? '—' }}</div>
            <div><strong>Event type:</strong> {{ $r->event_type ?? '—' }}</div>
            <div><strong>Stairs:</strong> {{ $r->stairs ? 'Yes' : 'No' }}</div>
            <div><strong>How did you hear about us:</strong> {{ $r->heard_about ?? '—' }}</div>
            <div><strong>Notes:</strong> {{ $r->notes ?? '—' }}</div>
          </div>
        </div>
      </div>
    </div>

    

    <!-- Items Section -->
    <div class="card" x-data="itemsManager()">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">Items</h3>
        @php $its = $r->items ?? collect(); @endphp
        @if($its && $its->count())
          <form method="post" action="{{ route('admin.reservations.items.update',['id'=>$r->id]) }}">
            @csrf
            <div class="overflow-x-auto">
              <table class="w-full border-collapse">
                <thead>
                  <tr class="border-b border-gray-200">
                    <th class="text-left p-3 font-semibold text-sm">Item</th>
                    <th class="text-left p-3 font-semibold text-sm">Description</th>
                    <th class="text-right p-3 font-semibold text-sm">Unit</th>
                    <th class="text-right p-3 font-semibold text-sm">Qty</th>
                    <th class="text-right p-3 font-semibold text-sm">Total</th>
                    <th class="w-16"></th>
                  </tr>
                </thead>
                <tbody>
                @foreach($its as $it)
                  <tr class="border-b border-gray-100">
                    <td class="p-3 text-sm">{{ $it->name_snapshot }}</td>
                    <td class="p-3">
                      <div x-data="{ editing: false, value: '{{ old('desc.'.$it->id, $it->description) }}' }">
                        <div x-show="!editing" 
                             @click="editing = true; $nextTick(() => $refs.input.focus())" 
                             class="text-gray-500 cursor-pointer py-2 px-3 hover:text-gray-700 transition-colors duration-150 min-h-[2rem] flex items-center text-sm"
                             x-text="value || 'Optional description'"
                             role="button"
                             tabindex="0"
                             aria-label="Click to edit description">
                        </div>
                        <input x-show="editing" 
                               x-ref="input"
                               type="text" 
                               name="desc[{{ $it->id }}]" 
                               x-model="value"
                               @blur="editing = false"
                               @keydown.enter="editing = false"
                               @keydown.escape="editing = false"
                               class="input w-full border-2 border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                               aria-label="Item description">
                      </div>
                    </td>
                    <td class="p-3 text-right text-sm">{{ $fmt($it->unit_price_snapshot ?? 0) }}</td>
                    <td class="p-3 text-right">
                      <input type="number" 
                             name="items[{{ $it->id }}]" 
                             value="{{ $it->qty }}" 
                             min="0" 
                             class="w-20 p-2 border border-gray-300 rounded text-center text-sm"
                             aria-label="Quantity for {{ $it->name_snapshot }}">
                    </td>
                    <td class="p-3 text-right text-sm">{{ $fmt($it->line_total ?? 0) }}</td>
                    <td class="p-3 text-right">
                      <form method="post" action="{{ route('admin.reservations.items.delete',['id'=>$r->id,'itemId'=>$it->id]) }}" onsubmit="return confirm('Delete this item?')" class="inline">
                        @csrf
                        <button class="icon-btn danger" type="submit" title="Delete item" aria-label="Delete {{ $it->name_snapshot }}">
                          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z"/></svg>
                        </button>
                      </form>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            @php $bal = max(0, (float)($r->total ?? 0) - (float)($r->deposit_paid ?? 0)); @endphp
            <!-- Totals: stacked, right-aligned with custom adjustments -->
            @php 
              $TAX = 0.1025;
              $adj = is_array($r->invoice_adjustments ?? null) ? $r->invoice_adjustments : [];
              $sub = (float)($r->subtotal ?? 0); $trav = (float)($r->travel_fee ?? 0); $grat = (float)($r->gratuity ?? 0);
              $adjSum = array_reduce($adj, fn($c,$a)=> $c + (float)($a['amount'] ?? 0), 0.0);
              $taxCalc = round(max(0, $sub + $adjSum) * $TAX, 2);
              $totalCalc = round($sub + $trav + $grat + $taxCalc + $adjSum, 2);
              $paidCalc = (float)($r->deposit_paid ?? 0);
              $balCalc = max(0, round($totalCalc - $paidCalc, 2));
            @endphp
            <div class="mt-4 flex justify-end">
              <div class="text-sm text-gray-700 text-right space-y-1" x-data="adjustmentsManager({
                    subtotal: {{ number_format($sub,2,'.','') }},
                    travel: {{ number_format($trav,2,'.','') }},
                    gratuity: {{ number_format($grat,2,'.','') }},
                    taxRate: {{ number_format($TAX,4,'.','') }},
                    paid: {{ number_format($r->deposit_paid ?? 0,2,'.','') }},
                    adjInit: @js($adj)
                })" x-init="init()">
                <!-- Action button aligned to right, icon-only -->
                <div class="flex items-center justify-end">
                  <button type="button"
                          class="adj-plus"
                          :disabled="rows.length>=2"
                          title="Add custom adjustment"
                          aria-label="Add custom adjustment"
                          @click="addRow()">+</button>
                </div>
                <!-- Totals before adjustments -->
                <div><strong>Subtotal:</strong> <span x-text="fmt(subtotal)">{{ $fmt($sub) }}</span></div>
                <div><strong>Travel fee:</strong> <span x-text="fmt(travel)">{{ $fmt($trav) }}</span></div>
                <!-- Adjustment rows inserted right below Travel fee -->
                <template x-for="(row,idx) in rows" :key="idx">
                  <div class="flex items-center gap-2 justify-end" style="margin:2px 0">
                    <input class="input" type="text" x-model="row.label" placeholder="Adjustment" aria-label="Adjustment label" style="width:160px;padding:4px 8px;font-size:13px;height:28px">
                    <input class="input" type="text" x-model="row.amountStr" @focus="row.editing=true" @blur="normalize(idx)" @input="recalc()" aria-label="Adjustment amount" style="width:120px;text-align:right;padding:4px 8px;font-size:13px;height:28px">
                    <button type="button" class="adj-remove" title="Remove" aria-label="Remove adjustment" @click="remove(idx)">×</button>
                    <input type="hidden" name="adj_label[]" :value="row.label">
                    <input type="hidden" name="adj_amount[]" :value="row.amount">
                  </div>
                </template>
                <!-- Remaining totals -->
                <div><strong>Gratuity:</strong> <span x-text="fmt(gratuity)">{{ $fmt($grat) }}</span></div>
                <div><strong>Tax:</strong> <span x-text="fmt(tax)">{{ $fmt($taxCalc) }}</span></div>
                <div><strong>Total:</strong> <span x-text="fmt(total)">{{ $fmt($totalCalc) }}</span></div>
                <div><strong>Paid:</strong> <span x-text="fmt(paid)">{{ $fmt($r->deposit_paid ?? 0) }}</span></div>
                <div><strong>Balance:</strong> <span x-text="fmt(balance)">{{ $fmt($balCalc) }}</span></div>
              </div>
            </div>
            <!-- Save button line, right-aligned below totals -->
            <div class="mt-2 flex justify-end">
              <button class="btn" type="submit">Save item changes</button>
            </div>
          </form>
        @else
          <p class="text-gray-500 text-sm">No items recorded.</p>
        @endif

        <!-- Add Items Section -->
        <div class="border-t border-gray-200 mt-8 pt-8">
          <h4 class="text-xl font-semibold mb-6 text-gray-800">Add Items</h4>
          
          <!-- Menu Items Section -->
          <div class="mb-8">
            <h5 class="text-lg font-medium text-gray-700 mb-4">Menu Items</h5>
            
            <!-- Static Menu Form -->
            <form method="post" action="{{ route('admin.reservations.items.add',['id'=>$r->id]) }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 p-4 bg-blue-50 rounded-lg border border-blue-200 mb-4">
              @csrf
              <div class="lg:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">From menu</label>
                <select name="menu_key" class="input w-full" id="menuKey" aria-label="Select menu item">
                  <option value="">Select item...</option>
                  @foreach($menuOptions as $key=>$opt)
                    <option value="{{ $key }}" data-price="{{ number_format($opt['price'],2,'.','') }}">{{ $opt['cat'] }} – {{ $opt['name'] }}</option>
                  @endforeach
                </select>
              </div>
              <div class="lg:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <input type="text" name="description" class="input w-full" placeholder="Optional description">
              </div>
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Qty</label>
                <input type="number" name="qty" min="1" value="0" class="input w-full">
              </div>
              <div class="lg:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price</label>
                <input type="text" class="input w-full bg-gray-100" id="unitPrice" value="$0.00" readonly>
              </div>
              <div class="lg:col-span-1 flex items-end justify-center">
                <button type="button" 
                        @click="addMenuRow()" 
                        class="bg-blue-500 hover:bg-blue-600 text-white rounded-md p-2 transition-colors duration-200" 
                        title="Add new menu item row"
                        aria-label="Add new menu item row">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5" aria-hidden="true">
                    <path d="M12 4a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6V5a1 1 0 0 1 1-1z"/>
                  </svg>
                </button>
              </div>
            </form>
            
            <!-- Dynamic Menu Rows -->
            <template x-for="(row, index) in menuRows" :key="index">
              <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 p-4 bg-blue-50 rounded-lg border border-blue-200 mb-4">
                <div class="lg:col-span-3">
                  <label class="block text-sm font-medium text-gray-700 mb-2">From menu</label>
                  <select x-model="row.menu_key" @change="updateUnitPrice(index, $event)" class="input w-full" aria-label="Select menu item">
                    <option value="">Select item...</option>
                    @foreach($menuOptions as $key=>$opt)
                      <option value="{{ $key }}" data-price="{{ number_format($opt['price'],2,'.','') }}">{{ $opt['cat'] }} – {{ $opt['name'] }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="lg:col-span-3">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                  <input type="text" x-model="row.description" class="input w-full" placeholder="Optional description">
                </div>
                <div class="lg:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Qty</label>
                  <input type="number" min="1" x-model="row.qty" class="input w-full">
                </div>
                <div class="lg:col-span-3">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price</label>
                  <input type="text" class="input w-full bg-gray-100" x-model="row.unit_price" readonly>
                </div>
                <div class="lg:col-span-1 flex items-end justify-center">
                  <button type="button" 
                          @click="addMenuRow()" 
                          class="bg-blue-500 hover:bg-blue-600 text-white rounded-md p-2 transition-colors duration-200" 
                          title="Add new menu item row"
                          aria-label="Add new menu item row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5" aria-hidden="true">
                      <path d="M12 4a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6V5a1 1 0 0 1 1-1z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </template>
          </div>

          <!-- Custom Items Section -->
          <div class="mb-8">
            <h5 class="text-lg font-medium text-gray-700 mb-4">Custom Items</h5>
            
            <!-- Static Custom Form -->
            <form method="post" action="{{ route('admin.reservations.items.add',['id'=>$r->id]) }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 p-4 bg-green-50 rounded-lg border border-green-200 mb-4">
              @csrf
              <div class="lg:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Custom name</label>
                <input type="text" name="custom_name" placeholder="Enter item name" class="input w-full">
              </div>
              <div class="lg:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <input type="text" name="description" class="input w-full" placeholder="Optional description">
              </div>
              <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Qty</label>
                <input type="number" name="qty" min="1" value="0" class="input w-full">
              </div>
              <div class="lg:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Custom Price</label>
                <input type="number" step="0.01" name="custom_price" placeholder="0.00" class="input w-full" id="customPrice">
              </div>
              <div class="lg:col-span-1 flex items-end justify-center">
                <button type="button" 
                        @click="addCustomRow()" 
                        class="bg-green-500 hover:bg-green-600 text-white rounded-md p-2 transition-colors duration-200" 
                        title="Add new custom item row"
                        aria-label="Add new custom item row">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5" aria-hidden="true">
                    <path d="M12 4a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6V5a1 1 0 0 1 1-1z"/>
                  </svg>
                </button>
              </div>
            </form>
            
            <!-- Dynamic Custom Rows -->
            <template x-for="(row, index) in customRows" :key="index">
              <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 p-4 bg-green-50 rounded-lg border border-green-200 mb-4">
                <div class="lg:col-span-3">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Custom name</label>
                  <input type="text" x-model="row.custom_name" placeholder="Enter item name" class="input w-full">
                </div>
                <div class="lg:col-span-3">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                  <input type="text" x-model="row.description" class="input w-full" placeholder="Optional description">
                </div>
                <div class="lg:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Qty</label>
                  <input type="number" min="1" x-model="row.qty" class="input w-full">
                </div>
                <div class="lg:col-span-3">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Custom Price</label>
                  <input type="number" step="0.01" x-model="row.custom_price" placeholder="0.00" class="input w-full">
                </div>
                <div class="lg:col-span-1 flex items-end justify-center">
                  <button type="button" 
                          @click="addCustomRow()" 
                          class="bg-green-500 hover:bg-green-600 text-white rounded-md p-2 transition-colors duration-200" 
                          title="Add new custom item row"
                          aria-label="Add new custom item row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5" aria-hidden="true">
                      <path d="M12 4a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6V5a1 1 0 0 1 1-1z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </template>
          </div>

          <!-- Save All Items Button -->
          <div class="flex justify-end">
            <button type="button" 
                    @click="saveAllItems()" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold text-lg shadow-lg transition-all duration-200 hover:shadow-xl" 
                    title="Save all items"
                    aria-describedby="save-all-help">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 inline-block mr-2" aria-hidden="true">
                <path d="M17 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-5 16a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm2-8H10V7a1 1 0 1 1 2 0v4z"/>
              </svg>
              Save All Items
            </button>
            <p id="save-all-help" class="sr-only">Saves all items added in the forms above</p>
          </div>
        </div>

        <script>
          function itemsManager() {
            return {
              menuRows: [],
              customRows: [],
              
              addMenuRow() {
                this.menuRows.push({
                  menu_key: '',
                  description: '',
                  qty: 1,
                  unit_price: '$0.00'
                });
              },
              
              addCustomRow() {
                this.customRows.push({
                  custom_name: '',
                  description: '',
                  qty: 1,
                  custom_price: ''
                });
              },
              
              async saveAllItems() {
                let allItemsSaved = [];
                let pendingSaves = [];
                
                // Check and prepare initial menu form save
                const initialMenuForm = document.querySelector('#menuKey').closest('form');
                const menuKey = initialMenuForm.querySelector('[name="menu_key"]').value;
                const menuQty = initialMenuForm.querySelector('[name="qty"]').value;
                const menuDesc = initialMenuForm.querySelector('[name="description"]').value;
                
                if (menuKey && menuQty > 0) {
                  pendingSaves.push({ type: 'menu-initial', data: { menu_key: menuKey, qty: menuQty, description: menuDesc } });
                }
                
                // Check and prepare initial custom form save
                const initialCustomForm = document.querySelector('#customPrice').closest('form');
                const customName = initialCustomForm.querySelector('[name="custom_name"]').value;
                const customQty = initialCustomForm.querySelector('[name="qty"]').value;
                const customPrice = initialCustomForm.querySelector('[name="custom_price"]').value;
                const customDesc = initialCustomForm.querySelector('[name="description"]').value;
                
                if (customName && customQty > 0 && customPrice) {
                  pendingSaves.push({ type: 'custom-initial', data: { custom_name: customName, qty: customQty, custom_price: customPrice, description: customDesc } });
                }
                
                // Prepare dynamic menu rows save
                const validMenuRows = this.menuRows.filter(row => row.menu_key && row.qty > 0);
                for (const row of validMenuRows) {
                  pendingSaves.push({ type: 'menu-dynamic', data: row });
                }
                
                // Prepare dynamic custom rows save
                const validCustomRows = this.customRows.filter(row => row.custom_name && row.qty > 0 && row.custom_price);
                for (const row of validCustomRows) {
                  pendingSaves.push({ type: 'custom-dynamic', data: row });
                }
                
                if (pendingSaves.length === 0) {
                  alert('No valid items to save. Please fill in the required fields:\n\nFor menu items: Select from menu + quantity\nFor custom items: Name + quantity + price');
                  return;
                }
                
                // Execute all saves
                for (const saveItem of pendingSaves) {
                  let success = false;
                  
                  if (saveItem.type.includes('menu')) {
                    success = await this.saveMenuItem(saveItem.data);
                  } else if (saveItem.type.includes('custom')) {
                    success = await this.saveCustomItem(saveItem.data);
                  }
                  
                  if (success) {
                    allItemsSaved.push(saveItem.type);
                    
                    // Remove from dynamic arrays if applicable
                    if (saveItem.type === 'menu-dynamic') {
                      const index = this.menuRows.indexOf(saveItem.data);
                      if (index > -1) this.menuRows.splice(index, 1);
                    } else if (saveItem.type === 'custom-dynamic') {
                      const index = this.customRows.indexOf(saveItem.data);
                      if (index > -1) this.customRows.splice(index, 1);
                    }
                  }
                }
                
                if (allItemsSaved.length > 0) {
                  // Clear initial forms if they were saved
                  if (allItemsSaved.includes('menu-initial')) {
                    initialMenuForm.reset();
                  }
                  if (allItemsSaved.includes('custom-initial')) {
                    initialCustomForm.reset();
                  }
                  
                  // Reload to show updated items
                  setTimeout(() => {
                    window.location.reload();
                  }, 500);
                } else {
                  alert('Failed to save items. Please check the console for errors.');
                }
              },
              
              async saveMenuItem(row) {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('menu_key', row.menu_key);
                formData.append('description', row.description);
                formData.append('qty', row.qty);
                
                try {
                  const response = await fetch('{{ route('admin.reservations.items.add',['id'=>$r->id]) }}', {
                    method: 'POST',
                    body: formData
                  });
                  return response.ok;
                } catch (error) {
                  console.error('Error saving menu item:', error);
                  return false;
                }
              },
              
              async saveCustomItem(row) {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('custom_name', row.custom_name);
                formData.append('description', row.description || '');
                formData.append('qty', row.qty);
                formData.append('custom_price', row.custom_price);
                
                try {
                  const response = await fetch('{{ route('admin.reservations.items.add',['id'=>$r->id]) }}', {
                    method: 'POST',
                    body: formData
                  });
                  return response.ok;
                } catch (error) {
                  console.error('Error saving custom item:', error);
                  return false;
                }
              },
              
              updateUnitPrice(index, event = null) {
                const row = this.menuRows[index];
                if (!row) return;
                
                // If event is provided, use it directly
                if (event && event.target) {
                  const selectedOption = event.target.options[event.target.selectedIndex];
                  const price = selectedOption?.dataset?.price || '0';
                  row.unit_price = '$' + Number(price).toFixed(2);
                  return;
                }
                
                // Fallback to finding the select element
                if (row.menu_key) {
                  // Find all select elements and match by value
                  const selects = document.querySelectorAll('select[x-model^="menuRows["]');
                  for (let select of selects) {
                    if (select.value === row.menu_key) {
                      const selectedOption = select.options[select.selectedIndex];
                      const price = selectedOption?.dataset?.price || '0';
                      row.unit_price = '$' + Number(price).toFixed(2);
                      break;
                    }
                  }
                } else {
                  row.unit_price = '$0.00';
                }
              },
              
              init() {
                // Original script functionality for initial form
                this.$nextTick(() => {
                  const sel = document.getElementById('menuKey');
                  const unit = document.getElementById('unitPrice');
                  function fmt(n){ try { return '$' + (Number(n||0)).toFixed(2); } catch(e){ return '$0.00'; } }
                  function refreshUnit(){
                    const v = sel ? sel.value : '';
                    unit.value = v ? fmt(sel.options[sel.selectedIndex]?.dataset?.price || 0) : '$0.00';
                  }
                  sel && sel.addEventListener('change', refreshUnit);
                  refreshUnit();
                });
              }
            }
          }
        </script>
        <script>
          function adjustmentsManager(init){
            return {
              subtotal: init.subtotal||0, travel: init.travel||0, gratuity: init.gratuity||0, taxRate: init.taxRate||0.1025, paid: init.paid||0,
              rows: [], tax: 0, total: 0, balance: 0,
              fmt(n){ return '$'+(Number(n||0)).toFixed(2); },
              parseAmount(s){ const v = (s||'').toString().replace(/[^0-9\-\.]/g,''); const n = parseFloat(v); return isNaN(n)?0:n; },
              normalize(i){ const r=this.rows[i]; r.editing=false; r.amount = this.parseAmount(r.amountStr); r.amountStr = this.fmt(r.amount); this.recalc(); },
              addRow(){ if (this.rows.length>=2) return; this.rows.push({label:'Adjustment', amount:0, amountStr:this.fmt(0), editing:false}); this.recalc(); },
              remove(i){ this.rows.splice(i,1); this.recalc(); },
              recalc(){ const adj = this.rows.reduce((s,r)=> s + (Number(r.amount)||0), 0); this.tax = Math.max(0, this.subtotal + adj) * this.taxRate; this.tax = Math.round(this.tax*100)/100; this.total = this.subtotal + this.travel + this.gratuity + this.tax + adj; this.balance = Math.max(0, this.total - this.paid); },
              init(){ const initRows = Array.isArray(init.adjInit) ? init.adjInit.slice(0,2) : []; this.rows = initRows.map(a=>({label: String(a.label||'Adjustment'), amount: Number(a.amount||0), amountStr: this.fmt(a.amount||0), editing:false})); this.recalc(); }
            }
          }
        </script>
      </div>
    </div>

    <!-- Hidden Print Content (moved near end of body in print) -->
    <div id="printContent" class="hidden"></div>

    <!-- Hidden Print Menu-Only Content (moved near end of body in print) -->
    <div id="printMenuContent" class="hidden"></div>


    <!-- Payments Section -->
    <div class="card" x-data="manualPayments({
        list: @js($r->manual_payments ?? []),
        basePaid: {{ number_format($r->deposit_paid ?? 0,2,'.','') }},
        total: {{ number_format($r->total ?? 0,2,'.','') }}
      })">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Payments</h3>
          <button type="button" class="adj-plus" :disabled="rows.length>=3" aria-label="Add manual payment" title="Add manual payment" @click="addRow()">+</button>
        </div>
        @php $pays = $r->payments ?? collect(); @endphp
        @if($pays && $pays->count())
          <div class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="text-left p-3 font-semibold text-sm">Date</th>
                  <th class="text-left p-3 font-semibold text-sm">Provider</th>
                  <th class="text-left p-3 font-semibold text-sm">Card</th>
                  <th class="text-left p-3 font-semibold text-sm">Status</th>
                  <th class="text-right p-3 font-semibold text-sm">Amount</th>
                  <th class="text-left p-3 font-semibold text-sm">Transaction</th>
                  <th class="text-left p-3 font-semibold text-sm">Actions</th>
                </tr>
              </thead>
              <tbody>
              @foreach($pays as $p)
                <tr class="border-b border-gray-100">
                  <td class="p-3 text-sm">{{ $p->created_at?->format('m/d/Y H:i') }}</td>
                  <td class="p-3 text-sm">{{ strtoupper($p->provider) }}</td>
                  <td class="p-3 text-sm">
                    @php
                      $brand = strtoupper((string)($p->card_brand ?? ''));
                      $last4 = (string)($p->card_last4 ?? '');
                    @endphp
                    @if($brand && $last4)
                      {{ $brand }} •••• {{ $last4 }}
                    @else
                      —
                    @endif
                  </td>
                  <td class="p-3 text-sm">{{ ucfirst($p->status) }}</td>
                  <td class="p-3 text-right text-sm">{{ $fmt($p->amount ?? 0) }}</td>
                  <td class="p-3 text-gray-500 text-sm">{{ $p->transaction_id ?? '' }}</td>
                  <td class="p-3 text-sm"></td>
                </tr>
              @endforeach
              <!-- Manual payment rows -->
              <template x-for="(row, idx) in rows" :key="row.id || idx">
                <tr class="border-b border-gray-100">
                  <td class="p-2 text-sm"><input type="datetime-local" class="input" x-model="row.date" style="padding:6px 8px;font-size:13px;height:30px"></td>
                  <td class="p-2 text-sm">
                    <select class="input" x-model="row.provider" style="padding:6px 8px;font-size:13px;height:30px">
                      <template x-for="opt in providers"><option :value="opt" x-text="opt"></option></template>
                    </select>
                  </td>
                  <td class="p-2 text-sm"><input type="text" class="input" x-model="row.ref" placeholder="Last4 / Ref" style="padding:6px 8px;font-size:13px;height:30px"></td>
                  <td class="p-2 text-sm">
                    <select class="input" x-model="row.status" style="padding:6px 8px;font-size:13px;height:30px">
                      <template x-for="s in statuses"><option :value="s" x-text="s"></option></template>
                    </select>
                  </td>
                  <td class="p-2 text-sm" style="text-align:right"><input type="number" step="0.01" min="0.01" class="input" x-model.number="row.amount" @blur="formatAmount(idx)" style="padding:6px 8px;font-size:13px;height:30px;text-align:right;width:120px"></td>
                  <td class="p-2 text-sm"><input type="text" class="input" x-model="row.transaction_id" placeholder="Txn id (optional)" style="padding:6px 8px;font-size:13px;height:30px"></td>
                  <td class="p-2 text-sm">
                    <div class="flex items-center gap-2">
                      <button type="button" class="btn secondary" @click="save(idx)" style="padding:6px 10px">💾 Save</button>
                      <button type="button" class="icon-btn danger" @click="remove(idx)" title="Delete" aria-label="Delete" style="width:30px;height:30px">✕</button>
                    </div>
                    <div class="text-red-600 text-xs mt-1" x-text="row.error"></div>
                  </td>
                </tr>
              </template>
              </tbody>
            </table>
          </div>
        @else
          <p class="text-gray-500 text-sm">No payments recorded.</p>
        @endif
        </div>
      </div>
    </div>

  <style>
    @media print {
      /* Hide only direct children of body by default */
      body > * { display: none !important; }

      /* Default print block visible */
      body:not(.print-menu) > #printContent { display: block !important; }

      /* Menu-only mode: show menu block */
      body.print-menu > #printMenuContent { display: block !important; }
      /* Override preview min-height on print to prevent blank pages in Safari */
      body.print-menu #printMenuContent { min-height: auto !important; }
      /* Avoid blank trailing page for menu print */
      @page { margin: 12mm; }
      body.print-menu #printMenuContent, 
      body.print-menu #printMenuContent .wrap { page-break-after: avoid; page-break-before: avoid; }
      body.print-menu #printMenuContent .box { page-break-inside: avoid; }
      
      
      .print-container {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        line-height: 1.4;
      }
      
      .print-title {
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #333;
        padding-bottom: 10px;
      }
      
      .print-section {
        margin-bottom: 25px;
        page-break-inside: avoid;
      }
      
      .print-section h2 {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #333;
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
      }
      
      .print-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 20px;
        font-size: 14px;
      }
      
      .print-grid div {
        padding: 4px 0;
      }
      
      .print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin-top: 10px;
      }
      
      .print-table th,
      .print-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
      }
      
      .print-table th {
        background-color: #f5f5f5;
        font-weight: bold;
      }
      
      .print-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
      }
      
      strong {
        font-weight: bold;
      }
    }
    /* Invoice-like styles scoped to menu print block */
    @media print {
      #printMenuContent .wrap{max-width:900px;margin:0 auto}
      #printMenuContent .brand{display:flex;align-items:center;gap:12px;margin-bottom:12px}
      #printMenuContent .brand img{height:48px;width:auto}
      #printMenuContent .muted{color:#6b7280}
      #printMenuContent .box{border:1px solid #e5e7eb;border-radius:12px;padding:12px;margin-top:10px}
      #printMenuContent .kv{font-size:13px}
      #printMenuContent .kv-row{display:flex;align-items:baseline;justify-content:flex-start;gap:6px;padding:2px 0}
      #printMenuContent .kv-label{color:#374151}
      #printMenuContent .kv-label::after{content: ":"; margin: 0 6px 0 2px}
      #printMenuContent .kv-val{text-align:left}
      #printMenuContent table{width:100%;border-collapse:collapse}
      #printMenuContent th,#printMenuContent td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:14px}
      #printMenuContent th{text-align:left}
      #printMenuContent .right{text-align:right}
      #printMenuContent .two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    }
    /* Screen preview styles for menu print block (mirror invoice styles) */
    body.show-menu-preview{background:#fff !important}
    body.show-menu-preview #printMenuContent{background:#fff;min-height:100vh}
    body.show-menu-preview #printMenuContent .wrap{max-width:900px;margin:0 auto;padding:24px}
    body.show-menu-preview #printMenuContent .brand{display:flex;align-items:center;gap:12px;margin-bottom:12px}
    body.show-menu-preview #printMenuContent .brand img{height:48px;width:auto}
    body.show-menu-preview #printMenuContent .muted{color:#6b7280}
    body.show-menu-preview #printMenuContent .box{border:1px solid #e5e7eb;border-radius:12px;padding:12px;margin-top:10px}
    body.show-menu-preview #printMenuContent .kv{font-size:13px}
    body.show-menu-preview #printMenuContent .kv-row{display:flex;align-items:baseline;justify-content:flex-start;gap:6px;padding:2px 0}
    body.show-menu-preview #printMenuContent .kv-label{color:#374151}
    body.show-menu-preview #printMenuContent .kv-label::after{content: ":"; margin: 0 6px 0 2px}
    body.show-menu-preview #printMenuContent .kv-val{text-align:left}
    body.show-menu-preview #printMenuContent table{width:100%;border-collapse:collapse}
    body.show-menu-preview #printMenuContent th, 
    body.show-menu-preview #printMenuContent td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:14px}
    body.show-menu-preview #printMenuContent th{text-align:left}
    body.show-menu-preview #printMenuContent .right{text-align:right}
    body.show-menu-preview #printMenuContent .two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    /* Screen preview: show only menu print block */
    body.show-menu-preview > * { display: none !important; }
    body.show-menu-preview > #printMenuContent { display: block !important; }
    @media print { .no-print { display: none !important; } }
  </style>

  <!-- Hidden Print Content (root-level) -->
  <div id="printContent" class="hidden">
    <div class="print-container">
      <h1 class="print-title">Reservation Details</h1>
      
      <div class="print-section">
        <h2>Customer Information</h2>
        <div class="print-grid">
          <div><strong>Invoice #:</strong> {{ $r->invoice_number ?? '—' }}</div>
          <div><strong>Customer name:</strong> {{ $r->customer_name ?? '—' }}</div>
          <div><strong>Phone:</strong> {{ $r->phone ?? '—' }}</div>
          <div><strong>Email:</strong> {{ $r->email ?? '—' }}</div>
          <div><strong>Company:</strong> {{ $r->company ?? '—' }}</div>
          <div><strong>Address:</strong> {{ $r->address ?? '—' }}</div>
          <div><strong>City:</strong> {{ $r->city ?? '—' }}</div>
          <div><strong>ZIP:</strong> {{ $r->zip_code ?? '—' }}</div>
        </div>
      </div>

      <div class="print-section">
        <h2>Event Details</h2>
        <div class="print-grid">
          <div><strong>Date:</strong> {{ $r->date?->format('m/d/Y') ?? '—' }}</div>
          <div><strong>Time:</strong> {{ substr((string)$r->time,0,5) ?? '—' }}</div>
          <div><strong>Guests:</strong> {{ $r->guests ?? '—' }}</div>
          <div><strong>Event type:</strong> {{ $r->event_type ?? '—' }}</div>
          <div><strong>Setup color:</strong> {{ $r->setup_color ?? '—' }}</div>
          <div><strong>Stairs:</strong> {{ $r->stairs ? 'Yes' : 'No' }}</div>
          <div><strong>How did you hear about us:</strong> {{ $r->heard_about ?? '—' }}</div>
          <div><strong>Notes:</strong> {{ $r->notes ?? '—' }}</div>
        </div>
      </div>

      @php $its = $r->items ?? collect(); @endphp
      @if($its && $its->count())
      <div class="print-section">
        <h2>Items</h2>
        <table class="print-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Description</th>
              <th>Qty</th>
              <th>Unit Price</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($its as $it)
            <tr>
              <td>{{ $it->name_snapshot ?? '—' }}</td>
              <td>{{ $it->description ?? '—' }}</td>
              <td>{{ $it->qty ?? 0 }}</td>
              <td>{{ $fmt($it->unit_price_snapshot ?? 0) }}</td>
              <td>{{ $fmt($it->line_total ?? 0) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  <!-- Hidden Print Menu-Only Content (root-level) -->
  <div id="printMenuContent" class="hidden">
    <div class="wrap">
      @php $backUrl = request('back') ?? url()->previous() ?? route('admin.reservations'); @endphp
      <div class="no-print" style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:8px">
        <a href="{{ $backUrl }}" class="btn" style="background:#4b5563">Back</a>
        <button class="btn" onclick="document.body.classList.remove('show-menu-preview'); document.body.classList.add('print-menu'); window.print(); setTimeout(()=>{document.body.classList.remove('print-menu'); document.body.classList.add('show-menu-preview');}, 400)">Print</button>
      </div>
      <div class="brand">
        <img src="/assets/brand/logo.png" alt="Hibachi Catering" onerror="this.style.display='none'">
        <div>
          <div style="font-weight:700">Hibachi Catering</div>
          <div class="muted">9022 Pulsar Ct, Corona, CA 92883</div>
          <div class="muted">
            Email: <a href="mailto:info@hibachicater.com" style="color:inherit">info@hibachicater.com</a>
            &nbsp;|&nbsp; Phone: <a href="tel:+19513269602" style="color:inherit">951-326-9602</a>
            &nbsp;|&nbsp; <a href="https://hibachicater.com" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline">hibachicater.com</a>
          </div>
        </div>
      </div>

      @php $dateFmt = $r->date?->format('m/d/Y'); @endphp
      <div class="head" style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;position:relative">
        <div></div>
        <div class="title-center" style="position:absolute;left:50%;top:0;transform:translateX(-50%);text-align:center;width:auto">
          <h2 style="margin:0;font-size:18px">Invoice #{{ $r->invoice_number ?? ($r->code ?? ('#'.$r->id)) }}
            @if($dateFmt)
              <span style="color:#b21e27"> · {{ $dateFmt }}</span>
            @endif
          </h2>
        </div>
      </div>

      <div class="two-col">
        <div class="box">
          <h3 style="margin:0 0 6px; font-size:14px">Customer</h3>
          <div class="kv">
            <div class="kv-row"><div class="kv-label">Name</div><div class="kv-val">{{ $r->customer_name ?? '—' }}</div></div>
            @if(!empty($r->company))
              <div class="kv-row"><div class="kv-label">Company</div><div class="kv-val">{{ $r->company }}</div></div>
            @endif
            <div class="kv-row"><div class="kv-label">Date</div><div class="kv-val">{{ $r->date?->format('m/d/Y') ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Address</div><div class="kv-val">{{ trim(($r->address ?? '')) ?: '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">City</div><div class="kv-val">{{ $r->city ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">ZIP</div><div class="kv-val">{{ $r->zip_code ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Email</div><div class="kv-val">{{ $r->email ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Phone</div><div class="kv-val">{{ $r->phone ?? '—' }}</div></div>
          </div>
        </div>

        <div class="box">
          <h3 style="margin:0 0 6px; font-size:14px">Event</h3>
          <div class="kv">
            <div class="kv-row"><div class="kv-label">Invoice #</div><div class="kv-val">{{ $r->invoice_number ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Guests</div><div class="kv-val">{{ $r->guests ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Time</div><div class="kv-val">{{ \Carbon\Carbon::parse($r->time)->format('g:i A') }}</div></div>
            <div class="kv-row"><div class="kv-label">Setup color</div><div class="kv-val">{{ $r->setup_color ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Event type</div><div class="kv-val">{{ $r->event_type ?? '—' }}</div></div>
            <div class="kv-row"><div class="kv-label">Stairs</div><div class="kv-val">{{ $r->stairs ? 'Yes' : 'No' }}</div></div>
            <div class="kv-row"><div class="kv-label">How did you hear</div><div class="kv-val">{{ $r->heard_about ?? '—' }}</div></div>
            @if($r->notes)
              <div class="kv-row"><div class="kv-label">Notes</div><div class="kv-val" style="max-width:60ch;text-align:left">{{ $r->notes }}</div></div>
            @endif
          </div>
        </div>
      </div>

      <div class="box">
        <h3 style="margin:0 0 6px; font-size:14px">Menu</h3>
        @php $items = $r->items ?? collect(); @endphp
        <table>
          <thead><tr><th>Item</th><th>Description</th><th class="right">Qty</th></tr></thead>
          <tbody>
            @forelse($items as $it)
              <tr>
                <td>
                  <div>{{ $it->name_snapshot }}</div>
                </td>
                <td style="color:#6b7280;font-size:12px">{{ $it->description }}</td>
                <td class="right">{{ $it->qty }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="muted">No items</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    function printReservation() {
      window.print();
    }
    // Preview menu print (no auto-print)
    (function(){
      try {
        var params = new URLSearchParams(window.location.search);
        if (params.get('print') === 'menu') {
          document.body.classList.add('show-menu-preview');
          var el = document.getElementById('printMenuContent');
          if (el) el.classList.remove('hidden');
        }
      } catch (e) {}
    })();
  </script>
  <script>
    function colorPicker(initial){
      return {
        open:false,
        color: initial || '#6b7280',
        colors: ['#ef4444','#f97316','#f59e0b','#22c55e','#10b981','#3b82f6','#6366f1','#a855f7','#0ea5e9','#6b7280','none'],
        toggle(){ this.open=!this.open; },
        async pick(c){
          this.color = (c && c!=='none') ? c : '#6b7280';
          this.open=false;
          try{
            const fd=new FormData();
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            if(c && c!=='none') fd.append('color', c); else fd.append('color','clear');
            const resp=await fetch('{{ route('admin.reservations.color',['id'=>$r->id]) }}',{method:'POST', body:fd});
            const js=await resp.json();
            if(!resp.ok||!js.ok){ alert(js.error||'Failed to save color'); return; }
            try { localStorage.setItem('resv_color_update', JSON.stringify({ id: {{ $r->id }}, color: js.color || null, ts: Date.now() })); } catch(e){}
          }catch(e){}
        }
      }
    }
  </script>
  <script>
    function colorPicker(initial){
      return {
        open:false,
        color: initial || '#6b7280',
        colors: ['#ef4444','#f97316','#f59e0b','#22c55e','#10b981','#3b82f6','#6366f1','#a855f7','#0ea5e9','#6b7280','none'],
        toggle(){ this.open=!this.open; },
        async pick(c){ this.color = (c && c!=='none') ? c : '#6b7280'; this.open=false; try{ const fd=new FormData(); fd.append('_token', document.querySelector('meta[name="csrf-token"]').content); if(c && c!=='none') fd.append('color', c); else fd.append('color','clear'); const resp=await fetch('{{ route('admin.reservations.color',['id'=>$r->id]) }}',{method:'POST', body:fd}); const js=await resp.json(); if(!resp.ok||!js.ok){ alert(js.error||'Failed to save color'); } }catch(e){} }
      }
    }
  </script>
  <script>
    function manualPayments(init){
      return {
        providers: ['Square','Zelle','Venmo','Paypal','Cashapp','Stripe','Cash','Check','Other'],
        statuses: ['Succeeded','Pending','Failed'],
        rows: (init.list||[]).slice(0,3).map(r=>({ id:r.id, date:r.date || new Date().toISOString().slice(0,16), provider:r.provider||'Square', ref:r.ref||'', status:r.status||'Succeeded', amount:Number(r.amount||0).toFixed(2), transaction_id:r.transaction_id||'', error:'' })),
        addRow(){ if (this.rows.length>=3) return; this.rows.unshift({id:'',date:new Date().toISOString().slice(0,16),provider:'Square',ref:'',status:'Succeeded',amount:'0.00',transaction_id:'',error:''}); },
        formatAmount(i){ const r=this.rows[i]; const n=parseFloat(r.amount||'0'); r.amount = isNaN(n)?'0.00':n.toFixed(2); },
        async save(i){ const r=this.rows[i]; r.error=''; const amt=parseFloat(r.amount||'0'); if(!(amt>0)){ r.error='Amount must be greater than 0'; return; }
          const fd = new FormData(); fd.append('_token', document.querySelector('meta[name="csrf-token"]').content); if(r.id) fd.append('id', r.id); fd.append('date', r.date); fd.append('provider', r.provider); fd.append('ref', r.ref); fd.append('status', r.status); fd.append('amount', parseFloat(r.amount||'0').toFixed(2)); if(r.transaction_id) fd.append('transaction', r.transaction_id);
          try { const resp = await fetch('{{ route('admin.reservations.payments.manual.save',['id'=>$r->id]) }}', { method:'POST', body: fd }); const json=await resp.json(); if(!resp.ok||!json.ok){ r.error=json.error||'Error saving payment'; return; }
            this.rows = (json.manual||[]).map(x=>({ id:x.id, date:x.date, provider:x.provider, ref:x.ref||'', status:x.status, amount:Number(x.amount||0).toFixed(2), transaction_id:x.transaction_id||'', error:'' }));
            if (window.adjustmentsSetPaidExtra) { window.adjustmentsSetPaidExtra(Number(json.manualPaid||0)); }
          } catch(e){ r.error='Network error'; }
        },
        async remove(i){ const r=this.rows[i]; r.error=''; if(!r.id){ this.rows.splice(i,1); return; }
          const fd = new FormData(); fd.append('_token', document.querySelector('meta[name="csrf-token"]').content); fd.append('id', r.id);
          try { const resp = await fetch('{{ route('admin.reservations.payments.manual.delete',['id'=>$r->id]) }}', { method:'POST', body: fd }); const json=await resp.json(); if(!resp.ok||!json.ok){ r.error=json.error||'Error deleting payment'; return; }
            this.rows = (json.manual||[]).map(x=>({ id:x.id, date:x.date, provider:x.provider, ref:x.ref||'', status:x.status, amount:Number(x.amount||0).toFixed(2), transaction_id:x.transaction_id||'', error:'' }));
            if (window.adjustmentsSetPaidExtra) { window.adjustmentsSetPaidExtra(Number(json.manualPaid||0)); }
          } catch(e){ r.error='Network error'; }
        }
      }
    }
    // Hook to update totals Paid in adjustments block by adding manual succeeded sum
    window.adjustmentsSetPaidExtra = function(extra){ try { document.querySelectorAll('[x-data^="adjustmentsManager"]').forEach(el=>{ const comp = Alpine.$data(el); comp.paid = Number({{ number_format($r->deposit_paid ?? 0,2,'.','') }}) + Number(extra||0); comp.recalc(); }); } catch(e){} }
  </script>
</body>
</html>
