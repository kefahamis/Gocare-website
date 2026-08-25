<?php

//
// Called from the manual-payment "Completed" handler instead of the Transaction
// Status query. Answers from mpesa_c2b_payments only, so it is instant; the
// Transaction Status API stays as a fallback for codes that never arrived.

namespace App\Services\Mpesa;

use App\Models\MpesaC2bPayment;
use Illuminate\Support\Facades\DB;

class ClaimC2bPayment
{
    public const CLAIMED      = 'claimed';       // attached to this application (or was already)
    public const NOT_FOUND    = 'not_found';     // no confirmation with this code (yet) - let the page retry
    public const ALREADY_USED = 'already_used';  // code belongs to a different application
    public const AMOUNT_SHORT = 'amount_short';  // paid less than the application fee

    /**
     * @param  string  $code            what the customer typed (any case/spacing)
     * @param  float   $expectedAmount  application fee in KES
     * @param  int     $applicationId         the application this payment settles
     * @return array{status: string, payment: ?MpesaC2bPayment}
     */
    public function claim(string $code, float $expectedAmount, int $applicationId): array
    {
        $code = MpesaC2bPayment::normaliseCode($code);

        if ($code === '') {
            return ['status' => self::NOT_FOUND, 'payment' => null];
        }

        return DB::transaction(function () use ($code, $expectedAmount, $applicationId) {
            // Row lock: two "Completed" clicks with the same code cannot both win.
            $payment = MpesaC2bPayment::where('trans_id', $code)->lockForUpdate()->first();

            if (! $payment) {
                return ['status' => self::NOT_FOUND, 'payment' => null];
            }

            if ($payment->isClaimed()) {
                // Re-submitting the same code for the same application is fine; another application is not.
                $status = (int) $payment->application_id === $applicationId ? self::CLAIMED : self::ALREADY_USED;

                return ['status' => $status, 'payment' => $payment];
            }

            if ((float) $payment->amount + 0.005 < $expectedAmount) {
                return ['status' => self::AMOUNT_SHORT, 'payment' => $payment];
            }

            $payment->forceFill(['application_id' => $applicationId, 'claimed_at' => now()])->save();

            return ['status' => self::CLAIMED, 'payment' => $payment];
        });
    }
}
