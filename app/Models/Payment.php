<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'package_id',
        'checkout_link',
        'external_id',
        'status',
        'payment_method',
        'amount',
        'payer_email',
        'description',
        'last_used_at',
    ];
}