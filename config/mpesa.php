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
];
