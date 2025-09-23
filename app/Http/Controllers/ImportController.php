<?php

namespace App\Http\Controllers;

use App\Services\CsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function __construct()
    {
        // Add permissions for import functionality
        $this->middleware(['permission:product-create'], ['only' => ['importProducts']]);
        $this->middleware(['permission:category-create'], ['only' => ['importCategories']]);
        $this->middleware(['permission:unit-create'], ['only' => ['importUnits']]);
    }

    /**
     * Show import page
     */
    public function index()
    {
        return view('imports.index');
    }

    /**
     * Import products from CSV file
     */
    public function importProducts(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv|max:10240', // Max 10MB, CSV only
        ]);

        try {
            $csvService = new CsvImportService();
            
            // Store uploaded file temporarily
            $file = $request->file('file');
            $filePath = $file->store('temp');
            $fullPath = storage_path('app/' . $filePath);
            
            // Import products
            $results = $csvService->importProducts($fullPath);
            
            // Clean up temporary file
            Storage::delete($filePath);
            
            $message = "تم استيراد {$results['success_count']} منتج بنجاح";
            if ($results['error_count'] > 0) {
                $message .= " مع {$results['error_count']} أخطاء";
                
                // Show first few errors to user
                if (!empty($results['errors'])) {
                    $message .= "\nأول الأخطاء: " . implode(', ', array_slice($results['errors'], 0, 3));
                }
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Product import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء استيراد المنتجات: ' . $e->getMessage());
        }
    }

    /**
     * Import units from CSV file
     */
    public function importUnits(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv|max:2048', // Max 2MB
        ]);

        try {
            $csvService = new CsvImportService();
            
            $file = $request->file('file');
            $filePath = $file->store('temp');
            $fullPath = storage_path('app/' . $filePath);
            
            $results = $csvService->importUnits($fullPath);
            
            Storage::delete($filePath);
            
            $message = "تم استيراد {$results['success_count']} وحدة بنجاح";
            if ($results['error_count'] > 0) {
                $message .= " مع {$results['error_count']} أخطاء";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Units import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء استيراد الوحدات: ' . $e->getMessage());
        }
    }

    /**
     * Import categories from CSV file
     */
    public function importCategories(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv|max:2048', // Max 2MB
        ]);

        try {
            $csvService = new CsvImportService();
            
            $file = $request->file('file');
            $filePath = $file->store('temp');
            $fullPath = storage_path('app/' . $filePath);
            
            $results = $csvService->importCategories($fullPath);
            
            Storage::delete($filePath);

            $message = "تم استيراد {$results['success_count']} قسم بنجاح";
            if ($results['error_count'] > 0) {
                $message .= " مع {$results['error_count']} أخطاء";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Categories import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء استيراد الأقسام: ' . $e->getMessage());
        }
    }

    /**
     * Import sub-categories from Excel file
     */
    public function importSubCategories(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048', // Max 2MB
        ]);

        try {
            $import = new SubCategoriesImport();
            Excel::import($import, $request->file('file'));

            $successCount = count($import->getDelegate()->toArray($request->file('file'))[0] ?? []);
            $errorCount = count($import->failures());

            $message = "تم استيراد {$successCount} قسم فرعي بنجاح";
            if ($errorCount > 0) {
                $message .= " مع {$errorCount} أخطاء";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Sub-categories import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء استيراد الأقسام الفرعية: ' . $e->getMessage());
        }
    }

    /**
     * Download sample CSV templates
     */
    public function downloadTemplate($type)
    {
        $templates = [
            'products' => [
                'filename' => 'products_template.csv',
                'headers' => ['name', 'code', 'category_name', 'sub_category_name', 'unit_name', 'stock', 'price', 'min_stock', 'max_stock', 'description', 'is_active'],
                'sample' => ['لابتوب ديل', 'DELL001', 'إلكترونيات', 'أجهزة كمبيوتر', 'قطعة', '10', '15000', '5', '50', 'لابتوب ديل انسبايرون', '1']
            ],
            'units' => [
                'filename' => 'units_template.csv',
                'headers' => ['name', 'symbol', 'description'],
                'sample' => ['قطعة', 'قطعة', 'وحدة العد']
            ],
            'categories' => [
                'filename' => 'categories_template.csv',
                'headers' => ['name', 'description', 'is_active'],
                'sample' => ['إلكترونيات', 'الأجهزة الإلكترونية', '1']
            ],
            'sub_categories' => [
                'filename' => 'sub_categories_template.csv',
                'headers' => ['name', 'category_name', 'description', 'is_active'],
                'sample' => ['أجهزة كمبيوتر', 'إلكترونيات', 'أجهزة الكمبيوتر المحمولة والمكتبية', '1']
            ]
        ];

        if (!isset($templates[$type])) {
            return redirect()->back()->with('error', 'نوع القالب غير صحيح');
        }

        $template = $templates[$type];
        
        // Create CSV content
        $csvContent = implode(',', $template['headers']) . "\n";
        if (isset($template['sample'])) {
            $csvContent .= implode(',', $template['sample']) . "\n";
        }
        
        // Return CSV download
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $template['filename'] . '"');
    }
}
