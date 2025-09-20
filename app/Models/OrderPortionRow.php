<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPortionRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'row_key',
        'label',
        'qty',
        'unit',
        'total',
        'ozs',
        'lbs',
        'position',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'total' => 'decimal:2',
        'ozs' => 'decimal:2',
        'lbs' => 'decimal:4',
        'position' => 'integer',
    ];
}
