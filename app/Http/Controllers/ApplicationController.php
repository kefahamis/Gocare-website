<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationReceived;
use App\Models\Application;
use App\Services\Mpesa\ClaimC2bPayment;
use App\Services\Mpesa\SettleStkPush;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    public function store(Request $request, MpesaService $mpesa): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        // The fee is read from config, never from the request: a client-supplied
        // amount could be edited in the browser before the STK push is sent.
        $amount = (int) config('gocare.application_fee');

        // A stale config cache makes a missing key read as null, which would
        // otherwise reach Daraja as Amount 0 and come back as a confusing
        // "Invalid Amount". Refuse before any row or payment is created.
        if ($amount < 1) {
            return $this->unconfiguredFeeResponse();
        }

        $application = $this->currentApplication($request);

        // Paying twice is never the intent: a double click after a confirmed
        // payment must not push again or open a second application.
        if ($application && $application->payment_status === 'paid') {
            return response()->json([
                'message' => 'This application is already paid.',
                'reference' => $application->reference,
                'payment_status' => $application->payment_status,
            ]);
        }

        if ($application) {
            // The prompt is deliberately re-sendable ("tap to try again"), but it
            // reuses this row rather than opening another. A second row would be
            // orphaned: a late callback for the first attempt would settle it
            // while the browser polls the newer reference and never sees payment.
            $application->update([
                'phone' => $request->input('phone'),
                'amount' => $amount,
                'data' => $request->except(['_token', 'amount']),
                // A hand-typed code already awaiting verification outranks a
                // fresh attempt, so only a pending or failed row is reset.
                'payment_status' => in_array($application->payment_status, ['pending', 'failed'], true)
                    ? 'pending'
                    : $application->payment_status,
                'payment_note' => null,
            ]);
        } else {
            $reference = $this->newReference();

            $application = Application::create([
                'reference' => $reference,
                'status' => 'submitted',
                'phone' => $request->input('phone'),
                'amount' => $amount,
                'payment_status' => 'pending',
                'data' => $request->except(['_token', 'amount']),
                'submitted_at' => now(),
            ]);

            // Remember which application this browser owns, so the later details
            // submission attaches to it instead of trusting a client-supplied ref.
            $request->session()->put('application_reference', $reference);
        }

        try {
            $result = $mpesa->stkPush($application->phone, $application->amount, $application->reference, 'Application Fee');
        } catch (\Exception $e) {
            Log::error('Application Mpesa initiation failed: ' . $e->getMessage(), [
                'reference' => $application->reference,
                'exception' => $e::class,
                'at' => $e->getFile() . ':' . $e->getLine(),
            ]);

            $payload = [
                'error' => 'Failed to initiate M-Pesa payment. Please try again.',
                'reference' => $application->reference
            ];

            // Never leak the underlying reason (it can carry credentials and
            // internal paths) unless the site is explicitly in debug mode.
            if (config('app.debug')) {
                $payload['debug'] = [
                    'message' => $e->getMessage(),
                    'exception' => $e::class,
                    'at' => $e->getFile() . ':' . $e->getLine(),
                ];
            }

            return response()->json($payload, 500);
        }

        $checkoutRequestId = $result['CheckoutRequestID'] ?? null;

        // Past this point the prompt is on the applicant's phone. Storing the id
        // is what lets the callback find this row, but if that write fails the
        // prompt is still real - reporting it as a failure would only push the
        // applicant into requesting a second one.
        if ($checkoutRequestId) {
            try {
                // Keep every attempt's id, not just the latest: an applicant who
                // pays an earlier prompt after asking for a new one must still
                // have that callback land on this application.
                $ids = $application->checkout_request_ids ?? [];
                $ids[] = $checkoutRequestId;

                $application->update([
                    'checkout_request_id' => $checkoutRequestId,
                    'checkout_request_ids' => array_values(array_unique($ids)),
                ]);
            } catch (\Throwable $e) {
                // The money can still arrive, so leave a loud trail to reconcile
                // against rather than losing the link silently.
                Log::critical('STK prompt sent but its CheckoutRequestID could not be stored. Reconcile this payment by hand.', [
                    'reference' => $application->reference,
                    'checkout_request_id' => $checkoutRequestId,
                    'message' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }
        }

        // The CheckoutRequestID is deliberately NOT returned: it is the only
        // thing a forged callback needs, and nothing in the page uses it. The
        // reference is what the browser polls with.
        return response()->json([
            'message' => 'Payment initiated. Please enter your M-Pesa PIN.',
            'reference' => $application->reference,
        ]);
    }

    /**
     * Details for paying by hand when the STK prompt never arrives.
     *
     * Mirrors how eCitizen falls back: show the paybill/till and an account
     * number, let the applicant pay from their own M-Pesa menu, and have them
     * confirm afterwards. The row is created here if this browser has not
     * started one, so there is always an account number to quote.
     */
    public function manualPayment(Request $request): JsonResponse
    {
        $amount = (int) config('gocare.application_fee');

        if ($amount < 1) {
            return $this->unconfiguredFeeResponse();
        }

        $application = $this->currentApplication($request);

        if (! $application) {
            $reference = $this->newReference();

            $application = Application::create([
                'reference' => $reference,
                'status' => 'submitted',
                'phone' => (string) $request->input('phone'),
                'amount' => $amount,
                'payment_status' => 'pending',
                'data' => $request->except(['_token', 'amount']),
                'submitted_at' => now(),
            ]);

            $request->session()->put('application_reference', $reference);
        }

        $isTill = config('mpesa.type') === 'till';

        return response()->json([
            'reference' => $application->reference,
            'amount' => $amount,
            'method' => $isTill ? 'till' : 'paybill',
            'number' => $isTill
                ? (string) (config('mpesa.till_number') ?: config('mpesa.shortcode'))
                : (string) config('mpesa.shortcode'),
            'account' => $application->reference,
            'payment_status' => $application->payment_status,
        ]);
    }

    /**
     * Record an applicant's "I have paid" claim after a manual M-Pesa payment,
     * and ask Safaricom to confirm the confirmation code they typed.
     *
     * The click is never treated as proof. The code is parked as
     * awaiting_verification and a Transaction Status query is sent; only the
     * result callback from Safaricom can move it to paid. When the query is
     * not configured the claim is simply parked for an admin to reconcile
     * against the M-Pesa statement, as it was before verification existed.
     */
    public function confirmManualPayment(Request $request, MpesaService $mpesa, ClaimC2bPayment $claimer): JsonResponse
    {
        $validated = $request->validate([
            'transaction_code' => 'required|string|regex:/^[A-Za-z0-9]{6,15}$/',
        ]);

        $application = $this->currentApplication($request);

        if (! $application) {
            return response()->json([
                'error' => 'We could not find your application in this browser session. Please reload the page and try again.',
            ], 404);
        }

        $code = strtoupper($validated['transaction_code']);

        // A confirmed callback outranks a typed code, so never walk it back.
        if ($application->payment_status === 'paid') {
            return response()->json([
                'message' => 'This application is already paid.',
                'reference' => $application->reference,
                'payment_status' => $application->payment_status,
                'verifying' => false,
            ]);
        }

        // C2B first: if Safaricom already pushed this payment to us, the answer
        // is in our own table and needs no query, no initiator and no waiting.
        // Only a code we never received falls through to Transaction Status.
        $c2b = $claimer->claim($code, (float) $application->amount, (int) $application->id);

        if ($c2b['status'] === ClaimC2bPayment::ALREADY_USED) {
            Log::warning('C2B code already claimed by another application', [
                'reference' => $application->reference,
                'code' => $code,
            ]);

            return response()->json([
                'error' => 'This confirmation code has already been used for another application. Please check the code on your M-Pesa message.',
            ], 422);
        }

        if ($c2b['status'] === ClaimC2bPayment::AMOUNT_SHORT) {
            $paid = (float) ($c2b['payment']->amount ?? 0);

            return response()->json([
                'error' => 'The amount paid (KES ' . number_format($paid) . ') is less than the application fee of KES ' . number_format((float) $application->amount) . '.',
            ], 422);
        }

        if ($c2b['status'] === ClaimC2bPayment::CLAIMED) {
            try {
                $settled = Application::where('id', $application->id)
                    ->where('payment_status', '!=', 'paid')
                    ->update([
                        'payment_status' => 'paid',
                        'payment_note' => null,
                        'mpesa_receipt' => $code,
                    ]);
            } catch (QueryException $e) {
                // The receipt is on another application row. The C2B claim above
                // already ruled out another application owning the payment, so
                // this is stale data rather than a double spend.
                Log::critical('C2B payment claimed but the receipt is already on another application. Reconcile by hand.', [
                    'reference' => $application->reference,
                    'code' => $code,
                ]);

                $settled = Application::where('id', $application->id)
                    ->where('payment_status', '!=', 'paid')
                    ->update([
                        'payment_status' => 'paid',
                        'payment_note' => 'Paid. The M-Pesa receipt is already recorded on another application and needs checking.',
                    ]);
            }

            if ($settled > 0) {
                Mail::to(config('gocare.notification_email'))->queue(new ApplicationReceived($application->refresh()));
            }

            Log::info('Manual M-Pesa code matched a C2B confirmation', [
                'reference' => $application->reference,
                'code' => $code,
            ]);

            return response()->json([
                'message' => 'Payment confirmed.',
                'reference' => $application->reference,
                'payment_status' => 'paid',
                'verifying' => false,
            ]);
        }

        // Someone else's code cannot be borrowed -- including one still being
        // verified. Checking only `paid` left a window where two applications
        // both sat in awaiting_verification on the same receipt and both
        // passed if their results landed together. The unique index on
        // mpesa_receipt is the backstop; this is the friendly refusal.
        $claimedElsewhere = Application::where('mpesa_receipt', $code)
            ->whereIn('payment_status', ['paid', 'awaiting_verification'])
            ->where('id', '!=', $application->id)
            ->exists();

        if ($claimedElsewhere) {
            Log::warning('Manual M-Pesa code already used by another application', [
                'reference' => $application->reference,
                'code' => $code,
            ]);

            return response()->json([
                'error' => 'This confirmation code has already been used for another application. Please check the code on your M-Pesa message.',
            ], 422);
        }

        // Re-submitting the same code while its query is still in flight must
        // not start another one: Safaricom would answer on a new conversation
        // id and the earlier result would arrive orphaned.
        $inFlight = $application->payment_status === 'awaiting_verification'
            && $application->mpesa_receipt === $code
            && filled($application->status_conversation_id)
            && $application->status_queried_at?->gt(now()->subMinutes(2));

        if ($inFlight) {
            return response()->json([
                'message' => 'Checking this code with M-Pesa. This usually takes a few seconds.',
                'reference' => $application->reference,
                'payment_status' => $application->payment_status,
                'verifying' => true,
            ]);
        }

        try {
            $application->update([
                'payment_status' => 'awaiting_verification',
                'mpesa_receipt' => $code,
                'payment_note' => null,
            ]);
        } catch (QueryException $e) {
            // The unique index caught a receipt the check above did not -- a
            // code held by a failed or pending row, or a genuine race.
            Log::warning('Manual M-Pesa code rejected by the unique index', [
                'reference' => $application->reference,
                'code' => $code,
            ]);

            return response()->json([
                'error' => 'This confirmation code has already been used for another application. Please check the code on your M-Pesa message.',
            ], 422);
        }

        Log::info('Manual M-Pesa payment claimed', [
            'reference' => $application->reference,
            'code' => $code,
        ]);

        if (! $mpesa->statusQueryConfigured()) {
            return response()->json([
                'message' => 'Thank you. We have recorded your payment and will confirm it shortly.',
                'reference' => $application->reference,
                'payment_status' => $application->payment_status,
                'verifying' => false,
            ]);
        }

        try {
            $result = $mpesa->transactionStatus($code, $application->reference);

            $application->update([
                'status_conversation_id' => $result['OriginatorConversationID'] ?? null,
                'status_queried_at' => now(),
            ]);

            return response()->json([
                'message' => 'Checking this code with M-Pesa. This usually takes a few seconds.',
                'reference' => $application->reference,
                'payment_status' => $application->payment_status,
                'verifying' => true,
            ]);
        } catch (\Exception $e) {
            // Verification is a bonus, not a gate: a Daraja outage must not stop
            // the applicant from recording a payment they genuinely made.
            Log::error('M-Pesa transaction status query failed: ' . $e->getMessage(), [
                'reference' => $application->reference,
                'code' => $code,
                'exception' => $e::class,
            ]);

            return response()->json([
                'message' => 'Thank you. We have recorded your payment and will confirm it shortly.',
                'reference' => $application->reference,
                'payment_status' => $application->payment_status,
                'verifying' => false,
            ]);
        }
    }

    private function newReference(): string
    {
        return 'GC-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }

    /**
     * The application this browser owns, from the session rather than the
     * request, so nobody can attach to someone else's reference.
     */
    private function currentApplication(Request $request): ?Application
    {
        $reference = $request->session()->get('application_reference');

        return $reference
            ? Application::where('reference', $reference)->first()
            : null;
    }

    private function unconfiguredFeeResponse(): JsonResponse
    {
        Log::error('Application fee is not configured; refusing to start a payment.', [
            'configured_value' => config('gocare.application_fee'),
            'hint' => 'Run php artisan config:clear if the config cache is stale.',
        ]);

        return response()->json([
            'error' => 'Application fee is not configured. Please contact us to complete your application.',
        ], 500);
    }

    /**
     * Persist the full 7-step application form.
     *
     * The payment step already created a row for this browser and settled it;
     * this fills in the rest of the answers. A confirmed fee is a precondition,
     * not a detail: the form refuses to leave the payment step without one, and
     * this refuses the submission for the same reason, so a hand-crafted POST
     * cannot get past it either.
     */
    public function submitDetails(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:32',
            'program' => 'nullable|string|max:255',
            'fields' => 'required|array',
        ]);

        // The reference is read from the session, never from the request, so an
        // applicant cannot overwrite someone else's submission by guessing one.
        $application = $this->currentApplication($request);

        // No row means no payment was ever started; a row that is not `paid` is
        // still pending, awaiting Safaricom's verification, or failed. Neither
        // may be submitted. The browser keeps the answers, so refusing here
        // costs the applicant nothing except the trip back to the fee.
        if (! $application || $application->payment_status !== 'paid') {
            Log::info('Application submission refused: fee not confirmed', [
                'reference' => $application?->reference,
                'payment_status' => $application?->payment_status,
            ]);

            return response()->json([
                'message' => 'Your application fee has not been confirmed yet. Complete the M-Pesa payment on the Payment step, then submit.',
                'reference' => $application?->reference,
                'payment_status' => $application?->payment_status ?? 'pending',
            ], 422);
        }

        $application->update([
            'data' => array_merge($application->data ?? [], $validated['fields']),
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Log::info('Application details saved', [
            'reference' => $application->reference,
            'payment_status' => $application->payment_status,
        ]);

        return response()->json([
            'message' => 'Application submitted.',
            'reference' => $application->reference,
            'payment_status' => $application->payment_status,
        ]);
    }

    public function mpesaCallback(Request $request, MpesaService $mpesa)
    {
        Log::info('M-Pesa Callback Received', $request->all());

        $payload = json_decode($request->getContent(), true);

        if (!isset($payload['Body']['stkCallback'])) {
            return response()->json(['message' => 'Invalid payload']);
        }

        $callback = $payload['Body']['stkCallback'];
        $checkoutRequestId = $callback['CheckoutRequestID'];
        $resultCode = $callback['ResultCode'];

        // Match the latest attempt or any earlier one, so a callback for a
        // prompt the applicant paid after retrying is not dropped.
        $application = Application::where('checkout_request_id', $checkoutRequestId)
            ->orWhereJsonContains('checkout_request_ids', $checkoutRequestId)
            ->first();

        if (!$application) {
            Log::warning("No application found for CheckoutRequestID: {$checkoutRequestId}");
            return response()->json(['message' => 'Not found']);
        }

        if ($resultCode == 0) {
            // This endpoint is unauthenticated -- Safaricom does not sign its
            // callbacks -- and the CheckoutRequestID it is keyed on is handed
            // to the browser when the prompt is sent. Anyone holding one could
            // POST a ResultCode 0 here and walk away with a paid application.
            // So the callback is treated as a HINT, and Safaricom is asked
            // directly before any money is believed in.
            $confirmed = $this->confirmedStkState($mpesa, $checkoutRequestId);

            if ($confirmed === 'failed') {
                Log::warning('Callback claimed success but Safaricom reports this prompt as failed. Ignoring.', [
                    'reference' => $application->reference,
                    'checkout_request_id' => $checkoutRequestId,
                    'ip' => $request->ip(),
                ]);

                return response()->json(['message' => 'Success']);
            }

            if ($confirmed !== 'paid') {
                // Could not confirm right now (Daraja unreachable, rate limited,
                // still processing). Fail closed and leave the row pending: the
                // scheduled sweep re-asks every minute and settles it then.
                Log::info('Callback could not be confirmed with Safaricom yet; leaving it to the sweep.', [
                    'reference' => $application->reference,
                    'checkout_request_id' => $checkoutRequestId,
                    'state' => $confirmed,
                ]);

                return response()->json(['message' => 'Success']);
            }

            // Payment successful
            $receipt = null;
            if (isset($callback['CallbackMetadata']['Item'])) {
                foreach ($callback['CallbackMetadata']['Item'] as $item) {
                    if ($item['Name'] == 'MpesaReceiptNumber') {
                        $receipt = $item['Value'];
                        break;
                    }
                }
            }

            // Safaricom retries a callback it thinks was not acknowledged, so
            // claim the transition atomically: whoever flips it away from paid
            // owns sending the email, and a repeat delivery is a no-op.
            try {
                $claimed = Application::where('id', $application->id)
                    ->where('payment_status', '!=', 'paid')
                    ->update([
                        'payment_status' => 'paid',
                        'payment_note' => null,
                        'mpesa_receipt' => $receipt,
                    ]);
            } catch (QueryException $e) {
                // Never let a constraint turn into a 500: Safaricom would retry
                // the callback and we would be no better off. Record it as paid
                // without the contested receipt and leave a trail to reconcile.
                Log::critical('M-Pesa receipt already belongs to another application. Reconcile by hand.', [
                    'reference' => $application->reference,
                    'receipt' => $receipt,
                    'checkout_request_id' => $checkoutRequestId,
                ]);

                $claimed = Application::where('id', $application->id)
                    ->where('payment_status', '!=', 'paid')
                    ->update([
                        'payment_status' => 'paid',
                        'payment_note' => 'Paid. The M-Pesa receipt is already recorded on another application and needs checking.',
                    ]);
            }

            if ($claimed === 0) {
                // Already paid -- but the STK query fallback can mark a payment
                // paid without a receipt, because that response carries none.
                // This is the only chance to record it, so backfill rather than
                // discarding the one message that has the number.
                if (blank($application->mpesa_receipt) && filled($receipt)) {
                    try {
                        $application->update(['mpesa_receipt' => $receipt]);
                    } catch (QueryException $e) {
                        Log::critical('Could not backfill an M-Pesa receipt: it is already on another application.', [
                            'reference' => $application->reference,
                            'receipt' => $receipt,
                        ]);

                        return response()->json(['message' => 'Success']);
                    }

                    Log::info('Backfilled M-Pesa receipt from a late callback', [
                        'reference' => $application->reference,
                        'receipt' => $receipt,
                    ]);
                }

                Log::info('Duplicate M-Pesa callback ignored', [
                    'reference' => $application->reference,
                    'checkout_request_id' => $checkoutRequestId,
                ]);

                return response()->json(['message' => 'Success']);
            }

            // Send email only when paid
            Mail::to(config('gocare.notification_email'))->queue(new ApplicationReceived($application->refresh()));

        } elseif (in_array($application->payment_status, ['paid', 'awaiting_verification'], true)) {
            // A late failure callback for an abandoned STK attempt must not
            // erase a payment the applicant already made by hand.
            Log::info('Ignoring failed callback for an already-settled application', [
                'reference' => $application->reference,
                'payment_status' => $application->payment_status,
            ]);
        } else {
            // Payment failed or cancelled
            $application->update([
                'payment_status' => 'failed'
            ]);
        }

        return response()->json(['message' => 'Success']);
    }

    /**
     * Safaricom's Transaction Status ResultURL callback.
     *
     * Carries the real outcome of a confirmation code an applicant typed in
     * after paying by hand. Only this can move a claim to paid.
     */
    /**
     * Refuse an unguarded Transaction Status callback once a token is set.
     *
     * Safaricom does not sign these, and the body alone decides whether an
     * application is paid -- so while the conversation id is hard to guess, it
     * is the only thing standing in the way. Once MPESA_STATUS_TOKEN is set,
     * the secret in the path is what authenticates the caller, and the legacy
     * unguarded paths stop working.
     *
     * A hit on the old path while a token is configured means the ResultURL in
     * .env was not updated alongside it. That is logged loudly rather than
     * silently 404ing, because the cost is a lost payment verdict.
     */
    private function guardStatusCallback(Request $request, ?string $token): void
    {
        $expected = (string) config('mpesa.status.token');

        if ($expected === '') {
            return; // Not enabled yet; the legacy paths remain in use.
        }

        if ($token === null) {
            Log::warning('Transaction Status callback arrived on the unguarded path while a token is configured. Update MPESA_STATUS_RESULT_URL and MPESA_STATUS_TIMEOUT_URL.', [
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            abort(404);
        }

        if (! hash_equals($expected, $token)) {
            Log::warning('Transaction Status callback presented a bad token.', [
                'ip' => $request->ip(),
            ]);

            abort(404);
        }
    }

    public function mpesaStatusCallback(Request $request, ?string $token = null)
    {
        $this->guardStatusCallback($request, $token);

        Log::info('M-Pesa Transaction Status Result Received', $request->all());

        $result = $request->input('Result', []);

        $application = $this->applicationForStatusResult($result);

        if (! $application) {
            Log::warning('No application matched a transaction status result', [
                'originator' => $result['OriginatorConversationID'] ?? null,
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        // An STK callback that already settled this application outranks a
        // typed code, so never walk a confirmed payment back.
        if ($application->payment_status === 'paid') {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $parameters = $this->statusResultParameters($result);

        // The result must be about the code the applicant typed. Matching is by
        // conversation id, which is sound, but this is the cheap second lock:
        // a mismatch means the verdict describes some other transaction, and
        // accepting it would mark the fee paid on evidence from elsewhere.
        $returnedReceipt = strtoupper(trim((string) ($parameters['ReceiptNo'] ?? '')));
        $claimedReceipt = strtoupper(trim((string) $application->mpesa_receipt));

        // Only meaningful on a successful lookup: a refused query (21, 2001)
        // carries no real receipt, and manualPaymentRejection() reports those
        // far more usefully than a generic mismatch would.
        $lookupSucceeded = (string) ($result['ResultCode'] ?? '') === '0';

        if ($lookupSucceeded && $returnedReceipt !== '' && $claimedReceipt !== '' && $returnedReceipt !== $claimedReceipt) {
            $application->update([
                'payment_note' => 'We could not confirm this code automatically. Your payment is recorded and our team will confirm it shortly.',
            ]);

            Log::warning('Transaction status result is for a different receipt than the one claimed', [
                'reference' => $application->reference,
                'claimed' => $claimedReceipt,
                'returned' => $returnedReceipt,
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $failure = $this->manualPaymentRejection(
            resultCode: (string) ($result['ResultCode'] ?? ''),
            transactionStatus: (string) ($parameters['TransactionStatus'] ?? ''),
            amount: isset($parameters['Amount']) ? (float) $parameters['Amount'] : null,
            // Safaricom is not consistent about which key carries the payee.
            creditParty: (string) (
                $parameters['CreditPartyName']
                ?? $parameters['ReceiverPartyPublicName']
                ?? ''
            ),
            expectedAmount: (float) $application->amount,
        );

        if ($failure !== null) {
            // Keep it awaiting_verification rather than failing it outright: a
            // mistyped code should not cost an applicant a payment they made.
            $application->update(['payment_note' => $failure]);

            Log::info('Manual M-Pesa code could not be confirmed', [
                'reference' => $application->reference,
                'code' => $application->mpesa_receipt,
                'reason' => $failure,
                // Safaricom's own verdict, so diagnosing this is one grep
                // rather than cross-referencing the raw result logged above.
                'result_code' => (string) ($result['ResultCode'] ?? ''),
                'result_desc' => (string) ($result['ResultDesc'] ?? ''),
                'transaction_status' => (string) ($parameters['TransactionStatus'] ?? ''),
                // Which fields Safaricom actually sent. The payee and amount
                // checks fail closed, so if either key is missing every code
                // lands in manual review -- this is how you see that, rather
                // than guessing at a silent backlog.
                'parameter_keys' => array_keys($parameters),
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        // Claim the transition atomically, as in mpesaCallback: a retried result
        // or a racing STK callback must not send the email a second time.
        // The receipt was already claimed on this row, so the index has nothing
        // new to refuse here; the try is for the pathological case where two
        // results race onto the same receipt.
        $claimed = Application::where('id', $application->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status' => 'paid',
                'payment_note' => null,
                // Equal to the claimed code by the guard above, or absent from
                // the result entirely -- in which case the applicant's own
                // code stands rather than being quietly replaced.
                'mpesa_receipt' => $returnedReceipt !== '' ? $returnedReceipt : $application->mpesa_receipt,
            ]);

        if ($claimed === 0) {
            Log::info('Duplicate M-Pesa transaction status result ignored', [
                'reference' => $application->reference,
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        // Matches the STK path: the notification email goes out once paid.
        Mail::to(config('gocare.notification_email'))->queue(new ApplicationReceived($application->refresh()));

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Safaricom's Transaction Status QueueTimeOutURL callback.
     */
    public function mpesaStatusTimeout(Request $request, ?string $token = null)
    {
        $this->guardStatusCallback($request, $token);

        Log::warning('M-Pesa Transaction Status timed out', $request->all());

        $application = $this->applicationForStatusResult($request->input('Result', []));

        if ($application && $application->payment_status === 'awaiting_verification') {
            $application->update([
                'payment_note' => 'M-Pesa did not respond in time. Our team will confirm this payment manually.',
            ]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Transaction Status result codes that mean Safaricom rejected our query
     * rather than the applicant's code.
     *
     *   21   The initiator is not allowed to initiate this request
     *        (the operator lacks the Transaction Status permission).
     *   2001 The initiator information is invalid
     *        (wrong initiator name, or a stale security credential).
     */
    private const STATUS_CONFIG_RESULT_CODES = ['21', '2001'];

    /**
     * Why a typed code cannot be accepted as payment, or null when it checks out.
     */
    private function manualPaymentRejection(
        string $resultCode,
        string $transactionStatus,
        ?float $amount,
        string $creditParty,
        float $expectedAmount,
    ): ?string {
        if ($resultCode !== '0') {
            // Codes where Safaricom refused the REQUEST, not the transaction:
            // the initiator lacks the Transaction Status permission (21), or
            // its credentials were rejected (2001). The applicant's code was
            // never looked up -- Safaricom echoes a placeholder TransactionID
            // in these results -- so telling them to re-read their SMS blames
            // them for our configuration. They keep awaiting_verification
            // either way; only the wording and the trail change.
            if (in_array($resultCode, self::STATUS_CONFIG_RESULT_CODES, true)) {
                return 'We could not verify this code automatically just yet. Your payment is recorded and our team will confirm it shortly.';
            }

            return 'We could not find this confirmation code on M-Pesa. Please check the code on your message.';
        }

        // Fails closed, like the payee and amount checks below: an absent
        // TransactionStatus is not evidence the transaction completed.
        if ($transactionStatus === '') {
            return 'We could not confirm the state of this transaction. Your payment is recorded and our team will confirm it shortly.';
        }

        if (! in_array(strtolower($transactionStatus), ['completed', 'success'], true)) {
            return 'M-Pesa reports this transaction as "' . $transactionStatus . '", so it cannot be accepted yet.';
        }

        // On Buy Goods the credit party can carry either the till customers pay
        // to or the head office / store number behind it, so accept both.
        $ourNumbers = array_filter([
            (string) config('mpesa.shortcode'),
            (string) config('mpesa.till_number'),
        ]);

        // Fail CLOSED on a missing payee. Transaction Status answers for any
        // valid M-Pesa code, not only ones paid to us, so the payee is the
        // ONLY thing tying a code to this organisation. Skipping the check
        // when the field is absent -- as this did -- accepted any code the
        // applicant had ever received, including payments made to someone
        // else entirely.
        if ($ourNumbers === [] || $creditParty === '') {
            return 'We could not confirm this payment was made to our M-Pesa number. Your payment is recorded and our team will confirm it shortly.';
        }

        $paidToUs = array_filter($ourNumbers, fn (string $number) => str_contains($creditParty, $number));

        if ($paidToUs === []) {
            return 'This payment was not made to our M-Pesa number. Please check the till number and try again.';
        }

        // Same reasoning for the amount: an absent Amount used to mean "no
        // amount check at all", so a KES 1 payment to our till passed.
        if ($expectedAmount > 0) {
            if ($amount === null) {
                return 'We could not confirm the amount paid. Your payment is recorded and our team will confirm it shortly.';
            }

            if ($amount + 0.001 < $expectedAmount) {
                return 'The amount paid (KES ' . number_format($amount) . ') is less than the application fee of KES ' . number_format($expectedAmount) . '.';
            }
        }

        return null;
    }

    /**
     * Match a status result back to the application that asked for it.
     *
     * Matched on the conversation id Safaricom issued for our query, never on
     * the receipt alone, so a result cannot be pointed at another application.
     */
    private function applicationForStatusResult(array $result): ?Application
    {
        $originator = $result['OriginatorConversationID'] ?? null;

        if (blank($originator)) {
            return null;
        }

        return Application::where('status_conversation_id', $originator)->first();
    }

    /**
     * Flatten Result.ResultParameters.ResultParameter into a key => value map.
     */
    private function statusResultParameters(array $result): array
    {
        $items = $result['ResultParameters']['ResultParameter'] ?? [];

        // A lone parameter can arrive unwrapped.
        if (isset($items['Key'])) {
            $items = [$items];
        }

        $flattened = [];

        foreach ($items as $item) {
            if (is_array($item) && isset($item['Key'])) {
                $flattened[$item['Key']] = $item['Value'] ?? null;
            }
        }

        return $flattened;
    }

    /**
     * What Safaricom says about one STK prompt, cached briefly.
     *
     * The cache is what stops a flood of forged callbacks turning into a flood
     * of outbound queries against a rate-limited endpoint. Only definitive
     * answers are cached: an unknown or still-processing state must be
     * re-asked, or a transient outage would be remembered as a verdict.
     */
    private function confirmedStkState(MpesaService $mpesa, string $checkoutRequestId): string
    {
        $key = 'stk-confirm:' . $checkoutRequestId;
        $cached = Cache::get($key);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        try {
            $state = (string) $mpesa->stkQuery($checkoutRequestId)['state'];
        } catch (\Throwable $e) {
            Log::warning('Could not confirm an STK callback: ' . $e->getMessage(), [
                'checkout_request_id' => $checkoutRequestId,
            ]);

            return 'unknown';
        }

        if (in_array($state, ['paid', 'failed'], true)) {
            Cache::put($key, $state, now()->addMinutes(10));
        }

        return $state;
    }

    /**
     * Fallback for an STK callback that never arrived.
     *
     * The page calls this once the poll has waited longer than a callback
     * normally takes. Safaricom knows the outcome even when the webhook was
     * lost, so asking is the difference between a completed application and an
     * applicant who paid and was told "no confirmation yet".
     *
     * Deliberately NOT wired into status(): that is polled every few seconds,
     * and this endpoint is rate limited at Safaricom. A short cache lock keeps
     * a retried tap or a second tab from turning one query into ten.
     */
    public function queryStkPush(Request $request, MpesaService $mpesa, SettleStkPush $settler): JsonResponse
    {
        $application = $this->currentApplication($request);

        if (! $application) {
            return response()->json(['error' => 'No application in this session.'], 404);
        }

        // Nothing to ask about, or nothing left to learn.
        if ($application->payment_status === 'paid' || blank($application->checkout_request_id)) {
            return response()->json([
                'payment_status' => $application->payment_status,
                'reference' => $application->reference,
                'checked' => false,
            ]);
        }

        $lock = 'stk-query:' . $application->id;

        if (Cache::get($lock)) {
            return response()->json([
                'payment_status' => $application->payment_status,
                'reference' => $application->reference,
                'checked' => false,
            ]);
        }

        Cache::put($lock, true, now()->addSeconds(20));

        try {
            $outcome = $mpesa->stkQuery((string) $application->checkout_request_id);
        } catch (\Throwable $e) {
            // The callback may still arrive; never turn a query outage into a
            // failed payment.
            Log::warning('STK query failed: ' . $e->getMessage(), [
                'reference' => $application->reference,
                'checkout_request_id' => $application->checkout_request_id,
            ]);

            return response()->json([
                'payment_status' => $application->payment_status,
                'reference' => $application->reference,
                'checked' => false,
            ]);
        }

        Log::info('STK query fallback', [
            'reference' => $application->reference,
            'checkout_request_id' => $application->checkout_request_id,
            'state' => $outcome['state'],
            'result_code' => $outcome['result_code'],
            'description' => $outcome['description'],
        ]);

        // Shared with the scheduled sweep so the two cannot disagree about what
        // a verdict means.
        $settler->apply($application, $outcome);

        return response()->json([
            'payment_status' => $application->refresh()->payment_status,
            'reference' => $application->reference,
            'checked' => true,
            'state' => $outcome['state'],
        ]);
    }

    public function status(string $reference): JsonResponse
    {
        $application = Application::where('reference', $reference)->firstOrFail();

        return response()->json([
            'payment_status' => $application->payment_status,
            'payment_note' => $application->payment_note,
            'reference' => $application->reference
        ]);
    }
}
