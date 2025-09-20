<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reservation_id','provider','amount','currency',
        'status','transaction_id','payload_json',
        'card_brand','card_last4',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
