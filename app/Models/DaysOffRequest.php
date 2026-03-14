<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaysOffRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'chef',
        'request_type',
        'start_date',
        'end_date',
        'status',
        'days',
        'approved_by',
        'notes',
        'unauthorized_days',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days' => 'integer',
        'unauthorized_days' => 'integer',
    ];
}
