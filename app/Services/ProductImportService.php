<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductImportService
{
    /**
     * Import products from spreadsheet file path.
     * Returns array with counts and errors.
     */
    public function import(string $filePath): array
    {
        $created = 0;
        $errors = [];

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            return ['created' => 0, 'errors' => ['File has no data rows']];
        }

        // First row is header
        $header = array_map(function ($h) { return strtolower(trim((string)$h)); }, $rows[0]);

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $data = [];
            foreach ($header as $colIndex => $colName) {
                $data[$colName] = isset($row[$colIndex]) ? trim((string)$row[$colIndex]) : null;
            }

            // Required fields: name
            if (empty($data['name'])) {
                $errors[] = "Row " . ($i+1) . ": missing name";
                continue;
            }

            try {
                $categoryIds = collect();
                if (!empty($data['category'])) {
                    $categoryIds = collect(preg_split('/[,;|]+/', $data['category']))
                        ->map(fn ($categoryName) => trim($categoryName))
                        ->filter()
                        ->map(function ($categoryName) {
                            return Category::firstOrCreate(
                                ['name' => $categoryName],
                                ['slug' => Str::slug($categoryName)]
                            )->id;
                        })
                        ->unique()
                        ->values();
                }

                $productData = [
                    'name' => $data['name'],
                    'sku' => $data['sku'] ?? null,
                    'barcode' => $data['barcode'] ?? null,
                    'price' => is_numeric($data['price'] ?? null) ? (float)$data['price'] : 0,
                    'wholesale_price' => is_numeric($data['wholesale_price'] ?? null) ? (float)$data['wholesale_price'] : 0,
                    'stock_quantity' => is_numeric($data['stock_quantity'] ?? null) ? (int)$data['stock_quantity'] : 0,
                    'dimensions' => $data['dimensions'] ?? null,
                    'image' => $data['image'] ?? null,
                    'category_id' => $categoryIds->first(),
                    'description' => $data['description'] ?? null,
                ];

                $product = Product::create($productData);
                $product->categories()->sync($categoryIds);
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($i+1) . ": " . $e->getMessage();
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }
}
