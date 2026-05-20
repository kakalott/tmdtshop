<?php

return [
    'enabled' => env('MOMO_ENABLED', false),
    'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
    'partner_code' => env('MOMO_PARTNER_CODE', ''),
    'access_key' => env('MOMO_ACCESS_KEY', ''),
    'secret_key' => env('MOMO_SECRET_KEY', ''),
    'return_url' => env('MOMO_RETURN_URL', env('APP_URL') . '/momo/return'),
    'notify_url' => env('MOMO_NOTIFY_URL', env('APP_URL') . '/momo/notify'),
    'request_type' => env('MOMO_REQUEST_TYPE', 'captureWallet'),
    'extra_data' => env('MOMO_EXTRA_DATA', ''),
];
