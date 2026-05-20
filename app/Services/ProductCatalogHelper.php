<?php

namespace App\Services;

use App\Models\Product;

class ProductCatalogHelper
{
    public static function productPath(int|string $id): string
    {
        return '/product/' . $id;
    }

    public static function productLink(int|string $id): string
    {
        return '[tại đây](' . self::productPath($id) . ')';
    }

    public static function displayImageUrl(Product $product): ?string
    {
        $image = self::normalizeImageUrl($product->image);

        if ($image) {
            return $image;
        }

        if (! $product->relationLoaded('variants')) {
            $product->load(['variants' => fn ($q) => $q
                ->select(['id', 'product_id', 'image'])
                ->whereNotNull('image')
                ->where('image', '!=', ''),
            ]);
        }

        foreach ($product->variants as $variant) {
            $image = self::normalizeImageUrl($variant->image);
            if ($image) {
                return $image;
            }
        }

        return null;
    }

    public static function imageMarkdown(Product $product): ?string
    {
        $url = self::displayImageUrl($product);

        if (! $url) {
            return null;
        }

        $name = self::escapeMarkdownAlt($product->name);

        return '![' . $name . '](' . $url . ')';
    }

    public static function formatPrice(Product $product): string
    {
        $amount = $product->sale_price ?? $product->price;

        return number_format((float) $amount, 0, ',', '.') . ' đ';
    }

    public static function escapeMarkdownAlt(string $text): string
    {
        return str_replace(['[', ']', '(', ')'], '', $text);
    }

    public static function normalizeImageUrl(?string $image): ?string
    {
        $image = trim((string) $image);

        if ($image === '') {
            return null;
        }

        if (str_starts_with($image, '//')) {
            $image = 'https:' . $image;
        }

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        return null;
    }
}
