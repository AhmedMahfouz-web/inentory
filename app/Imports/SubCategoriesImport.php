<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\SubCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class SubCategoriesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find category by name
        $category = null;
        if (!empty($row['category_name'])) {
            $category = Category::where('name', trim($row['category_name']))->first();
            
            // If category doesn't exist, create it
            if (!$category) {
                $category = Category::create([
                    'name' => trim($row['category_name']),
                    'is_active' => true
                ]);
            }
        }

        return SubCategory::firstOrCreate(
            [
                'name' => trim($row['name']),
                'category_id' => $category ? $category->id : null
            ],
            [
                'name' => trim($row['name']),
                'category_id' => $category ? $category->id : null,
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
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'name.required' => 'اسم القسم الفرعي مطلوب',
            'category_name.required' => 'اسم القسم الرئيسي مطلوب',
        ];
    }
}
