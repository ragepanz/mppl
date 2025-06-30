<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    protected $fillable = [
        'type',
        'bank_name', 
        'account_name',
        'account_number',
        'instructions'
    ];
}
