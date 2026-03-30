@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark fw-bold text-center py-3 fs-5">
                     CẬP NHẬT THÔNG TIN CÁ NHÂN
                </div>
                <div class="card-body mt-3">
                    
                    <form action="/profile/update" method="POST">
                        @csrf
                        @method('PUT') <div class="mb-3">
                            <label class="form-label fw-bold">Họ và tên</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email đăng nhập</label>
                            <input type="email" class="form-control bg-light text-muted" value="{{ $user->email }}" readonly>
                            <small class="text-danger fst-italic">Để bảo mật, bạn không thể tự đổi email đăng nhập.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Số điện thoại liên hệ</label>
                            <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" placeholder="Nhập số điện thoại...">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Địa chỉ giao hàng mặc định</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Nhập địa chỉ nhận hàng của bạn...">{{ $user->address }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="/profile" class="btn btn-secondary fw-bold flex-grow-1"> Hủy bỏ</a>
                            <button type="submit" class="btn btn-warning fw-bold flex-grow-1 text-dark"> Lưu Thay Đổi</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection