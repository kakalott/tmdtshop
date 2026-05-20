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
                $categoryId = null;
                if (!empty($data['category'])) {
                    $cat = Category::firstOrCreate(
                        ['name' => $data['category']],
                        ['slug' => Str::slug($data['category'])]
                    );
                    $categoryId = $cat->id;
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
                    'category_id' => $categoryId,
                    'description' => $data['description'] ?? null,
                ];

                Product::create($productData);
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($i+1) . ": " . $e->getMessage();
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }
}
