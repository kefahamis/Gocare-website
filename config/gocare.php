<?php

return [
    'notification_email' => env('GOCARE_NOTIFICATION_EMAIL', 'admissions@example.com'),
    'application_fee' => (int) env('GOCARE_APPLICATION_FEE', 1000),

    // One paid application per phone number. Set false where a single phone
    // legitimately applies more than once -- a parent applying for two
    // children -- or while testing, where the tester's own number already has
    // a paid application and would otherwise never get another prompt.
    // A payment still in flight always blocks a second prompt regardless:
    // that is double-charge protection, not a business rule.
    'one_application_per_phone' => filter_var(env('GOCARE_ONE_APPLICATION_PER_PHONE', true), FILTER_VALIDATE_BOOLEAN),
];
