@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary">TỔNG KHO NHỰA GIA DỤNG</h1>
        <p class="text-muted">Chuyên cung cấp sỉ & lẻ bàn ghế, tủ nhựa, rổ rá chất lượng cao</p>
    </div>

    <div class="row">
        @foreach($products as $product)
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}" style="object-fit: cover; height: 250px;">
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold">{{ $product->name }}</h5>
                    <p class="card-text text-danger mb-1 fs-5">
                        Lẻ: {{ number_format($product->price, 0, ',', '.') }} đ
                    </p>
                    <p class="card-text text-success mb-3">
                        <small>Sỉ (B2B): {{ number_format($product->wholesale_price, 0, ',', '.') }} đ</small>
                    </p>
                    
                    <button class="btn btn-outline-primary mt-auto">
                        Thêm vào giỏ hàng
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection