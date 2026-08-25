<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class MpesaService
{
    protected string $baseUrl;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $passkey;
    protected string $shortcode;
    protected string $partyB;
    protected string $transactionType;

    public function __construct()
    {
        $env = strtolower((string) config('mpesa.env'));
        $this->baseUrl = in_array($env, ['production', 'live'], true)
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';

        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->passkey = config('mpesa.passkey');

        // For Buy Goods, BusinessShortCode (and the password) use the Head
        // Office / store number, while PartyB is the Till number. For Paybill
        // the two are the same value.
        $this->shortcode = (string) config('mpesa.shortcode');
        $this->partyB = (string) (config('mpesa.till_number') ?: $this->shortcode);
        $this->transactionType = config('mpesa.type') === 'till'
            ? 'CustomerBuyGoodsOnline'
            : 'CustomerPayBillOnline';
    }

    /**
     * Get M-Pesa Access Token
     */
    /**
     * Daraja tokens last about an hour, so this caches for 55 minutes rather
     * than fetching one per call. Keyed on the credentials so a key rotation
     * or an env switch cannot serve a token minted for the other environment.
     */
    public function getAccessToken(): string
    {
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);

        return Cache::remember(
            'mpesa:token:' . md5($this->baseUrl . '|' . $credentials),
            now()->addMinutes(55),
            fn () => $this->fetchAccessToken($credentials)
        );
    }

    private function fetchAccessToken(string $credentials): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');

        if ($response->successful()) {
            return $response->json('access_token');
        }

        Log::error('M-Pesa Token Generation Failed', [
            'response' => $response->body()
        ]);
        
        throw new Exception('Failed to generate M-Pesa access token');
    }

    /**
     * Initiate STK Push
     */
    public function stkPush(string $phoneNumber, float $amount, string $reference, string $description = 'Payment')
    {
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        // Format phone number to 254...
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        $callbackUrl = (string) config('mpesa.callback_url');

        // Daraja silently rejects anything that is not a publicly reachable
        // absolute https URL, so fail loudly here instead of at Safaricom.
        if (!str_starts_with($callbackUrl, 'https://')) {
            throw new Exception(
                'MPESA_CALLBACK_URL must be an absolute https:// URL reachable from the internet. Got: '
                . ($callbackUrl ?: '(empty)')
            );
        }

        $token = $this->getAccessToken();

        $body = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $this->transactionType,
            'Amount' => round($amount),
            'PartyA' => $phoneNumber,
            'PartyB' => $this->partyB,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => substr($reference, 0, 12),
            'TransactionDesc' => substr($description, 0, 13)
        ];

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $body);

        if ($response->successful()) {
            $json = $response->json();

            // Daraja can answer 200 with a non-zero ResponseCode; that means no
            // prompt was sent, so don't report it back as a success.
            if (isset($json['ResponseCode']) && (string) $json['ResponseCode'] !== '0') {
                Log::error('M-Pesa STK Push rejected', ['request' => $body, 'response' => $json]);
                throw new Exception('M-Pesa rejected the STK push: ' . $response->body());
            }

            return $json;
        }

        Log::error('M-Pesa STK Push Failed', [
            'endpoint' => $this->baseUrl,
            'request' => $body,
            'status' => $response->status(),
            'response' => $response->body()
        ]);

        throw new Exception('Failed to initiate M-Pesa STK push: ' . $response->body());
    }
    
    /**
     * Ask Safaricom what became of one STK prompt.
     *
     * The fallback for a callback that never arrives -- a dropped webhook, a
     * network blip at Safaricom's end -- where the customer paid but the site
     * never heard. Unlike Transaction Status this is synchronous, needs no
     * initiator or security credential, and answers about the prompt rather
     * than about a receipt.
     *
     * Two things make the raw response awkward, and both are normalised here:
     * Daraja answers HTTP 500 with errorCode 500.001.1001 while the customer
     * still has the prompt open, which is "keep waiting", not a failure; and
     * it is rate limited, so callers must not poll it.
     *
     * @return array{state:string,result_code:string,description:string}
     *         state is paid|failed|pending|unknown
     */
    public function stkQuery(string $checkoutRequestId): array
    {
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $response = Http::withToken($this->getAccessToken())
            ->post($this->baseUrl . '/mpesa/stkpushquery/v1/query', [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ]);

        $json = $response->json();

        if (! is_array($json)) {
            Log::warning('M-Pesa STK query returned no JSON', [
                'checkout_request_id' => $checkoutRequestId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['state' => 'unknown', 'result_code' => '', 'description' => ''];
        }

        // "The transaction is being processed" -- the prompt is still open on
        // the phone. Daraja sends this as an error, but it is the one answer
        // that means nothing has gone wrong.
        $errorCode = (string) ($json['errorCode'] ?? '');

        if ($errorCode === '500.001.1001') {
            return [
                'state' => 'pending',
                'result_code' => $errorCode,
                'description' => (string) ($json['errorMessage'] ?? 'Transaction is being processed'),
            ];
        }

        if ($errorCode !== '') {
            Log::warning('M-Pesa STK query rejected', [
                'checkout_request_id' => $checkoutRequestId,
                'response' => $json,
            ]);

            return [
                'state' => 'unknown',
                'result_code' => $errorCode,
                'description' => (string) ($json['errorMessage'] ?? ''),
            ];
        }

        $resultCode = (string) ($json['ResultCode'] ?? '');
        $description = (string) ($json['ResultDesc'] ?? '');

        if ($resultCode === '0') {
            $state = 'paid';
        } elseif ($resultCode === '') {
            $state = 'unknown';
        } else {
            // 1032 cancelled, 1037 timed out, 1 insufficient balance, and the
            // rest -- all of them mean this prompt will never be paid.
            $state = 'failed';
        }

        return ['state' => $state, 'result_code' => $resultCode, 'description' => $description];
    }

    /**
     * Is the Transaction Status query configured?
     *
     * When it is not, the manual payment flow falls back to simply recording
     * the applicant's code, exactly as it behaved before verification existed.
     */
    public function statusQueryConfigured(): bool
    {
        $status = config('mpesa.status');

        if (blank($status['initiator'] ?? null)) {
            return false;
        }

        if (blank($status['security_credential'] ?? null) && blank($status['initiator_password'] ?? null)) {
            return false;
        }

        return str_starts_with((string) ($status['result_url'] ?? ''), 'https://');
    }

    /**
     * Ask Safaricom to confirm a single M-Pesa confirmation code.
     *
     * This endpoint is asynchronous: the POST is only an acknowledgement, and
     * the transaction details arrive later on the configured ResultURL. The
     * returned OriginatorConversationID is what ties that result back to us.
     *
     * @see https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query
     */
    public function transactionStatus(string $transactionCode, string $reference)
    {
        $status = config('mpesa.status');

        $resultUrl = (string) $status['result_url'];
        $timeoutUrl = (string) $status['timeout_url'];

        // Same rule as the STK callback: Daraja silently drops anything that is
        // not a publicly reachable absolute https URL.
        foreach (['MPESA_STATUS_RESULT_URL' => $resultUrl, 'MPESA_STATUS_TIMEOUT_URL' => $timeoutUrl] as $name => $url) {
            if (!str_starts_with($url, 'https://')) {
                throw new Exception(
                    $name . ' must be an absolute https:// URL reachable from the internet. Got: ' . ($url ?: '(empty)')
                );
            }
        }

        $token = $this->getAccessToken();

        $body = [
            'Initiator' => $status['initiator'],
            'SecurityCredential' => $this->securityCredential(),
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => strtoupper(trim($transactionCode)),
            // For Buy Goods this is the Head Office / store number, as with the
            // STK password above — not the till customers pay to.
            'PartyA' => $this->shortcode,
            'IdentifierType' => (string) $status['identifier_type'],
            'ResultURL' => $resultUrl,
            'QueueTimeOutURL' => $timeoutUrl,
            'Remarks' => 'Application fee verification',
            // The whole reference: Daraja echoes this back in
            // Result.ReferenceData, and the 12-char cut used to drop the
            // unique tail, leaving every attempt on a given day identical.
            'Occasion' => substr($reference, 0, 100),
        ];

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/mpesa/transactionstatus/v1/query', $body);

        if ($response->successful()) {
            $json = $response->json();

            // As with the STK push, a 200 can still carry a rejection.
            if (isset($json['ResponseCode']) && (string) $json['ResponseCode'] !== '0') {
                Log::error('M-Pesa transaction status rejected', [
                    'request' => $this->redact($body),
                    'response' => $json,
                ]);

                throw new Exception('M-Pesa rejected the transaction status query: ' . $response->body());
            }

            return $json;
        }

        Log::error('M-Pesa Transaction Status Failed', [
            'endpoint' => $this->baseUrl,
            'request' => $this->redact($body),
            'status' => $response->status(),
            'response' => $response->body(),
        ]);

        throw new Exception('Failed to query M-Pesa transaction status: ' . $response->body());
    }

    /**
     * The initiator password encrypted with the Safaricom public certificate.
     */
    protected function securityCredential(): string
    {
        $status = config('mpesa.status');

        if (filled($status['security_credential'] ?? null)) {
            return (string) $status['security_credential'];
        }

        $certificatePath = (string) $status['certificate_path'];

        if (!is_readable($certificatePath)) {
            throw new Exception('The Safaricom public certificate could not be read at: ' . ($certificatePath ?: '(empty)'));
        }

        $publicKey = openssl_pkey_get_public((string) file_get_contents($certificatePath));

        if ($publicKey === false) {
            throw new Exception('The Safaricom public certificate at ' . $certificatePath . ' is not a valid certificate.');
        }

        $encrypted = '';

        if (!openssl_public_encrypt((string) $status['initiator_password'], $encrypted, $publicKey, OPENSSL_PKCS1_PADDING)) {
            throw new Exception('Could not build the M-Pesa security credential.');
        }

        return base64_encode($encrypted);
    }

    /**
     * Keep the encrypted credential out of the logs.
     */
    protected function redact(array $body): array
    {
        unset($body['SecurityCredential']);

        return $body;
    }

    /**
     * Helper to format phone number
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }
        
        return $phone;
    }
}
