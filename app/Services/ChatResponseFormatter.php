<?php

namespace App\Services;

use App\Models\Product;

class ChatResponseFormatter
{
    public function format(string $text): string
    {
        $text = $this->normalizeInternalAppLinks($text);
        $text = $this->repairBrokenLinks($text);
        $text = $this->normalizeAllProductLinks($text);
        $text = $this->pathsToMarkdownLinks($text);

        return $this->enrichMentionedProducts($text);
    }

    private function normalizeInternalAppLinks(string $text): string
    {
        return preg_replace(
            '#https?://(?:localhost|127\.0\.0\.1)(?::\d+)?(/[^)\s]*)#u',
            '$1',
            $text
        );
    }

    private function repairBrokenLinks(string $text): string
    {
        $text = preg_replace_callback(
            '#\[tại đây\]\([^)]*\[tại đây\]\(([^)]+/product/(\d+))\)[^)]*\)#u',
            fn (array $m) => ProductCatalogHelper::productLink($m[2]),
            $text
        );

        $text = preg_replace_callback(
            '#\[tại đây\]\([^)]*\[tại đây\]\((https?://[^)]+/product/(\d+))\)#u',
            fn (array $m) => ProductCatalogHelper::productLink($m[2]),
            $text
        );

        return preg_replace_callback(
            '#\[tại đây\]\((https?://[^)\[]+)\[tại đây\]\((https?://[^)]+/product/(\d+))\)\)#u',
            fn (array $m) => ProductCatalogHelper::productLink($m[3]),
            $text
        );
    }

    /**
     * Mọi link sản phẩm → đường dẫn tương đối /product/{id} (đúng host user đang mở).
     */
    private function normalizeAllProductLinks(string $text): string
    {
        return preg_replace_callback(
            '#\[tại đây\]\((?:https?://[^)]+)?(/product/\d+)\)#u',
            fn (array $m) => '[tại đây](' . $m[1] . ')',
            $text
        );
    }

    private function pathsToMarkdownLinks(string $text): string
    {
        $stored = [];

        $text = preg_replace_callback(
            '/\[[^\]]*\]\([^)]*\)/u',
            function (array $m) use (&$stored) {
                $key = '___MDLINK_' . count($stored) . '___';
                $stored[$key] = $m[0];

                return $key;
            },
            $text
        );

        $pages = [
            '/profile/orders',
            '/checkout',
            '/cart',
            '/login',
        ];

        foreach ($pages as $path) {
            $text = str_replace($path, '[tại đây](' . $path . ')', $text);
        }

        $text = preg_replace_callback(
            '#/product/(\d+)\b#u',
            fn (array $m) => ProductCatalogHelper::productLink($m[1]),
            $text
        );

        foreach ($stored as $key => $link) {
            $normalized = preg_replace(
                '#\[tại đây\]\((?:https?://[^)]+)?(/(?:profile/orders|checkout|cart|login))\)#u',
                '[tại đây]($1)',
                $link
            );
            $normalized = preg_replace_callback(
                '#\[tại đây\]\((?:https?://[^)]+)?(/product/\d+)\)#u',
                fn (array $m) => '[tại đây](' . $m[1] . ')',
                $normalized
            );
            $text = str_replace($key, $normalized, $text);
        }

        return $text;
    }

    /**
     * Chèn ảnh + tên/giá ngay trước từng dòng sản phẩm (không gom xuống cuối).
     */
    private function enrichMentionedProducts(string $text): string
    {
        $productIds = $this->extractProductIds($text);

        foreach ($productIds as $productId) {
            $product = Product::query()
                ->select(['id', 'name', 'image', 'price', 'sale_price', 'stock_quantity'])
                ->with(['variants' => fn ($q) => $q
                    ->select(['id', 'product_id', 'image'])
                    ->whereNotNull('image')
                    ->where('image', '!=', ''),
                ])
                ->find($productId);

            if (! $product) {
                continue;
            }

            $inlineBlock = $this->buildInlineProductBlock($product);
            $idPattern = preg_quote((string) $productId, '/');

            $linePatterns = [
                '/^(\s*[\*\-]\s*)([^\n]*\[#?' . $idPattern . '\][^\n]*)$/mu',
                '/^(\s*[\*\-]\s*)([^\n]*\[ID\s+' . $idPattern . '\][^\n]*)$/mu',
                '/^(\s*[\*\-]\s*)([^\n]*\/product\/' . $idPattern . '[^\n]*)$/mu',
            ];

            foreach ($linePatterns as $pattern) {
                $replaced = preg_replace($pattern, '$1' . $inlineBlock, $text, 1, $count);
                if ($count > 0) {
                    $text = $replaced;
                    break;
                }
            }
        }

        return $this->stripTrailingProductDump($text);
    }

    private function buildInlineProductBlock(Product $product): string
    {
        $parts = [];
        $imageMd = ProductCatalogHelper::imageMarkdown($product);

        if ($imageMd) {
            $parts[] = $imageMd;
        }

        $stock = (int) $product->stock_quantity;
        $stockText = $stock > 0 ? "còn {$stock} sp" : 'hết hàng';

        $parts[] = '**' . ProductCatalogHelper::escapeMarkdownAlt($product->name) . '**'
            . ' · ' . ProductCatalogHelper::formatPrice($product)
            . ' · ' . $stockText
            . ' · ' . ProductCatalogHelper::productLink($product->id);

        return implode("\n  ", $parts);
    }

    /** Gỡ khối ảnh/link trùng ở cuối tin (format cũ). */
    private function stripTrailingProductDump(string $text): string
    {
        return preg_replace(
            '/\n\n(?:!\[[^\]]*\]\([^)]+\)\n(?:\[tại đây\]\(\/product\/\d+\)\n?)+)+$/u',
            '',
            $text
        );
    }

    private function extractProductIds(string $text): array
    {
        $ids = [];

        if (preg_match_all('#/product/(\d+)#u', $text, $m1)) {
            $ids = array_merge($ids, $m1[1]);
        }

        if (preg_match_all('/\[#(\d+)\]/u', $text, $m2)) {
            $ids = array_merge($ids, $m2[1]);
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    public function pageUrls(): array
    {
        return [
            'Giỏ hàng' => '/cart',
            'Thanh toán' => '/checkout',
            'Đơn hàng của tôi' => '/profile/orders',
            'Đăng nhập' => '/login',
        ];
    }
}
