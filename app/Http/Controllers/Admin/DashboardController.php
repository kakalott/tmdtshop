<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Nhận bộ lọc thời gian
        $time = $request->get('time', 'all'); 
        $orderQuery = Order::query();

        // 2. Lọc mốc thời gian (Dùng now() để luôn lấy giờ thực tế chính xác)
        if ($time == 'day') {
            $orderQuery->whereDate('created_at', now()->toDateString());
        } elseif ($time == 'week') {
            $orderQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($time == 'month') {
            $orderQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }

        // 3. TÍNH DOANH THU & TỈ LỆ ĐƠN
        $totalRevenue = (clone $orderQuery)->where('status', 'completed')->sum('total_amount');
        
        $totalOrders = (clone $orderQuery)->count();
        $completedOrders = (clone $orderQuery)->where('status', 'completed')->count();
        $cancelledOrders = (clone $orderQuery)->where('status', 'cancelled')->count();
        
        $successRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;
        $cancelRate = $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 1) : 0;

        // 4. KHÁCH HÀNG
        $activeCustomersCount = (clone $orderQuery)->distinct('user_id')->count('user_id');

        $topBuyers = (clone $orderQuery)->where('status', 'completed')
            ->select('user_id', \Illuminate\Support\Facades\DB::raw('SUM(total_amount) as total_spent'), \Illuminate\Support\Facades\DB::raw('COUNT(id) as order_count'))
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->take(5)
            ->with('user')
            ->get();

        // 5. SẢN PHẨM BÁN CHẠY NHẤT
        $bestSellingProducts = OrderDetail::whereHas('order', function($q) use ($time) {
                $q->where('status', 'completed');
                if ($time == 'day') $q->whereDate('created_at', now()->toDateString());
                elseif ($time == 'week') $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                elseif ($time == 'month') $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            })
            ->select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->with('product')
            ->get();

        return view('admin.dashboard.index', compact(
            'time', 'totalRevenue', 'totalOrders', 'completedOrders', 'cancelledOrders', 
            'successRate', 'cancelRate', 'activeCustomersCount', 'topBuyers', 'bestSellingProducts'
        ));
    }
}