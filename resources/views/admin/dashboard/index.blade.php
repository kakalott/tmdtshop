@extends('layouts.app')

@section('content')
@php
    $money = fn ($value) => number_format((float) $value, 0, ',', '.') . 'đ';
    $number = fn ($value) => number_format((float) $value, 0, ',', '.');
    $growthText = fn ($value) => ($value >= 0 ? '+' : '-') . abs($value) . '% so với kỳ trước';
    $growthClass = fn ($value) => $value >= 0 ? 'text-success bg-success-subtle' : 'text-danger bg-danger-subtle';
@endphp

<style>
    .admin-dashboard {
        background: #f5f7fb;
        min-height: calc(100vh - 120px);
        margin: -1.5rem 0 0;
        padding: 1.5rem 0 3rem;
    }

    .dashboard-hero {
        border: 0;
        border-radius: 26px;
        background: linear-gradient(135deg, #111827, #1d4ed8 55%, #0891b2);
        color: #fff;
        overflow: hidden;
        position: relative;
    }

    .dashboard-hero::after {
        content: "";
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .12);
        position: absolute;
        right: -60px;
        top: -90px;
    }

    .dashboard-filter .btn,
    .dashboard-filter .form-control {
        border-radius: 999px;
    }

    .kpi-card,
    .dashboard-card {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
    }

    .kpi-card {
        overflow: hidden;
        height: 100%;
        position: relative;
    }

    .kpi-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 5px;
        background: linear-gradient(90deg, #2563eb, #06b6d4);
    }

    .kpi-label {
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-size: .78rem;
    }

    .kpi-value {
        color: #0f172a;
        font-weight: 900;
        letter-spacing: -.04em;
    }

    .mini-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .65rem;
        border-radius: 999px;
        font-weight: 800;
        font-size: .78rem;
    }

    .chart-box {
        height: 330px;
    }

    .chart-box-sm {
        height: 260px;
    }

    .table > :not(caption) > * > * {
        padding: .9rem 1rem;
    }

    .rank-badge {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 900;
    }

    .insight-item {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        border-radius: 18px;
        padding: 1rem;
        color: #1e3a8a;
        font-weight: 700;
    }

    .stock-danger {
        background: #fff1f2;
    }

    @media (max-width: 768px) {
        .admin-dashboard {
            margin-top: -1rem;
        }
        .dashboard-filter .btn,
        .dashboard-filter .form-control {
            width: 100%;
        }
        .chart-box,
        .chart-box-sm {
            height: 260px;
        }
    }
</style>

