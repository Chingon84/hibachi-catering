<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodFeedback extends Model
{
    use HasFactory;

    protected $table = 'good_feedback';

    protected $fillable = [
        'event_date',
        'date_received',
        'chef',
        'source',
        'compliment',
        'assistant',
    ];

    protected $casts = [
        'event_date' => 'date',
        'date_received' => 'date',
    ];

    public function getFeedbackIdAttribute(): string
    {
        return 'GF-' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }
}
