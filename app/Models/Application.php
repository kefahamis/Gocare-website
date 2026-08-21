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
        'checkout_request_ids',
        'mpesa_receipt',
        'status_conversation_id',
        'status_queried_at',
        'payment_status',
        'payment_note',
        'data',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'checkout_request_ids' => 'array',
            'submitted_at' => 'datetime',
            'status_queried_at' => 'datetime',
        ];
    }
}
