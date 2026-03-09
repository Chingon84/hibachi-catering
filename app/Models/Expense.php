<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'Rent',
        'Electricity',
        'Water',
        'Internet',
        'Gasoline',
        'Payroll',
        'Taxes',
        'Insurance',
        'Food Cost',
        'Supplies',
        'Maintenance',
        'Marketing',
        'Software',
        'Repairs',
        'Other',
    ];

    protected $fillable = [
        'expense_date',
        'category',
        'description',
        'amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
