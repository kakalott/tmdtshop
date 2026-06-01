<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private int $lowStockThreshold = 5;

    public function index(Request $request)
    {
        [$time, $startDate, $endDate] = $this->resolveDateRange($request);
        [$previousStartDate, $previousEndDate] = $this->resolvePreviousDateRange($startDate, $endDate);

        $ordersInRange = Order::query()->whereBetween('created_at', [$startDate, $endDate]);
        $completedOrdersInRange = (clone $ordersInRange)->where('status', 'completed');

        $previousOrders = Order::query()->whereBetween('created_at', [$previousStartDate, $previousEndDate]);
        $previousCompletedOrders = (clone $previousOrders)->where('status', 'completed');

        $totalRevenue = (clone $completedOrdersInRange)->sum('total_amount');
        $previousRevenue = (clone $previousCompletedOrders)->sum('total_amount');
        $revenueGrowth = $this->percentChange($totalRevenue, $previousRevenue);

        $totalOrders = (clone $ordersInRange)->count();
        $previousTotalOrders = (clone $previousOrders)->count();
        $orderGrowth = $this->percentChange($totalOrders, $previousTotalOrders);

        $completedOrders = (clone $ordersInRange)->where('status', 'completed')->count();
        $cancelledOrders = (clone $ordersInRange)->whereIn('status', ['cancelled', 'canceled'])->count();
        $successRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;
        $cancelRate = $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 1) : 0;

        $activeCustomersCount = (clone $ordersInRange)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $previousActiveCustomers = (clone $previousOrders)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $customerGrowth = $this->percentChange($activeCustomersCount, $previousActiveCustomers);

        $newCustomers = User::query()
            ->where('role', 'customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalProducts = Product::count();
        $activeProducts = Product::where('stock_quantity', '>', 0)->count();

        $revenueTrend = $this->buildTimeSeries(
            (clone $completedOrdersInRange)->get(['total_amount', 'created_at']),
            $startDate,
            $endDate,
            'sum',
            'total_amount'
        );

        $customerTrend = $this->buildTimeSeries(
            User::query()
                ->where('role', 'customer')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['id', 'created_at']),
            $startDate,
            $endDate,
            'count'
        );

        $bestSellingProducts = OrderDetail::query()
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(quantity * price) as total_revenue')
            )
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with(['product.variants'])
            ->take(10)
            ->get();

        $statusCounts = (clone $ordersInRange)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $orderStatusStats = collect([
            ['label' => 'Chờ xác nhận', 'keys' => ['pending']],
            ['label' => 'Đang xử lý', 'keys' => ['processing']],
            ['label' => 'Đang giao hàng', 'keys' => ['shipping', 'shipped', 'delivering']],
            ['label' => 'Hoàn thành', 'keys' => ['completed']],
            ['label' => 'Đã hủy', 'keys' => ['cancelled', 'canceled']],
        ])->map(function ($item) use ($statusCounts) {
            $item['total'] = collect($item['keys'])->sum(fn ($key) => (int) ($statusCounts[$key] ?? 0));
            unset($item['keys']);
            return $item;
        });

        $topCustomers = (clone $completedOrdersInRange)
            ->select(
                'user_id',
                DB::raw('MAX(customer_name) as customer_name'),
                DB::raw('COUNT(id) as order_count'),
                DB::raw('SUM(total_amount) as total_spent')
            )
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->with('user')
            ->take(10)
            ->get();

        $categoryRevenue = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw("COALESCE(categories.name, 'Chưa phân loại') as category_name")
            ->selectRaw('SUM(order_details.quantity * order_details.price) as revenue')
            ->groupByRaw("COALESCE(categories.name, 'Chưa phân loại')")
            ->orderByDesc('revenue')
            ->take(10)
            ->get();

        $lowStockProducts = Product::query()
            ->with('variants')
            ->where('stock_quantity', '<=', $this->lowStockThreshold)
            ->orderBy('stock_quantity')
            ->take(10)
            ->get();

        $voucherStats = DB::table('voucher_usages')
            ->join('vouchers', 'vouchers.id', '=', 'voucher_usages.voucher_id')
            ->leftJoin('orders', 'orders.id', '=', 'voucher_usages.order_id')
            ->whereBetween('voucher_usages.created_at', [$startDate, $endDate])
            ->selectRaw('vouchers.code as code')
            ->selectRaw('COUNT(voucher_usages.id) as used_count')
            ->selectRaw("SUM(CASE WHEN orders.status = 'completed' THEN orders.total_amount ELSE 0 END) as revenue_generated")
            ->selectRaw('SUM(voucher_usages.discount_amount) as discount_total')
            ->groupBy('vouchers.id', 'vouchers.code')
            ->orderByDesc('used_count')
            ->take(10)
            ->get();

        $bannerStats = $this->buildBannerStats();

        $aiInsights = $this->buildAiInsights(
            $revenueGrowth,
            $bestSellingProducts,
            $lowStockProducts,
            $categoryRevenue,
            $newCustomers,
            $cancelRate
        );

        return view('admin.dashboard.index', compact(
            'time',
            'startDate',
            'endDate',
            'totalRevenue',
            'revenueGrowth',
            'totalOrders',
            'orderGrowth',
            'completedOrders',
            'cancelledOrders',
            'successRate',
            'cancelRate',
            'activeCustomersCount',
            'customerGrowth',
            'newCustomers',
            'totalProducts',
            'activeProducts',
            'revenueTrend',
            'bestSellingProducts',
            'orderStatusStats',
            'topCustomers',
            'customerTrend',
            'categoryRevenue',
            'lowStockProducts',
            'voucherStats',
            'bannerStats',
            'aiInsights'
        ));
    }

    private function resolveDateRange(Request $request): array
    {
        $time = $request->get('time', 'last30');
        $now = now();

        if ($time === 'today') {
            return [$time, $now->copy()->startOfDay(), $now->copy()->endOfDay()];
        }

        if ($time === 'last7') {
            return [$time, $now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];
        }

        if ($time === 'last90') {
            return [$time, $now->copy()->subMonths(3)->startOfDay(), $now->copy()->endOfDay()];
        }

        if ($time === 'year') {
            return [$time, $now->copy()->subYear()->addDay()->startOfDay(), $now->copy()->endOfDay()];
        }

        if ($time === 'custom') {
            $from = $request->filled('date_from')
                ? Carbon::parse($request->date_from)->startOfDay()
                : $now->copy()->subDays(29)->startOfDay();

            $to = $request->filled('date_to')
                ? Carbon::parse($request->date_to)->endOfDay()
                : $now->copy()->endOfDay();

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$time, $from, $to];
        }

        return ['last30', $now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()];
    }

    private function resolvePreviousDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $days = (int) $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1;

        return [
            $startDate->copy()->subDays($days),
            $startDate->copy()->subSecond(),
        ];
    }

    private function percentChange(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function buildTimeSeries(Collection $records, Carbon $startDate, Carbon $endDate, string $type = 'sum', ?string $field = null): array
    {
        $days = (int) $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1;
        $mode = $days <= 31 ? 'day' : ($days <= 120 ? 'week' : 'month');

        $slots = [];
        $cursor = match ($mode) {
            'week' => $startDate->copy()->startOfWeek(),
            'month' => $startDate->copy()->startOfMonth(),
            default => $startDate->copy()->startOfDay(),
        };

        while ($cursor->lte($endDate)) {
            $key = $this->slotKey($cursor, $mode);
            $slots[$key] = [
                'label' => $this->slotLabel($cursor, $mode),
                'value' => 0,
            ];

            match ($mode) {
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonth(),
                default => $cursor->addDay(),
            };
        }

        foreach ($records as $record) {
            $createdAt = Carbon::parse($record->created_at);
            $key = $this->slotKey($createdAt, $mode);

            if (! isset($slots[$key])) {
                continue;
            }

            $slots[$key]['value'] += $type === 'count' ? 1 : (float) ($record->{$field} ?? 0);
        }

        $rows = array_values($slots);

        return [
            'mode' => $mode,
            'labels' => array_column($rows, 'label'),
            'data' => array_column($rows, 'value'),
            'rows' => $rows,
        ];
    }

    private function slotKey(Carbon $date, string $mode): string
    {
        return match ($mode) {
            'week' => $date->copy()->startOfWeek()->format('Y-m-d'),
            'month' => $date->copy()->startOfMonth()->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    private function slotLabel(Carbon $date, string $mode): string
    {
        return match ($mode) {
            'week' => 'Tuần ' . $date->copy()->startOfWeek()->format('d/m'),
            'month' => $date->format('m/Y'),
            default => $date->format('d/m'),
        };
    }

    private function buildBannerStats(): Collection
    {
        $viewColumn = Schema::hasColumn('banners', 'view_count') ? 'view_count' : (Schema::hasColumn('banners', 'views') ? 'views' : null);
        $clickColumn = Schema::hasColumn('banners', 'click_count') ? 'click_count' : (Schema::hasColumn('banners', 'clicks') ? 'clicks' : null);

        return Banner::query()
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->take(10)
            ->get()
            ->map(fn (Banner $banner) => [
                'title' => $banner->title ?: 'Banner #' . $banner->id,
                'is_active' => (bool) $banner->is_active,
                'views' => $viewColumn ? (int) $banner->{$viewColumn} : null,
                'clicks' => $clickColumn ? (int) $banner->{$clickColumn} : null,
            ]);
    }

    private function buildAiInsights(
        float $revenueGrowth,
        Collection $bestSellingProducts,
        Collection $lowStockProducts,
        Collection $categoryRevenue,
        int $newCustomers,
        float $cancelRate
    ): array {
        $insights = [];

        if ($revenueGrowth > 0) {
            $insights[] = 'Doanh thu đang tăng ' . $revenueGrowth . '% so với kỳ liền trước.';
        } elseif ($revenueGrowth < 0) {
            $insights[] = 'Doanh thu đang giảm ' . abs($revenueGrowth) . '% so với kỳ liền trước, nên kiểm tra lại khuyến mãi và nguồn đơn.';
        } else {
            $insights[] = 'Doanh thu chưa thay đổi đáng kể so với kỳ liền trước.';
        }

        $topProduct = $bestSellingProducts->first();
        if ($topProduct) {
            $insights[] = ($topProduct->product->name ?? 'Một sản phẩm') . ' là sản phẩm bán chạy nhất với ' . (int) $topProduct->total_sold . ' sản phẩm đã bán.';
        }

        $lowStockProduct = $lowStockProducts->first();
        if ($lowStockProduct) {
            $insights[] = $lowStockProduct->name . ' có nguy cơ hết hàng, tồn kho hiện còn ' . (int) $lowStockProduct->stock_quantity . ' sản phẩm.';
        }

        $topCategory = $categoryRevenue->first();
        if ($topCategory) {
            $insights[] = 'Danh mục ' . $topCategory->category_name . ' đang đóng góp doanh thu cao nhất.';
        }

        if ($newCustomers > 0) {
            $insights[] = 'Có ' . $newCustomers . ' khách hàng mới trong khoảng thời gian đang xem.';
        }

        if ($cancelRate >= 20) {
            $insights[] = 'Tỷ lệ hủy đơn đang ở mức ' . $cancelRate . '%, cần kiểm tra tồn kho, vận chuyển hoặc quy trình xác nhận đơn.';
        }

        return $insights;
    }
}
