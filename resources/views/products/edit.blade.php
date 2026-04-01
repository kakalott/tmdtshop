@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark fw-bold fs-5"> Sửa Thông Tin Sản Phẩm</div>
                <div class="card-body p-4 bg-light">
                    
                    <form action="/admin/products/{{ $product->id }}" method="POST">
                        @csrf 
                        @method('PUT') 
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control border-warning" value="{{ $product->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold"> Phân loại Danh mục <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select border-warning" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold"> Giá bán lẻ (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control text-danger fw-bold fs-5 border-warning" value="{{ $product->price }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold"> Giá bán sỉ (VNĐ) <span class="text-muted fs-6 fw-normal">(Tùy chọn)</span></label>
                                <input type="number" name="wholesale_price" class="form-control text-primary fw-bold fs-5 border-warning" value="{{ $product->wholesale_price }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"> Link Ảnh Bìa (URL)</label>
                            <input type="text" name="image" class="form-control" value="{{ $product->image }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold"> Mô tả chi tiết</label>
                            <textarea name="description" class="form-control" rows="5">{{ $product->description }}</textarea>
                        </div>

                        <div class="card mb-4 border-warning shadow-sm">
                            <div class="card-header bg-warning text-dark fw-bold d-flex justify-content-between align-items-center">
                                <span> Phân loại  <span class="text-danger">*</span></span>
                                <button type="button" class="btn btn-sm btn-dark fw-bold" onclick="addVariantRow()">+ Thêm Phân Loại</button>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered align-middle mb-0 text-center bg-white">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tên loại (Giữ nguyên nếu không chia màu)</th>
                                            <th width="150">Số lượng Kho</th>
                                            <th width="300">Ảnh </th>
                                            <th width="60">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variantBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <script>
                            let variantIndex = 0;
                            let existingVariants = {!! json_encode($product->variants) !!};

                            window.onload = function() {
                                if(existingVariants.length > 0) {
                                    existingVariants.forEach(v => {
                                        let colorName = v.color || v.size || 'Mặc định';
                                        addVariantRow(colorName, v.stock_quantity, v.image);
                                    });
                                } else {
                                    addVariantRow('Mặc định');
                                }
                            };

                            function addVariantRow(color = '', stock = '0', image = '') {
                                let tbody = document.getElementById('variantBody');
                                if(image === null) image = '';
                                
                                let tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td><input type="text" name="variants[${variantIndex}][color]" class="form-control border-warning fw-bold text-primary" placeholder="VD: Màu Xanh" value="${color}" required></td>
                                    <td><input type="number" name="variants[${variantIndex}][stock_quantity]" class="form-control text-center fw-bold" value="${stock}" min="0" required></td>
                                    <td><input type="text" name="variants[${variantIndex}][image]" class="form-control" placeholder="Link ảnh..." value="${image}"></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="if(document.querySelectorAll('#variantBody tr').length > 1) this.closest('tr').remove(); else alert('Phải giữ lại ít nhất 1 phân loại để nhập kho!');">❌</button></td>
                                `;
                                tbody.appendChild(tr);
                                variantIndex++;
                            }
                        </script>

                        <button type="submit" class="btn btn-warning w-100 fw-bold fs-5 py-3 text-dark shadow-sm"> Cập Nhật Thay Đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection