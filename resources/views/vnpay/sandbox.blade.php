@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white fw-bold fs-4">VNPay Sandbox</div>
                <div class="card-body">
                    <h5 class="mb-3">Đơn hàng #{{ $order->id }}</h5>
                    <p><strong>Số tiền cần thanh toán:</strong> {{ number_format($order->total_amount, 0, ',', '.') }}đ</p>
                    <p class="text-muted">Đây là trang giả lập VNPay sandbox. Nhấn nút bên dưới để mô phỏng thanh toán thành công.</p>
                    <form action="{{ route('vnpay.sandbox.pay', ['id' => $order->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg w-100">Thanh toán thành công</button>
                    </form>
                    <a href="/checkout?order_id={{ $order->id }}" class="btn btn-outline-secondary btn-sm w-100 mt-3">Quay lại trang thanh toán</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
