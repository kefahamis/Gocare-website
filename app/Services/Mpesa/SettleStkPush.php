<?php

namespace App\Services\Mpesa;

use App\Mail\ApplicationReceived;
use App\Models\Application;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Apply the verdict of an STK query to an application.
 *
 * Shared by the browser-driven fallback (ApplicationController::queryStkPush)
 * and the scheduled sweep, so the two cannot drift into disagreeing about what
 * "paid" means. Every transition here is the same atomic claim the callback
 * uses: whoever flips the row away from paid owns sending the email, so a
 * callback landing in the same second cannot send a second one.
 */
class SettleStkPush
{
    public const PAID = 'paid';
    public const FAILED = 'failed';
    public const UNCHANGED = 'unchanged';

    /**
     * @param  array{state:string,result_code:string,description:string}  $outcome
     */
    public function apply(Application $application, array $outcome): string
    {
        if ($outcome['state'] === 'paid') {
            return $this->markPaid($application);
        }

        // Only a still-pending row may be failed. A code typed by hand in the
        // meantime, or a callback that already settled it, outranks an
        // abandoned prompt.
        if ($outcome['state'] === 'failed' && $application->payment_status === 'pending') {
            $application->update([
                'payment_status' => 'failed',
                'payment_note' => $outcome['description'] ?: 'The payment prompt was cancelled or timed out.',
            ]);

            return self::FAILED;
        }

        return self::UNCHANGED;
    }

    private function markPaid(Application $application): string
    {
        try {
            $claimed = Application::where('id', $application->id)
                ->where('payment_status', '!=', 'paid')
                ->update(['payment_status' => 'paid', 'payment_note' => null]);
        } catch (QueryException $e) {
            Log::critical('Could not settle an STK payment. Reconcile by hand.', [
                'reference' => $application->reference,
                'message' => $e->getMessage(),
            ]);

            return self::UNCHANGED;
        }

        if ($claimed === 0) {
            return self::UNCHANGED;
        }

        // The STK query response carries no MpesaReceiptNumber, so the receipt
        // stays empty until the callback arrives; mpesaCallback() backfills it.
        Mail::to(config('gocare.notification_email'))->queue(new ApplicationReceived($application->refresh()));

        return self::PAID;
    }
}
