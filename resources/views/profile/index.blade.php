@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold text-center py-3 fs-5">
                     THÔNG TIN CÁ NHÂN
                </div>
                <div class="card-body mt-4 mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center border-end mb-4 mb-md-0">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&color=fff&size=150" class="rounded-circle mb-3 shadow-sm border border-3 border-light">
                            <h4 class="fw-bold text-primary mb-1">{{ $user->name }}</h4>
                            <div class="mt-2">
                                @if($user->role == 'admin') 
                                    <span class="badge bg-danger fs-6 px-3 py-2">Quản trị viên</span>
                                @elseif($user->role == 'employee')
                                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">Nhân viên</span>
                                @else 
                                    <span class="badge bg-success fs-6 px-3 py-2">Khách hàng thành viên</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-8 ps-md-4">
                            <h5 class="fw-bold border-bottom pb-2 mb-3 text-secondary">Chi tiết liên hệ</h5>
                            
                            <p class="fs-5 mb-2"><strong> Họ và tên:</strong> {{ $user->name }}</p>
                            <p class="fs-5 mb-2"><strong> Email:</strong> {{ $user->email }}</p>
                            <p class="fs-5 mb-2"><strong> Số điện thoại:</strong> <span class="{{ $user->phone ? '' : 'text-muted fst-italic' }}">{{ $user->phone ?? 'Chưa cập nhật' }}</span></p>
                            <p class="fs-5 mb-2"><strong> Địa chỉ:</strong> <span class="{{ $user->address ? '' : 'text-muted fst-italic' }}">{{ $user->address ?? 'Chưa cập nhật' }}</span></p>
                            <p class="fs-5 mb-4 border-bottom pb-3"><strong> Ngày tham gia:</strong> {{ $user->created_at->format('d/m/Y') }}</p>

                            <div class="d-flex gap-2">
                                <a href="/profile/edit" class="btn btn-warning fw-bold flex-grow-1">
                                     Chỉnh sửa thông tin
                                </a>
                                <a href="/profile/orders" class="btn btn-info text-white fw-bold flex-grow-1">
                                     Xem đơn hàng
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection