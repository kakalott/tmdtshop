@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header {{ isset($editingCategory) ? 'bg-warning text-dark' : 'bg-success text-white' }} fw-bold">
                    {{ isset($editingCategory) ? 'Sửa Danh Mục' : '➕ Thêm Danh Mục Mới' }}
                </div>
                <div class="card-body">
                    <form action="{{ isset($editingCategory) ? '/admin/categories/' . $editingCategory->id : '/admin/categories' }}" method="POST">
                        @csrf
                        @isset($editingCategory)
                            @method('PUT')
                        @endisset

                        <div class="mb-3">
                            <label class="form-label">Tên danh mục</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $editingCategory->name ?? '') }}" required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn {{ isset($editingCategory) ? 'btn-warning' : 'btn-success' }} w-100 fw-bold">
                            {{ isset($editingCategory) ? 'Cập Nhật' : 'Thêm Ngay' }}
                        </button>

                        @isset($editingCategory)
                            <a href="/admin/categories" class="btn btn-outline-secondary w-100 fw-bold mt-2">Hủy sửa</a>
                        @endisset
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">Danh Sách Các Loại Đồ Nhựa</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tên Danh Mục</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                                <tr>
                                    <td>{{ $cat->id }}</td>
                                    <td class="fw-bold text-primary">{{ $cat->name }}</td>
                                    <td class="text-end">
                                        <a href="/admin/categories/{{ $cat->id }}/edit" class="btn btn-sm btn-outline-warning me-1">Sửa</a>
                                        <form action="/admin/categories/{{ $cat->id }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Chắc chắn xóa?')">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
