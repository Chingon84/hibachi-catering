<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_city',
        'event_date',
        'event_time',
        'event_guests',
        'event_type',
        'setup_color',
        'reservation_id',
        'status',
        'issue_date',
        'due_date',
        'payment_collection',
        'subtotal',
        'travel_fee',
        'tax_enabled',
        'tax_rate',
        'tax',
        'gratuity_enabled',
        'gratuity',
        'deposit_enabled',
        'deposit_amount',
        'service_charge_enabled',
        'service_charge_rate',
        'service_charge',
        'discount_enabled',
        'discount_rate',
        'discount',
        'total',
        'amount_paid',
        'balance',
        'memo',
        'footer_note',
        'internal_note',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'event_date' => 'date',
        'event_guests' => 'integer',
        'subtotal' => 'decimal:2',
        'travel_fee' => 'decimal:2',
        'tax_enabled' => 'boolean',
        'tax_rate' => 'decimal:2',
        'tax' => 'decimal:2',
        'gratuity_enabled' => 'boolean',
        'gratuity' => 'decimal:2',
        'deposit_enabled' => 'boolean',
        'deposit_amount' => 'decimal:2',
        'service_charge_enabled' => 'boolean',
        'service_charge_rate' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount_enabled' => 'boolean',
        'discount_rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
