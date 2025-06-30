<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'payment_notes',
        'payment_verified_at',
        'payment_rejected_at',
    ];

    protected $casts = [
        'payment_verified_at' => 'datetime',
        'payment_rejected_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}