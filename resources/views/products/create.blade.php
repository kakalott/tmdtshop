@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold fs-5"> Thêm Sản Phẩm Mới</div>
                <div class="card-body p-4 bg-light">
                    
                    <form action="/admin/products/store" method="POST">
                        @csrf 

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="VD: Ghế nhựa lùn" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold"> Phân loại Danh mục <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Click để chọn danh mục --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold"> Giá bán lẻ (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control text-danger fw-bold fs-5" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold"> Giá bán sỉ (VNĐ) <span class="text-muted fs-6 fw-normal">(Tùy chọn)</span></label>
                                <input type="number" name="wholesale_price" class="form-control text-primary fw-bold fs-5" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"> Link Ảnh Bìa (URL)</label>
                            <input type="text" name="image" class="form-control" placeholder="https://...">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold"> Mô tả chi tiết</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Nhập chất liệu, công dụng..."></textarea>
                        </div>  

                        <div class="card mb-4 border-info shadow-sm">
                            <div class="card-header bg-info text-dark fw-bold d-flex justify-content-between align-items-center">
                                <span> Phân loại <span class="text-danger">*</span></span>
                                <button type="button" class="btn btn-sm btn-dark fw-bold" onclick="addVariantRow()">+ Thêm Phân Loại</button>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered align-middle mb-0 text-center bg-white">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tên loại </th>
                                            <th width="150">Số lượng Kho</th>
                                            <th width="300">Ảnh </th>
                                            <th width="60">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variantBody">
                                        <tr>
                                            <td><input type="text" name="variants[0][color]" class="form-control border-info fw-bold text-primary" value="Mặc định" required></td>
                                            <td><input type="number" name="variants[0][stock_quantity]" class="form-control text-center fw-bold" value="0" min="0" required></td>
                                            <td><input type="text" name="variants[0][image]" class="form-control" placeholder="Link ảnh..."></td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger fw-bold" disabled>❌</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <script>
                            let variantIndex = 1; 
                            function addVariantRow() {
                                let tbody = document.getElementById('variantBody');
                                let tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td><input type="text" name="variants[${variantIndex}][color]" class="form-control border-info fw-bold text-primary" placeholder="VD: Màu Xanh" required></td>
                                    <td><input type="number" name="variants[${variantIndex}][stock_quantity]" class="form-control text-center fw-bold" value="0" min="0" required></td>
                                    <td><input type="text" name="variants[${variantIndex}][image]" class="form-control" placeholder="Link ảnh..."></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="this.closest('tr').remove()">❌</button></td>
                                `;
                                tbody.appendChild(tr);
                                variantIndex++;
                            }
                        </script>          
                        
                        <button type="submit" class="btn btn-success w-100 fw-bold fs-5 py-3 shadow-sm"> Lưu Sản Phẩm Vào Kho</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection