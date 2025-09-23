<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find or create category by name
        $category = null;
        if (!empty($row['category_name'])) {
            $category = Category::firstOrCreate(
                ['name' => trim($row['category_name'])],
                ['name' => trim($row['category_name'])]
            );
        }

        // Find or create sub-category by name (with category relationship)
        $subCategory = null;
        if (!empty($row['sub_category_name'])) {
            $subCategory = SubCategory::firstOrCreate(
                [
                    'name' => trim($row['sub_category_name']),
                    'category_id' => $category ? $category->id : null
                ],
                [
                    'name' => trim($row['sub_category_name']),
                    'category_id' => $category ? $category->id : null
                ]
            );
        }

        // Find or create unit by name
        $unit = null;
        if (!empty($row['unit_name'])) {
            $unit = Unit::firstOrCreate(
                ['name' => trim($row['unit_name'])],
                ['name' => trim($row['unit_name'])]
            );
        }

        // Create or update product
        return Product::updateOrCreate(
            ['code' => $row['code']], // Use code as unique identifier
            [
                'name' => $row['name'],
                'code' => $row['code'],
                'category_id' => $category ? $category->id : null,
                'sub_category_id' => $subCategory ? $subCategory->id : null,
                'unit_id' => $unit ? $unit->id : null,
                'stock' => $row['stock'] ?? 0,
                'price' => $row['price'] ?? 0,
                'min_stock' => $row['min_stock'] ?? null,
                'max_stock' => $row['max_stock'] ?? null,
                'description' => $row['description'] ?? null,
                'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : true,
            ]
        );
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100',
            'category_name' => 'nullable|string|max:255',
            'sub_category_name' => 'nullable|string|max:255',
            'unit_name' => 'nullable|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'name.required' => 'اسم المنتج مطلوب',
            'code.required' => 'كود المنتج مطلوب',
            'stock.numeric' => 'الكمية يجب أن تكون رقم',
            'price.numeric' => 'السعر يجب أن تكون رقم',
        ];
    }

    /**
     * Handle model creation with better error handling
     */
    private function createOrUpdateProduct(array $row)
    {
        try {
            // Validate required fields
            if (empty($row['name']) || empty($row['code'])) {
                Log::warning('Skipping row due to missing required fields', $row);
                return null;
            }

            // Find or create category by name
            $category = null;
            if (!empty($row['category_name'])) {
                $category = Category::firstOrCreate(
                    ['name' => trim($row['category_name'])],
                    ['name' => trim($row['category_name'])]
                );
            }

            // Find or create sub-category by name (with category relationship)
            $subCategory = null;
            if (!empty($row['sub_category_name'])) {
                $subCategory = SubCategory::firstOrCreate(
                    [
                        'name' => trim($row['sub_category_name']),
                        'category_id' => $category ? $category->id : null
                    ],
                    [
                        'name' => trim($row['sub_category_name']),
                        'category_id' => $category ? $category->id : null
                    ]
                );
            }

            // Find or create unit by name
            $unit = null;
            if (!empty($row['unit_name'])) {
                $unit = Unit::firstOrCreate(
                    ['name' => trim($row['unit_name'])],
                    ['name' => trim($row['unit_name'])]
                );
            }

            // Create or update product
            return Product::updateOrCreate(
                ['code' => $row['code']], // Use code as unique identifier
                [
                    'name' => $row['name'],
                    'code' => $row['code'],
                    'category_id' => $category ? $category->id : null,
                    'sub_category_id' => $subCategory ? $subCategory->id : null,
                    'unit_id' => $unit ? $unit->id : null,
                    'stock' => is_numeric($row['stock'] ?? 0) ? (float)$row['stock'] : 0,
                    'price' => is_numeric($row['price'] ?? 0) ? (float)$row['price'] : 0,
                    'min_stock' => is_numeric($row['min_stock'] ?? null) ? (float)$row['min_stock'] : null,
                    'max_stock' => is_numeric($row['max_stock'] ?? null) ? (float)$row['max_stock'] : null,
                    'description' => $row['description'] ?? null,
                    'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : true,
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error creating/updating product', [
                'row' => $row,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
