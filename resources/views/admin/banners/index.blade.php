@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Quản lý Banner</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary mb-3">+ Thêm banner</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tiêu đề</th>
                <th>Link</th>
                <th>Thứ tự</th>
                <th>Trạng thái</th>
                <th width="180">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($banners as $banner)
                <tr>
                    <td>{{ $banner->id }}</td>
                    <td>
                        <img src="{{ asset('storage/' . $banner->image) }}" width="120" alt="banner">
                    </td>
                    <td>{{ $banner->title }}</td>
                    <td>{{ $banner->link }}</td>
                    <td>{{ $banner->sort_order }}</td>
                    <td>
                        {{ $banner->is_active ? 'Hiển thị' : 'Ẩn' }}
                    </td>
                    <td>
                        <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-warning btn-sm">Sửa</a>

                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Bạn có chắc muốn xóa?')" class="btn btn-danger btn-sm">
                                Xóa
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Chưa có banner nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection