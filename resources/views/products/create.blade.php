@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">Thêm Sản Phẩm Mới (Kho Đồ Nhựa)</div>
                <div class="card-body">
                    
                    <form action="/admin/products/store" method="POST">
                        @csrf 

                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm (VD: Ghế nhựa lùn)</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"> Phân loại Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Click để chọn danh mục --</option>
        
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
        
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá bán lẻ (VNĐ)</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá bán sỉ (VNĐ)</label>
                                <input type="number" name="wholesale_price" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số lượng nhập kho</label>
                                <input type="number" name="stock_quantity" class="form-control" value="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link Ảnh (URL)</label>
                                <input type="text" name="image" class="form-control" placeholder="https://...">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold">Lưu Vào Kho</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection