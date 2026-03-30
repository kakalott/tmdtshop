@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Sửa Banner</h2>

    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Tiêu đề</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $banner->title) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh hiện tại</label><br>
            <img src="{{ asset('storage/' . $banner->image) }}" width="200" alt="banner">
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh mới</label>
            <input type="file" name="image" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Link</label>
            <input type="text" name="link" class="form-control" value="{{ old('link', $banner->link) }}">
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
