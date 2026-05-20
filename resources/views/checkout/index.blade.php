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

        <form action="/checkout/process" method="POST" id="checkout-form">
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
                            <div class="voucher-strip mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="voucher-icon me-2">%</span>
                                    <span class="fw-bold">Shop Voucher</span>
                                </div>
                                <button type="button" class="btn btn-link fw-bold text-decoration-none p-0" data-bs-toggle="modal" data-bs-target="#voucherModal">
                                    {{ $summary['voucher'] ? $summary['voucher']->code : 'Chon Voucher' }}
                                </button>
                            </div>
                            <input type="hidden" name="voucher_code" id="selected-voucher-code" value="{{ old('voucher_code', request('voucher_code')) }}">
                            @if($summary['voucher'])
                                <div class="alert alert-success py-2 fw-bold">
                                    Da ap dung {{ $summary['voucher']->code }}: -{{ number_format($summary['discount'], 0, ',', '.') }}d
                                </div>
                            @elseif($summary['voucher_error'])
                                <div class="alert alert-danger py-2 fw-bold">{{ $summary['voucher_error'] }}</div>
                            @endif
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

        <div class="modal fade" id="voucherModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content voucher-modal">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Chon Shop Voucher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="voucher-code-row mb-3">
                            <label class="fw-bold text-muted mb-0">Ma Voucher</label>
                            <input type="text" class="form-control text-uppercase" id="manual-voucher-code" value="{{ old('voucher_code', request('voucher_code')) }}" placeholder="Nhap ma voucher">
                            <button type="button" class="btn btn-outline-secondary fw-bold" id="apply-manual-voucher">AP DUNG</button>
                        </div>

                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <div>
                                <h6 class="fw-bold mb-0">Ma giam gia cua shop</h6>
                                <small class="text-muted">Chi voucher du dieu kien moi co the chon</small>
                            </div>
                            <small class="text-muted">Tam tinh: {{ number_format($summary['subtotal'], 0, ',', '.') }}d</small>
                        </div>

                        <div class="voucher-list">
                            @forelse($voucherOptions as $voucher)
                                @php
                                    $isSelected = $summary['voucher'] && $summary['voucher']->id === $voucher->id;
                                    $valueText = $voucher->type === 'percent'
                                        ? 'Giam ' . rtrim(rtrim(number_format($voucher->value, 2), '0'), '.') . '%'
                                        : 'Giam ' . number_format($voucher->value, 0, ',', '.') . 'd';
                                @endphp

                                <label class="voucher-ticket {{ $voucher->checkout_available ? '' : 'is-disabled' }} {{ $isSelected ? 'is-selected' : '' }}">
                                    <div class="voucher-ticket-left">
                                        <div class="voucher-ticket-mark">%</div>
                                        <div class="fw-bold text-white">Shop</div>
                                        <small>Voucher</small>
                                    </div>
                                    <div class="voucher-ticket-body">
                                        <div class="fw-bold">{{ $valueText }}</div>
                                        <div class="text-muted">Don toi thieu {{ number_format($voucher->min_order_amount, 0, ',', '.') }}d</div>
                                        @if($voucher->type === 'percent' && $voucher->max_discount_amount)
                                            <small class="text-muted d-block">Giam toi da {{ number_format($voucher->max_discount_amount, 0, ',', '.') }}d</small>
                                        @endif
                                        <div class="mt-1">
                                            <span class="voucher-code-badge">{{ $voucher->code }}</span>
                                            @if($voucher->ends_at)
                                                <small class="text-muted ms-2">HSD: {{ $voucher->ends_at->format('d/m/Y') }}</small>
                                            @endif
                                        </div>
                                        @if($voucher->checkout_available)
                                            <small class="text-success fw-bold">Giam duoc {{ number_format($voucher->checkout_discount, 0, ',', '.') }}d</small>
                                        @else
                                            <small class="text-danger fw-bold">{{ $voucher->checkout_reason }}</small>
                                        @endif
                                    </div>
                                    <div class="voucher-ticket-radio">
                                        <input type="radio" name="voucher_picker" value="{{ $voucher->code }}" {{ $isSelected ? 'checked' : '' }} {{ $voucher->checkout_available ? '' : 'disabled' }}>
                                    </div>
                                </label>
                            @empty
                                <div class="text-center text-muted py-4">Hien chua co voucher nao.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary px-5" data-bs-dismiss="modal">TRO LAI</button>
                        <button type="button" class="btn btn-danger px-5 fw-bold" id="confirm-voucher">DONG Y</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .voucher-strip {
        align-items: center;
        background: #fff;
        border: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        padding: 14px 16px;
    }

    .voucher-icon {
        align-items: center;
        border: 1px dashed #ee4d2d;
        color: #ee4d2d;
        display: inline-flex;
        font-weight: 700;
        height: 24px;
        justify-content: center;
        width: 24px;
    }

    .voucher-code-row {
        align-items: center;
        background: #f8f8f8;
        display: grid;
        gap: 12px;
        grid-template-columns: 110px 1fr 120px;
        padding: 16px;
    }

    .voucher-list {
        max-height: 430px;
        overflow-y: auto;
        padding-right: 6px;
    }

    .voucher-ticket {
        align-items: stretch;
        background: #fff;
        border: 1px solid #e5e5e5;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        cursor: pointer;
        display: grid;
        grid-template-columns: 150px 1fr 48px;
        margin-bottom: 12px;
        min-height: 128px;
        position: relative;
    }

    .voucher-ticket.is-selected {
        border-color: #ee4d2d;
    }

    .voucher-ticket.is-disabled {
        cursor: not-allowed;
        opacity: 0.45;
    }

    .voucher-ticket-left {
        align-items: center;
        background: #87d4cc;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 12px;
        text-align: center;
    }

    .voucher-ticket-mark {
        align-items: center;
        background: #ee4d2d;
        border-radius: 50%;
        display: flex;
        font-size: 28px;
        font-weight: 700;
        height: 58px;
        justify-content: center;
        margin-bottom: 8px;
        width: 58px;
    }

    .voucher-ticket-body {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 16px;
    }

    .voucher-code-badge {
        border: 1px solid #ee4d2d;
        color: #ee4d2d;
        display: inline-block;
        font-size: 12px;
        padding: 1px 6px;
    }

    .voucher-ticket-radio {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .voucher-ticket-radio input {
        height: 20px;
        width: 20px;
    }

    @media (max-width: 767px) {
        .voucher-code-row {
            grid-template-columns: 1fr;
        }

        .voucher-ticket {
            grid-template-columns: 110px 1fr 40px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkoutForm = document.getElementById('checkout-form');
        const selectedVoucherCode = document.getElementById('selected-voucher-code');
        const confirmVoucher = document.getElementById('confirm-voucher');
        const manualVoucherCode = document.getElementById('manual-voucher-code');
        const applyManualVoucher = document.getElementById('apply-manual-voucher');

        function applyVoucher(code) {
            selectedVoucherCode.value = (code || '').trim().toUpperCase();
            checkoutForm.action = '/checkout';
            checkoutForm.method = 'GET';
            checkoutForm.submit();
        }

        confirmVoucher?.addEventListener('click', function () {
            const checkedVoucher = document.querySelector('input[name="voucher_picker"]:checked');
            applyVoucher(checkedVoucher ? checkedVoucher.value : '');
        });

        applyManualVoucher?.addEventListener('click', function () {
            applyVoucher(manualVoucherCode.value);
        });
    });
</script>
@endsection
