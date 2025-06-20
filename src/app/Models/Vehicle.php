<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Vehicle extends Model
{
    protected $fillable = [
        'nama',
        'merk',
        'tipe',
        'tahun',
        'harga',
        'stok',
        'foto',
    ];

    // RELASI: Kendaraan bisa dipakai di banyak pesanan
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
