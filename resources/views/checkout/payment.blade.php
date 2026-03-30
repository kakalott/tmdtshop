@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-primary border-2 text-center">
                <div class="card-header bg-primary text-white fw-bold fs-4 py-3">
                    THANH TOÁN QUÉT MÃ QR
                </div>
                <div class="card-body mt-3 mb-3">
                    <h5 class="fw-bold text-dark">Mã Đơn Hàng: #{{ $order->id }}</h5>
                    <p class="fs-5 mb-4">Tổng số tiền cần thanh toán: <br><span class="text-danger fw-bold fs-2">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span></p>

                    <img src="https://img.vietqr.io/image/MB-0987654321-print.png?amount={{ $order->total_amount }}&addInfo=Thanh toan don hang {{ $order->id }}&accountName=TONG KHO NHUA" 
                         alt="Mã QR Thanh Toán" class="img-fluid border p-2 rounded shadow-sm mb-4" style="max-width: 300px;">
                    
                    <p class="text-muted fst-italic mb-4">Sử dụng App Ngân hàng hoặc Momo để quét mã phía trên. <br>Nội dung chuyển khoản: <strong>Thanh toan don hang {{ $order->id }}</strong></p>

                    <a href="/" class="btn btn-outline-secondary fw-bold px-4 mt-2">
                         Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection