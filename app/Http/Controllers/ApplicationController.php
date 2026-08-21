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
     * Record an applicant's "I have paid" claim after a manual M-Pesa payment.
     *
     * The click is never treated as proof: without a Daraja callback for this
     * payment there is nothing to check the code against, so the claim is
     * parked as awaiting_verification for an admin to reconcile against the
     * M-Pesa statement.
     */
    public function confirmManualPayment(Request $request): JsonResponse
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

        // A confirmed callback outranks a typed code, so never walk it back.
        if ($application->payment_status !== 'paid') {
            $application->update([
                'payment_status' => 'awaiting_verification',
                'mpesa_receipt' => strtoupper($validated['transaction_code']),
            ]);
        }

        Log::info('Manual M-Pesa payment claimed', [
            'reference' => $application->reference,
            'code' => strtoupper($validated['transaction_code']),
        ]);

        return response()->json([
            'message' => 'Thank you. We have recorded your payment and will confirm it shortly.',
            'reference' => $application->reference,
            'payment_status' => $application->payment_status,
        ]);
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

    public function status(string $reference): JsonResponse
    {
        $application = Application::where('reference', $reference)->firstOrFail();

        return response()->json([
            'payment_status' => $application->payment_status,
            'reference' => $application->reference
        ]);
    }
}
