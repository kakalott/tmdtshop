<?php

return [
    'enabled' => env('VIETQR_ENABLED', true),
    'bank_bin' => env('VIETQR_BANK_BIN', 'vietinbank'), // Abbreviation or BIN of recipient bank
    'account_number' => env('VIETQR_ACCOUNT_NUMBER', '102873918239'), // Recipient account number
    'account_name' => env('VIETQR_ACCOUNT_NAME', 'NGUYEN VAN A'), // Recipient account name (uppercase, no accent)
    'template' => env('VIETQR_TEMPLATE', 'compact2'), // compact, compact2, qr_only, print
    'auto_mock' => env('VIETQR_AUTO_MOCK', true), // Automatically complete order on user click for sandbox/simulation
];
