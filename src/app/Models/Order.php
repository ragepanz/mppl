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
        'catatan'
    ];

    protected $casts = [
        'tanggal_order' => 'date',
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
}
