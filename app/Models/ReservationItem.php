<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\MenuLabel;

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

    public function getNameSnapshotAttribute($value)
    {
        return MenuLabel::standardize($value);
    }

    public function setNameSnapshotAttribute($value)
    {
        $this->attributes['name_snapshot'] = MenuLabel::standardize($value);
    }
}
