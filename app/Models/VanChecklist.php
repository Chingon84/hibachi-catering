<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VanChecklist extends Model
{
    use HasFactory;

    public const CHECKLIST_TYPES = ['Dispatch', 'Return'];

    public const TRIP_STATUSES = ['Complete', 'Missing Equipment', 'Damaged', 'Needs Review'];

    public const CLEAN_STATUSES = ['PASS', 'OK', 'NO'];

    public const GAS_LEVEL_OPTIONS = ['Empty', '25%', '50%', '75%', 'Full'];

    protected $fillable = [
        'date_time',
        'user',
        'van_number',
        'checklist_type',
        'trip_status',
        'gas_level',
        'grills',
        'grills_numbers',
        'propane',
        'tables',
        'chairs',
        'chairs_covers',
        'dolly',
        'ramps',
        'mats',
        'clean',
        'notes',
        'picture1',
        'picture2',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'grills' => 'integer',
        'propane' => 'integer',
        'tables' => 'integer',
        'chairs' => 'integer',
        'chairs_covers' => 'integer',
        'dolly' => 'integer',
        'ramps' => 'integer',
        'mats' => 'integer',
    ];
}
