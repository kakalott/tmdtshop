@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"> Quản Lý Kho Đồ Nhựa</h2>
        <a href="/admin/products/create" class="btn btn-primary fw-bold">+ Thêm Sản Phẩm Mới</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-dark">
                    <tr class="text-center">
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th class="text-start">Tên Sản Phẩm</th>
                        <th>Giá Bán Lẻ</th>
                        <th>Giá Sỉ (B2B)</th>
                        <th>Tồn Kho</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach($products as $p)
                    <tr>
                        <td>{{ $p->id }}</td>
                        <td>
                            <img src="{{ $p->image }}" alt="Ảnh" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        </td>
                        <td class="text-start fw-bold text-primary">{{ $p->name }}</td>
                        <td class="text-danger fw-bold">{{ number_format($p->price, 0, ',', '.') }}đ</td>
                        <td class="text-success fw-bold">{{ number_format($p->wholesale_price, 0, ',', '.') }}đ</td>
                        <td>
                            @if($p->stock_quantity > 10)
                                <span class="badge bg-success">{{ $p->stock_quantity }}</span>
                            @else
                                <span class="badge bg-danger">Sắp hết ({{ $p->stock_quantity }})</span>
                            @endif
                        </td>
                        <td>
                            <a href="/admin/products/{{ $p->id }}/edit" class="btn btn-warning btn-sm fw-bold text-dark"> Sửa</a>
                            
                            <form action="/admin/products/{{ $p->id }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi kho không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm fw-bold"> Xóa</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection