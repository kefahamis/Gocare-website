<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaVerification extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_TIMED_OUT = 'timed_out';

    protected $fillable = [
        'public_token',
        'transaction_code',
        'phone',
        'application_reference',
        'claim_key',
        'status',
        'conversation_id',
        'originator_conversation_id',
        'result_code',
        'result_description',
        'amount',
        'receipt_party',
        'transaction_status',
        'transaction_completed_at',
        'request_payload',
        'result_payload',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'request_payload' => 'array',
            'result_payload' => 'array',
            'transaction_completed_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function isSettled(): bool
    {
        return $this->status !== self::STATUS_PENDING;
    }
}
