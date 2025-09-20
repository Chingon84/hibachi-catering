<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reservation_id','menu_id','name_snapshot','description',
        'unit_price_snapshot','qty','line_total',
    ];
    public $timestamps = true;
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    protected $casts = [
        'unit_price_snapshot' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];
}
