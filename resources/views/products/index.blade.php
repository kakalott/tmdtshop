@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"> Quản Lý Kho Đồ Nhựa</h2>
        <a href="/admin/products/create" class="btn btn-primary fw-bold">+ Thêm Sản Phẩm Mới</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th width="60">ID</th>
                            <th width="80">Ảnh</th>
                            <th class="text-start">Tên Sản Phẩm</th>
                            <th>Giá Bán Lẻ</th>
                            <th>Giá Sỉ (B2B)</th>
                            <th>Tồn Kho (Tổng)</th>
                            <th width="150">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($products as $p)
                        <tr>
                            <td class="fw-bold text-muted">{{ $p->id }}</td>
                            <td>
                                @php
                                    // 1. Ưu tiên lấy ảnh bìa
                                    $displayImage = $p->image;
                                    
                                    // 2. Nếu không có ảnh bìa, kiểm tra xem có biến thể (variants) nào không
                                    // *Lưu ý: Nếu Model của bạn đặt tên hàm là productVariants() thì đổi chữ variants thành productVariants nhé!
                                    if (empty($displayImage) && $p->variants && $p->variants->count() > 0) {
                                        // Lôi cái ảnh của phân loại đầu tiên ra
                                        $displayImage = $p->variants->first()->image;
                                    }

                                    // 3. Nếu phân loại đó cũng lười không nhập ảnh luôn, thì xài ảnh trắng
                                    if (empty($displayImage)) {
                                        $displayImage = 'https://dummyimage.com/200x200/cccccc/000000.png&text=No+Image';
                                    }
                                @endphp

                                <img src="{{ $displayImage }}" 
                                     alt="Ảnh" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"
                                     class="border shadow-sm">
                            </td>
                            <td class="text-start fw-bold text-primary fs-6">{{ $p->name }}</td>
                            <td class="text-danger fw-bold">{{ number_format($p->price, 0, ',', '.') }}đ</td>
                            <td class="text-success fw-bold">{{ number_format($p->wholesale_price, 0, ',', '.') }}đ</td>
                            <td>
                                @if($p->stock_quantity > 10)
                                    <span class="badge bg-success fs-6">{{ $p->stock_quantity }}</span>
                                @elseif($p->stock_quantity > 0)
                                    <span class="badge bg-warning text-dark fs-6">Sắp hết ({{ $p->stock_quantity }})</span>
                                @else
                                    <span class="badge bg-danger fs-6">Hết hàng</span>
                                @endif
                            </td>
                            <td>
                                <a href="/admin/products/{{ $p->id }}/edit" class="btn btn-warning btn-sm fw-bold text-dark">✏️ Sửa</a>
                                
                                <form action="/admin/products/{{ $p->id }}" method="POST" class="d-inline" onsubmit="return confirm('⚠️ Bạn có chắc chắn muốn xóa sản phẩm này khỏi kho không? Hành động này không thể hoàn tác!');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm fw-bold"> Xóa</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted fst-italic">
                                <h5>Chưa có sản phẩm nào trong kho.</h5>
                                <p>Hãy bấm nút "Thêm Sản Phẩm Mới" ở góc trên để bắt đầu!</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection