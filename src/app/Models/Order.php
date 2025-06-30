<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Order extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'tanggal_order',
        'status',
        'total_harga',
        'payment_method',
        'payment_amount',
        'payment_date',
        'payment_proof',
        'payment_notes',
        'payment_verified_at',
        'payment_rejected_at'
    ];

    protected $casts = [
        'tanggal_order' => 'date',
        'payment_details' => 'array',
        'payment_date' => 'date',
        'payment_verified_at' => 'datetime',
        'payment_rejected_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function salesReport(): HasOne
    {
        return $this->hasOne(SalesReport::class);
    }

    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending')->whereNotNull('payment_proof');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(OrderPayment::class);
    }
}
