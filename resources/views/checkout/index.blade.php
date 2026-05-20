@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="fw-bold mb-4 text-primary">Thanh Toan Don Hang</h2>

    @if ($errors->any())
        <div class="alert alert-danger fw-bold">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($order))
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-primary mb-4">
                    <div class="card-header bg-primary text-white fw-bold">Thanh toan VNPay cho don #{{ $order->id }}</div>
                    <div class="card-body">
                        <p class="mb-2">Tong tien: <strong class="text-danger">{{ number_format($order->total_amount, 0, ',', '.') }}d</strong></p>
                        <p class="text-muted">Noi dung: <strong>Thanh toan don hang #{{ $order->id }}</strong></p>

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if($payUrl)
                            <div class="text-center mb-3">
                                <img src="https://api.qrserver.com/v1/create-qr-code?size=300x300&data={{ urlencode($payUrl) }}" alt="QR Payment" class="img-fluid border rounded shadow-sm">
                            </div>
                            <div class="row gx-2">
                                <div class="col-md-6 mb-2">
                                    <form action="/vnpay/sandbox/{{ $order->id }}/pay" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100 fw-bold py-3">Thanh toan thanh cong</button>
                                    </form>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <a href="/profile/orders" class="btn btn-outline-secondary w-100 fw-bold py-3">Tro ve</a>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">Khong the tao URL thanh toan VNPay. Vui long thu lai sau.</div>
                            <a href="/profile/orders" class="btn btn-outline-secondary w-100 fw-bold py-3">Tro ve</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        @php
            $summary = $checkoutSummary ?? ['subtotal' => 0, 'discount' => 0, 'total' => 0, 'voucher' => null, 'voucher_error' => null];
        @endphp

        <form action="/checkout/process" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-7">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white fw-bold fs-5 border-bottom-0 pt-3">
                            Thong Tin Nhan Hang
                        </div>
                        <div class="card-body bg-light rounded m-2">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Ho va Ten</label>
                                    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', auth()->user()->name) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">So dien thoai <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_phone" class="form-control border-primary" value="{{ old('customer_phone', auth()->user()->phone) }}" required placeholder="Nhap SDT nguoi nhan...">
                                </div>
                            </div>

                            <div class="mb-3" id="address_box">
                                <label class="form-label fw-bold">Dia chi giao hang chi tiet <span class="text-danger">*</span></label>
                                <textarea name="shipping_address" class="form-control border-primary" rows="2" required placeholder="Nhap so nha, duong, phuong/xa...">{{ old('shipping_address', auth()->user()->address) }}</textarea>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold">Ghi chu don hang (tuy chon)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Vi du: Giao ngoai gio hanh chinh...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white fw-bold fs-5 border-bottom-0 pt-3">
                            Phuong Thuc Thanh Toan
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3 p-3 border rounded border-success bg-light">
                                <input class="form-check-input ms-1 mt-2" type="radio" name="payment_method" id="pay_cod" value="COD" {{ old('payment_method', 'COD') === 'COD' ? 'checked' : '' }}>
                                <label class="form-check-label ms-2 fw-bold text-dark" for="pay_cod">
                                    Thanh toan khi nhan hang (COD)
                                </label>
                            </div>
                            <div class="form-check p-3 border rounded">
                                <input class="form-check-input ms-1 mt-2" type="radio" name="payment_method" id="pay_online" value="ONLINE" {{ old('payment_method') === 'ONLINE' ? 'checked' : '' }}>
                                <label class="form-check-label ms-2 fw-bold text-primary" for="pay_online">
                                    Chuyen khoan truc tuyen
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card shadow border-warning sticky-top" style="top: 20px;">
                        <div class="card-header bg-warning text-dark fw-bold fs-5 pt-3 pb-3">
                            Tom Tat Don Hang
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($cartItems as $item)
                                    @php
                                        $isWholesale = ($item->quantity >= 10 && $item->product->wholesale_price > 0);
                                        $price = $item->product->getPriceByQuantity($item->quantity);
                                        $subTotal = $price * $item->quantity;
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

                                                @if($item->variant && $item->variant->color !== 'Mac dinh')
                                                    <small class="badge bg-info text-dark fw-normal mb-1">Loai: {{ $item->variant->color }}</small>
                                                @endif

                                                @if($isWholesale)
                                                    <small class="badge bg-success fw-normal mb-1">Gia si</small>
                                                @endif

                                                <br>
                                                <small class="text-muted fw-bold">SL: {{ $item->quantity }} x {{ number_format($price, 0, ',', '.') }}d</small>
                                            </div>
                                        </div>
                                        <span class="fw-bold text-danger">{{ number_format($subTotal, 0, ',', '.') }}d</span>
                                    </li>

                                    <input type="hidden" name="cart_ids[]" value="{{ $item->id }}">
                                    <input type="hidden" name="selected_carts[]" value="{{ $item->id }}">
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-footer bg-white mt-2">
                            <div class="d-flex justify-content-between fs-6 mb-2 mt-2">
                                <span class="text-muted">Tam tinh:</span>
                                <span class="fw-bold text-dark">{{ number_format($summary['subtotal'], 0, ',', '.') }}d</span>
                            </div>
                            <div class="d-flex justify-content-between fs-6 mb-3">
                                <span class="text-muted">Phi van chuyen:</span>
                                <span class="fw-bold text-success" id="shipping_fee">Mien phi</span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ma voucher</label>
                                <div class="input-group">
                                    <input type="text" name="voucher_code" class="form-control text-uppercase" value="{{ old('voucher_code', request('voucher_code')) }}" placeholder="Nhap ma giam gia">
                                    <button type="submit" class="btn btn-outline-primary fw-bold" formaction="/checkout" formmethod="GET">Ap dung</button>
                                </div>
                                @if($summary['voucher'])
                                    <small class="text-success fw-bold">Da ap dung: {{ $summary['voucher']->code }}</small>
                                @elseif($summary['voucher_error'])
                                    <small class="text-danger fw-bold">{{ $summary['voucher_error'] }}</small>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between fs-6 mb-3">
                                <span class="text-muted">Giam gia:</span>
                                <span class="fw-bold text-success">-{{ number_format($summary['discount'], 0, ',', '.') }}d</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold fs-5">Tong Thanh Toan:</span>
                                <span class="fw-bold text-danger fs-3">{{ number_format($summary['total'], 0, ',', '.') }}d</span>
                            </div>

                            <button type="submit" class="btn btn-danger w-100 fw-bold py-3 fs-5 shadow">XAC NHAN DAT HANG</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection
