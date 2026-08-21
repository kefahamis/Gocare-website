<?php

namespace App\Services\Mpesa;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin client over the Safaricom Daraja Transaction Status API.
 *
 * The query endpoint is asynchronous: the POST only acknowledges the request,
 * while the transaction details are delivered later to the configured ResultURL.
 *
 * @see https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query
 */
class TransactionStatusClient
{
    private const OAUTH_PATH = '/oauth/v1/generate?grant_type=client_credentials';

    private const QUERY_PATH = '/mpesa/transactionstatus/v1/query';

    // Namespaced so it cannot collide with any token the STK push caches.
    private const TOKEN_CACHE_KEY = 'application_payments.mpesa_status_token';

    /**
     * Ask Safaricom to confirm a single M-Pesa confirmation code.
     *
     * @return array{ConversationID: ?string, OriginatorConversationID: ?string, ResponseCode: ?string, ResponseDescription: ?string, payload: array<string, mixed>}
     *
     * @throws MpesaException
     */
    public function query(string $transactionCode, string $remarks = 'Application fee verification', ?string $occasion = null): array
    {
        $this->assertConfigured();

        $config = $this->config();

        $payload = [
            'Initiator' => $config['initiator'],
            'SecurityCredential' => $this->securityCredential(),
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => strtoupper(trim($transactionCode)),
            'PartyA' => $config['shortcode'],
            'IdentifierType' => (string) $config['identifier_type'],
            'ResultURL' => $config['result_url'],
            'QueueTimeOutURL' => $config['timeout_url'],
            'Remarks' => $remarks,
            'Occasion' => $occasion ?: 'ApplicationFee',
        ];

        $response = Http::withToken($this->accessToken())
            ->timeout($config['request_timeout'])
            ->acceptJson()
            ->asJson()
            ->post($config['base_url'].self::QUERY_PATH, $payload);

        if ($response->failed()) {
            Log::warning('M-Pesa transaction status query rejected.', [
                'transaction_code' => $payload['TransactionID'],
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new MpesaException(
                $response->json('errorMessage') ?: 'Safaricom rejected the verification request.'
            );
        }

        $body = $response->json() ?? [];

        if (($body['ResponseCode'] ?? null) !== '0') {
            throw new MpesaException(
                $body['ResponseDescription'] ?? 'Safaricom did not accept the verification request.'
            );
        }

        return [
            'ConversationID' => $body['ConversationID'] ?? null,
            'OriginatorConversationID' => $body['OriginatorConversationID'] ?? null,
            'ResponseCode' => $body['ResponseCode'] ?? null,
            'ResponseDescription' => $body['ResponseDescription'] ?? null,
            'payload' => $this->redact($payload),
        ];
    }

    public function isConfigured(): bool
    {
        return $this->missingConfigKeys() === [];
    }

    /**
     * @throws MpesaException
     */
    private function assertConfigured(): void
    {
        $missing = $this->missingConfigKeys();

        if ($missing !== []) {
            // The applicant cannot act on this, so keep the detail in the log.
            Log::error('M-Pesa verification is not configured.', ['missing' => $missing]);

            throw new MpesaException('Online verification is temporarily unavailable. Please contact admissions with your confirmation code.');
        }
    }

    /**
     * @return list<string>
     */
    private function missingConfigKeys(): array
    {
        $config = $this->config();

        $required = ['consumer_key', 'consumer_secret', 'shortcode', 'initiator', 'result_url', 'timeout_url'];

        $missing = array_values(array_filter($required, fn (string $key) => blank($config[$key])));

        if (blank($config['security_credential']) && blank($config['initiator_password'])) {
            $missing[] = 'security_credential';
        }

        return $missing;
    }

    /**
     * @throws MpesaException
     */
    private function accessToken(): string
    {
        $config = $this->config();

        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::withBasicAuth($config['consumer_key'], $config['consumer_secret'])
            ->timeout($config['request_timeout'])
            ->acceptJson()
            ->get($config['base_url'].self::OAUTH_PATH);

        if ($response->failed() || blank($response->json('access_token'))) {
            Log::warning('M-Pesa OAuth token request failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new MpesaException('Could not authenticate with Safaricom. Please try again shortly.');
        }

        $token = (string) $response->json('access_token');

        // Daraja tokens live for 3600s; expire ours a minute early to avoid races.
        $ttl = max(60, (int) $response->json('expires_in', 3600) - 60);

        Cache::put(self::TOKEN_CACHE_KEY, $token, $ttl);

        return $token;
    }

    /**
     * The initiator password encrypted with the Safaricom public certificate.
     *
     * @throws MpesaException
     */
    private function securityCredential(): string
    {
        $config = $this->config();

        if (filled($config['security_credential'])) {
            return $config['security_credential'];
        }

        $certificatePath = $config['certificate_path'];

        if (blank($certificatePath) || ! is_readable($certificatePath)) {
            throw new MpesaException('The Safaricom public certificate could not be read.');
        }

        $publicKey = openssl_pkey_get_public(file_get_contents($certificatePath));

        if ($publicKey === false) {
            throw new MpesaException('The Safaricom public certificate is invalid.');
        }

        $encrypted = '';

        if (! openssl_public_encrypt($config['initiator_password'], $encrypted, $publicKey, OPENSSL_PKCS1_PADDING)) {
            throw new MpesaException('Could not build the M-Pesa security credential.');
        }

        return base64_encode($encrypted);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function redact(array $payload): array
    {
        unset($payload['SecurityCredential']);

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function config(): array
    {
        return config('application_payments.daraja');
    }
}
