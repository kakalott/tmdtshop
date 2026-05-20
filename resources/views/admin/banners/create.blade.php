@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Thêm Banner</h2>

    <form action="{{ route('admin.banners.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Tiêu đề</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Đường link ảnh (URL)</label>
            <input type="text" name="image" class="form-control" value="{{ old('image') }}" placeholder="Nhập đường link ảnh (VD: https://...)" required>
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

                <input type="hidden" name="link" value="{{ old('link') }}" data-banner-link-input>
                <div class="form-text mt-2">Ví dụ: banner đồ nhựa chọn “Trang danh mục”; banner mặt hàng cụ thể chọn “Trang sản phẩm”.</div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Thứ tự hiển thị</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
            <label class="form-check-label" for="is_active">
                Hiển thị banner
            </label>
        </div>

        <button type="submit" class="btn btn-success">Lưu</button>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection
