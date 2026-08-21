<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationReceived;
use App\Models\Application;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        try {
            $result = $mpesa->stkPush($application->phone, $application->amount, $application->reference, 'Application Fee');

            if (isset($result['CheckoutRequestID'])) {
                $application->update(['checkout_request_id' => $result['CheckoutRequestID']]);
            }

            return response()->json([
                'message' => 'Payment initiated. Please enter your M-Pesa PIN.',
                'reference' => $application->reference,
                'checkout_request_id' => $result['CheckoutRequestID'] ?? null
            ]);
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
    public function confirmManualPayment(Request $request, MpesaService $mpesa): JsonResponse
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

        // Someone else's confirmed code cannot be borrowed.
        $claimedElsewhere = Application::where('mpesa_receipt', $code)
            ->where('payment_status', 'paid')
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

        $application->update([
            'payment_status' => 'awaiting_verification',
            'mpesa_receipt' => $code,
            'payment_note' => null,
        ]);

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
     * The M-Pesa step already created a row for this browser; this fills in the
     * rest of the answers. If the applicant never triggered a payment there is
     * no row yet, so one is created rather than losing the submission.
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

        if ($application) {
            $application->update([
                'data' => array_merge($application->data ?? [], $validated['fields']),
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        } else {
            $application = Application::create([
                'reference' => $this->newReference(),
                'status' => 'submitted',
                'phone' => $validated['phone'],
                'amount' => (int) config('gocare.application_fee'),
                'payment_status' => 'pending',
                'data' => $validated['fields'],
                'submitted_at' => now(),
            ]);

            $request->session()->put('application_reference', $application->reference);
        }

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

    public function mpesaCallback(Request $request)
    {
        Log::info('M-Pesa Callback Received', $request->all());

        $payload = json_decode($request->getContent(), true);

        if (!isset($payload['Body']['stkCallback'])) {
            return response()->json(['message' => 'Invalid payload']);
        }

        $callback = $payload['Body']['stkCallback'];
        $checkoutRequestId = $callback['CheckoutRequestID'];
        $resultCode = $callback['ResultCode'];

        $application = Application::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$application) {
            Log::warning("No application found for CheckoutRequestID: {$checkoutRequestId}");
            return response()->json(['message' => 'Not found']);
        }

        if ($resultCode == 0) {
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

            $application->update([
                'payment_status' => 'paid',
                'mpesa_receipt' => $receipt
            ]);

            // Send email only when paid
            Mail::to(config('gocare.notification_email'))->send(new ApplicationReceived($application));

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
    public function mpesaStatusCallback(Request $request)
    {
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

        $failure = $this->manualPaymentRejection(
            resultCode: (string) ($result['ResultCode'] ?? ''),
            transactionStatus: (string) ($parameters['TransactionStatus'] ?? ''),
            amount: isset($parameters['Amount']) ? (float) $parameters['Amount'] : null,
            creditParty: (string) ($parameters['CreditPartyName'] ?? ''),
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
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $application->update([
            'payment_status' => 'paid',
            'payment_note' => null,
            'mpesa_receipt' => $parameters['ReceiptNo'] ?? $application->mpesa_receipt,
        ]);

        // Matches the STK path: the notification email goes out once paid.
        Mail::to(config('gocare.notification_email'))->send(new ApplicationReceived($application));

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Safaricom's Transaction Status QueueTimeOutURL callback.
     */
    public function mpesaStatusTimeout(Request $request)
    {
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
            return 'We could not find this confirmation code on M-Pesa. Please check the code on your message.';
        }

        if ($transactionStatus !== '' && ! in_array(strtolower($transactionStatus), ['completed', 'success'], true)) {
            return 'M-Pesa reports this transaction as "' . $transactionStatus . '", so it cannot be accepted yet.';
        }

        // On Buy Goods the credit party can carry either the till customers pay
        // to or the head office / store number behind it, so accept both.
        $ourNumbers = array_filter([
            (string) config('mpesa.shortcode'),
            (string) config('mpesa.till_number'),
        ]);

        if ($ourNumbers !== [] && $creditParty !== '') {
            $paidToUs = array_filter($ourNumbers, fn (string $number) => str_contains($creditParty, $number));

            if ($paidToUs === []) {
                return 'This payment was not made to our M-Pesa number. Please check the till number and try again.';
            }
        }

        if ($expectedAmount > 0 && $amount !== null && $amount + 0.001 < $expectedAmount) {
            return 'The amount paid (KES ' . number_format($amount) . ') is less than the application fee of KES ' . number_format($expectedAmount) . '.';
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
