<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Order;

class MomoService
{
    public function createPayment(Order $order): string
    {
        if (!config('momo.enabled')) {
            throw new \RuntimeException('Momo payment is not enabled.');
        }

        $endpoint = config('momo.endpoint');
        $partnerCode = config('momo.partner_code');
        $accessKey = config('momo.access_key');
        $secretKey = config('momo.secret_key');
        $returnUrl = config('momo.return_url');
        $notifyUrl = config('momo.notify_url');
        $requestType = config('momo.request_type', 'captureWallet');
        $extraData = config('momo.extra_data', '');

        if (empty($partnerCode) || empty($accessKey) || empty($secretKey) || empty($returnUrl) || empty($notifyUrl)) {
            throw new \RuntimeException('Momo configuration is incomplete. Please set MOMO_PARTNER_CODE, MOMO_ACCESS_KEY, MOMO_SECRET_KEY, MOMO_RETURN_URL, and MOMO_NOTIFY_URL.');
        }

        $orderId = $order->id . '_' . time();
        $requestId = $orderId;
        $amount = (string) max(0, $order->total_amount);
        $orderInfo = "Thanh toan don hang #{$order->id}";

        $rawHash = sprintf(
            'accessKey=%s&amount=%s&extraData=%s&ipnUrl=%s&orderId=%s&orderInfo=%s&partnerCode=%s&redirectUrl=%s&requestId=%s&requestType=%s',
            $accessKey,
            $amount,
            $extraData,
            $notifyUrl,
            $orderId,
            $orderInfo,
            $partnerCode,
            $returnUrl,
            $requestId,
            $requestType
        );

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $payload = [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'returnUrl' => $returnUrl,
            'notifyUrl' => $notifyUrl,
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
            'lang' => 'vi',
        ];

        $response = Http::acceptJson()->post($endpoint, $payload)->json();

        if (!is_array($response) || !isset($response['resultCode'])) {
            throw new \RuntimeException('Momo response invalid.');
        }

        if ($response['resultCode'] != 0) {
            $message = $response['localMessage'] ?? $response['message'] ?? 'Momo payment request failed';
            throw new \RuntimeException($message);
        }

        return $response['payUrl'] ?? throw new \RuntimeException('Momo payUrl not returned.');
    }

    public function verifySignature(array $payload): bool
    {
        $secretKey = config('momo.secret_key');

        if (empty($payload['signature'])) {
            return false;
        }

        $fields = [
            'accessKey',
            'amount',
            'extraData',
            'message',
            'orderId',
            'orderInfo',
            'orderType',
            'partnerCode',
            'payType',
            'requestId',
            'responseTime',
            'resultCode',
            'transId',
        ];

        $rawHash = '';
        foreach ($fields as $key) {
            if (isset($payload[$key])) {
                $rawHash .= ($rawHash === '' ? '' : '&') . $key . '=' . $payload[$key];
            }
        }

        $computed = hash_hmac('sha256', $rawHash, $secretKey);

        return hash_equals($computed, $payload['signature']);
    }

    public function extractOrderId(string $momoOrderId): ?int
    {
        $parts = explode('_', $momoOrderId);
        return isset($parts[0]) ? (int) $parts[0] : null;
    }
}
