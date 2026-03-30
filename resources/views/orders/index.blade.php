@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"> Quản Lý Đơn Hàng</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="px-3">Mã Đơn</th>
                        <th>Khách Hàng</th>
                        <th>Thanh Toán</th>
                        <th>Tổng Tiền</th>
                        <th>Ngày Đặt</th>
                        <th width="150" class="text-center">Trạng Thái</th>
                        <th class="text-center" width="280">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="px-3 fw-bold text-primary">#{{ $order->id }}</td>
                        <td>
                            <strong>{{ $order->customer_name }}</strong><br>
                            <small class="text-muted">SĐT {{ $order->customer_phone }}</small>
                        </td>
                        <td>
                            @if($order->payment_method == 'ONLINE')
                                <span class="badge bg-primary"> ONLINE</span>
                            @else
                                <span class="badge bg-secondary"> COD</span>
                            @endif
                        </td>
                        <td class="fw-bold text-danger fs-5">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                        <td><small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small></td>
                        
                        <td class="text-center">
                            @if($order->status == 'unpaid')
                                <span class="badge bg-danger px-2 py-2"> Chưa thanh toán</span>
                            @elseif($order->status == 'pending')
                                <span class="badge bg-warning text-dark px-2 py-2">Chờ xử lý</span>
                            @elseif($order->status == 'shipping')
                                <span class="badge bg-info text-dark px-2 py-2"> Đang giao</span>
                            @elseif($order->status == 'completed')
                                <span class="badge bg-success px-2 py-2"> Hoàn thành</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-secondary px-2 py-2"> Đã hủy</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-info text-white fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#orderModal{{ $order->id }}" title="Xem chi tiết">
                                    Chi tiết
                                </button>

                                @if($order->status == 'pending' || $order->status == 'unpaid')
                                    <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="status" value="shipping">
                                        <button type="submit" class="btn btn-sm btn-primary fw-bold shadow-sm">
                                             Giao Hàng
                                        </button>
                                    </form>
                                @endif

                                @if($order->status == 'shipping')
                                    <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-sm btn-success fw-bold shadow-sm">
                                             Hoàn Thành
                                        </button>
                                    </form>
                                @endif

                                @if(in_array($order->status, ['pending', 'unpaid', 'shipping']))
                                    <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="m-0" onsubmit="return confirm('⚠️ Chú ý: Đơn hàng sẽ bị hủy và số lượng sẽ được cộng trả lại vào kho. Bạn có chắc chắn?');">
                                        @csrf
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold shadow-sm">
                                             Hủy
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="modal fade text-start" id="orderModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title fw-bold">Chi tiết đơn hàng #{{ $order->id }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row mb-4 bg-light p-3 rounded">
                                                <div class="col-md-6">
                                                    <p class="mb-1 text-muted">Người nhận:</p>
                                                    <h6 class="fw-bold">{{ $order->customer_name }}</h6>
                                                    <p class="mb-1 text-muted mt-2">Số điện thoại:</p>
                                                    <h6 class="fw-bold">{{ $order->customer_phone }}</h6>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1 text-muted">Địa chỉ nhận hàng:</p>
                                                    <h6 class="fw-bold">{{ $order->shipping_address }}</h6>
                                                    <p class="mb-1 text-muted mt-2">Ghi chú của khách:</p>
                                                    <h6 class="fw-bold text-danger">{{ $order->notes ?? 'Không có ghi chú' }}</h6>
                                                </div>
                                            </div>
                                            
                                            <h6 class="fw-bold border-bottom pb-2 text-primary"> Sản phẩm đã đặt:</h6>
                                            <ul class="list-group list-group-flush mb-3">
                                                @foreach($order->details as $detail)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $detail->product->image ?? 'https://via.placeholder.com/50' }}" width="50" class="rounded me-3 border shadow-sm">
                                                            <div>
                                                                <span class="fw-bold text-dark">{{ $detail->product->name ?? 'Sản phẩm đã bị xóa khỏi kho' }}</span><br>
                                                                <small class="text-muted">Số lượng: {{ $detail->quantity }} x {{ number_format($detail->price, 0, ',', '.') }}đ</small>
                                                            </div>
                                                        </div>
                                                        <span class="fw-bold text-danger fs-6">{{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}đ</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            
                                            <div class="d-flex justify-content-between align-items-center mt-4">
                                                <span class="fs-5 fw-bold text-muted">Tổng thanh toán:</span>
                                                <span class="fs-3 fw-bold text-danger">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted fst-italic">Chưa có đơn hàng nào trong hệ thống!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection