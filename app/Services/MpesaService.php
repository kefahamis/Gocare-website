<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
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
    public function getAccessToken(): string
    {
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        
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
