@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="fw-bold mb-4"> Quản Lý Phân Quyền Nhân Sự</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên người dùng</th>
                        <th>Email</th>
                        <th>Ngày đăng ký</th>
                        <th>Chức vụ hiện tại</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td class="fw-bold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td>
                            <form action="/admin/users/{{ $user->id }}/role" method="POST" class="d-flex gap-2">
                                @csrf
                                <select name="role" class="form-select form-select-sm" {{ $user->id == auth()->id() ? 'disabled' : '' }}>
                                    <option value="customer" {{ $user->role == 'customer' ? 'selected' : '' }}>Khách hàng</option>
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin </option>
                                </select>
                                
                                @if($user->id != auth()->id())
                                    <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
                                @endif
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