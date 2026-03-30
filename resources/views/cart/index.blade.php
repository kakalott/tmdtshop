@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="fw-bold mb-4"> Giỏ Hàng Của Bạn</h2>

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 fw-bold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif

    @if($cartItems->count() > 0)
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white fw-bold d-flex align-items-center pt-3 pb-3 border-bottom">
                        <input type="checkbox" id="check-all" class="form-check-input me-3" style="transform: scale(1.5);">
                        <span class="fs-5">CHỌN TẤT CẢ ({{ $cartItems->count() }} sản phẩm)</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($cartItems as $item)
                                @if(!$item->product) @continue @endif 
                                
                                <li class="list-group-item d-flex align-items-center p-3">
                                    <input type="checkbox" value="{{ $item->id }}" 
                                           class="form-check-input item-check me-3" style="transform: scale(1.5);"
                                           data-price="{{ $item->product->sale_price ?? $item->product->price }}" 
                                           data-quantity="{{ $item->quantity }}"
                                           data-name="{{ $item->product->name }}">
                                    
                                    <img src="{{ $item->product->image ?? 'https://via.placeholder.com/60' }}" width="80" class="rounded border me-3">
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold text-primary mb-1">{{ $item->product->name }}</h6>
                                        <span class="text-danger fw-bold">{{ number_format($item->product->sale_price ?? $item->product->price, 0, ',', '.') }}đ</span>
                                        <br>
                                        <small class="text-muted fw-bold"> Kho còn: {{ $item->product->stock_quantity }}</small>
                                    </div>

                                    <div class="me-3">
                                        <form action="/cart/update" method="POST" class="d-flex align-items-center mb-0">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="cart_id" value="{{ $item->id }}">
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" 
                                                       class="form-control text-center" min="1" max="{{ $item->product->stock_quantity }}">
                                                <button type="submit" class="btn btn-outline-primary" title="Cập nhật số lượng">cập nhật</button>
                                            </div>
                                        </form>
                                    </div>

                                    <form action="/cart/remove" method="POST" class="m-0">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="cart_id" value="{{ $item->id }}">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa món này">xóa</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-warning sticky-top" style="top: 20px;">
                    <div class="card-header bg-warning text-dark fw-bold pt-3 pb-3 fs-5">
                         Hóa Đơn 
                    </div>
                    <div class="card-body">
                        
                        <ul id="summary-items-list" class="list-group list-group-flush mb-3" style="max-height: 250px; overflow-y: auto;">
                            <li class="list-group-item text-center text-muted fst-italic py-3 border-0">Chưa có sản phẩm nào được chọn</li>
                        </ul>
                        <hr>

                        <div class="d-flex justify-content-between mb-3 mt-3 fs-5">
                            <span>Số món đã chọn:</span>
                            <span class="fw-bold text-primary" id="total-items">0 món</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 fs-5">
                            <span>Tổng tiền:</span>
                            <span class="fw-bold text-danger fs-3" id="total-price">0đ</span>
                        </div>
                        
                        <form action="/checkout" method="GET" id="checkout-form">
                            <button type="submit" class="btn btn-danger w-100 fw-bold py-3 fs-5 shadow" id="btn-checkout" disabled>
                                THANH TOÁN 
                            </button>
                        </form>
                        <small class="text-muted d-block text-center mt-3">Vui lòng tick chọn ít nhất 1 sản phẩm để thanh toán</small>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const checkAll = document.getElementById('check-all');
            const itemChecks = document.querySelectorAll('.item-check');
            const totalPriceEl = document.getElementById('total-price');
            const totalItemsEl = document.getElementById('total-items');
            const btnCheckout = document.getElementById('btn-checkout');
            const checkoutForm = document.getElementById('checkout-form');
            const summaryItemsList = document.getElementById('summary-items-list');

            // Hàm tính tổng tiền và vẽ danh sách
            function calculateTotal() {
                let total = 0;
                let count = 0;
                let summaryHTML = ''; // Chuỗi chứa HTML các món sẽ vẽ ra

                itemChecks.forEach(check => {
                    if (check.checked) {
                        let price = parseFloat(check.getAttribute('data-price'));
                        let qty = parseInt(check.getAttribute('data-quantity'));
                        let name = check.getAttribute('data-name'); // Lấy tên sản phẩm
                        let subTotal = price * qty;
                        
                        total += subTotal;
                        count++;

                        // Format tiền tệ VN
                        let formattedPrice = new Intl.NumberFormat('vi-VN').format(price) + 'đ';
                        let formattedSubTotal = new Intl.NumberFormat('vi-VN').format(subTotal) + 'đ';

                        // Vẽ HTML cho từng món
                        summaryHTML += `
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <div class="me-2" style="max-width: 65%;">
                                    <div class="fw-bold text-truncate text-dark" title="${name}" style="font-size: 0.9rem;">${name}</div>
                                    <small class="text-muted">SL: ${qty} x ${formattedPrice}</small>
                                </div>
                                <span class="fw-bold text-danger" style="font-size: 0.95rem;">${formattedSubTotal}</span>
                            </li>
                        `;
                    }
                });

                // Nếu không có món nào được chọn thì hiện câu thông báo
                if(count === 0) {
                     summaryHTML = '<li class="list-group-item text-center text-muted fst-italic py-3 border-0">Chưa có sản phẩm nào được chọn</li>';
                }

                // Bơm HTML vào Giao diện
                summaryItemsList.innerHTML = summaryHTML;
                totalPriceEl.innerText = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
                totalItemsEl.innerText = count + ' món';
                
                // Bật/tắt nút Checkout
                btnCheckout.disabled = count === 0;
                checkAll.checked = count === itemChecks.length && itemChecks.length > 0;
            }

            // Gọi sự kiện khi tick
            checkAll.addEventListener('change', function() {
                itemChecks.forEach(check => check.checked = this.checked);
                calculateTotal();
            });

            itemChecks.forEach(check => {
                check.addEventListener('change', calculateTotal);
            });

            // Gửi dữ liệu đi khi bấm thanh toán
            checkoutForm.addEventListener('submit', function(e) {
                document.querySelectorAll('.hidden-cart-id').forEach(el => el.remove());
                itemChecks.forEach(check => {
                    if (check.checked) {
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'selected_carts[]';
                        input.value = check.value;
                        input.className = 'hidden-cart-id';
                        checkoutForm.appendChild(input);
                    }
                });
            });
        </script>

    @else
        <div class="text-center py-5">
            <h4 class="text-muted mb-3">Giỏ hàng của bạn đang trống!</h4>
            <a href="/" class="btn btn-primary fw-bold px-4"> Tiếp tục mua sắm</a>
        </div>
    @endif
</div>
@endsection