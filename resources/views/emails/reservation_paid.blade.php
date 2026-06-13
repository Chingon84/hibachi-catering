<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Reservation Paid</title>
  </head>
  <body style="margin:0;padding:0;background:#f3f4f6;">
    @php
      $r = $reservation ?? null;
      $items = $r?->items ?? collect();
      $missing = '—';
      $font = 'Inter, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif';
      $money = fn($value) => '$' . number_format((float) ($value ?? 0), 2);
      $value = fn($value) => filled($value) ? $value : $missing;

      try {
          $date = $r?->date ? \Carbon\Carbon::parse($r->date)->format('M j, Y') : $missing;
      } catch (\Throwable $e) {
          $date = $r?->date ? (string) $r->date : $missing;
      }

      try {
          $time = $r?->time ? \Carbon\Carbon::parse($r->time)->format('g:i A') : $missing;
      } catch (\Throwable $e) {
          $time = $r?->time ? substr((string) $r->time, 0, 5) : $missing;
      }

      $totals = \App\Support\ReservationTotals::compute($r);
      $subtotal = $totals['subtotal'] ?? 0;
      $travel = $totals['travel'] ?? 0;
      $gratuity = $totals['gratuity'] ?? 0;
      $tax = $totals['tax'] ?? 0;
      $total = $totals['total'] ?? 0;
      $adjustments = $totals['adjustments'] ?? [];
      $paidDeposit = $totals['deposit_display'] ?? 0;
      $paid = round((float) ($totals['paid_total'] ?? 0), 2);
      $balance = $totals['balance'] ?? 0;
      $invoiceNo = $r?->invoice_number ?? ($r?->code ?? ($r?->id ? ('#' . $r->id) : $missing));
      $reservationCode = $r?->code ?? ($r?->id ? ('#' . $r->id) : $missing);
      $addressLine = trim(implode(' ', array_filter([
          trim((string) ($r?->address ?? '')),
          trim((string) ($r?->city ?? '')),
          trim((string) ($r?->zip_code ?? '')),
      ]))) ?: $missing;

      $logoUrl = asset('assets/brand/logo.png');

      $business = [];
      try {
          if (class_exists(\App\Models\AdminSetting::class)) {
              $business = \App\Models\AdminSetting::valuesForGroup('business_profile', []);
          }
      } catch (\Throwable $e) {
          $business = [];
      }
      $businessPhone = trim((string) ($business['business_phone'] ?? ''));
      $businessEmail = trim((string) ($business['business_email'] ?? ''));
      $businessWebsite = trim((string) ($business['website'] ?? ''));
    @endphp

    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
      New Reservation Paid for {{ $reservationCode }} · Invoice #{{ $invoiceNo }} · {{ $money($paid) }} paid.
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f3f4f6;margin:0;padding:0;">
      <tr>
        <td align="center" style="padding:28px 14px;">
          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="640" style="width:100%;max-width:640px;border-collapse:separate;border-spacing:0;">
            <tr>
              <td style="background:#101827;border-radius:18px 18px 0 0;padding:22px 24px 20px;font-family:{{ $font }};color:#ffffff;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                  <tr>
                    <td valign="middle" style="padding:0;">
                      <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                          <td valign="middle" style="padding:0 12px 0 0;">
                            <img src="{{ $logoUrl }}" width="48" height="48" alt="Hibachi Catering" style="display:block;width:48px;height:48px;border-radius:12px;background:#ffffff;border:1px solid rgba(255,255,255,.16);object-fit:contain;">
                          </td>
                          <td valign="middle" style="padding:0;">
                            <div style="font-size:17px;line-height:1.2;font-weight:800;letter-spacing:.02em;color:#ffffff;">Hibachi Catering</div>
                            <div style="font-size:12px;line-height:1.4;color:#cbd5e1;margin-top:2px;">Corona HQ Operations</div>
                          </td>
                        </tr>
                      </table>
                    </td>
                    <td align="right" valign="middle" style="padding:0;">
                      <span style="display:inline-block;border:1px solid rgba(255,255,255,.18);border-radius:999px;padding:7px 11px;color:#e5e7eb;font-size:11px;line-height:1;font-weight:800;text-transform:uppercase;letter-spacing:.08em;">Admin Notification</span>
                    </td>
                  </tr>
                </table>

                <div style="height:18px;line-height:18px;">&nbsp;</div>
                <div style="height:3px;width:56px;background:#b21e27;border-radius:99px;line-height:3px;">&nbsp;</div>
                <h1 style="margin:14px 0 7px;font-size:28px;line-height:1.15;font-weight:850;color:#ffffff;">New Reservation Paid</h1>
                <div style="font-size:14px;line-height:1.6;color:#d1d5db;">
                  Reservation Code: <strong style="color:#ffffff;">{{ $reservationCode }}</strong><br>
                  Invoice #<strong style="color:#ffffff;">{{ $invoiceNo }}</strong>
                </div>
              </td>
            </tr>

            <tr>
              <td style="background:#ffffff;border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb;padding:22px 24px 8px;font-family:{{ $font }};color:#111827;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 16px;">
                  <tr>
                    <td valign="middle" style="padding:0;">
                      <span style="display:inline-block;background:#dcfce7;border:1px solid #bbf7d0;color:#166534;border-radius:999px;padding:8px 13px;font-size:12px;font-weight:900;letter-spacing:.09em;text-transform:uppercase;">Paid</span>
                    </td>
                    <td align="right" valign="middle" style="padding:0;color:#64748b;font-size:13px;line-height:1.4;">
                      Payment received for admin review
                    </td>
                  </tr>
                </table>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#fffafa;border:1px solid #fee2e2;border-radius:14px;border-collapse:separate;border-spacing:0;overflow:hidden;margin:0 0 16px;">
                  <tr>
                    <td colspan="2" style="padding:14px 16px 4px;">
                      <div style="font-size:11px;line-height:1.2;font-weight:900;color:#b21e27;text-transform:uppercase;letter-spacing:.08em;">Event Summary</div>
                    </td>
                  </tr>
                  <tr>
                    <td width="50%" style="padding:11px 16px;border-top:1px solid #fee2e2;">
                      <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Event Date</div>
                      <div style="font-size:16px;line-height:1.45;font-weight:800;color:#111827;margin-top:3px;">{{ $date }}</div>
                    </td>
                    <td width="50%" style="padding:11px 16px;border-top:1px solid #fee2e2;">
                      <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Event Time</div>
                      <div style="font-size:16px;line-height:1.45;font-weight:800;color:#111827;margin-top:3px;">{{ $time }}</div>
                    </td>
                  </tr>
                  <tr>
                    <td width="50%" style="padding:11px 16px;border-top:1px solid #fee2e2;">
                      <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Guests</div>
                      <div style="font-size:16px;line-height:1.45;font-weight:800;color:#111827;margin-top:3px;">{{ $value($r?->guests) }}</div>
                    </td>
                    <td width="50%" style="padding:11px 16px;border-top:1px solid #fee2e2;">
                      <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Invoice</div>
                      <div style="font-size:16px;line-height:1.45;font-weight:800;color:#111827;margin-top:3px;">#{{ $invoiceNo }}</div>
                    </td>
                  </tr>
                </table>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e5e7eb;border-radius:14px;border-collapse:separate;border-spacing:0;overflow:hidden;margin:0 0 16px;">
                  <tr>
                    <td colspan="2" style="background:#f8fafc;padding:13px 16px;border-bottom:1px solid #e5e7eb;">
                      <div style="font-size:12px;font-weight:900;color:#111827;text-transform:uppercase;letter-spacing:.08em;">Client &amp; Event Details</div>
                    </td>
                  </tr>
                  @foreach([
                    'Client Name' => $value($r?->customer_name),
                    'Email' => $value($r?->email),
                    'Phone' => $value($r?->phone),
                    'Event Address' => $addressLine,
                    'Event Type' => $value($r?->event_type),
                    'Setup Color' => $value($r?->setup_color),
                    'Special Request / Notes' => $value($r?->notes),
                  ] as $label => $detail)
                    <tr>
                      <td width="35%" valign="top" style="padding:11px 16px;border-bottom:1px solid #f1f5f9;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;">{{ $label }}</td>
                      <td width="65%" valign="top" style="padding:11px 16px;border-bottom:1px solid #f1f5f9;color:#111827;font-size:14px;line-height:1.5;font-weight:600;">{{ $detail }}</td>
                    </tr>
                  @endforeach
                </table>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e5e7eb;border-radius:14px;border-collapse:separate;border-spacing:0;overflow:hidden;margin:0 0 16px;">
                  <tr>
                    <td colspan="2" style="background:#f8fafc;padding:13px 16px;border-bottom:1px solid #e5e7eb;">
                      <div style="font-size:12px;font-weight:900;color:#111827;text-transform:uppercase;letter-spacing:.08em;">Totals</div>
                    </td>
                  </tr>
                  @foreach([
                    'Subtotal' => $money($subtotal),
                    'Travel' => $money($travel),
                  ] as $label => $amount)
                    <tr>
                      <td style="padding:10px 16px;border-bottom:1px solid #f1f5f9;color:#475569;font-size:14px;">{{ $label }}</td>
                      <td align="right" style="padding:10px 16px;border-bottom:1px solid #f1f5f9;color:#111827;font-size:14px;font-weight:700;">{{ $amount }}</td>
                    </tr>
                  @endforeach
                  @if(!empty($adjustments))
                    @foreach($adjustments as $adj)
                      <tr>
                        <td style="padding:10px 16px;border-bottom:1px solid #f1f5f9;color:#475569;font-size:14px;">{{ $adj['label'] ?? 'Adjustment' }}</td>
                        <td align="right" style="padding:10px 16px;border-bottom:1px solid #f1f5f9;color:#111827;font-size:14px;font-weight:700;">{{ $money($adj['amount'] ?? 0) }}</td>
                      </tr>
                    @endforeach
                  @endif
                  @foreach([
                    'Gratuity' => $money($gratuity),
                    'Tax' => $money($tax),
                  ] as $label => $amount)
                    <tr>
                      <td style="padding:10px 16px;border-bottom:1px solid #f1f5f9;color:#475569;font-size:14px;">{{ $label }}</td>
                      <td align="right" style="padding:10px 16px;border-bottom:1px solid #f1f5f9;color:#111827;font-size:14px;font-weight:700;">{{ $amount }}</td>
                    </tr>
                  @endforeach
                  <tr>
                    <td style="padding:14px 16px;border-bottom:1px solid #e5e7eb;color:#111827;font-size:16px;font-weight:900;">Total</td>
                    <td align="right" style="padding:14px 16px;border-bottom:1px solid #e5e7eb;color:#111827;font-size:18px;font-weight:900;">{{ $money($total) }}</td>
                  </tr>
                  <tr>
                    <td style="padding:10px 16px;border-bottom:1px solid #f1f5f9;color:#475569;font-size:14px;">Deposit paid</td>
                    <td align="right" style="padding:10px 16px;border-bottom:1px solid #f1f5f9;color:#166534;font-size:14px;font-weight:800;">-{{ $money($paidDeposit) }}</td>
                  </tr>
                  <tr>
                    <td style="padding:10px 16px;border-bottom:1px solid #f1f5f9;color:#166534;font-size:14px;font-weight:800;">Paid amount</td>
                    <td align="right" style="padding:10px 16px;border-bottom:1px solid #f1f5f9;color:#166534;font-size:14px;font-weight:900;">-{{ $money($paid) }}</td>
                  </tr>
                  <tr>
                    <td style="padding:12px 16px;color:#111827;font-size:15px;font-weight:900;">Balance due</td>
                    <td align="right" style="padding:12px 16px;color:#111827;font-size:16px;font-weight:900;">{{ $money($balance) }}</td>
                  </tr>
                </table>

                @if($items && $items->count())
                  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e5e7eb;border-radius:14px;border-collapse:separate;border-spacing:0;overflow:hidden;margin:0 0 16px;">
                    <tr>
                      <td colspan="4" style="background:#f8fafc;padding:13px 16px;border-bottom:1px solid #e5e7eb;">
                        <div style="font-size:12px;font-weight:900;color:#111827;text-transform:uppercase;letter-spacing:.08em;">Menu Items</div>
                      </td>
                    </tr>
                    <tr>
                      <th align="left" style="padding:10px 12px;border-bottom:1px solid #e5e7eb;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.06em;">Item</th>
                      <th align="right" style="padding:10px 12px;border-bottom:1px solid #e5e7eb;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.06em;">Unit</th>
                      <th align="right" style="padding:10px 12px;border-bottom:1px solid #e5e7eb;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.06em;">Qty</th>
                      <th align="right" style="padding:10px 12px;border-bottom:1px solid #e5e7eb;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.06em;">Total</th>
                    </tr>
                    @foreach($items as $it)
                      <tr>
                        <td valign="top" style="padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#111827;font-size:13px;line-height:1.4;font-weight:700;">
                          {{ $it->name_snapshot ?? $missing }}
                          @if(!empty($it->description))
                            <div style="color:#64748b;font-size:12px;font-weight:500;margin-top:2px;">{{ $it->description }}</div>
                          @endif
                        </td>
                        <td align="right" valign="top" style="padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#111827;font-size:13px;white-space:nowrap;">{{ $money($it->unit_price_snapshot ?? 0) }}</td>
                        <td align="right" valign="top" style="padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#111827;font-size:13px;">{{ $it->qty ?? 0 }}</td>
                        <td align="right" valign="top" style="padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#111827;font-size:13px;font-weight:800;white-space:nowrap;">{{ $money($it->line_total ?? 0) }}</td>
                      </tr>
                    @endforeach
                  </table>
                @endif

              </td>
            </tr>

            <tr>
              <td style="background:#ffffff;border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;border-radius:0 0 18px 18px;padding:0 24px 24px;font-family:{{ $font }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#101827;border-radius:14px;">
                  <tr>
                    <td align="center" style="padding:17px 18px;color:#cbd5e1;font-size:12px;line-height:1.6;">
                      <strong style="display:block;color:#ffffff;font-size:13px;">Hibachi Catering Admin Notification</strong>
                      Please do not reply to this automated message.
                      @if($businessPhone || $businessEmail || $businessWebsite)
                        <br>
                        {{ implode(' · ', array_filter([$businessPhone, $businessEmail, $businessWebsite])) }}
                      @else
                        <br>
                        info@hibachicater.com · 951-326-9602
                      @endif
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
