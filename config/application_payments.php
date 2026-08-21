<?php

/*
|--------------------------------------------------------------------------
| Application Fee Payments
|--------------------------------------------------------------------------
|
| Settings for the "pay manually instead" option on the application form,
| where the applicant pays the fee themselves and then enters the M-Pesa
| confirmation code for the site to confirm with Safaricom.
|
| This file deliberately owns no STK push settings. It reads the same env
| values the STK push already uses, so there is still one set of Daraja
| credentials, but it keeps its own config file so nothing here can clash
| with the STK push configuration.
|
*/

return [

    'fee' => (int) env('GOCARE_APPLICATION_FEE', 1000),

    /*
    | What the applicant is told to do. Driven by MPESA_TYPE so these
    | instructions always match the shortcode the STK push is set up for.
    */
    'manual' => [
        'type' => env('MPESA_TYPE', 'paybill'),
        'till' => env('MPESA_TILL_NUMBER'),
        'paybill' => env('GOCARE_MPESA_PAYBILL') ?: env('MPESA_SHORTCODE'),
        'account_hint' => env('GOCARE_MPESA_ACCOUNT_HINT', 'Your ID Number'),
    ],

    /*
    | Daraja Transaction Status API, used to confirm a confirmation code.
    | The consumer key/secret and shortcode are shared with the STK push;
    | the initiator credentials below are specific to this query.
    */
    'daraja' => [
        'env' => env('MPESA_ENV', 'sandbox'),
        'base_url' => rtrim(env('MPESA_BASE_URL') ?: (
            env('MPESA_ENV') === 'live' ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke'
        ), '/'),

        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),

        // Head office / store number: the PartyA of the query, not the number
        // customers dial. The till they actually pay to is under 'manual' above.
        'shortcode' => env('MPESA_SHORTCODE'),
        'till_number' => env('MPESA_TILL_NUMBER'),

        // How Safaricom identifies PartyA.
        // 1 = MSISDN, 2 = Till Number, 4 = Organization Shortcode.
        'identifier_type' => env('MPESA_IDENTIFIER_TYPE', '4'),

        // API operator with the "Transaction Status Query" role. This is not
        // the STK passkey, which this feature never reads.
        'initiator' => env('MPESA_STATUS_INITIATOR'),
        'initiator_password' => env('MPESA_STATUS_INITIATOR_PASSWORD'),

        // Either supply a pre-encrypted credential, or leave it null and let the
        // client encrypt the initiator password with the Safaricom public cert.
        'security_credential' => env('MPESA_STATUS_SECURITY_CREDENTIAL'),
        'certificate_path' => env('MPESA_STATUS_CERTIFICATE_PATH') ?: storage_path('app/mpesa/production.cer'),

        // Publicly reachable HTTPS callbacks Safaricom posts the result to.
        'result_url' => env('MPESA_STATUS_RESULT_URL'),
        'timeout_url' => env('MPESA_STATUS_TIMEOUT_URL'),

        // Safaricom does not sign its callbacks, so this secret must appear in
        // the callback URLs above or the endpoints answer 404.
        'callback_secret' => env('MPESA_STATUS_CALLBACK_SECRET'),

        'request_timeout' => (int) env('MPESA_STATUS_REQUEST_TIMEOUT', 30),
    ],

];
