<!doctype html>
<html>
  <body style="margin:0;padding:0;background:#f7f7fb;">
    @php
      $r = $reservation ?? null;
      $items = $r?->items ?? collect();
      $date = $r?->date ? \Carbon\Carbon::parse($r->date)->format('m/d/Y') : '—';
      try { $time = $r?->time ? \Carbon\Carbon::parse($r->time)->format('g:i A') : '—'; } catch (\Throwable $e) { $time = $r?->time ? substr($r->time,0,5) : '—'; }
      $totals = \App\Support\ReservationTotals::compute($r);
      $subtotal = $totals['subtotal'];
      $travel   = $totals['travel'];
      $gratuity = $totals['gratuity'];
      $tax      = $totals['tax'];
      $total    = $totals['total'];
      $adjustments = $totals['adjustments'];
      $paidDeposit = $totals['deposit_display'];
      $paidOther   = $totals['additional_paid'];
      $paid        = round($totals['paid_total'], 2);
      $balance  = $totals['balance'];
      $invoiceNo = $r->invoice_number ?? ($r->code ?? ('#'.$r->id));
      $payUrl = $pay_url ?? '';
    @endphp

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f7f7fb">
      <tr>
        <td align="center" style="padding:24px 12px">
          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width:600px;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
            <tr>
              <td style="background:#111827;padding:18px 20px;color:#fff;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif">
                <div style="font-size:18px;font-weight:700">Hibachi Catering</div>
                <div style="font-size:12px;color:#d1d5db">New Reservation Paid · Invoice #{{ $invoiceNo }}</div>
              </td>
            </tr>
            <tr>
              <td style="padding:20px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#111;">
                <h1 style="margin:0 0 8px;font-size:20px;line-height:1.3">New Reservation Paid</h1>
                <p style="margin:0 0 12px;color:#374151;font-size:14px">Code: <strong>{{ $r->code ?? ('#'.$r->id) }}</strong></p>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:14px;margin:0 0 12px">
                  <tr>
                    <td style="padding:6px 0;color:#374151;width:40%">Invoice #</td>
                    <td style="padding:6px 0;font-weight:600;color:#111">{{ $invoiceNo }}</td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;color:#374151">Event</td>
                    <td style="padding:6px 0;font-weight:600;color:#111">{{ $date }} at {{ $time }} · Guests: {{ $r->guests }}</td>
                  </tr>
                </table>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:14px;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin:0 0 12px">
                  <tr>
                    <td colspan="2" style="background:#f9fafb;padding:10px 12px;font-weight:700;color:#111">Client & Event</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151;width:40%">Name</td>
                    <td style="padding:8px 12px;color:#111">{{ $r->customer_name ?? '—' }}</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Email</td>
                    <td style="padding:8px 12px;color:#111">{{ $r->email ?? '—' }}</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Phone</td>
                    <td style="padding:8px 12px;color:#111">{{ $r->phone ?? '—' }}</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Time</td>
                    <td style="padding:8px 12px;color:#111">{{ $time }}</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Address of the event</td>
                    <td style="padding:8px 12px;color:#111">{{ trim(($r->address ?? '') . ' ' . ($r->city ?? '') . ' ' . ($r->zip_code ?? '')) ?: '—' }}</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Color setup</td>
                    <td style="padding:8px 12px;color:#111">{{ $r->setup_color ?? '—' }}</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Special request / Notes</td>
                    <td style="padding:8px 12px;color:#111">{{ $r->notes ?? '—' }}</td>
                  </tr>
                </table>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:14px;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin:0 0 12px">
                  <tr>
                    <td colspan="2" style="background:#f9fafb;padding:10px 12px;font-weight:700;color:#111">Totals</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Subtotal</td>
                    <td align="right" style="padding:8px 12px;color:#111">${{ number_format($subtotal,2) }}</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Travel</td>
                    <td align="right" style="padding:8px 12px;color:#111">${{ number_format($travel,2) }}</td>
                  </tr>
                  @if(!empty($adjustments))
                    @foreach($adjustments as $adj)
                      <tr>
                        <td style="padding:8px 12px;color:#374151">{{ $adj['label'] }}</td>
                        <td align="right" style="padding:8px 12px;color:#111">${{ number_format((float)$adj['amount'],2) }}</td>
                      </tr>
                    @endforeach
                  @endif
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Gratuity</td>
                    <td align="right" style="padding:8px 12px;color:#111">${{ number_format($gratuity,2) }}</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Tax</td>
                    <td align="right" style="padding:8px 12px;color:#111">${{ number_format($tax,2) }}</td>
                  </tr>
                  <tr>
                    <td style="padding:10px 12px;border-top:1px solid #e5e7eb;font-weight:700;color:#111">Total</td>
                    <td align="right" style="padding:10px 12px;border-top:1px solid #e5e7eb;font-weight:700;color:#111">${{ number_format($total,2) }}</td>
                  </tr>
                  <tr>
                    <td style="padding:8px 12px;color:#374151">Deposit paid</td>
                    <td align="right" style="padding:8px 12px;color:#16a34a">-${{ number_format($paidDeposit,2) }}</td>
                  </tr>
                  @if ($paidOther > 0)
                    <tr>
                      <td style="padding:8px 12px;color:#374151">Additional paid</td>
                      <td align="right" style="padding:8px 12px;color:#16a34a">-${{ number_format($paidOther,2) }}</td>
                    </tr>
                  @endif
                  <tr>
                    <td style="padding:8px 12px;color:#16a34a">Total paid</td>
                    <td align="right" style="padding:8px 12px;color:#16a34a">-${{ number_format($paid,2) }}</td>
                  </tr>
                  <tr>
                    <td style="padding:10px 12px;border-top:1px solid #e5e7eb;font-weight:700;color:#111">Balance</td>
                    <td align="right" style="padding:10px 12px;border-top:1px solid #e5e7eb;font-weight:700;color:#111">${{ number_format($balance,2) }}</td>
                  </tr>
                </table>

                @if ($payUrl && $balance > 0)
                  <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="left" style="margin:8px 0 0">
                    <tr>
                      <td align="center" bgcolor="#b21e27" style="border-radius:10px">
                        <a href="{{ $payUrl }}" target="_blank" rel="noopener" style="display:inline-block;padding:10px 14px;color:#ffffff;text-decoration:none;font-weight:700;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif">Pay Balance</a>
                      </td>
                    </tr>
                  </table>
                @endif

                @if($items && $items->count())
                  <h3 style="margin:18px 0 6px;font-size:16px">Items</h3>
                  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;font-size:13px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
                    <tr style="background:#f9fafb">
                      <th align="left" style="padding:8px 10px">Item</th>
                      <th align="right" style="padding:8px 10px">Unit</th>
                      <th align="right" style="padding:8px 10px">Qty</th>
                      <th align="right" style="padding:8px 10px">Total</th>
                    </tr>
                    @foreach($items as $it)
                      <tr>
                        <td style="padding:8px 10px;border-top:1px solid #e5e7eb">
                          <div>{{ $it->name_snapshot }}</div>
                          @if(!empty($it->description))
                            <div style="color:#6b7280;font-size:12px">{{ $it->description }}</div>
                          @endif
                        </td>
                        <td align="right" style="padding:8px 10px;border-top:1px solid #e5e7eb">${{ number_format((float)$it->unit_price_snapshot,2) }}</td>
                        <td align="right" style="padding:8px 10px;border-top:1px solid #e5e7eb">{{ $it->qty }}</td>
                        <td align="right" style="padding:8px 10px;border-top:1px solid #e5e7eb">${{ number_format((float)$it->line_total,2) }}</td>
                      </tr>
                    @endforeach
                  </table>
                @endif
              </td>
            </tr>
            <tr>
              <td style="background:#f9fafb;padding:14px 20px;text-align:center;color:#6b7280;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;font-size:12px">
                Hibachi Catering · 9022 Pulsar Ct, Corona, CA 92883 · info@hibachicater.com · 951‑326‑9602
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
