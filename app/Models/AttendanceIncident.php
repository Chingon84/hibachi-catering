<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'chef',
        'incident_type',
        'units',
        'authorized',
        'manager',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'authorized' => 'boolean',
        'units' => 'integer',
    ];

    public function getIncidentIdAttribute(): string
    {
        return 'AT-' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }
}
