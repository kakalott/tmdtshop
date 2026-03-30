@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 px-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">Thống kê</h2>
        
        <form action="/admin/dashboard" method="GET" class="d-flex align-items-center">
            <label class="fw-bold me-2 text-muted">Hiển thị theo:</label>
            <select name="time" class="form-select border-primary fw-bold" onchange="this.form.submit()" style="width: 180px;">
                <option value="all" {{ $time == 'all' ? 'selected' : '' }}> Tất cả thời gian</option>
                <option value="day" {{ $time == 'day' ? 'selected' : '' }}> Hôm nay</option>
                <option value="week" {{ $time == 'week' ? 'selected' : '' }}> Tuần này</option>
                <option value="month" {{ $time == 'month' ? 'selected' : '' }}> Tháng này</option>
            </select>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white shadow border-0 h-100">
                <div class="card-body">
                    <h6 class="text-uppercase fw-bold mb-2"> Tổng Doanh Thu</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($totalRevenue, 0, ',', '.') }}đ</h2>
                    
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white shadow border-0 h-100">
                <div class="card-body position-relative">
                    <h6 class="text-uppercase fw-bold mb-2"> Đơn Hoàn Thành / Tổng Đơn</h6>
                    <h2 class="fw-bold mb-0">
                        {{ $completedOrders }} <span class="fs-4 fw-normal">/ {{ $totalOrders }}</span>
                        <span class="fs-5 fw-normal ms-2 text-white">({{ $successRate }}%)</span>
                    </h2>
                    <small class="text-light">Tỉ lệ giao hàng thành công</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card bg-danger text-white shadow border-0 h-100" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#topBuyersModal">
                <div class="card-body position-relative">
                    <h6 class="text-uppercase fw-bold mb-2"> Số Khách Mua Hàng</h6>
                    <h2 class="fw-bold mb-0">{{ $activeCustomersCount }} <span class="fs-5 fw-normal">người</span></h2>
                    <small class="text-light text-decoration-underline"> chi tiết</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-warning text-dark fw-bold py-3 fs-5">
             Top 5 Sản Phẩm Bán Chạy Nhất
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3" width="80">Hạng</th>
                        <th>Sản Phẩm</th>
                        <th class="text-center">Số lượng đã bán</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bestSellingProducts as $index => $item)
                    <tr>
                        <td class="px-3 fw-bold fs-5 text-danger">#{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $item->product->image ?? 'https://via.placeholder.com/40' }}" width="40" class="rounded border me-3">
                                <strong class="text-primary">{{ $item->product->name ?? 'Sản phẩm đã xóa' }}</strong>
                            </div>
                        </td>
                        <td class="text-center fw-bold fs-5">{{ $item->total_sold }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted fst-italic">Chưa có dữ liệu bán hàng trong khoảng thời gian này!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="modal fade" id="orderRatesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Biểu Đồ Tỉ Lệ Đơn Hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-3 text-success"> Tỉ lệ giao thành công: {{ $successRate }}% ({{ $completedOrders }}/{{ $totalOrders }} đơn)</h6>
                <div class="progress mb-4" style="height: 25px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: {{ $successRate }}%"></div>
                </div>

                <h6 class="fw-bold mb-3 text-danger"> Tỉ lệ đơn bị hủy: {{ $cancelRate }}% ({{ $cancelledOrders }}/{{ $totalOrders }} đơn)</h6>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" style="width: {{ $cancelRate }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="topBuyersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"> Danh Sách Khách Hàng Mua Nhiều Nhất</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">Tên Khách Hàng</th>
                            <th class="text-center">Số đơn đã mua</th>
                            <th class="text-end px-4">Tổng tiền đã chi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topBuyers as $buyer)
                        <tr>
                            <td class="px-4 fw-bold text-primary">{{ $buyer->user->name ?? 'Tài khoản đã xóa' }}</td>
                            <td class="text-center fw-bold">{{ $buyer->order_count }} đơn</td>
                            <td class="text-end px-4 fw-bold text-danger fs-5">{{ number_format($buyer->total_spent, 0, ',', '.') }}đ</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted fst-italic">Chưa có khách hàng nào!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection