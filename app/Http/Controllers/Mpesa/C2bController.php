<?php

//
// Routes (no CSRF middleware on these):
//   POST payments/c2b/{token}/confirm   -> confirm()
//   POST payments/c2b/{token}/validate  -> validatePayment()
//
// The {token} segment is the shared secret from config('mpesa.c2b.token').
// Safaricom does not sign these requests, so the unguessable path is what stops
// a stranger from inserting a fake payment and then "claiming" it.

namespace App\Http\Controllers\Mpesa;

use App\Http\Controllers\Controller;
use App\Models\MpesaC2bPayment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class C2bController extends Controller
{
    /**
     * Safaricom POSTs here once a payment to the till has completed.
     */
    public function confirm(Request $request, string $token): JsonResponse
    {
        $this->guard($token);

        $data = $request->all();
        Log::info('C2B confirmation received', $data);

        $transId = MpesaC2bPayment::normaliseCode((string) ($data['TransID'] ?? ''));

        if ($transId !== '') {
            // firstOrCreate makes redelivery of the same confirmation harmless.
            MpesaC2bPayment::firstOrCreate(
                ['trans_id' => $transId],
                [
                    'transaction_type'    => $data['TransactionType'] ?? null,
                    'trans_time'          => $this->parseTransTime($data['TransTime'] ?? null),
                    'amount'              => (float) ($data['TransAmount'] ?? 0),
                    'business_short_code' => $data['BusinessShortCode'] ?? null,
                    'bill_ref_number'     => $data['BillRefNumber'] ?? null,
                    'msisdn'              => $data['MSISDN'] ?? null,
                    'first_name'          => $data['FirstName'] ?? null,
                    'middle_name'         => $data['MiddleName'] ?? null,
                    'last_name'           => $data['LastName'] ?? null,
                    'payload'             => $data,
                ]
            );
        }

        // Always 200, even for odd payloads: repeated non-200s get the URL flagged as failing.
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    /**
     * Only called if you ask Safaricom to switch on External Validation for the
     * shortcode. Until then it never fires; accepting everything is the safe default.
     * (Named validatePayment because Controller already has a validate() helper.)
     */
    public function validatePayment(Request $request, string $token): JsonResponse
    {
        $this->guard($token);
        Log::info('C2B validation received', $request->all());

        return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Accepted']);
    }

    private function guard(string $token): void
    {
        $expected = (string) config('mpesa.c2b.token');

        if ($expected === '' || ! hash_equals($expected, $token)) {
            abort(404);
        }
    }

    private function parseTransTime(?string $value): ?Carbon
    {
        if (! $value || strlen($value) !== 14) {
            return null;
        }

        try {
            return Carbon::createFromFormat('YmdHis', $value, 'Africa/Nairobi');
        } catch (\Throwable) {
            return null;
        }
    }
}
