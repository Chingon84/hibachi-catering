<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Support\ReservationTotals;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

private ?array $staffPaymentSummaryCache = null;

public const EVENT_MARKER_OPTIONS = [
    'fire' => ['icon' => '🔥', 'label' => 'Fire'],
    'check' => ['icon' => '✔', 'label' => 'Check'],
    'star' => ['icon' => '⭐', 'label' => 'Star'],
    'vip' => ['icon' => 'VIP', 'label' => 'VIP'],
    'cake' => ['icon' => '🎂', 'label' => 'Cake'],
];

   protected $fillable = [
  'code','invoice_number','public_invoice_token','invoice_status','status','guests','date','time',
  'customer_name','phone','email','address','company','city','zip_code',
  'event_type','setup_color','stairs','heard_about',
  'notes','distance_miles','subtotal','tax','gratuity','travel_fee','discount','total',
  'deposit_due','deposit_paid','balance',
  'amount_paid_total',
  'booked_by',
  'invoice_adjustments',
  'color',
  'event_markers',
  'manual_payments',
];

protected $casts = [
  'date' => 'date',
  'stairs' => 'boolean',
  'distance_miles' => 'decimal:2',
  'subtotal' => 'decimal:2',
  'tax' => 'decimal:2',
  'gratuity' => 'decimal:2',
  'travel_fee' => 'decimal:2',
  'discount' => 'decimal:2',
  'total' => 'decimal:2',
  'deposit_due' => 'decimal:2',
  'deposit_paid' => 'decimal:2',
  'amount_paid_total' => 'decimal:2',
  'balance' => 'decimal:2',
  'invoice_number' => 'integer',
  'invoice_adjustments' => 'array',
  'event_markers' => 'array',
  'manual_payments' => 'array',
];

public static function eventMarkerOptions(): array
{
    return self::EVENT_MARKER_OPTIONS;
}

public function normalizedEventMarkers(): array
{
    $allowed = array_keys(self::EVENT_MARKER_OPTIONS);
    $markers = array_values(array_filter((array) ($this->event_markers ?? []), fn ($marker) => in_array((string) $marker, $allowed, true)));

    return array_values(array_unique($markers));
}

public function eventMarkerMeta(): array
{
    return collect($this->normalizedEventMarkers())
        ->map(fn (string $marker) => ['key' => $marker] + self::EVENT_MARKER_OPTIONS[$marker])
        ->values()
        ->all();
}

public function eventMarkerDisplay(): string
{
    return collect($this->eventMarkerMeta())
        ->pluck('icon')
        ->implode(' ');
}

public function items()
{
    return $this->hasMany(ReservationItem::class);
}
public function payments()
{
  return $this->hasMany(Payment::class);
}

public function staffEventConfirmations(): HasMany
{
    return $this->hasMany(StaffEventConfirmation::class);
}

public function scheduleAssignment(): HasOne
{
    return $this->hasOne(ScheduleAssignment::class);
}

public function staffConfirmationSummaryFor(User|int|null $user): array
{
    if (!$user) {
        return StaffEventConfirmation::emptySummary();
    }

    $userId = $user instanceof User ? (int) $user->id : (int) $user;
    $confirmation = $this->relationLoaded('staffEventConfirmations')
        ? $this->staffEventConfirmations->firstWhere('user_id', $userId)
        : $this->staffEventConfirmations()->where('user_id', $userId)->first();

    return $confirmation?->summary() ?? StaffEventConfirmation::emptySummary();
}

public function staffPaymentSummary(): array
{
    if ($this->staffPaymentSummaryCache !== null) {
        return $this->staffPaymentSummaryCache;
    }

    $totals = ReservationTotals::compute($this);
    $balance = max(0, round((float) ($totals['balance'] ?? $this->balance ?? 0), 2));
    $paidTotal = max(0, round((float) ($totals['paid_total'] ?? $this->amount_paid_total ?? 0), 2));
    $gratuity = max(0, round((float) ($totals['gratuity'] ?? $this->gratuity ?? 0), 2));
    $stored = strtolower(trim((string) ($this->invoice_status ?? '')));

    if ($balance <= 0.009) {
        $key = 'paid';
        $label = 'Paid';
    } elseif ($paidTotal > 0.009 || in_array($stored, ['partial', 'partially_paid'], true)) {
        $key = 'partial';
        $label = 'Partial';
    } else {
        $key = 'unpaid';
        $label = 'Unpaid';
    }

    return $this->staffPaymentSummaryCache = [
        'status_key' => $key,
        'status_label' => $label,
        'balance_due' => $balance,
        'gratuity' => $gratuity,
    ];
}

protected static function booted()
{
    static::creating(function (Reservation $r) {
        if (empty($r->public_invoice_token)) {
            $r->public_invoice_token = static::generatePublicInvoiceToken();
        }
    });

    static::deleting(function (Reservation $r) {
        $r->items()->delete();
        $r->payments()->delete();
    });
}

public function ensurePublicInvoiceToken(): string
{
    if (!empty($this->public_invoice_token)) {
        return (string) $this->public_invoice_token;
    }

    $this->public_invoice_token = static::generatePublicInvoiceToken();
    $this->saveQuietly();

    return (string) $this->public_invoice_token;
}

public static function generatePublicInvoiceToken(): string
{
    do {
        $token = Str::random(48);
    } while (static::withTrashed()->where('public_invoice_token', $token)->exists());

    return $token;
}
}
