<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'company',
        'phone_primary', 'phone_alt',
        'email_primary', 'email_alt',
        'address1_street', 'address1_city', 'address1_state', 'address1_zip',
        'address2_street', 'address2_city', 'address2_state', 'address2_zip',
        'social_links', 'referral_source', 'internal_notes', 'status', 'website',
        'last_event_date', 'last_guests',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}
