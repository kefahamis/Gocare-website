<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaC2bPayment extends Model
{
    protected $fillable = [
        'trans_id', 'transaction_type', 'trans_time', 'amount', 'business_short_code',
        'bill_ref_number', 'msisdn', 'first_name', 'middle_name', 'last_name',
        'payload', 'application_id', 'claimed_at',
    ];

    protected $casts = [
        'trans_time' => 'datetime',
        'claimed_at' => 'datetime',
        'amount'     => 'decimal:2',
        'payload'    => 'array',
    ];

    public function isClaimed(): bool
    {
        return $this->claimed_at !== null;
    }

    /**
     * Receipt codes are 10 upper-case alphanumerics. Customers type them with
     * spaces, lower case and the odd dash, so normalise before storing or matching.
     */
    public static function normaliseCode(string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
    }
}
