<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

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
        'last_event_date', 'last_event_at', 'last_guests', 'events_count',
        'source', 'created_from_reservation_id', 'total_events_count', 'lifetime_value', 'last_event_total',
    ];

    protected $casts = [
        'social_links' => 'array',
        'last_event_date' => 'date',
        'last_event_at' => 'datetime',
        'lifetime_value' => 'decimal:2',
        'last_event_total' => 'decimal:2',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'client_reservations')
            ->withPivot(['event_date', 'total', 'paid', 'balance', 'status'])
            ->withTimestamps();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ClientActivity::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ClientPhoto::class)->latest();
    }

    public function reservationsQuery(): Builder
    {
        $emails = array_values(array_unique(array_filter([
            $this->email_primary,
            $this->email_alt,
        ], fn ($v) => !empty($v))));

        $phones = array_values(array_unique(array_filter([
            $this->phone_primary,
            $this->phone_alt,
        ], fn ($v) => !empty($v))));

        $linkedReservationIds = [];
        if (Schema::hasTable('client_reservations')) {
            $linkedReservationIds = ClientReservation::query()
                ->where('client_id', $this->id)
                ->pluck('reservation_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $fallbackReservationId = (int) ($this->created_from_reservation_id ?? 0);

        if (empty($emails) && empty($phones) && empty($linkedReservationIds) && $fallbackReservationId <= 0) {
            return Reservation::query()->whereRaw('1=0');
        }

        return Reservation::query()->where(function (Builder $q) use ($emails, $phones, $linkedReservationIds, $fallbackReservationId) {
            $hasClauses = false;

            if (!empty($emails)) {
                $q->whereIn('email', $emails);
                $hasClauses = true;
            }
            if (!empty($phones)) {
                $method = $hasClauses ? 'orWhereIn' : 'whereIn';
                $q->{$method}('phone', $phones);
                $hasClauses = true;
            }
            if (!empty($linkedReservationIds)) {
                $method = $hasClauses ? 'orWhereIn' : 'whereIn';
                $q->{$method}('id', $linkedReservationIds);
                $hasClauses = true;
            }
            if ($fallbackReservationId > 0) {
                $method = $hasClauses ? 'orWhere' : 'where';
                $q->{$method}('id', $fallbackReservationId);
            }
        });
    }

    public function total_events(): int
    {
        return (int) $this->reservationsQuery()->count();
    }

    public function total_events_booked(): int
    {
        return $this->total_events();
    }

    public function cancelled_events_count(): int
    {
        return (int) $this->reservationsQuery()->where('status', 'canceled')->count();
    }

    public function last_event_at(): ?Carbon
    {
        $cached = $this->getAttribute('last_event_at');
        if ($cached instanceof Carbon) {
            return $cached;
        }
        if (!empty($cached)) {
            return Carbon::parse((string) $cached);
        }

        $date = $this->reservationsQuery()->max('date');
        return $date ? Carbon::parse($date) : null;
    }

    public function next_event_at(): ?Carbon
    {
        $date = $this->reservationsQuery()
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->value('date');

        return $date ? Carbon::parse($date) : null;
    }

    public function total_spent(): float
    {
        $sum = $this->reservationsQuery()
            ->where(function (Builder $q) {
                $q->where('status', 'confirmed')
                    ->orWhere('invoice_status', 'paid');
            })
            ->sum('total');

        return (float) $sum;
    }

    public function outstanding_balance(): float
    {
        $sum = $this->reservationsQuery()
            ->where('status', '!=', 'canceled')
            ->where('balance', '>', 0)
            ->sum('balance');

        return (float) $sum;
    }

    public function days_since_last_event(): ?int
    {
        $last = $this->last_event_at();
        return $last ? $last->startOfDay()->diffInDays(now()->startOfDay()) : null;
    }
}