<div class="admin-dashboard">
    <div class="container-fluid px-4">
        <div class="card dashboard-hero shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5 position-relative">
                <div class="row align-items-end g-4">
                    <div class="col-lg-6">
                        <div class="badge rounded-pill bg-white text-primary fw-bold mb-3 px-3 py-2">Dashboard quản trị</div>
                        <h1 class="display-6 fw-black fw-bold mb-2">Thống kê hệ thống thương mại điện tử</h1>
                        <p class="mb-0 text-white-50">
                            Dữ liệu từ {{ $startDate->format('d/m/Y') }} đến {{ $endDate->format('d/m/Y') }}.
                            Theo dõi doanh thu, đơn hàng, khách hàng, sản phẩm, tồn kho và marketing trong một màn hình.
                        </p>
                    </div>
                    <div class="col-lg-6">
                        <form action="/admin/dashboard" method="GET" class="dashboard-filter bg-white bg-opacity-10 rounded-4 p-3">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach([
                                    'today' => 'Hôm nay',
                                    'last7' => '7 ngày',
                                    'last30' => '30 ngày',
                                    'last90' => '3 tháng',
                                    'year' => '1 năm',
                                ] as $value => $label)
                                    <button type="submit" name="time" value="{{ $value }}" class="btn {{ $time === $value ? 'btn-light text-primary fw-bold' : 'btn-outline-light' }}">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>

                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $startDate->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $endDate->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4 d-grid">
                                    <button type="submit" name="time" value="custom" class="btn btn-warning fw-bold">Lọc tùy chọn</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card">
                    <div class="card-body p-4">
                        <div class="kpi-label mb-2">Doanh thu</div>
                        <h2 class="kpi-value mb-3">{{ $money($totalRevenue) }}</h2>
                        <span class="mini-badge {{ $growthClass($revenueGrowth) }}">{{ $growthText($revenueGrowth) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card">
                    <div class="card-body p-4">
                        <div class="kpi-label mb-2">Đơn hàng</div>
                        <h2 class="kpi-value mb-3">{{ $number($totalOrders) }}</h2>
                        <span class="mini-badge {{ $growthClass($orderGrowth) }}">{{ $growthText($orderGrowth) }}</span>
                        <div class="small text-muted mt-3">Hoàn thành: {{ $completedOrders }} · Hủy: {{ $cancelledOrders }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card">
                    <div class="card-body p-4">
                        <div class="kpi-label mb-2">Khách hàng</div>
                        <h2 class="kpi-value mb-3">{{ $number($activeCustomersCount) }}</h2>
                        <span class="mini-badge {{ $growthClass($customerGrowth) }}">{{ $growthText($customerGrowth) }}</span>
                        <div class="small text-muted mt-3">Khách hàng mới: {{ $newCustomers }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card">
                    <div class="card-body p-4">
                        <div class="kpi-label mb-2">Sản phẩm</div>
                        <h2 class="kpi-value mb-3">{{ $number($totalProducts) }}</h2>
                        <span class="mini-badge text-primary bg-primary-subtle">Đang kinh doanh: {{ $activeProducts }}</span>
                        <div class="small text-muted mt-3">Ngưỡng cảnh báo: 5 sản phẩm</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <h4 class="fw-bold mb-1">Biểu đồ doanh thu</h4>
                                <p class="text-muted mb-0">Xu hướng doanh thu theo {{ $revenueTrend['mode'] === 'day' ? 'ngày' : ($revenueTrend['mode'] === 'week' ? 'tuần' : 'tháng') }}.</p>
                            </div>
                            <span class="badge rounded-pill text-bg-primary px-3 py-2">{{ $money($totalRevenue) }}</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-box">
                            <canvas id="revenueLineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h4 class="fw-bold mb-1">Trạng thái đơn hàng</h4>
                        <p class="text-muted mb-0">Tỷ lệ xử lý đơn theo trạng thái.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-box-sm mb-3">
                            <canvas id="orderStatusPieChart"></canvas>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Trạng thái</th>
                                        <th class="text-end">Số lượng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orderStatusStats as $status)
                                        <tr>
                                            <td>{{ $status['label'] }}</td>
                                            <td class="text-end fw-bold">{{ $status['total'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="small text-muted mt-3">Tỷ lệ hoàn thành: {{ $successRate }}% · Tỷ lệ hủy: {{ $cancelRate }}%</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h4 class="fw-bold mb-1">Chi tiết doanh thu</h4>
                        <p class="text-muted mb-0">Bảng dữ liệu tương ứng với biểu đồ doanh thu.</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Thời gian</th>
                                        <th class="text-end">Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($revenueTrend['rows'] as $row)
                                        <tr>
                                            <td class="fw-bold">{{ $row['label'] }}</td>
                                            <td class="text-end fw-bold text-success">{{ $money($row['value']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-center text-muted py-4">Chưa có dữ liệu doanh thu.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h4 class="fw-bold mb-1">AI Insight</h4>
                        <p class="text-muted mb-0">Gợi ý tự động từ dữ liệu bán hàng.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-grid gap-3">
                            @forelse($aiInsights as $insight)
                                <div class="insight-item">{{ $insight }}</div>
                            @empty
                                <div class="text-muted fst-italic">Chưa đủ dữ liệu để tạo insight.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-7">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h4 class="fw-bold mb-1">Top sản phẩm bán chạy</h4>
                        <p class="text-muted mb-0">Top 10 sản phẩm theo số lượng bán ra.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-box-sm">
                            <canvas id="bestProductBarChart"></canvas>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Hạng</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Đã bán</th>
                                    <th class="text-end">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bestSellingProducts as $index => $item)
                                    @php
                                        $product = $item->product;
                                        $productImage = $product?->image ?: ($product?->variants?->first()?->image ?? 'https://dummyimage.com/80x80/e5e7eb/111827.png&text=BRAVE');
                                    @endphp
                                    <tr>
                                        <td><span class="rank-badge">{{ $index + 1 }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $productImage }}" width="48" height="48" class="rounded-3 border" style="object-fit: cover;" alt="{{ $product->name ?? 'Sản phẩm' }}">
                                                <div>
                                                    <div class="fw-bold">{{ $product->name ?? 'Sản phẩm đã xóa' }}</div>
                                                    <div class="small text-muted">ID: {{ $item->product_id ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold">{{ $number($item->total_sold) }}</td>
                                        <td class="text-end fw-bold text-success">{{ $money($item->total_revenue) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Chưa có sản phẩm bán chạy trong khoảng thời gian này.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h4 class="fw-bold mb-1">Doanh thu theo danh mục</h4>
                        <p class="text-muted mb-0">Tỷ trọng đóng góp doanh thu của từng danh mục.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-box-sm mb-3">
                            <canvas id="categoryRevenueChart"></canvas>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Danh mục</th>
                                        <th class="text-end">Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categoryRevenue as $category)
                                        <tr>
                                            <td class="fw-bold">{{ $category->category_name }}</td>
                                            <td class="text-end fw-bold text-success">{{ $money($category->revenue) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-center text-muted py-4">Chưa có doanh thu theo danh mục.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h4 class="fw-bold mb-1">Top khách hàng</h4>
                        <p class="text-muted mb-0">Khách hàng có tổng chi tiêu cao nhất.</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Khách hàng</th>
                                        <th class="text-center">Số đơn</th>
                                        <th class="text-end">Tổng chi tiêu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topCustomers as $customer)
                                        <tr>
                                            <td class="fw-bold text-primary">{{ $customer->user->name ?? $customer->customer_name ?? 'Khách vãng lai' }}</td>
                                            <td class="text-center fw-bold">{{ $customer->order_count }}</td>
                                            <td class="text-end fw-bold text-danger">{{ $money($customer->total_spent) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">Chưa có khách hàng mua hàng trong khoảng thời gian này.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h4 class="fw-bold mb-1">Khách hàng mới</h4>
                        <p class="text-muted mb-0">Theo dõi tốc độ tăng trưởng người dùng.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-box-sm">
                            <canvas id="newCustomerLineChart"></canvas>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Thời gian</th>
                                    <th class="text-end">Khách mới</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customerTrend['rows'] as $row)
                                    <tr>
                                        <td class="fw-bold">{{ $row['label'] }}</td>
                                        <td class="text-end fw-bold">{{ $number($row['value']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h4 class="fw-bold mb-1">Cảnh báo tồn kho</h4>
                        <p class="text-muted mb-0">Sản phẩm có tồn kho thấp cần nhập thêm.</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Tồn kho</th>
                                        <th class="text-center">Ngưỡng cảnh báo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lowStockProducts as $product)
                                        @php
                                            $productImage = $product->image ?: ($product->variants->first()->image ?? 'https://dummyimage.com/80x80/e5e7eb/111827.png&text=BRAVE');
                                        @endphp
                                        <tr class="{{ $product->stock_quantity <= 2 ? 'stock-danger' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="{{ $productImage }}" width="46" height="46" class="rounded-3 border" style="object-fit: cover;" alt="{{ $product->name }}">
                                                    <div>
                                                        <div class="fw-bold">{{ $product->name }}</div>
                                                        <div class="small text-muted">{{ $product->stock_quantity <= 0 ? 'Đã hết hàng' : 'Sắp hết hàng' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $product->stock_quantity <= 2 ? 'text-bg-danger' : 'text-bg-warning' }} fs-6">{{ $product->stock_quantity }}</span>
                                            </td>
                                            <td class="text-center fw-bold">5</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">Không có sản phẩm tồn kho thấp.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h4 class="fw-bold mb-1">Hiệu quả Voucher</h4>
                        <p class="text-muted mb-0">Doanh thu tạo ra từ các mã giảm giá.</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã giảm giá</th>
                                        <th class="text-center">Số lượt sử dụng</th>
                                        <th class="text-end">Doanh thu tạo ra</th>
                                        <th class="text-end">Tổng giảm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($voucherStats as $voucher)
                                        <tr>
                                            <td><span class="badge text-bg-dark px-3 py-2">{{ $voucher->code }}</span></td>
                                            <td class="text-center fw-bold">{{ $voucher->used_count }}</td>
                                            <td class="text-end fw-bold text-success">{{ $money($voucher->revenue_generated) }}</td>
                                            <td class="text-end fw-bold text-danger">{{ $money($voucher->discount_total) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Chưa có voucher được sử dụng trong khoảng thời gian này.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-card">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h4 class="fw-bold mb-1">Hiệu quả Banner</h4>
                <p class="text-muted mb-0">Nếu hệ thống chưa lưu lượt xem/lượt nhấp, bảng sẽ hiển thị trạng thái banner hiện có.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Banner</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Lượt xem</th>
                                <th class="text-center">Lượt nhấp</th>
                                <th class="text-center">CTR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bannerStats as $banner)
                                @php
                                    $hasMetrics = $banner['views'] !== null && $banner['clicks'] !== null;
                                    $ctr = $hasMetrics && $banner['views'] > 0 ? round(($banner['clicks'] / $banner['views']) * 100, 2) . '%' : 'N/A';
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $banner['title'] }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $banner['is_active'] ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $banner['is_active'] ? 'Đang chạy' : 'Tạm tắt' }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $hasMetrics ? $number($banner['views']) : 'N/A' }}</td>
                                    <td class="text-center fw-bold">{{ $hasMetrics ? $number($banner['clicks']) : 'N/A' }}</td>
                                    <td class="text-center fw-bold">{{ $ctr }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Chưa có banner.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            return;
        }

        const moneyFormatter = new Intl.NumberFormat('vi-VN');
        const compactFormatter = new Intl.NumberFormat('vi-VN', { notation: 'compact' });

        const moneyLabel = value => moneyFormatter.format(value || 0) + 'đ';
        const numberLabel = value => moneyFormatter.format(value || 0);

        const baseMoneyOptions = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => `${context.dataset.label || 'Giá trị'}: ${moneyLabel(context.raw)}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => compactFormatter.format(value) + 'đ' }
                }
            }
        };

        const createChart = (id, config) => {
            const element = document.getElementById(id);
            if (element) {
                new Chart(element, config);
            }
        };

        createChart('revenueLineChart', {
            type: 'line',
            data: {
                labels: @json($revenueTrend['labels']),
                datasets: [{
                    label: 'Doanh thu',
                    data: @json($revenueTrend['data']),
                    borderWidth: 3,
                    pointRadius: 4,
                    tension: .35,
                    fill: true
                }]
            },
            options: baseMoneyOptions
        });

        createChart('orderStatusPieChart', {
            type: 'doughnut',
            data: {
                labels: @json($orderStatusStats->pluck('label')->values()),
                datasets: [{
                    label: 'Số đơn',
                    data: @json($orderStatusStats->pluck('total')->values()),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: context => `${context.label}: ${numberLabel(context.raw)} đơn` } }
                }
            }
        });

        createChart('bestProductBarChart', {
            type: 'bar',
            data: {
                labels: @json($bestSellingProducts->map(fn ($item) => $item->product->name ?? 'Sản phẩm đã xóa')->values()),
                datasets: [{
                    label: 'Đã bán',
                    data: @json($bestSellingProducts->pluck('total_sold')->values()),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: context => `${numberLabel(context.raw)} sản phẩm` } }
                },
                scales: { y: { beginAtZero: true } }
            }
        });

        createChart('categoryRevenueChart', {
            type: 'doughnut',
            data: {
                labels: @json($categoryRevenue->pluck('category_name')->values()),
                datasets: [{
                    label: 'Doanh thu',
                    data: @json($categoryRevenue->pluck('revenue')->values()),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: context => `${context.label}: ${moneyLabel(context.raw)}` } }
                }
            }
        });

        createChart('newCustomerLineChart', {
            type: 'line',
            data: {
                labels: @json($customerTrend['labels']),
                datasets: [{
                    label: 'Khách hàng mới',
                    data: @json($customerTrend['data']),
                    borderWidth: 3,
                    pointRadius: 4,
                    tension: .35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: context => `${numberLabel(context.raw)} khách mới` } }
                },
                scales: { y: { beginAtZero: true, precision: 0 } }
            }
        });
    });
</script>
@endsection
