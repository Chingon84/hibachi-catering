<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

   protected $fillable = [
  'code','invoice_number','invoice_status','status','guests','date','time',
  'customer_name','phone','email','address','company','city','zip_code',
  'event_type','setup_color','stairs','heard_about',
  'notes','distance_miles','subtotal','tax','gratuity','travel_fee','discount','total',
  'deposit_due','deposit_paid','balance',
  'booked_by',
  'invoice_adjustments',
  'color',
  'manual_payments',
];

protected $casts = [
  'date' => 'date',
  'stairs' => 'boolean',
  'distance_miles' => 'decimal:2',
  'subtotal' => 'decimal:2',
  'tax' => 'decimal:2',
  'gratuity' => 'decimal:2',
  'travel_fee' => 'decimal:2',
  'discount' => 'decimal:2',
  'total' => 'decimal:2',
  'deposit_due' => 'decimal:2',
  'deposit_paid' => 'decimal:2',
  'balance' => 'decimal:2',
  'invoice_number' => 'integer',
  'invoice_adjustments' => 'array',
  'manual_payments' => 'array',
];

public function items()
{
    return $this->hasMany(ReservationItem::class);
}
public function payments()
{
  return $this->hasMany(Payment::class);
}

protected static function booted()
{
    static::deleting(function(Reservation $r){
        try { $r->items()->delete(); } catch (\Throwable $e) {}
        try { $r->payments()->delete(); } catch (\Throwable $e) {}
    });
}
}
