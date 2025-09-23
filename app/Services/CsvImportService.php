<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Unit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CsvImportService
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;

    /**
     * Import products from CSV file
     */
    public function importProducts($filePath)
    {
        $this->resetCounters();
        
        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Cannot open file');
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception('Invalid CSV format - no headers found');
        }

        // Convert headers to lowercase and trim
        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $headers);

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            if (count($row) !== count($headers)) {
                $this->addError($rowNumber, 'Column count mismatch');
                continue;
            }

            // Combine headers with row data
            $data = array_combine($headers, $row);
            
            try {
                $this->processProductRow($data, $rowNumber);
                $this->successCount++;
            } catch (\Exception $e) {
                $this->addError($rowNumber, $e->getMessage());
            }
        }

        fclose($handle);
        return $this->getResults();
    }

    /**
     * Import categories from CSV file
     */
    public function importCategories($filePath)
    {
        $this->resetCounters();
        
        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Cannot open file');
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception('Invalid CSV format - no headers found');
        }

        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $headers);

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            if (count($row) !== count($headers)) {
                $this->addError($rowNumber, 'Column count mismatch');
                continue;
            }

            $data = array_combine($headers, $row);
            
            try {
                $this->processCategoryRow($data, $rowNumber);
                $this->successCount++;
            } catch (\Exception $e) {
                $this->addError($rowNumber, $e->getMessage());
            }
        }

        fclose($handle);
        return $this->getResults();
    }

    /**
     * Import units from CSV file
     */
    public function importUnits($filePath)
    {
        $this->resetCounters();
        
        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Cannot open file');
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception('Invalid CSV format - no headers found');
        }

        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $headers);

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            if (count($row) !== count($headers)) {
                $this->addError($rowNumber, 'Column count mismatch');
                continue;
            }

            $data = array_combine($headers, $row);
            
            try {
                $this->processUnitRow($data, $rowNumber);
                $this->successCount++;
            } catch (\Exception $e) {
                $this->addError($rowNumber, $e->getMessage());
            }
        }

        fclose($handle);
        return $this->getResults();
    }

    /**
     * Process a single product row
     */
    protected function processProductRow($data, $rowNumber)
    {
        // Validate required fields
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        // Find or create category by name
        $category = null;
        if (!empty($data['category_name'])) {
            $category = Category::firstOrCreate(
                ['name' => trim($data['category_name'])],
                ['name' => trim($data['category_name'])]
            );
        }

        // Find or create sub-category by name
        $subCategory = null;
        if (!empty($data['sub_category_name'])) {
            $subCategory = SubCategory::firstOrCreate(
                [
                    'name' => trim($data['sub_category_name']),
                    'category_id' => $category ? $category->id : null
                ],
                [
                    'name' => trim($data['sub_category_name']),
                    'category_id' => $category ? $category->id : null
                ]
            );
        }

        // Find or create unit by name
        $unit = null;
        if (!empty($data['unit_name'])) {
            $unit = Unit::firstOrCreate(
                ['name' => trim($data['unit_name'])],
                ['name' => trim($data['unit_name'])]
            );
        }

        // Create or update product
        Product::updateOrCreate(
            ['code' => $data['code']],
            [
                'name' => $data['name'],
                'code' => $data['code'],
                'category_id' => $category ? $category->id : null,
                'sub_category_id' => $subCategory ? $subCategory->id : null,
                'unit_id' => $unit ? $unit->id : null,
                'stock' => is_numeric($data['stock'] ?? 0) ? (float)$data['stock'] : 0,
                'price' => is_numeric($data['price'] ?? 0) ? (float)$data['price'] : 0,
                'min_stock' => is_numeric($data['min_stock'] ?? null) ? (float)$data['min_stock'] : null,
                'max_stock' => is_numeric($data['max_stock'] ?? null) ? (float)$data['max_stock'] : null,
                'description' => $data['description'] ?? null,
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
            ]
        );
    }

    /**
     * Process a single category row
     */
    protected function processCategoryRow($data, $rowNumber)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        Category::firstOrCreate(
            ['name' => trim($data['name'])],
            [
                'name' => trim($data['name']),
                'description' => $data['description'] ?? null,
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
            ]
        );
    }

    /**
     * Process a single unit row
     */
    protected function processUnitRow($data, $rowNumber)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        Unit::firstOrCreate(
            ['name' => trim($data['name'])],
            [
                'name' => trim($data['name']),
                'symbol' => $data['symbol'] ?? null,
                'description' => $data['description'] ?? null,
            ]
        );
    }

    /**
     * Reset counters
     */
    protected function resetCounters()
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->errorCount = 0;
    }

    /**
     * Add error
     */
    protected function addError($rowNumber, $message)
    {
        $this->errors[] = "Row {$rowNumber}: {$message}";
        $this->errorCount++;
        Log::warning("Import error on row {$rowNumber}: {$message}");
    }

    /**
     * Get import results
     */
    protected function getResults()
    {
        return [
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors
        ];
    }
}
