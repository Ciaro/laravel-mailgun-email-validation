<?php

return [
    // Private Mailgun API key.
    'key' => env('MAILGUN_API_KEY', ''),

    // Use api.eu.mailgun.net instead if you are in the EU region.
    'endpoint' => env('MAILGUN_VALIDATE_ENDPOINT', 'https://api.mailgun.net/v3/address/private/validate'),

    // Disable the SSL certificate check on localhost env.
    'verifySsl' => env('MAILGUN_API_VERIFY_SSL', true),
];
