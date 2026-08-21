<?php

return [
    'env' => env('MPESA_ENV', 'sandbox'),
    
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    
    'passkey' => env('MPESA_PASSKEY'),

    // 'paybill' or 'till'. Decides the STK TransactionType.
    'type' => env('MPESA_TYPE'),

    // Paybill: your paybill number.
    // Till:    your Head Office / store number (this is what the password is built from).
    'shortcode' => env('MPESA_SHORTCODE', '7425208'),

    // Till only: the Till number customers actually pay to (sent as PartyB).
    // Leave unset for paybill — it then falls back to the shortcode.
    'till_number' => env('MPESA_TILL_NUMBER'),
    
    // Must be an absolute, publicly reachable https URL — Safaricom POSTs to it.
    // The matching route is defined in routes/web.php as /mpesa/callback.
    'callback_url' => env('MPESA_CALLBACK_URL') ?: rtrim((string) env('APP_URL'), '/') . '/mpesa/callback',

    /*
    | Transaction Status query, used to confirm a confirmation code an applicant
    | typed in after paying by hand. Leave the initiator blank and the manual
    | payment flow behaves as before: the code is recorded for an admin to
    | reconcile, with no call to Safaricom.
    |
    | The initiator is an API operator created in the M-Pesa org portal with the
    | "Transaction Status Query" role. It is not the STK passkey above.
    */
    'status' => [
        'initiator' => env('MPESA_STATUS_INITIATOR'),
        'initiator_password' => env('MPESA_STATUS_INITIATOR_PASSWORD'),

        // Supply a pre-encrypted credential, or leave it blank to have the
        // initiator password encrypted with the Safaricom public certificate.
        'security_credential' => env('MPESA_STATUS_SECURITY_CREDENTIAL'),
        'certificate_path' => env('MPESA_STATUS_CERTIFICATE_PATH')
            ?: storage_path('app/mpesa/production.cer'),

        // How Safaricom identifies PartyA (the shortcode above).
        // 1 = MSISDN, 2 = Till Number, 4 = Organization Shortcode.
        'identifier_type' => env('MPESA_STATUS_IDENTIFIER_TYPE', '4'),

        // Absolute, publicly reachable https URLs, as with callback_url above.
        // The matching routes are /mpesa/status/result and /mpesa/status/timeout.
        'result_url' => env('MPESA_STATUS_RESULT_URL')
            ?: rtrim((string) env('APP_URL'), '/') . '/mpesa/status/result',
        'timeout_url' => env('MPESA_STATUS_TIMEOUT_URL')
            ?: rtrim((string) env('APP_URL'), '/') . '/mpesa/status/timeout',
    ],
];
