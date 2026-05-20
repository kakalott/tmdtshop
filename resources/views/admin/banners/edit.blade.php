@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Sửa Banner</h2>

    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Tiêu đề</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $banner->title) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh hiện tại</label><br>
            <img src="{{ $banner->image }}" width="200" alt="banner" class="img-thumbnail mb-2">
        </div>

        <div class="mb-3">
            <label class="form-label">Đường link ảnh (URL)</label>
            <input type="text" name="image" class="form-control" value="{{ old('image', $banner->image) }}" placeholder="Nhập đường link ảnh (VD: https://...)">
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <label class="form-label fw-bold">Link đích khi click vào banner</label>

                <div class="row g-3" data-banner-link-builder>
                    <div class="col-md-4">
                        <label class="form-label">Kiểu liên kết</label>
                        <select class="form-select" data-link-type>
                            <option value="">Không gắn link</option>
                            <option value="category">Trang danh mục</option>
                            <option value="product">Trang sản phẩm</option>
                            <option value="custom">URL tùy chỉnh</option>
                        </select>
                    </div>

                    <div class="col-md-8 d-none" data-link-panel="category">
                        <label class="form-label">Chọn danh mục</label>
                        <select class="form-select" data-link-value>
                            <option value="">Chọn danh mục</option>
                            @foreach($categories as $category)
                                <option value="{{ url('/?category=' . $category->id) }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8 d-none" data-link-panel="product">
                        <label class="form-label">Chọn sản phẩm</label>
                        <select class="form-select" data-link-value>
                            <option value="">Chọn sản phẩm</option>
                            @foreach($products as $product)
                                <option value="{{ url('/product/' . $product->id) }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8 d-none" data-link-panel="custom">
                        <label class="form-label">URL tùy chỉnh</label>
                        <input type="text" class="form-control" data-link-value placeholder="VD: /?search=do+nhua hoặc https://...">
                    </div>
                </div>

                <input type="hidden" name="link" value="{{ old('link', $banner->link) }}" data-banner-link-input>
                <div class="form-text mt-2">Link hiện tại: <span data-banner-link-preview>{{ old('link', $banner->link) ?: 'Chưa gắn link' }}</span></div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Thứ tự hiển thị</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $banner->sort_order) }}">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                {{ $banner->is_active ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                Hiển thị banner
            </label>
        </div>

        <button type="submit" class="btn btn-success">Cập nhật</button>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection
