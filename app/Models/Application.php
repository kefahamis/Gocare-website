<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'reference',
        'status',
        'phone',
        'amount',
        'checkout_request_id',
        'mpesa_receipt',
        'payment_status',
        'data',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'submitted_at' => 'datetime',
        ];
    }
}
