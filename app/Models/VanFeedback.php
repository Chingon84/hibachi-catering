<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VanFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_date',
        'date_received',
        'chef',
        'van',
        'description',
        'action_taken',
    ];

    protected $casts = [
        'event_date' => 'date',
        'date_received' => 'date',
    ];

    public function getVanfbIdAttribute(): string
    {
        return 'VF-' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }
}
