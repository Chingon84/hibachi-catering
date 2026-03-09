<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'reservation_id',
        'event_date',
        'total',
        'paid',
        'balance',
        'status',
    ];

    protected $casts = [
        'event_date' => 'date',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];
}
