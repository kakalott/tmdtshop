@extends('layouts.app')

@section('content')
@php
    $activeCategory = $categories->firstWhere('id', request('category'));
@endphp

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

            <div class="carousel-inner rounded overflow-hidden shadow-sm">
                @foreach($banners as $key => $banner)
                    <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                        @if($banner->link)
                            <a href="{{ $banner->link }}">
                                <img src="{{ $banner->image }}"
                                     class="d-block w-100"
                                     alt="{{ $banner->title ?? 'Banner' }}"
                                     style="height: 400px; object-fit: cover;">
                            </a>
                        @else
                            <img src="{{ $banner->image }}"
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

<section class="brave-category-strip" aria-label="Danh mục nổi bật">
    <a href="{{ url('/') }}" class="{{ !request('category') ? 'active' : '' }}">Tất cả</a>
    @foreach($categories->take(10) as $cat)
        <a href="{{ url('/?category=' . $cat->id) }}" class="{{ request('category') == $cat->id ? 'active' : '' }}">{{ $cat->name }}</a>
    @endforeach
</section>

<section id="brave-products" class="brave-products">
    <div class="brave-section-heading">
        <div>
            <p>{{ $activeCategory ? $activeCategory->name : 'Dành cho bạn' }}</p>
            <h2>{{ request('search') ? 'Kết quả tìm kiếm' : 'Sản phẩm nổi bật' }}</h2>
        </div>
        <form action="{{ url('/') }}" method="GET" class="brave-inline-search">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Tìm trong BRAVE">
            <button type="submit">Tìm kiếm</button>
        </form>
    </div>

    <div class="brave-product-grid">
        @forelse($products as $p)
            @php
                $displayImage = $p->image;

                if (empty($displayImage) && $p->variants && $p->variants->count() > 0) {
                    $displayImage = $p->variants->first()->image;
                }

                if (empty($displayImage)) {
                    $displayImage = 'https://dummyimage.com/600x760/f1f1f1/111111.png&text=BRAVE';
                }

                $mainPrice = $p->sale_price ?? $p->price;
            @endphp

            <article class="brave-product-card">
                <a href="/product/{{ $p->id }}" class="brave-product-card__image">
                    <img src="{{ $displayImage }}" alt="{{ $p->name }}" loading="lazy">
                    @if($p->wholesale_price && $p->wholesale_price < $mainPrice)
                        <span>Giá sỉ</span>
                    @endif
                </a>
                <div class="brave-product-card__body">
                    <a href="/product/{{ $p->id }}" class="brave-product-card__title" title="{{ $p->name }}">{{ $p->name }}</a>
                    <div class="brave-product-card__meta">
                        <strong>{{ number_format($mainPrice, 0, ',', '.') }}đ</strong>
                        <span>Kho: {{ $p->stock_quantity }}</span>
                    </div>
                    <a href="/product/{{ $p->id }}" class="brave-product-card__action">Xem nhanh</a>
                </div>
            </article>
        @empty
            <div class="brave-empty">
                <h2>Không tìm thấy sản phẩm nào</h2>
                <p>Thử tìm từ khóa khác hoặc quay về tất cả sản phẩm.</p>
                <a href="{{ url('/') }}">Xem tất cả</a>
            </div>
        @endforelse
    </div>
</section>
@endsection
