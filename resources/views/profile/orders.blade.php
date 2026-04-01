@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"> Lịch Sử Đơn Hàng</h2>
        <a href="/profile" class="btn btn-outline-secondary fw-bold"> Trở về Hồ sơ</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold fs-5 text-center shadow-sm">{{ session('success') }}</div>
    @endif

    @if($orders->count() > 0)
        <div class="row">
            @foreach($orders as $order)
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-0 border-start border-5 
                        {{ $order->status == 'unpaid' ? 'border-danger' : ($order->status == 'pending' ? 'border-warning' : 'border-success') }}">
                        
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <div>
                                <span class="fw-bold fs-5 text-dark">Mã đơn: #{{ $order->id }}</span>
                                <span class="text-muted ms-3">Thời gian {{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            
                            <div>
                                @if($order->status == 'unpaid')
                                    <span class="badge bg-danger fs-6 px-3 py-2">Chưa thanh toán</span>
                                @elseif($order->status == 'pending')
                                    <span class="badge bg-warning text-dark fs-6 px-3 py-2"> Chờ xử lý (COD)</span>
                                @elseif($order->status == 'shipping')
                                    <span class="badge bg-info text-dark fs-6 px-3 py-2">Đang giao hàng</span>
                                @elseif($order->status == 'completed')
                                    <span class="badge bg-success fs-6 px-3 py-2"> Đã hoàn thành</span>
                                @else
                                    <span class="badge bg-secondary fs-6 px-3 py-2">Đã hủy</span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body bg-light p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($order->details as $detail)
                                    @php
                                        // Ưu tiên lấy ảnh của phân loại trong đơn hàng, nếu không có lấy ảnh sản phẩm
                                        $orderItemImg = ($detail->variant && $detail->variant->image) 
                                                        ? $detail->variant->image 
                                                        : ($detail->product->image ?? 'https://via.placeholder.com/60');
                                    @endphp
                                    <li class="list-group-item d-flex align-items-center p-3 border-0 bg-transparent border-bottom">
                                        <img src="{{ $orderItemImg }}" width="70" height="70" class="rounded border me-3" style="object-fit: cover;">
                                        
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold text-dark mb-1">{{ $detail->product->name ?? 'Sản phẩm đã bị xóa' }}</h6>
                                            
                                            @if($detail->variant && $detail->variant->color !== 'Mặc định')
                                                <div class="mb-1">
                                                    <span class="badge bg-light text-secondary border fw-normal">Phân loại: {{ $detail->variant->color }}</span>
                                                </div>
                                            @endif
                                            
                                            <small class="text-muted fw-bold">Số lượng: {{ $detail->quantity }} x {{ number_format($detail->price, 0, ',', '.') }}đ</small>
                                        </div>
                                        <span class="text-danger fw-bold fs-5">{{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}đ</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                            <span class="fs-5 text-muted">Tổng thanh toán: <strong class="text-danger fs-3">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></span>
                            
                            <div class="d-flex gap-2">
                                @if($order->status == 'unpaid' && $order->payment_method == 'ONLINE')
                                    <a href="/checkout/payment/{{ $order->id }}" class="btn btn-primary fw-bold px-4 shadow-sm">
                                         Thanh Toán Ngay
                                    </a>
                                @endif

                                @if(in_array($order->status, ['pending', 'unpaid']))
                                    <form action="/profile/orders/{{ $order->id }}/cancel" method="POST" onsubmit="return confirm('⚠️ Bạn có chắc chắn muốn hủy đơn hàng này không?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger fw-bold px-4 shadow-sm">
                                             Hủy Đơn Hàng
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <h1 style="font-size: 4rem;">📦</h1>
            <h4 class="text-muted mb-3 mt-3">Bạn chưa có đơn hàng nào!</h4>
            <a href="/" class="btn btn-primary fw-bold px-4 py-2">Khám phá đồ nhựa ngay</a>
        </div>
    @endif
</div>
@endsection