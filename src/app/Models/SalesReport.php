<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReport extends Model
{
    protected $fillable = [
        'order_id',
        'keterangan'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
