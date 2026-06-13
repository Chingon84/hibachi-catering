<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $mode === 'edit' ? 'Edit invoice' : 'Create invoice' }}</title>
  <link rel="stylesheet" href="/assets/admin.css">
  <style>
    body{background:#fff;color:#172033}
    .create-shell{min-height:100vh;display:grid;grid-template-rows:auto 1fr}
    .topbar{height:68px;border-bottom:1px solid #e5eaf2;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:0 16px}
    .top-left,.top-actions{display:flex;align-items:center;gap:12px}
    .back-x{width:34px;height:34px;border:0;background:#fff;color:#1f2937;font-size:24px;line-height:1;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
    .top-title{font-size:14px;font-weight:650;color:#2f3340}
    .btn-purple{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:8px;background:#a78bfa;color:#fff;font-weight:850;padding:9px 13px;text-decoration:none;cursor:pointer}
    .btn-light{display:inline-flex;align-items:center;justify-content:center;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#334155;font-weight:750;padding:8px 12px;text-decoration:none;cursor:pointer}
    .workspace{display:grid;grid-template-columns:minmax(560px,1fr) minmax(480px,52vw);min-height:calc(100vh - 68px)}
    .form-pane{padding:48px 44px 64px;max-width:840px;margin:0 auto;width:100%}
    .preview-pane{background:#f3f6fa;border-left:1px solid #e5eaf2;padding:46px 40px;display:block}
    .workspace.preview-hidden{grid-template-columns:1fr}
    .workspace.preview-hidden .preview-pane{display:none}
    .section{margin-bottom:52px}
    .section h2{font-size:21px;margin:0 0 14px;color:#3b3f4c}
    .helper{font-size:14px;color:#23324d;margin:0 0 18px}
    .field{margin-bottom:12px}
    .label{display:block;font-size:12px;font-weight:800;color:#475569;margin-bottom:5px}
    .input,.select,textarea{border-color:#d6dee9;border-radius:7px}
    .customer-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .customer-grid .full{grid-column:1 / -1}
    .customer-picker{position:relative;z-index:20}
    .customer-dropdown{position:absolute;left:0;right:0;top:calc(100% + 6px);display:none;border:1px solid #d6dee9;border-radius:8px;background:#fff;padding:8px 0;max-height:286px;overflow:auto;box-shadow:0 18px 38px rgba(15,23,42,.14)}
    .customer-dropdown.open{display:block}
    .customer-title{font-size:11px;font-weight:850;color:#4b5563;margin:8px 14px 6px;text-transform:uppercase}
    .customer-option{display:flex;align-items:center;gap:8px;width:100%;border:0;background:#fff;text-align:left;padding:8px 14px;cursor:pointer;color:#1f2937}
    .customer-option:hover,.customer-option.is-active{background:#f8fafc}
    .customer-option span{color:#64748b}
    .customer-empty{display:none;padding:9px 14px;color:#64748b;font-size:13px}
    .item-table{width:100%;border-collapse:collapse;margin-top:12px}
    .item-table th,.item-table td{padding:6px 4px;text-align:left;font-size:12px;vertical-align:top}
    .item-table th{color:#64748b;font-weight:850}
    .item-table .input,.item-table .select{min-height:36px;padding:7px 9px}
    .item-table .qty{width:76px}
    .item-table .price{width:115px}
    .item-table .amount{width:104px;font-weight:850;text-align:right;padding-top:14px}
    .menu-static{width:74px;min-height:36px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #d6dee9;border-radius:7px;background:#fff;color:#1f2937;font-size:14px;box-shadow:0 1px 2px rgba(15,23,42,.04);user-select:none}
    .item-picker{position:relative;min-width:240px}
    .item-dropdown{position:absolute;left:0;right:0;top:calc(100% + 6px);display:none;z-index:35;border:1px solid #d6dee9;border-radius:8px;background:#fff;padding:6px 0;max-height:320px;overflow:auto;box-shadow:0 18px 38px rgba(15,23,42,.14)}
    .item-dropdown.open{display:block}
    .item-category{padding:8px 12px 5px;color:#64748b;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.05em;background:#fff}
    .item-option{display:flex;align-items:center;justify-content:space-between;gap:10px;width:100%;border:0;background:#fff;text-align:left;padding:8px 12px;cursor:pointer;color:#1f2937}
    .item-option:hover{background:#f8fafc}
    .item-option span:first-child{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .item-option span:last-child{color:#64748b;font-weight:800;white-space:nowrap}
    .item-empty{display:none;padding:9px 12px;color:#64748b;font-size:13px}
    .icon-btn-small{width:34px;height:34px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#334155;font-size:20px;cursor:pointer}
    .option-row{display:flex;gap:12px;align-items:flex-start;margin:12px 0;color:#7a879d}
    .option-row input{margin-top:4px}
    .due-row{display:flex;gap:10px;align-items:center;margin:10px 0 0 30px;max-width:430px}
    .adjustments{display:grid;gap:10px;max-width:560px}
    .adjustment-row{display:grid;grid-template-columns:minmax(160px,1fr) 150px;gap:12px;align-items:center;border:1px solid #e2e8f0;border-radius:9px;padding:10px 12px;background:#fff}
    .adjustment-toggle{display:flex;gap:10px;align-items:flex-start;color:#1f2937;font-weight:800}
    .adjustment-toggle input{margin-top:3px}
    .adjustment-toggle small{display:block;color:#64748b;font-weight:600;margin-top:2px}
    .input-wrap{position:relative}
    .input-wrap .input{padding-right:34px}
    .input-suffix{position:absolute;right:11px;top:50%;transform:translateY(-50%);color:#64748b;font-weight:850;font-size:13px}
    .adjustment-row.is-disabled .input{background:#f8fafc;color:#94a3b8}
    .memo-text{color:#b21e27;font-weight:850}
    textarea.memo-text{min-height:120px}
    .preview-title{display:flex;align-items:center;gap:7px;font-size:22px;font-weight:850;margin:0 0 18px;color:#3b3f4c}
    .preview-tabs{display:flex;gap:22px;border-bottom:1px solid #cbd5e1;margin-bottom:24px}
    .preview-tabs span{padding:0 0 12px;font-size:14px;font-weight:850;color:#1f2937}
    .preview-tabs .active{color:#5438ff;border-bottom:2px solid #5438ff}
    .paper{background:#fff;border-top:3px solid #45aa82;width:min(570px,100%);min-height:720px;margin:0 auto;box-shadow:0 18px 46px rgba(15,23,42,.16);padding:32px}
    .paper-head{display:flex;justify-content:space-between;gap:20px;align-items:flex-start}
    .paper h1{font-size:22px;margin:0 0 16px}
    .paper-logo{width:56px;height:56px;object-fit:contain}
    .paper-meta,.paper-small{font-size:11px;line-height:1.45;color:#111827}
    .paper-grid{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin:22px 0}
    .due-line{font-size:16px;font-weight:850;margin:20px 0}
    .preview-table{width:100%;border-collapse:collapse;font-size:11px}
    .preview-table th,.preview-table td{border-bottom:1px solid #d1d5db;padding:6px 4px;text-align:left}
    .preview-table th:last-child,.preview-table td:last-child{text-align:right}
    .paper-total{display:grid;grid-template-columns:1fr 150px;margin-left:auto;margin-top:8px;font-size:12px;max-width:280px}
    .paper-total div{padding:3px 0}
    .paper-total div:nth-child(2n){text-align:right}
    .paper-footer{border-top:1px solid #e5e7eb;margin-top:160px;padding-top:16px;font-size:10px;color:#475569}
    .error{border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:8px;padding:10px 12px;margin-bottom:18px;font-size:13px}
    .warning{border:1px solid #fde68a;background:#fffbeb;color:#854d0e;border-radius:8px;padding:10px 12px;margin-bottom:18px;font-size:13px}
    @media (max-width:1100px){.workspace{grid-template-columns:1fr}.preview-pane{border-left:0;border-top:1px solid #e5eaf2}.form-pane{max-width:none}}
    @media (max-width:680px){.form-pane,.preview-pane{padding:24px 16px}.customer-grid,.paper-grid,.adjustment-row{grid-template-columns:1fr}.topbar{height:auto;min-height:68px;align-items:flex-start;padding:12px;flex-direction:column}.workspace{min-height:auto}}
  </style>
  @php
    $dueDate = old('custom_due_date', $invoice?->due_date?->format('Y-m-d'));
    $itemRows = [];
    if (old('item_description')) {
      foreach ((array) old('item_description') as $i => $desc) {
        $itemRows[] = [
          'menu_item_id' => old("item_menu_id.$i"),
          'description' => $desc,
          'quantity' => old("item_qty.$i", 1),
          'unit_price' => old("item_unit_price.$i", 0),
        ];
      }
    } elseif($invoice) {
      foreach ($invoice->items as $item) {
        $itemRows[] = [
          'menu_item_id' => $item->menu_item_id,
          'description' => $item->description,
          'quantity' => $item->quantity,
          'unit_price' => $item->unit_price,
        ];
      }
    }
    if (empty($itemRows)) {
      $itemRows[] = ['menu_item_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => 0];
    }
    $menuCatalog = $menuItems->map(fn($menu) => [
      'id' => $menu->id,
      'name' => $menu->name,
      'price' => (float) $menu->price,
      'category' => trim((string) ($menu->category ?? '')) ?: 'Uncategorized',
    ])->values();
    $checked = fn($name, $default = false) => filter_var(old($name, $default), FILTER_VALIDATE_BOOLEAN);
    $adjustValue = fn($name, $default = 0) => old($name, $default);
  @endphp
</head>
<body>
  <div class="create-shell">
    <header class="topbar">
      <div class="top-left">
        <a class="back-x" href="{{ route('admin.invoices') }}" aria-label="Back">&times;</a>
        <div class="top-title">{{ $mode === 'edit' ? 'Edit invoice' : 'Create invoice' }}</div>
      </div>
      <div class="top-actions">
        <button type="button" class="btn-light" id="togglePreviewBtn">Hide preview</button>
        <button type="submit" class="btn-purple" form="invoiceForm">Review invoice</button>
      </div>
    </header>

    <div class="workspace" id="workspace">
      <main class="form-pane">
        @if(!$standaloneReady)
          <div class="warning">Run migrations before creating standalone invoices. Existing reservation invoices are not affected.</div>
        @endif
        @if($errors->any())
          <div class="error">{{ $errors->first() }}</div>
        @endif

        <form id="invoiceForm" method="post" action="{{ $mode === 'edit' && $invoice ? route('admin.invoices.update', ['invoice' => $invoice]) : route('admin.invoices.store') }}">
          @csrf
          <section class="section">
            <h2>Customer</h2>
            <div class="field">
              <label class="label" for="customerName">Name</label>
              <div class="customer-picker" id="customerPicker">
                <input id="customerName" class="input js-preview" name="customer_name" value="{{ old('customer_name', $invoice?->customer_name) }}" placeholder="Find or add a customer..." autocomplete="off" required>
                <div class="customer-dropdown" id="customerDropdown">
                  <button type="button" class="customer-option" data-name="" data-email="" data-phone="" data-address="" data-city="" data-event-date="" data-event-time="" data-event-guests="" data-event-type="" data-setup-color="">+ Add new customer</button>
                  <div class="customer-title">Clients</div>
                  @foreach($recentClients as $client)
                    @php $name = trim($client->full_name ?: ($client->company ?? '')); @endphp
                    <button
                      type="button"
                      class="customer-option"
                      data-name="{{ $name }}"
                      data-email="{{ $client->email_primary }}"
                      data-phone="{{ $client->phone_primary }}"
                      data-address="{{ $client->invoice_address }}"
                      data-city="{{ $client->invoice_city }}"
                      data-event-date="{{ $client->invoice_event_date }}"
                      data-event-time="{{ $client->invoice_event_time }}"
                      data-event-guests="{{ $client->invoice_event_guests }}"
                      data-event-type="{{ $client->invoice_event_type }}"
                      data-setup-color="{{ $client->invoice_setup_color }}"
                    >
                      {{ $name ?: 'Unnamed client' }} <span>{{ $client->email_primary }}</span>
                    </button>
                  @endforeach
                  <div class="customer-empty" id="customerEmpty">No matching clients.</div>
                </div>
              </div>
            </div>
            <div class="customer-grid">
              <div class="field">
                <label class="label" for="customerEmail">Email</label>
                <input id="customerEmail" class="input js-preview" type="email" name="customer_email" value="{{ old('customer_email', $invoice?->customer_email) }}" placeholder="customer@email.com" required>
              </div>
              <div class="field">
                <label class="label" for="customerPhone">Phone optional</label>
                <input id="customerPhone" class="input js-preview" name="customer_phone" value="{{ old('customer_phone', $invoice?->customer_phone) }}" placeholder="Phone">
              </div>
              <div class="field full">
                <label class="label" for="customerAddress">Address</label>
                <input id="customerAddress" class="input js-preview" name="customer_address" value="{{ old('customer_address', $invoice?->customer_address) }}" placeholder="Event address">
              </div>
              <div class="field">
                <label class="label" for="customerCity">City</label>
                <input id="customerCity" class="input js-preview" name="customer_city" value="{{ old('customer_city', $invoice?->customer_city) }}" placeholder="City for tax lookup">
              </div>
              <div class="field">
                <label class="label" for="eventDate">Event date</label>
                <input id="eventDate" class="input js-preview" type="date" name="event_date" value="{{ old('event_date', $invoice?->event_date?->format('Y-m-d')) }}">
              </div>
              <div class="field">
                <label class="label" for="eventTime">Time</label>
                <input id="eventTime" class="input js-preview" type="time" name="event_time" value="{{ old('event_time', $invoice?->event_time) }}">
              </div>
              <div class="field">
                <label class="label" for="eventGuests">Guests</label>
                <input id="eventGuests" class="input js-preview" type="number" min="0" step="1" name="event_guests" value="{{ old('event_guests', $invoice?->event_guests) }}" placeholder="Guests">
              </div>
              <div class="field">
                <label class="label" for="eventType">Event type</label>
                <input id="eventType" class="input js-preview" name="event_type" value="{{ old('event_type', $invoice?->event_type) }}" placeholder="Event type">
              </div>
              <div class="field full">
                <label class="label" for="setupColor">Setup color</label>
                <input id="setupColor" class="input js-preview" name="setup_color" value="{{ old('setup_color', $invoice?->setup_color) }}" placeholder="Setup color">
              </div>
            </div>
          </section>

          <section class="section">
            <h2>Items</h2>
            <p class="helper">Add single, one-time items or products from your menu to this invoice.</p>
            <table class="item-table">
              <thead><tr><th>Menu</th><th>Description</th><th>Qty</th><th>Unit price</th><th>Amount</th><th></th></tr></thead>
              <tbody id="itemsBody">
                @foreach($itemRows as $row)
                  <tr class="item-row">
                    <td>
                      <input type="hidden" class="menu-item-id" name="item_menu_id[]" value="{{ $row['menu_item_id'] ?? '' }}">
                      <span class="menu-static">Custom</span>
                    </td>
                    <td>
                      <div class="item-picker">
                        <input class="input item-desc js-preview" name="item_description[]" value="{{ $row['description'] ?? '' }}" placeholder="Find or add an item" autocomplete="off">
                        <div class="item-dropdown">
                          <div class="item-empty">No matching menu items.</div>
                        </div>
                      </div>
                    </td>
                    <td><input class="input qty js-preview" name="item_qty[]" type="number" min="0" step="1" value="{{ $row['quantity'] ?? 1 }}"></td>
                    <td><input class="input price js-preview" name="item_unit_price[]" type="number" min="0" step="0.01" value="{{ $row['unit_price'] ?? 0 }}"></td>
                    <td class="amount">$0.00</td>
                    <td><button type="button" class="icon-btn-small remove-row">&times;</button></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <button type="button" class="btn-light" id="addItemBtn" style="margin-top:10px">+ Add item</button>
          </section>

          <section class="section">
            <h2>Payment collection</h2>
            <input type="hidden" name="payment_collection" value="request_payment">
            <label class="option-row"><input type="radio" value="request_payment" checked disabled> <span><strong>Request payment</strong><br><small>Create an invoice requesting payment on a specific date</small></span></label>
            <div class="due-row">
              <select class="select js-preview" id="dueOption" name="due_option">
                @foreach(['today'=>'Today','tomorrow'=>'Tomorrow','7'=>'7 days','14'=>'14 days','30'=>'30 days','45'=>'45 days','60'=>'60 days','90'=>'90 days','custom'=>'Custom'] as $key => $label)
                  <option value="{{ $key }}" {{ old('due_option', $invoice ? 'custom' : '30') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              <input class="input js-preview" id="customDueDate" type="date" name="custom_due_date" value="{{ $dueDate }}" style="display:none">
            </div>
          </section>

          <section class="section">
            <h2>Invoice adjustments</h2>
            <p class="helper">Turn on only the adjustments that should appear on this invoice.</p>
            <div class="adjustments">
              <div class="adjustment-row" data-adjustment-row>
                <label class="adjustment-toggle">
                  <input type="hidden" name="tax_enabled" value="0">
                  <input class="js-preview adjustment-check" type="checkbox" name="tax_enabled" value="1" data-adjustment-check {{ $checked('tax_enabled', $invoice?->tax_enabled ?? true) ? 'checked' : '' }}>
                  <span>Tax <small>Auto-applies Custom Tax by city, or Default Tax Rate when no city match exists.</small></span>
                </label>
                <div class="input-wrap">
                  <input class="input js-preview" name="tax_rate" type="number" min="0" max="100" step="0.01" value="{{ $adjustValue('tax_rate', $invoice?->tax_rate ?? ($defaultTaxRate ?? 10.25)) }}" data-adjustment-input data-adjustment="tax" data-auto-tax-rate readonly>
                  <span class="input-suffix">%</span>
                </div>
              </div>

              <div class="adjustment-row">
                <label class="adjustment-toggle">
                  <span>Travel fee <small>Taxable catering/event service charge</small></span>
                </label>
                <div class="input-wrap">
                  <input class="input js-preview" name="travel_fee" type="number" min="0" max="10000" step="0.01" value="{{ $adjustValue('travel_fee', $invoice?->travel_fee ?? 0) }}">
                  <span class="input-suffix">$</span>
                </div>
              </div>

              <div class="adjustment-row" data-adjustment-row>
                <label class="adjustment-toggle">
                  <input type="hidden" name="gratuity_enabled" value="0">
                  <input class="js-preview adjustment-check" type="checkbox" name="gratuity_enabled" value="1" data-adjustment-check {{ $checked('gratuity_enabled', $invoice?->gratuity_enabled ?? false) ? 'checked' : '' }}>
                  <span>Gratuity <small>Fixed amount added to the invoice</small></span>
                </label>
                <div class="input-wrap">
                  <input class="input js-preview" name="gratuity_amount" type="number" min="0" max="10000" step="0.01" value="{{ $adjustValue('gratuity_amount', $invoice?->gratuity ?? 0) }}" data-adjustment-input data-adjustment="gratuity">
                  <span class="input-suffix">$</span>
                </div>
              </div>

              <div class="adjustment-row" data-adjustment-row>
                <label class="adjustment-toggle">
                  <input type="hidden" name="deposit_enabled" value="0">
                  <input class="js-preview adjustment-check" type="checkbox" name="deposit_enabled" value="1" data-adjustment-check {{ $checked('deposit_enabled', $invoice?->deposit_enabled ?? false) ? 'checked' : '' }}>
                  <span>Deposit <small>Reduces the invoice amount due</small></span>
                </label>
                <div class="input-wrap">
                  <input class="input js-preview" name="deposit_amount" type="number" min="0" max="10000" step="0.01" value="{{ $adjustValue('deposit_amount', $invoice?->deposit_amount ?? $invoice?->amount_paid ?? 0) }}" data-adjustment-input data-adjustment="deposit">
                  <span class="input-suffix">$</span>
                </div>
              </div>

              <div class="adjustment-row" data-adjustment-row>
                <label class="adjustment-toggle">
                  <input type="hidden" name="service_charge_enabled" value="0">
                  <input class="js-preview adjustment-check" type="checkbox" name="service_charge_enabled" value="1" data-adjustment-check {{ $checked('service_charge_enabled', $invoice?->service_charge_enabled ?? false) ? 'checked' : '' }}>
                  <span>Service charge <small>Percentage added to the invoice</small></span>
                </label>
                <div class="input-wrap">
                  <input class="input js-preview" name="service_charge_rate" type="number" min="0" max="100" step="0.01" value="{{ $adjustValue('service_charge_rate', $invoice?->service_charge_rate ?? 0) }}" data-adjustment-input data-adjustment="service">
                  <span class="input-suffix">%</span>
                </div>
              </div>

              <div class="adjustment-row" data-adjustment-row>
                <label class="adjustment-toggle">
                  <input type="hidden" name="discount_enabled" value="0">
                  <input class="js-preview adjustment-check" type="checkbox" name="discount_enabled" value="1" data-adjustment-check {{ $checked('discount_enabled', $invoice?->discount_enabled ?? false) ? 'checked' : '' }}>
                  <span>Discount <small>Percentage reduced from the invoice</small></span>
                </label>
                <div class="input-wrap">
                  <input class="input js-preview" name="discount_rate" type="number" min="0" max="100" step="0.01" value="{{ $adjustValue('discount_rate', $invoice?->discount_rate ?? 0) }}" data-adjustment-input data-adjustment="discount">
                  <span class="input-suffix">%</span>
                </div>
              </div>
            </div>
          </section>

          <section class="section">
            <h2>Additional options</h2>
            <p class="helper">Customize your invoice with additional fields.</p>
            <label class="label" for="memoInput">Memo</label>
            <textarea id="memoInput" class="js-preview memo-text" name="memo" placeholder="Thanks for your business!">{{ old('memo', $invoice?->memo) }}</textarea>
            <label class="label" for="footerNote">Footer note</label>
            <textarea id="footerNote" class="js-preview" name="footer_note" placeholder="Footer note">{{ old('footer_note', $invoice?->footer_note) }}</textarea>
          </section>
        </form>
      </main>

      <aside class="preview-pane">
        <h2 class="preview-title">Preview <span style="font-size:13px;color:#64748b">⚙</span></h2>
        <div class="preview-tabs"><span class="active">Invoice PDF</span><span>Email</span><span>Payment page</span></div>
        <div class="paper">
          <div class="paper-head">
            <div>
              <h1>Invoice</h1>
              <div class="paper-meta">
                <strong>Invoice number</strong> {{ $invoice?->invoice_number ?? 'DRAFT' }}<br>
                <strong>Date of issue</strong> <span id="previewIssue">{{ now()->format('M j, Y') }}</span><br>
                <strong>Date due</strong> <span id="previewDue">-</span>
              </div>
            </div>
            <img class="paper-logo" src="/assets/brand/logo.png" alt="Hibachi Catering" onerror="this.style.display='none'">
          </div>
          <div class="paper-grid paper-small">
            <div><strong>Hibachi Catering</strong><br>9022 Pulsar Ct<br>Corona, California 92883<br>United States<br>+1 951-326-9602</div>
            <div>
              <strong>Bill to</strong><br>
              <span id="previewCustomer">Example Customer</span><br>
              <span id="previewPhone"></span><br>
              <span id="previewEmail"></span><br>
              <span id="previewAddress"></span><br>
              <span id="previewEvent"></span><br>
              <span id="previewGuests"></span><br>
              <span id="previewEventType"></span><br>
              <span id="previewSetupColor"></span>
            </div>
          </div>
          <div class="due-line"><span id="previewDueAmount">$0.00</span> due <span id="previewDueInline">-</span></div>
          <table class="preview-table">
            <thead><tr><th>Description</th><th>Qty</th><th>Unit price</th><th>Amount</th></tr></thead>
            <tbody id="previewItems"></tbody>
          </table>
          <div class="paper-total">
            <div>Subtotal</div><div id="previewSubtotal">$0.00</div>
            <div id="previewDiscountLabel" style="display:none">Discount</div><div id="previewDiscount" style="display:none">-$0.00</div>
            <div id="previewTravelLabel" style="display:none">Travel fee</div><div id="previewTravel" style="display:none">$0.00</div>
            <div id="previewServiceLabel" style="display:none">Service charge</div><div id="previewService" style="display:none">$0.00</div>
            <div id="previewGratuityLabel" style="display:none">Gratuity</div><div id="previewGratuity" style="display:none">$0.00</div>
            <div id="previewTaxLabel" style="display:none">Tax</div><div id="previewTax" style="display:none">$0.00</div>
            <div>Total</div><div id="previewTotal">$0.00</div>
            <div id="previewDepositLabel" style="display:none">Deposit</div><div id="previewDeposit" style="display:none">-$0.00</div>
            <div><strong>Amount due</strong></div><div><strong id="previewBalance">$0.00 USD</strong></div>
          </div>
          <div id="previewMemo" class="paper-small memo-text" style="margin-top:22px"></div>
          <div class="paper-footer" id="previewFooter">{{ old('footer_note', $invoice?->footer_note) }}</div>
        </div>
      </aside>
    </div>
  </div>

  <template id="itemRowTemplate">
    <tr class="item-row">
      <td><input type="hidden" class="menu-item-id" name="item_menu_id[]" value=""><span class="menu-static">Custom</span></td>
      <td>
        <div class="item-picker">
          <input class="input item-desc js-preview" name="item_description[]" placeholder="Find or add an item" autocomplete="off">
          <div class="item-dropdown">
            <div class="item-empty">No matching menu items.</div>
          </div>
        </div>
      </td>
      <td><input class="input qty js-preview" name="item_qty[]" type="number" min="0" step="1" value="1"></td>
      <td><input class="input price js-preview" name="item_unit_price[]" type="number" min="0" step="0.01" value="0"></td>
      <td class="amount">$0.00</td>
      <td><button type="button" class="icon-btn-small remove-row">&times;</button></td>
    </tr>
  </template>

  <script>
    const fmt = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
    const menuCatalog = @json($menuCatalog);
    const customTaxRates = @json($customTaxRates ?? []);
    const defaultTaxRate = Number(@json($defaultTaxRate ?? 10.25));
    const workspace = document.getElementById('workspace');
    const itemsBody = document.getElementById('itemsBody');
    const customDueDate = document.getElementById('customDueDate');
    const dueOption = document.getElementById('dueOption');
    const memoInput = document.getElementById('memoInput');
    const customerPicker = document.getElementById('customerPicker');
    const customerName = document.getElementById('customerName');
    const customerDropdown = document.getElementById('customerDropdown');
    const customerEmpty = document.getElementById('customerEmpty');
    const customerCity = document.getElementById('customerCity');

    function openCustomerDropdown(){
      customerDropdown.classList.add('open');
      filterCustomers();
    }

    function closeCustomerDropdown(){
      customerDropdown.classList.remove('open');
    }

    function filterCustomers(){
      const term = customerName.value.trim().toLowerCase();
      let visible = 0;
      document.querySelectorAll('.customer-option').forEach((option, index) => {
        if (index === 0) {
          option.style.display = 'flex';
          return;
        }
        const haystack = `${option.dataset.name || ''} ${option.dataset.email || ''} ${option.dataset.phone || ''}`.toLowerCase();
        const show = term === '' || haystack.includes(term);
        option.style.display = show ? 'flex' : 'none';
        if (show) visible++;
      });
      customerEmpty.style.display = visible === 0 ? 'block' : 'none';
    }

    function escapeHtml(value){
      return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
      }[char]));
    }

    function cityKey(value){
      return String(value || '').trim().replace(/\s+/g, ' ').toLowerCase();
    }

    function cityFromAddress(address){
      const parts = String(address || '').split(',').map(part => part.trim()).filter(Boolean);
      if (parts.length < 2) return '';
      const last = parts[parts.length - 1] || '';
      if (/^\d{5}(-\d{4})?$/.test(last)) {
        return parts.length >= 3 ? parts[parts.length - 2] : '';
      }
      if (/^[A-Z]{2}\s+\d{5}(-\d{4})?$/i.test(last)) {
        return parts.length >= 2 ? parts[parts.length - 2] : '';
      }
      return parts[1] || '';
    }

    function invoiceCity(){
      return customerCity?.value?.trim() || cityFromAddress(document.getElementById('customerAddress')?.value || '');
    }

    function resolvedTaxRate(){
      const key = cityKey(invoiceCity());
      const match = customTaxRates.find(rate => cityKey(rate.city_key || rate.city_name) === key);
      return Number(match?.tax_rate ?? defaultTaxRate);
    }

    function groupedMenuItems(term = ''){
      const normalized = term.trim().toLowerCase();
      const groups = new Map();
      menuCatalog.forEach(item => {
        const haystack = `${item.name || ''} ${item.category || ''}`.toLowerCase();
        if (normalized !== '' && !haystack.includes(normalized)) return;
        const category = item.category || 'Uncategorized';
        if (!groups.has(category)) groups.set(category, []);
        groups.get(category).push(item);
      });
      return groups;
    }

    function renderItemDropdown(row){
      const dropdown = row.querySelector('.item-dropdown');
      const input = row.querySelector('.item-desc');
      const term = input.value || '';
      const groups = groupedMenuItems(term);
      let html = '';

      groups.forEach((items, category) => {
        html += `<div class="item-category">${escapeHtml(category)}</div>`;
        items.forEach(item => {
          const itemId = item.id !== null && item.id !== '' && Number.isInteger(Number(item.id)) ? item.id : '';
          html += `<button type="button" class="item-option" data-id="${itemId}" data-name="${escapeHtml(item.name)}" data-price="${item.price}"><span>${escapeHtml(item.name)}</span><span>${fmt.format(Number(item.price || 0))}</span></button>`;
        });
      });

      const empty = '<div class="item-empty" style="display:block">No matching menu items.</div>';
      dropdown.innerHTML = html || empty;
    }

    function openItemDropdown(row){
      document.querySelectorAll('.item-dropdown.open').forEach(dropdown => {
        if (dropdown !== row.querySelector('.item-dropdown')) dropdown.classList.remove('open');
      });
      renderItemDropdown(row);
      row.querySelector('.item-dropdown')?.classList.add('open');
    }

    function closeItemDropdowns(){
      document.querySelectorAll('.item-dropdown.open').forEach(dropdown => dropdown.classList.remove('open'));
    }

    function selectMenuItem(row, option){
      const id = option.dataset.id || '';
      const name = option.dataset.name || '';
      const price = option.dataset.price || '0';
      row.querySelector('.item-desc').value = name;
      row.querySelector('.price').value = price;
      const menuItemId = row.querySelector('.menu-item-id');
      if (menuItemId) menuItemId.value = id;
      row.querySelector('.item-dropdown')?.classList.remove('open');
      recalc();
    }

    function dueLabel(){
      if (dueOption.value === 'custom') {
        return customDueDate.value ? new Date(customDueDate.value + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';
      }
      const days = { today: 0, tomorrow: 1, '7': 7, '14': 14, '30': 30, '45': 45, '60': 60, '90': 90 }[dueOption.value] ?? 30;
      const date = new Date();
      date.setDate(date.getDate() + days);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function updateDuePicker(){
      customDueDate.style.display = dueOption.value === 'custom' ? 'block' : 'none';
    }

    function displayDate(value){
      return value ? new Date(value + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
    }

    function displayTime(value){
      if (!value) return '';
      const [hour, minute] = value.split(':').map(part => Number(part));
      if (Number.isNaN(hour) || Number.isNaN(minute)) return value;
      const date = new Date();
      date.setHours(hour, minute, 0, 0);
      return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }

    function numField(name){
      const field = document.querySelector(`[name="${name}"]`);
      return Number(field?.value || 0);
    }

    function checkedField(name){
      const field = document.querySelector(`input[name="${name}"][type="checkbox"]`);
      return Boolean(field?.checked);
    }

    function togglePreviewValue(labelId, valueId, show, value){
      const label = document.getElementById(labelId);
      const amount = document.getElementById(valueId);
      label.style.display = show ? '' : 'none';
      amount.style.display = show ? '' : 'none';
      amount.textContent = value;
    }

    function updateAdjustmentInputs(){
      document.querySelectorAll('[data-adjustment-row]').forEach(row => {
        const enabled = row.querySelector('[data-adjustment-check]')?.checked;
        const input = row.querySelector('[data-adjustment-input]');
        row.classList.toggle('is-disabled', !enabled);
        if (input) input.disabled = !enabled;
      });
    }

    function recalc(){
      updateDuePicker();
      updateAdjustmentInputs();
      let subtotal = 0;
      const previewItems = document.getElementById('previewItems');
      previewItems.innerHTML = '';
      document.querySelectorAll('.item-row').forEach(row => {
        const desc = row.querySelector('.item-desc')?.value || '';
        const qty = Number(row.querySelector('.qty')?.value || 0);
        const price = Number(row.querySelector('.price')?.value || 0);
        const amount = Math.max(0, qty * price);
        subtotal += amount;
        row.querySelector('.amount').textContent = fmt.format(amount);
        if (desc || amount > 0) {
          const tr = document.createElement('tr');
          tr.innerHTML = `<td>${desc || 'Item'}</td><td>${qty}</td><td>${fmt.format(price)}</td><td>${fmt.format(amount)}</td>`;
          previewItems.appendChild(tr);
        }
      });
      if (!previewItems.children.length) {
        previewItems.innerHTML = '<tr><td colspan="4">No items</td></tr>';
      }
      const customer = document.getElementById('customerName').value || 'Example Customer';
      const email = document.getElementById('customerEmail').value || '';
      const phone = document.getElementById('customerPhone').value || '';
      const address = document.getElementById('customerAddress').value || '';
      const eventDate = displayDate(document.getElementById('eventDate').value || '');
      const eventTime = displayTime(document.getElementById('eventTime').value || '');
      const eventGuests = document.getElementById('eventGuests').value || '';
      const eventType = document.getElementById('eventType').value || '';
      const setupColor = document.getElementById('setupColor').value || '';
      const due = dueLabel();
      document.getElementById('previewCustomer').textContent = customer;
      document.getElementById('previewPhone').textContent = phone;
      document.getElementById('previewEmail').textContent = email;
      document.getElementById('previewAddress').textContent = address;
      document.getElementById('previewEvent').textContent = [eventDate, eventTime].filter(Boolean).join(' at ');
      document.getElementById('previewGuests').textContent = eventGuests ? `${eventGuests} guests` : '';
      document.getElementById('previewEventType').textContent = eventType;
      document.getElementById('previewSetupColor').textContent = setupColor;
      document.getElementById('previewDue').textContent = due;
      document.getElementById('previewDueInline').textContent = due;

      const discountEnabled = checkedField('discount_enabled');
      const discountRate = Math.min(100, Math.max(0, numField('discount_rate')));
      const discount = discountEnabled ? subtotal * (discountRate / 100) : 0;
      const travel = Math.min(10000, Math.max(0, numField('travel_fee')));
      const serviceEnabled = checkedField('service_charge_enabled');
      const serviceRate = Math.min(100, Math.max(0, numField('service_charge_rate')));
      const service = serviceEnabled ? subtotal * (serviceRate / 100) : 0;
      const gratuityEnabled = checkedField('gratuity_enabled');
      const gratuity = gratuityEnabled ? Math.min(10000, Math.max(0, numField('gratuity_amount'))) : 0;
      const taxEnabled = checkedField('tax_enabled');
      const taxRateField = document.querySelector('[name="tax_rate"]');
      const taxRate = Math.min(100, Math.max(0, resolvedTaxRate()));
      if (taxRateField) taxRateField.value = taxRate.toFixed(2);
      // California catering tax: taxable base includes food/items subtotal, travel fee,
      // and mandatory gratuity/service charge. Voluntary tips are excluded.
      const taxableBase = Math.max(0, subtotal - discount + travel + service + gratuity);
      const tax = taxEnabled ? Math.round(Math.round(taxableBase * 100) * (taxRate / 100)) / 100 : 0;
      const total = Math.max(0, taxableBase + tax);
      const depositEnabled = checkedField('deposit_enabled');
      const deposit = depositEnabled ? Math.min(Math.max(0, numField('deposit_amount')), total) : 0;
      const balance = Math.max(0, total - deposit);

      document.getElementById('previewDueAmount').textContent = fmt.format(balance);
      document.getElementById('previewSubtotal').textContent = fmt.format(subtotal);
      togglePreviewValue('previewDiscountLabel', 'previewDiscount', discountEnabled, `- ${fmt.format(discount)}`);
      togglePreviewValue('previewTravelLabel', 'previewTravel', travel > 0, fmt.format(travel));
      togglePreviewValue('previewServiceLabel', 'previewService', serviceEnabled, fmt.format(service));
      togglePreviewValue('previewGratuityLabel', 'previewGratuity', gratuityEnabled, fmt.format(gratuity));
      togglePreviewValue('previewTaxLabel', 'previewTax', taxEnabled, fmt.format(tax));
      document.getElementById('previewTotal').textContent = fmt.format(total);
      togglePreviewValue('previewDepositLabel', 'previewDeposit', depositEnabled, `- ${fmt.format(deposit)}`);
      document.getElementById('previewBalance').textContent = `${fmt.format(balance)} USD`;
      const memo = memoInput.value || '';
      document.getElementById('previewMemo').textContent = memo;
      document.getElementById('previewFooter').textContent = document.getElementById('footerNote').value || '';
    }

    document.addEventListener('input', e => {
      if (e.target === customerName) {
        openCustomerDropdown();
      }
      if (e.target.classList.contains('item-desc')) {
        openItemDropdown(e.target.closest('.item-row'));
      }
      if (e.target.closest('#invoiceForm')) recalc();
    });
    document.addEventListener('change', e => {
      recalc();
    });
    document.getElementById('addItemBtn').addEventListener('click', () => {
      itemsBody.appendChild(document.getElementById('itemRowTemplate').content.cloneNode(true));
      recalc();
    });
    document.addEventListener('click', e => {
      if (e.target.classList.contains('remove-row')) {
        if (document.querySelectorAll('.item-row').length > 1) e.target.closest('.item-row').remove();
        recalc();
      }
      if (e.target.classList.contains('item-desc')) {
        openItemDropdown(e.target.closest('.item-row'));
      } else if (!e.target.closest('.item-picker')) {
        closeItemDropdowns();
      }
      const itemOption = e.target.closest('.item-option');
      if (itemOption) {
        selectMenuItem(itemOption.closest('.item-row'), itemOption);
      }
      if (e.target === customerName) {
        openCustomerDropdown();
      } else if (!e.target.closest('#customerPicker')) {
        closeCustomerDropdown();
      }
      const customerOption = e.target.closest('.customer-option');
      if (customerOption) {
        customerName.value = customerOption.dataset.name || '';
        document.getElementById('customerEmail').value = customerOption.dataset.email || '';
        document.getElementById('customerPhone').value = customerOption.dataset.phone || '';
        document.getElementById('customerAddress').value = customerOption.dataset.address || '';
        document.getElementById('customerCity').value = customerOption.dataset.city || '';
        document.getElementById('eventDate').value = customerOption.dataset.eventDate || '';
        document.getElementById('eventTime').value = customerOption.dataset.eventTime || '';
        document.getElementById('eventGuests').value = customerOption.dataset.eventGuests || '';
        document.getElementById('eventType').value = customerOption.dataset.eventType || '';
        document.getElementById('setupColor').value = customerOption.dataset.setupColor || '';
        closeCustomerDropdown();
        recalc();
      }
    });
    document.getElementById('togglePreviewBtn').addEventListener('click', e => {
      workspace.classList.toggle('preview-hidden');
      e.target.textContent = workspace.classList.contains('preview-hidden') ? 'Show preview' : 'Hide preview';
    });
    customerName.addEventListener('focus', openCustomerDropdown);
    document.addEventListener('focusin', e => {
      if (e.target.classList.contains('item-desc')) {
        openItemDropdown(e.target.closest('.item-row'));
      }
    });
    recalc();
  </script>
</body>
</html>
