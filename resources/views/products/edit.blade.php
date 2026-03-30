@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark fw-bold"> Sửa Thông Tin Sản Phẩm</div>
                <div class="card-body">
                    
                    <form action="/admin/products/{{ $product->id }}" method="POST">
                        @csrf 
                        @method('PUT') <div class="mb-3">
                            <label class="form-label">Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                        </div>
                        <div class="mb-4 p-3 bg-light rounded border">
                            <label class="form-label fw-bold text-dark"> Phân loại Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select border-primary" required>
                                <option value="">-- Vui lòng chọn danh mục cho sản phẩm --</option>
                                
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                                
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá bán lẻ (VNĐ)</label>
                                <input type="number" name="price" class="form-control" value="{{ $product->price }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá bán sỉ (VNĐ)</label>
                                <input type="number" name="wholesale_price" class="form-control" value="{{ $product->wholesale_price }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số lượng tồn kho</label>
                                <input type="number" name="stock_quantity" class="form-control" value="{{ $product->stock_quantity }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link Ảnh (URL)</label>
                                <input type="text" name="image" class="form-control" value="{{ $product->image }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold text-dark">Lưu Thay Đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection