@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none fw-bold"> Trang Chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row bg-white p-4 shadow-sm rounded border">
        
        <div class="col-md-5 mb-4">
            <div class="text-center d-flex align-items-center justify-content-center mb-3" style="background-color: #f8f9fa; border-radius: 8px; height: 450px;">
                @php
                    $mainImg = $product->image ?: ($product->variants->first()->image ?? 'https://dummyimage.com/600x600/cccccc/000000.png&text=No+Image');
                @endphp
                <img id="mainImage" src="{{ $mainImg }}" alt="{{ $product->name }}" class="img-fluid rounded p-2" style="max-height: 430px; object-fit: contain;">
            </div>
            
            <div class="d-flex overflow-auto pb-2 gap-2" id="thumbnail-container">
                @if($product->image)
                    <img src="{{ $product->image }}" 
                         class="thumbnail-img active-thumb border rounded" 
                         style="width: 70px; height: 70px; object-fit: cover; cursor: pointer;"
                         onclick="changeMainImage('{{ $product->image }}', this)">
                @endif
                
                @foreach($product->variants as $v)
                    @if($v->image)
                        <img src="{{ $v->image }}" 
                             class="thumbnail-img border rounded" 
                             style="width: 70px; height: 70px; object-fit: cover; cursor: pointer;"
                             onclick="changeMainImage('{{ $v->image }}', this)">
                    @endif
                @endforeach
            </div>
        </div>

        <div class="col-md-7 ps-md-5">
            <h2 class="fw-bold text-dark mb-3">{{ $product->name }}</h2>
            
            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                <span class="text-warning fs-5 me-2">
                    @for($i=1; $i<=5; $i++)
                        {{ $i <= round($avgRating) ? '★' : '☆' }}
                    @endfor
                </span>
                <span class="text-muted me-3">({{ $product->reviews->count() }} đánh giá)</span>
                <span class="badge bg-success fs-6" id="stockBadge">Kho: {{ $product->stock_quantity }}</span>
            </div>
            
            <div class="bg-light p-3 rounded mb-4">
                <h2 class="fw-bold text-danger mb-0">{{ number_format($product->price, 0, ',', '.') }}đ</h2>
                @if($product->wholesale_price)
                    <small class="text-muted">Giá sỉ (B2B): {{ number_format($product->wholesale_price, 0, ',', '.') }}đ</small>
                @endif
            </div>

            <form action="/cart/add/{{ $product->id }}" method="GET" class="mb-4">
                <div id="variants-area" class="mb-4">
                    <label class="fw-bold text-muted mb-2">Chọn Loại:</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($product->variants as $key => $v)
                            <input type="radio" class="btn-check" name="variant_id" id="variant_{{ $v->id }}" value="{{ $v->id }}" 
                                   {{ $key == 0 ? 'checked' : '' }} 
                                   data-image="{{ $v->image ?? $product->image }}"
                                   data-stock="{{ $v->stock_quantity }}"
                                   onchange="handleVariantChange(this)">
                            
                            <label class="btn btn-outline-primary fw-bold {{ $v->stock_quantity <= 0 ? 'disabled' : '' }}" for="variant_{{ $v->id }}">
                                {{ $v->color }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <div class="me-3">
                        <label class="fw-bold text-muted mb-1">Số lượng</label>
                        <input type="number" name="quantity" id="quantityInput" value="1" min="1" max="{{ $product->variants->first()->stock_quantity ?? $product->stock_quantity }}" class="form-control text-center fw-bold fs-5" style="width: 100px; height: 50px;">
                    </div>
                    
                    <button type="submit" class="btn btn-danger fw-bold shadow-sm flex-grow-1" style="height: 50px; font-size: 1.1rem;" id="addToCartBtn">
                         THÊM VÀO GIỎ HÀNG
                    </button>
                </div>
            </form>

            <hr>

            <h5 class="fw-bold mt-4 text-primary"> Thông tin chi tiết:</h5>
            <div class="p-3 bg-light rounded text-dark" style="line-height: 1.8; font-size: 1.05rem;">
                {!! nl2br(e($product->description ?? 'Chưa có mô tả cho sản phẩm này.')) !!}
            </div>
        </div>
    </div>

    </div>

<script>
    // Hàm 1: Đổi ảnh khi khách bấm vào các bức ảnh nhỏ
    function changeMainImage(imageUrl, thumbElement) {
        document.getElementById('mainImage').src = imageUrl;
        
        // Đổi màu viền cho bức ảnh nhỏ vừa được bấm
        let allThumbs = document.querySelectorAll('.thumbnail-img');
        allThumbs.forEach(el => {
            el.classList.remove('border-primary', 'border-2');
            el.style.opacity = '0.6';
        });
        
        if(thumbElement) {
            thumbElement.classList.add('border-primary', 'border-2');
            thumbElement.style.opacity = '1';
        }
    }

    // Hàm 2: Xử lý khi khách bấm nút chọn Màu sắc
    function handleVariantChange(radioElement) {
        // 1. Nhảy ảnh
        let imageUrl = radioElement.getAttribute('data-image');
        if (imageUrl && imageUrl !== '') {
            changeMainImage(imageUrl, null);
        }

        // 2. Nhảy Tồn kho
        let stock = parseInt(radioElement.getAttribute('data-stock'));
        let stockBadge = document.getElementById('stockBadge');
        let quantityInput = document.getElementById('quantityInput');
        let addToCartBtn = document.getElementById('addToCartBtn');

        if (stock > 0) {
            stockBadge.innerHTML = 'Kho: ' + stock;
            stockBadge.className = 'badge bg-success fs-6';
            quantityInput.max = stock;
            addToCartBtn.disabled = false;
            addToCartBtn.innerHTML = '🛒 THÊM VÀO GIỎ HÀNG';
        } else {
            stockBadge.innerHTML = 'Tạm hết hàng';
            stockBadge.className = 'badge bg-danger fs-6';
            quantityInput.value = 1;
            quantityInput.max = 0;
            addToCartBtn.disabled = true;
            addToCartBtn.innerHTML = 'HẾT HÀNG';
        }
    }

    // Tự động kích hoạt các hiệu ứng lúc vừa mở trang
    window.onload = function() {
        let firstThumb = document.querySelector('.thumbnail-img');
        if(firstThumb) {
            firstThumb.style.opacity = '1';
            firstThumb.classList.add('border-primary', 'border-2');
        } else {
            // Làm mờ đi nếu chưa chọn
            document.querySelectorAll('.thumbnail-img').forEach(el => el.style.opacity = '0.6');
        }

        // Tự động kích hoạt màu đang được check mặc định
        let checkedVariant = document.querySelector('input[name="variant_id"]:checked');
        if(checkedVariant) {
            handleVariantChange(checkedVariant);
        }
    };
</script>

<style>
    /* CSS làm đẹp cho cái cục chọn phân loại */
    .btn-check:checked + .btn-outline-primary {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endsection