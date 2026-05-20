@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary">Chi tiết đơn hàng #{{ $order->id }}</h2>
            <p class="text-muted mb-0">Trạng thái: 
                @if($order->status == 'unpaid')
                    <span class="badge bg-danger">Chưa thanh toán</span>
                @elseif($order->status == 'pending')
                    <span class="badge bg-warning text-dark">Chờ xử lý</span>
                @elseif($order->status == 'paid')
                    <span class="badge bg-success">Đã thanh toán</span>
                @elseif($order->status == 'shipping')
                    <span class="badge bg-info text-dark">Đang giao</span>
                @elseif($order->status == 'completed')
                    <span class="badge bg-success">Hoàn thành</span>
                @elseif($order->status == 'cancelled')
                    <span class="badge bg-secondary">Đã hủy</span>
                @endif
            </p>
        </div>
        <a href="/profile/orders" class="btn btn-outline-secondary fw-bold">Quay lại đơn hàng</a>
    </div>

    <div class="row gy-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">Thông tin khách hàng</div>
                <div class="card-body">
                    <p class="mb-2"><strong>Người nhận:</strong> {{ $order->customer_name }}</p>
                    <p class="mb-2"><strong>Điện thoại:</strong> {{ $order->customer_phone }}</p>
                    <p class="mb-2"><strong>Địa chỉ giao hàng:</strong> {{ $order->shipping_address }}</p>
                    <p class="mb-2"><strong>Phương thức thanh toán:</strong> {{ $order->payment_method == 'ONLINE' ? 'Online VNPay' : 'Thanh toán khi nhận hàng (COD)' }}</p>
                    <p class="mb-2"><strong>Ghi chú:</strong> {{ $order->notes ?: 'Không có ghi chú' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white fw-bold">Tổng đơn hàng</div>
                <div class="card-body">
                    @if($order->discount_amount > 0)
                        <p class="mb-2"><strong>Tam tinh:</strong> {{ number_format($order->subtotal_amount, 0, ',', '.') }}d</p>
                        <p class="mb-2 text-success"><strong>Voucher {{ $order->voucher_code }}:</strong> -{{ number_format($order->discount_amount, 0, ',', '.') }}d</p>
                    @endif
                    <p class="mb-2"><strong>Tong tien:</strong> <span class="text-danger">{{ number_format($order->total_amount, 0, ',', '.') }}d</span></p>
                    <p class="mb-2"><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <p class="mb-2"><strong>Trạng thái:</strong> {{ ucfirst($order->status) }}</p>
                    @if($order->status === 'unpaid' && $order->payment_method === 'ONLINE')
                        <a href="/checkout?order_id={{ $order->id }}" class="btn btn-primary w-100 fw-bold mt-3">Thanh toán đơn hàng này</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light fw-bold">Chi tiết sản phẩm</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($order->details as $detail)
                            @php
                                $itemImage = ($detail->variant && $detail->variant->image)
                                            ? $detail->variant->image
                                            : ($detail->product->image ?? 'https://via.placeholder.com/80');
                            @endphp
                            <li class="list-group-item d-flex align-items-center py-3">
                                <img src="{{ $itemImage }}" width="80" height="80" class="rounded border me-3" style="object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1">{{ $detail->product->name ?? 'Sản phẩm đã bị xóa' }}</h6>
                                    @if($detail->variant && $detail->variant->color !== 'Mặc định')
                                        <div class="mb-1"><span class="badge bg-info text-dark">Loại: {{ $detail->variant->color }}</span></div>
                                    @endif
                                    <p class="mb-1 text-muted">SL: {{ $detail->quantity }} x {{ number_format($detail->price, 0, ',', '.') }}đ</p>
                                </div>
                                <span class="fw-bold text-danger fs-5">{{ number_format($detail->quantity * $detail->price, 0, ',', '.') }}đ</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
