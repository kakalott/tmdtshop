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
        <div class="col-md-5 mb-4 text-center d-flex align-items-center justify-content-center" style="background-color: #f8f9fa; border-radius: 8px;">
            <img id="mainImage" src="{{ $product->image ?? 'https://via.placeholder.com/400' }}" alt="{{ $product->name }}" class="img-fluid rounded p-2" style="max-height: 450px; object-fit: contain;">
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
                <span class="badge bg-success fs-6">Kho: {{ $product->stock_quantity }}</span>
            </div>
            
            <div class="bg-light p-3 rounded mb-4">
                <h2 class="fw-bold text-danger mb-0">{{ number_format($product->price, 0, ',', '.') }}đ</h2>
                @if($product->wholesale_price)
                    <small class="text-muted">Giá sỉ (mua số lượng lớn): {{ number_format($product->wholesale_price, 0, ',', '.') }}đ</small>
                @endif
            </div>

            <div id="variants-area" class="mb-4">
                <p class="text-muted fst-italic">Khu vực chọn Màu sắc/Kích thước sẽ được tích hợp tại đây...</p>
            </div>

            <form action="/cart/add/{{ $product->id }}" method="GET" class="d-flex align-items-center mb-4">
                <div class="me-3">
                    <label class="fw-bold text-muted mb-1">Số lượng</label>
                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" class="form-control text-center fw-bold fs-5" style="width: 100px; height: 50px;">
                </div>
                
                <button type="submit" class="btn btn-danger fw-bold shadow-sm flex-grow-1" style="height: 50px; font-size: 1.1rem;">
                     THÊM VÀO GIỎ HÀNG
                </button>
            </form>

            <hr>

            <h5 class="fw-bold mt-4 text-primary"> Thông tin chi tiết:</h5>
            <div class="p-3 bg-light rounded text-dark" style="line-height: 1.8; font-size: 1.05rem;">
                {!! nl2br(e($product->description ?? 'Chưa có mô tả cho sản phẩm này.')) !!}
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold fs-5 py-3 border-bottom-0">
                     Đánh Giá Sản Phẩm
                </div>
                <div class="card-body bg-light">
                    @auth
                        <form action="/product/{{ $product->id }}/review" method="POST" class="mb-5 bg-white p-4 rounded border shadow-sm">
                            @csrf
                            <h6 class="fw-bold mb-3">Gửi đánh giá của bạn</h6>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Mức độ hài lòng:</label>
                                <select name="rating" class="form-select w-auto border-warning text-warning fw-bold fs-5" required>
                                    <option value="5">⭐⭐⭐⭐⭐ - Tuyệt vời</option>
                                    <option value="4">⭐⭐⭐⭐ - Rất tốt</option>
                                    <option value="3">⭐⭐⭐ - Bình thường</option>
                                    <option value="2">⭐⭐ - Kém</option>
                                    <option value="1">⭐ - Rất tệ</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <textarea name="comment" class="form-control" rows="3" placeholder="Chia sẻ cảm nhận của bạn về chất liệu, độ bền, màu sắc..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-warning fw-bold text-dark px-4">Gửi Đánh Giá</button>
                        </form>
                    @else
                        <div class="alert alert-info text-center shadow-sm">
                             Vui lòng <a href="{{ route('login') }}" class="fw-bold">Đăng nhập</a> để viết đánh giá cho sản phẩm này!
                        </div>
                    @endauth

                    <hr>

                    <div class="mt-4">
                        <h6 class="fw-bold mb-4">Các đánh giá gần đây:</h6>
                        
                        @forelse($product->reviews->sortByDesc('created_at') as $review)
                            <div class="d-flex mb-4 pb-3 border-bottom">
                                <div class="me-3">
                                    <div class="bg-secondary text-white fw-bold rounded-circle d-flex justify-content-center align-items-center" style="width: 45px; height: 45px;">
                                        {{ substr($review->user->name ?? 'K', 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-dark">{{ $review->user->name ?? 'Khách ẩn danh' }}</strong>
                                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="text-warning fs-6 mb-1">
                                        @for($i=1; $i<=5; $i++)
                                            {{ $i <= $review->rating ? '★' : '☆' }}
                                        @endfor
                                    </div>
                                    <p class="mb-0 text-dark">{{ $review->comment }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-4 fst-italic">Chưa có đánh giá nào. Hãy là người đầu tiên bóc tem sản phẩm này!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection