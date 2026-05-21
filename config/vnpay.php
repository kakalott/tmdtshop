<?php

return [
    'enabled' => env('VNPAY_ENABLED', true),
    'sandbox_mode' => env('VNPAY_SANDBOX_MODE', true),
    'local_mock' => env('VNPAY_LOCAL_MOCK', false),
    'endpoint' => env('VNPAY_ENDPOINT', env('VNPAY_SANDBOX_MODE', true) 
        ? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html' 
        : 'https://pay.vnpayment.vn/paymentv2/vpcpay.html'),
    'tmn_code' => env('VNPAY_TMN_CODE', '1SNJ89L8'),
    'hash_secret' => env('VNPAY_HASH_SECRET', 'ODJLXOCEWMFIEJXHJNMZUVFFVRDDXLOT'),
    'return_url' => env('VNPAY_RETURN_URL', env('APP_URL') . '/vnpay/return'),
    'notify_url' => env('VNPAY_NOTIFY_URL', env('APP_URL') . '/vnpay/notify'),
    'locale' => env('VNPAY_LOCALE', 'vn'),
];
