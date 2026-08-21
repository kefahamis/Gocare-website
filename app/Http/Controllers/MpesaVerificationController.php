<?php

namespace App\Http\Controllers;

use App\Models\MpesaVerification;
use App\Services\Mpesa\MpesaException;
use App\Services\Mpesa\TransactionStatusClient;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MpesaVerificationController extends Controller
{
    public function __construct(private readonly TransactionStatusClient $client) {}

    /**
     * Submit an M-Pesa confirmation code to Safaricom for verification.
     *
     * Daraja answers asynchronously, so this only kicks off the query; the
     * browser polls status() until the ResultURL callback lands.
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^[A-Za-z0-9]{10}$/'],
            'phone' => ['nullable', 'string', 'regex:/^254[17][0-9]{8}$/'],
            'reference' => ['nullable', 'string', 'max:64'],
        ], [
            'code.regex' => 'An M-Pesa confirmation code is 10 letters and numbers, e.g. SHK3XY8ZT9.',
            'phone.regex' => 'Enter the M-Pesa number in the format 2547XXXXXXXX.',
        ]);

        $code = Str::upper($validated['code']);

        // The applicant's reference once they have one, otherwise their session.
        $claimKey = $validated['reference'] ?? $request->session()->getId();

        $existing = MpesaVerification::where('transaction_code', $code)
            ->orderByDesc('id')
            ->first();

        // A code already confirmed for another applicant cannot be reused.
        if ($existing?->status === MpesaVerification::STATUS_CONFIRMED) {
            if (filled($existing->claim_key) && $existing->claim_key !== $claimKey) {
                return response()->json([
                    'status' => MpesaVerification::STATUS_FAILED,
                    'message' => 'This confirmation code has already been used for another application.',
                ], 422);
            }

            return response()->json($this->present($existing));
        }

        // Avoid re-querying Safaricom while an identical request is still in flight.
        if ($existing?->status === MpesaVerification::STATUS_PENDING && $existing->created_at?->gt(now()->subMinutes(2))) {
            return response()->json($this->present($existing));
        }

        $verification = MpesaVerification::create([
            'public_token' => (string) Str::uuid(),
            'transaction_code' => $code,
            'phone' => $validated['phone'] ?? null,
            'application_reference' => $validated['reference'] ?? null,
            'claim_key' => $claimKey,
            'status' => MpesaVerification::STATUS_PENDING,
        ]);

        try {
            $result = $this->client->query(
                transactionCode: $code,
                remarks: 'GoCare application fee verification',
                occasion: $validated['reference'] ?? 'ApplicationFee',
            );
        } catch (MpesaException $e) {
            $verification->update([
                'status' => MpesaVerification::STATUS_FAILED,
                'result_description' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => MpesaVerification::STATUS_FAILED,
                'token' => $verification->public_token,
                'message' => $e->getMessage(),
            ], 422);
        }

        $verification->update([
            'conversation_id' => $result['ConversationID'],
            'originator_conversation_id' => $result['OriginatorConversationID'],
            'request_payload' => $result['payload'],
        ]);

        return response()->json($this->present($verification));
    }

    /**
     * Poll the outcome of a verification the browser started.
     */
    public function status(string $token): JsonResponse
    {
        $verification = MpesaVerification::where('public_token', $token)->firstOrFail();

        // Safaricom normally calls back within seconds; stop waiting after two minutes.
        if ($verification->status === MpesaVerification::STATUS_PENDING
            && $verification->created_at?->lt(now()->subMinutes(2))) {
            $verification->update([
                'status' => MpesaVerification::STATUS_TIMED_OUT,
                'result_description' => 'Safaricom did not respond in time. Please try again or contact admissions.',
            ]);
        }

        return response()->json($this->present($verification));
    }

    /**
     * Safaricom ResultURL callback carrying the actual transaction status.
     */
    public function result(Request $request, ?string $secret = null): JsonResponse
    {
        $this->assertCallbackSecret($secret);

        $result = $request->input('Result', []);

        $verification = $this->locate($result);

        if (! $verification) {
            Log::warning('Unmatched M-Pesa transaction status result.', ['payload' => $request->all()]);

            return $this->acknowledge();
        }

        if ($verification->isSettled()) {
            return $this->acknowledge();
        }

        $parameters = $this->parameters($result);
        $resultCode = (string) ($result['ResultCode'] ?? '');
        $transactionStatus = (string) ($parameters['TransactionStatus'] ?? '');
        $amount = isset($parameters['Amount']) ? (float) $parameters['Amount'] : null;
        $creditParty = (string) ($parameters['CreditPartyName'] ?? '');

        $verification->fill([
            'result_code' => $resultCode,
            'result_description' => $result['ResultDesc'] ?? null,
            'amount' => $amount,
            'receipt_party' => $creditParty ?: null,
            'transaction_status' => $transactionStatus ?: null,
            'transaction_completed_at' => $this->parseTimestamp($parameters['FinalisedTime'] ?? $parameters['InitiatedTime'] ?? null),
            'result_payload' => $result,
        ]);

        if ($failure = $this->rejectionReason($resultCode, $transactionStatus, $amount, $creditParty)) {
            $verification->fill([
                'status' => MpesaVerification::STATUS_FAILED,
                'result_description' => $failure,
            ])->save();

            return $this->acknowledge();
        }

        $verification->fill([
            'status' => MpesaVerification::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ])->save();

        return $this->acknowledge();
    }

    /**
     * Safaricom QueueTimeOutURL callback.
     */
    public function timeout(Request $request, ?string $secret = null): JsonResponse
    {
        $this->assertCallbackSecret($secret);

        $verification = $this->locate($request->input('Result', []));

        if ($verification && ! $verification->isSettled()) {
            $verification->update([
                'status' => MpesaVerification::STATUS_TIMED_OUT,
                'result_description' => 'Safaricom timed out while checking this code. Please try again shortly.',
                'result_payload' => $request->all(),
            ]);
        }

        return $this->acknowledge();
    }

    /**
     * Safaricom does not sign its callbacks, so when a shared secret is
     * configured it must be present in the callback URL.
     */
    private function assertCallbackSecret(?string $secret): void
    {
        $expected = (string) config('application_payments.daraja.callback_secret');

        if ($expected === '') {
            return;
        }

        if ($secret === null || ! hash_equals($expected, $secret)) {
            Log::warning('M-Pesa callback rejected: bad or missing secret.');

            abort(404);
        }
    }

    /**
     * Why this transaction cannot be accepted as an application fee payment,
     * or null when it checks out.
     */
    private function rejectionReason(string $resultCode, string $transactionStatus, ?float $amount, string $creditParty): ?string
    {
        if ($resultCode !== '0') {
            return 'We could not find this confirmation code on M-Pesa. Please check it and try again.';
        }

        if ($transactionStatus !== '' && ! in_array(Str::lower($transactionStatus), ['completed', 'success'], true)) {
            return 'M-Pesa reports this transaction as "'.$transactionStatus.'", so it cannot be accepted yet.';
        }

        // On a till setup the credit party carries the till or the store number,
        // so accept either rather than only the Transaction Status PartyA.
        $ourNumbers = array_filter([
            (string) config('application_payments.daraja.shortcode'),
            (string) config('application_payments.daraja.till_number'),
        ]);

        if ($ourNumbers !== [] && $creditParty !== '') {
            $paidToUs = array_filter($ourNumbers, fn (string $number) => str_contains($creditParty, $number));

            if ($paidToUs === []) {
                $target = config('application_payments.manual.type') === 'till' ? 'till' : 'paybill';

                return "This payment was not made to the GoCare {$target}. Please check the number you paid to and try again.";
            }
        }

        $fee = (int) config('application_payments.fee');

        if ($fee > 0 && $amount !== null && $amount + 0.001 < $fee) {
            return 'The amount paid (KES '.number_format($amount, 0).') is less than the application fee of KES '.number_format($fee).'.';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function locate(array $result): ?MpesaVerification
    {
        $parameters = $this->parameters($result);

        $originator = $result['OriginatorConversationID'] ?? ($parameters['OriginatorConversationID'] ?? null);
        $conversation = $result['ConversationID'] ?? ($parameters['ConversationID'] ?? null);
        $transactionId = $result['TransactionID'] ?? ($parameters['ReceiptNo'] ?? null);

        if (blank($originator) && blank($conversation) && blank($transactionId)) {
            return null;
        }

        return MpesaVerification::query()
            ->where(function ($query) use ($originator, $conversation, $transactionId) {
                $query->when($originator, fn ($q) => $q->orWhere('originator_conversation_id', $originator))
                    ->when($conversation, fn ($q) => $q->orWhere('conversation_id', $conversation))
                    ->when($transactionId, fn ($q) => $q->orWhere(function ($inner) use ($transactionId) {
                        $inner->where('transaction_code', Str::upper($transactionId))
                            ->where('status', MpesaVerification::STATUS_PENDING);
                    }));
            })
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Flatten Result.ResultParameters.ResultParameter into a key => value map.
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function parameters(array $result): array
    {
        $items = $result['ResultParameters']['ResultParameter'] ?? [];

        // A lone parameter can arrive unwrapped.
        if (isset($items['Key'])) {
            $items = [$items];
        }

        return collect($items)
            ->filter(fn ($item) => is_array($item) && isset($item['Key']))
            ->mapWithKeys(fn ($item) => [$item['Key'] => $item['Value'] ?? null])
            ->all();
    }

    private function parseTimestamp(mixed $value): ?CarbonImmutable
    {
        if (blank($value)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('YmdHis', (string) $value) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function present(MpesaVerification $verification): array
    {
        return [
            'status' => $verification->status,
            'token' => $verification->public_token,
            'code' => $verification->transaction_code,
            'amount' => $verification->amount !== null ? (float) $verification->amount : null,
            'paid_at' => $verification->transaction_completed_at?->toDateString(),
            'message' => match ($verification->status) {
                MpesaVerification::STATUS_CONFIRMED => 'Payment confirmed by M-Pesa.',
                MpesaVerification::STATUS_PENDING => 'Checking this code with M-Pesa...',
                default => $verification->result_description ?: 'We could not confirm this code.',
            },
        ];
    }

    private function acknowledge(): JsonResponse
    {
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }
}
