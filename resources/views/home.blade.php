@extends('layouts.app')

@section('content')

@if(isset($banners) && $banners->count() > 0)
    <div class="container mt-3">
        <div id="homeBannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            
            <div class="carousel-indicators">
                @foreach($banners as $key => $banner)
                    <button type="button"
                            data-bs-target="#homeBannerCarousel"
                            data-bs-slide-to="{{ $key }}"
                            class="{{ $key == 0 ? 'active' : '' }}"
                            aria-current="{{ $key == 0 ? 'true' : 'false' }}"
                            aria-label="Slide {{ $key + 1 }}">
                    </button>
                @endforeach
            </div>

            <div class="carousel-inner rounded overflow-hidden">
                @foreach($banners as $key => $banner)
                    <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                        @if($banner->link)
                            <a href="{{ $banner->link }}">
                                <img src="{{ asset('storage/' . $banner->image) }}"
                                     class="d-block w-100"
                                     alt="{{ $banner->title ?? 'Banner' }}"
                                     style="height: 400px; object-fit: cover;">
                            </a>
                        @else
                            <img src="{{ asset('storage/' . $banner->image) }}"
                                 class="d-block w-100"
                                 alt="{{ $banner->title ?? 'Banner' }}"
                                 style="height: 400px; object-fit: cover;">
                        @endif
                    </div>
                @endforeach
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>
@endif
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 text-center bg-primary text-white p-5 rounded shadow-sm">
            <h1 class="fw-bold"> Tổng Kho Nhựa Gia Dụng</h1>
            <p class="fs-5">Chất lượng cao - Giá tận xưởng - Giao hàng toàn quốc</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">
                     Danh Mục Sản Phẩm
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item {{ !request('category') ? 'bg-light border-start border-primary border-4' : '' }}">
                        <a href="/" class="text-decoration-none text-primary fw-bold"> Tất Cả Sản Phẩm</a>
                    </li>
                    
                    @foreach($categories as $cat)
                        <li class="list-group-item {{ request('category') == $cat->id ? 'bg-light border-start border-primary border-4' : '' }}">
                            <a href="/?category={{ $cat->id }}" class="text-decoration-none text-dark fw-bold">{{ $cat->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="col-md-9">
            
            <form action="/" method="GET" class="mb-4">
                <div class="input-group shadow-sm">
                    <input type="text" name="search" class="form-control border-primary form-control-lg" placeholder="🔍 Bạn đang tìm rổ nhựa, tủ, hay hộp đựng thực phẩm?..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary fw-bold px-4">TÌM KIẾM</button>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold"> Sản Phẩm Nổi Bật</h4>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4">
                @forelse($products as $p)
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 product-card">
                        <img src="{{ $p->image ?? 'https://via.placeholder.com/300x300?text=No+Image' }}" class="card-img-top" alt="{{ $p->name }}" style="height: 220px; object-fit: cover;">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-truncate" title="{{ $p->name }}">{{ $p->name }}</h5>
                            
                            <div class="mb-3">
                                @if($p->sale_price)
                                    <span class="text-danger fw-bold fs-5">{{ number_format($p->sale_price, 0, ',', '.') }}đ</span>
                                    <span class="text-muted text-decoration-line-through ms-2" style="font-size: 0.9rem;">{{ number_format($p->price, 0, ',', '.') }}đ</span>
                                @else
                                    <span class="text-danger fw-bold fs-5">{{ number_format($p->price, 0, ',', '.') }}đ</span>
                                @endif
                            </div>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 0.85rem;">Kho: {{ $p->stock_quantity }}</span>
                                <a href="/cart/add/{{ $p->id }}" class="btn btn-outline-primary btn-sm fw-bold"> Thêm Giỏ</a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        Không tìm thấy sản phẩm nào! Bạn thử tìm từ khóa khác xem sao.
                    </div>
                </div>
                @endforelse
            </div>

        </div>
    </div>
</div>

<style>
    .product-card { transition: transform 0.2s, box-shadow 0.2s; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>
@endsection