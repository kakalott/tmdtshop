<?php

namespace App\Services;

use App\Models\Order;

class VnpayService
{
    public function createPayment(Order $order): string
    {
        $endpoint = config('vnpay.endpoint');
        $localMock = config('vnpay.local_mock', false);
        $tmnCode = config('vnpay.tmn_code');
        $hashSecret = config('vnpay.hash_secret');
        $returnUrl = config('vnpay.return_url');
        $locale = config('vnpay.locale', 'vn');

        if ($localMock || empty($tmnCode) || empty($hashSecret)) {
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

    public function getResponseDescription(string $code): string
    {
        $descriptions = [
            '00' => 'Giao dịch thành công.',
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới gian lận, giao dịch bất thường).',
            '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
            '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần.',
            '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.',
            '12' => 'Giao dịch không thành công do: Tài khoản/Thẻ của khách hàng bị khóa.',
            '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP). Xin quý khách vui lòng thực hiện lại giao dịch.',
            '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch.',
            '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
            '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Giao dịch không thành công do: Khách hàng nhập sai mật khẩu thanh toán nhiều lần. Xin quý khách vui lòng thực hiện lại giao dịch.',
            '99' => 'Giao dịch không thành công do lỗi hệ thống VNPAY.',
        ];

        return $descriptions[$code] ?? 'Lỗi không xác định (Mã lỗi: ' . $code . ').';
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
