<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class ShopContextService
{
    public function __construct(
        private ChatResponseFormatter $formatter
    ) {}

    public function build(?User $user): string
    {
        $sections = [
            $this->shopInfoSection(),
            $this->linksSection(),
            $this->categoriesSection(),
            $this->productsSection(),
        ];

        if ($user) {
            $sections[] = $this->customerSection($user);
            $sections[] = $this->ordersSection($user);
        }

        return implode("\n\n", array_filter($sections));
    }

    private function shopInfoSection(): string
    {
        $policies = collect(config('chatbot.policies', []))
            ->map(fn ($line) => "- {$line}")
            ->implode("\n");

        return "=== THÔNG TIN CỬA HÀNG ===\n"
            . 'Tên: ' . config('chatbot.shop_name') . "\n"
            . "Chính sách:\n{$policies}";
    }

    private function linksSection(): string
    {
        $lines = collect($this->formatter->pageUrls())
            ->map(fn ($url, $label) => "- {$label}: {$url}")
            ->implode("\n");

        return "=== LIÊN KẾT TRANG (dùng trong [tại đây](url), không ghi /cart...) ===\n{$lines}";
    }

    private function categoriesSection(): string
    {
        $categories = Category::query()->orderBy('name')->pluck('name');

        if ($categories->isEmpty()) {
            return '';
        }

        return "=== DANH MỤC ===\n" . $categories->implode(', ');
    }

    private function productsSection(): string
    {
        $limit = config('chatbot.max_products_in_context', 20);

        $products = Product::query()
            ->with([
                'category:id,name',
                'categories:id,name',
                'variants' => fn ($q) => $q
                    ->select(['id', 'product_id', 'image'])
                    ->whereNotNull('image')
                    ->where('image', '!=', ''),
            ])
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'name', 'price', 'sale_price', 'wholesale_price', 'stock_quantity', 'category_id', 'description', 'image']);

        if ($products->isEmpty()) {
            return "=== SẢN PHẨM ===\n(Chưa có sản phẩm trong hệ thống)";
        }

        $lines = $products->map(function (Product $product) {
            $price = $product->sale_price ?? $product->price;
            $category = $product->categories->pluck('name')->filter()->implode(', ') ?: ($product->category?->name ?? 'Khác');
            $stock = (int) $product->stock_quantity;
            $stockText = $stock > 0 ? "còn {$stock}" : 'hết hàng';
            $wholesale = $product->wholesale_price > 0
                ? ' | Giá sỉ (≥10): ' . $this->formatMoney($product->wholesale_price)
                : '';

            $desc = $product->description
                ? ' | ' . mb_substr(strip_tags($product->description), 0, 80)
                : '';

            $pagePath = ProductCatalogHelper::productPath($product->id);
            $imageUrl = ProductCatalogHelper::displayImageUrl($product);

            return sprintf(
                '- [ID %d] %s | %s | %s | %s%s | trang: %s | anh: %s%s',
                $product->id,
                $product->name,
                $category,
                $this->formatMoney($price),
                $stockText,
                $wholesale,
                $pagePath,
                $imageUrl ?: '(không có ảnh)',
                $desc
            );
        });

        return "=== SẢN PHẨM (mới nhất, tối đa {$limit}) ===\n" . $lines->implode("\n");
    }

    private function customerSection(User $user): string
    {
        return "=== KHÁCH ĐANG CHAT ===\n"
            . "Tên: {$user->name}\n"
            . 'Email: ' . ($user->email ?? '(chưa có)') . "\n"
            . 'SĐT: ' . ($user->phone ?? '(chưa cập nhật)') . "\n"
            . 'Địa chỉ: ' . ($user->address ?? '(chưa cập nhật)');
    }

    private function ordersSection(User $user): string
    {
        $limit = config('chatbot.max_orders_in_context', 5);
        $labels = config('chatbot.order_status_labels', []);

        $orders = Order::with(['details.product:id,name'])
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            return "=== ĐƠN HÀNG CỦA KHÁCH ===\n(Chưa có đơn nào)";
        }

        $lines = $orders->map(function (Order $order) use ($labels) {
            $status = $labels[$order->status] ?? $order->status;
            $items = $order->details->map(function ($detail) {
                $name = $detail->product?->name ?? 'Sản phẩm';
                return "{$name} x{$detail->quantity}";
            })->implode(', ');

            return sprintf(
                '- Đơn #%d | %s | %s | Tổng: %s | %s',
                $order->id,
                $status,
                $order->created_at?->format('d/m/Y H:i') ?? '',
                $this->formatMoney($order->total_amount),
                $items ?: '(không có chi tiết)'
            );
        });

        return "=== ĐƠN HÀNG GẦN ĐÂY (tối đa {$limit}) ===\n" . $lines->implode("\n");
    }

    private function formatMoney($amount): string
    {
        return number_format((float) $amount, 0, ',', '.') . ' đ';
    }

}
