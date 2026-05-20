<?php

namespace App\Services;

use App\Models\Order;

class VnpayService
{
    public function createPayment(Order $order): string
    {
        $endpoint = config('vnpay.endpoint');
        $sandboxMode = config('vnpay.sandbox_mode', true);
        $tmnCode = config('vnpay.tmn_code');
        $hashSecret = config('vnpay.hash_secret');
        $returnUrl = config('vnpay.return_url');
        $locale = config('vnpay.locale', 'vn');

        if ($sandboxMode || empty($tmnCode) || empty($hashSecret)) {
            return route('vnpay.sandbox', ['id' => $order->id]);
        }

        $amount = (int) $order->total_amount * 100;
        $transactionRef = $order->id . '_' . time();
        $orderInfo = "Thanh toan don hang #{$order->id}";
        $createDate = date('YmdHis');
        $ipAddr = request()->ip();

        $data = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => $amount,
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $transactionRef,
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => $locale,
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_IpAddr' => $ipAddr,
            'vnp_CreateDate' => $createDate,
            'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
        ];

        ksort($data);
        $data['vnp_SecureHash'] = hash_hmac('sha512', $this->buildHashData($data), $hashSecret);

        return $endpoint . '?' . http_build_query($data);
    }

    public function verifySignature(array $payload): bool
    {
        if (empty($payload['vnp_SecureHash'])) {
            return false;
        }

        $secret = config('vnpay.hash_secret');
        $data = $payload;
        unset($data['vnp_SecureHash']);
        unset($data['vnp_SecureHashType']);

        ksort($data);
        $computed = hash_hmac('sha512', $this->buildHashData($data), $secret);

        return hash_equals($computed, $payload['vnp_SecureHash']);
    }

    public function isSuccessfulPayment(array $payload): bool
    {
        return ($payload['vnp_ResponseCode'] ?? null) === '00'
            && ($payload['vnp_TransactionStatus'] ?? null) === '00';
    }

    public function amountMatches(Order $order, array $payload): bool
    {
        return (int) ($payload['vnp_Amount'] ?? 0) === ((int) $order->total_amount * 100);
    }

    public function extractOrderId(string $txnRef): ?int
    {
        $parts = explode('_', $txnRef);
        return isset($parts[0]) ? (int) $parts[0] : null;
    }

    private function buildHashData(array $data): string
    {
        $hashData = [];

        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $hashData[] = urlencode($key) . '=' . urlencode($value);
        }

        return implode('&', $hashData);
    }
}
