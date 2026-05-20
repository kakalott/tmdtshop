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

                    <div class="alert alert-info mb-4">
                        <strong>VNPay</strong><br>
                        Đơn hàng sẽ tự chuyển sang hoàn thành khi VNPay xác nhận giao dịch thành công.
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($payUrl)
                        <div class="mb-4">
                            <p class="fw-bold">Quét mã QR bên dưới để thanh toán qua VNPay:</p>
                            <img src="https://api.qrserver.com/v1/create-qr-code?size=300x300&data={{ urlencode($payUrl) }}" alt="QR Payment" class="img-fluid border rounded shadow-sm mb-3">
                            <p class="small text-muted">Hoặc mở liên kết này nếu không quét được QR:</p>
                            <a href="{{ $payUrl }}" target="_blank" class="d-inline-block text-primary text-decoration-underline mb-3">{{ $payUrl }}</a>
                        </div>
                    @endif

                    @if($order->status === 'unpaid' && $order->payment_method === 'ONLINE')
                        <form action="/checkout/payment/{{ $order->id }}/start" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success fw-bold px-4 mt-2">Thanh toán bằng VNPay</button>
                        </form>
                    @elseif($order->status === 'completed')
                        <div class="alert alert-success fw-bold">Đơn hàng đã được thanh toán và hoàn thành.</div>
                    @else
                        <div class="alert alert-secondary fw-bold">Đơn hàng không thể thanh toán online ở trạng thái hiện tại.</div>
                    @endif

                    <a href="/" class="btn btn-outline-secondary fw-bold px-4 mt-2">
                         Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
