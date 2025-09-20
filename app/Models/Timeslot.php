<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timeslot extends Model
{
    use HasFactory;

    // Permitir asignación masiva de estos campos
    protected $fillable = [
        'date',
        'time',
        'capacity',
        'is_open',
        'notes',
    ];

    // Casts útiles
    protected $casts = [
        'date' => 'date',      // Y-m-d
        'is_open' => 'boolean'
    ];
}
