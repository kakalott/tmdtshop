@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="fw-bold mb-4 text-primary"> Thanh Toán Đơn Hàng</h2>

    <form action="/checkout/process" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-7">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white fw-bold fs-5 border-bottom-0 pt-3">
                          Thông Tin Nhận Hàng
                    </div>
                    <div class="card-body bg-light rounded m-2">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Họ và Tên</label>
                                <input type="text" name="customer_name" class="form-control" value="{{ auth()->user()->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="customer_phone" class="form-control border-primary" value="{{ auth()->user()->phone }}" required placeholder="Nhập SĐT người nhận...">
                            </div>
                        </div>

                        <div class="mb-3" id="address_box">
                            <label class="form-label fw-bold">Địa chỉ giao hàng chi tiết <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" class="form-control border-primary" rows="2" required placeholder="Nhập số nhà, đường, phường/xã...">{{ auth()->user()->address }}</textarea>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label fw-bold">Ghi chú đơn hàng (Tùy chọn)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Ví dụ: Giao ngoài giờ hành chính..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white fw-bold fs-5 border-bottom-0 pt-3">
                          Phương Thức Thanh Toán
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3 p-3 border rounded border-success bg-light">
                            <input class="form-check-input ms-1 mt-2" type="radio" name="payment_method" id="pay_cod" value="COD" checked>
                            <label class="form-check-label ms-2 fw-bold text-dark" for="pay_cod">
                                 Thanh toán khi nhận hàng (COD)
                            </label>
                        </div>
                        <div class="form-check p-3 border rounded">
                            <input class="form-check-input ms-1 mt-2" type="radio" name="payment_method" id="pay_online" value="ONLINE">
                            <label class="form-check-label ms-2 fw-bold text-primary" for="pay_online">
                                 Chuyển khoản trực tuyến (VNPay / Momo)
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-5">
                <div class="card shadow border-warning sticky-top" style="top: 20px;">
                    <div class="card-header bg-warning text-dark fw-bold fs-5 pt-3 pb-3">
                          Tóm Tắt Đơn Hàng
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @php $total = 0; @endphp
                            
                            @foreach($cartItems as $item)
                                @php 
                                    // 1. Logic lấy giá (ưu tiên giá khuyến mãi)
                                    $price = $item->product->sale_price ?? $item->product->price;
                                    $subTotal = $price * $item->quantity;
                                    $total += $subTotal; 

                                    // 2. Logic lấy ảnh: Ưu tiên ảnh phân loại trước
                                    $checkoutImg = ($item->variant && $item->variant->image) 
                                                    ? $item->variant->image 
                                                    : ($item->product->image ?? 'https://via.placeholder.com/50');
                                @endphp

                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $checkoutImg }}" width="50" height="50" class="rounded me-3 border" style="object-fit: cover;">
                                        
                                        <div>
                                            <span class="fw-bold d-block text-truncate" style="max-width: 200px;" title="{{ $item->product->name }}">
                                                {{ $item->product->name }}
                                            </span>
                                            
                                            @if($item->variant && $item->variant->color !== 'Mặc định')
                                                <small class="badge bg-info text-dark fw-normal">Loại: {{ $item->variant->color }}</small>
                                            @endif
                                            
                                            <br>
                                            <small class="text-muted">SL: {{ $item->quantity }} x {{ number_format($price, 0, ',', '.') }}đ</small>
                                        </div>
                                    </div>
                                    <span class="fw-bold text-danger">{{ number_format($subTotal, 0, ',', '.') }}đ</span>
                                </li>
                                
                                <input type="hidden" name="cart_ids[]" value="{{ $item->id }}">
                            @endforeach
                            
                        </ul>
                    </div>
                    <div class="card-footer bg-white mt-2">
                        <div class="d-flex justify-content-between fs-6 mb-2 mt-2">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-bold text-dark">{{ number_format($total, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="d-flex justify-content-between fs-6 mb-3">
                            <span class="text-muted">Phí vận chuyển:</span>
                            <span class="fw-bold text-success" id="shipping_fee">Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold fs-5">Tổng Thanh Toán:</span>
                            <span class="fw-bold text-danger fs-3">{{ number_format($total, 0, ',', '.') }}đ</span>
                        </div>
                        
                        <input type="hidden" name="total_amount" value="{{ $total }}">
                        
                        <button type="submit" class="btn btn-danger w-100 fw-bold py-3 fs-5 shadow"> XÁC NHẬN ĐẶT HÀNG</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection